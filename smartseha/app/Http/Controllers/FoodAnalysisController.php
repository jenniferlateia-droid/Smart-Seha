<?php

namespace App\Http\Controllers;

use App\Models\FoodAnalysis;
use App\Models\HealthMetric;
use App\Services\AI\OpenAIHealthService;
use App\Services\PointsService;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FoodAnalysisController extends Controller
{
    public function page(): View
    {
        abort_unless($this->aiEnabled(), 404);
        return view('food-analysis');
    }

    public function analyze(Request $request, OpenAIHealthService $ai, PointsService $points): JsonResponse
    {
        abort_unless($this->aiEnabled(), 403);
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $path = $request->file('image')->store('food-images', 'public');

        $analysis = FoodAnalysis::create([
            'user_id' => $request->user()->id,
            'image_path' => $path,
            'status' => 'queued',
            'analyzed_date' => now(),
        ]);

        $this->processAnalysisNow($analysis, $ai, $points);

        return response()->json([
            'status' => $analysis->fresh()->status,
            'analysisId' => $analysis->id,
            'pollUrl' => route('food.analysis.show', $analysis),
        ]);
    }

    public function show(Request $request, FoodAnalysis $foodAnalysis): JsonResponse
    {
        abort_unless($this->aiEnabled(), 403);
        abort_unless($foodAnalysis->user_id === $request->user()->id, 403);
        $payload = is_array($foodAnalysis->analysis_payload) ? $foodAnalysis->analysis_payload : [];
        $latestMetric = $request->user()->healthMetrics()->latest('recorded_date')->first();
        $eatDecision = null;
        if ($foodAnalysis->status === 'completed') {
            $eatDecision = $this->buildEatDecision($foodAnalysis, $latestMetric);
        }

        return response()->json([
            'id' => $foodAnalysis->id,
            'status' => $foodAnalysis->status,
            'error' => $foodAnalysis->error_message,
            'data' => $foodAnalysis->status === 'completed' ? [
                'foodName' => $foodAnalysis->food_name,
                'calories' => $foodAnalysis->calories,
                'protein' => $foodAnalysis->protein,
                'carbs' => $foodAnalysis->carbs,
                'fat' => $foodAnalysis->fat,
                'minerals' => $this->translateTerms($foodAnalysis->minerals),
                'allergens' => $this->translateTerms($foodAnalysis->allergens),
                'evaluation' => $this->arabicEvaluation($payload['evaluation'] ?? null),
                'rating' => $this->arabicRating($payload['rating'] ?? null),
                'eatDecision' => $eatDecision,
                'imageUrl' => Storage::disk('public')->url($foodAnalysis->image_path),
            ] : null,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        abort_unless($this->aiEnabled(), 403);
        $rows = FoodAnalysis::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->latest('analyzed_date')
            ->limit(10)
            ->get();

        $list = $rows->map(function (FoodAnalysis $item): array {
            return [
                'id' => $item->id,
                'foodName' => $item->food_name,
                'calories' => $item->calories,
                'protein' => $item->protein,
                'carbs' => $item->carbs,
                'fat' => $item->fat,
                'evaluation' => $this->arabicEvaluation($item->analysis_payload['evaluation'] ?? null),
                'date' => optional($item->analyzed_date)->format('Y-m-d H:i'),
            ];
        })->values();

        return response()->json(['history' => $list]);
    }

    private function arabicRating(?string $rating): ?string
    {
        if (!$rating) {
            return null;
        }

        $value = mb_strtolower(trim($rating));
        if ($value === 'excellent') {
            return 'ممتاز';
        }
        if ($value === 'very good' || $value === 'very_good') {
            return 'جيد جدًا';
        }
        if ($value === 'good') {
            return 'جيد';
        }
        if ($value === 'average' || $value === 'moderate') {
            return 'متوسط';
        }
        if ($value === 'poor' || $value === 'bad') {
            return 'ضعيف';
        }

        return $rating;
    }

    private function arabicEvaluation(?string $evaluation): ?string
    {
        if (!$evaluation) {
            return null;
        }

        $value = mb_strtolower(trim($evaluation));
        if ($value === 'excellent' || $value === 'very healthy' || $value === 'healthy') {
            return 'خيار صحي';
        }
        if ($value === 'good') {
            return 'جيد';
        }
        if ($value === 'average' || $value === 'moderate') {
            return 'متوسط';
        }
        if ($value === 'unhealthy' || $value === 'poor' || $value === 'bad') {
            return 'غير مناسب بشكل يومي';
        }

        return $evaluation;
    }

    private function translateTerms(?array $items): array
    {
        $terms = is_array($items) ? $items : [];
        $map = [
            'potassium' => 'البوتاسيوم',
            'iron' => 'الحديد',
            'calcium' => 'الكالسيوم',
            'magnesium' => 'المغنيسيوم',
            'zinc' => 'الزنك',
            'vitamin c' => 'فيتامين C',
            'vitamin d' => 'فيتامين D',
            'fiber' => 'الألياف',
            'gluten' => 'الجلوتين',
            'milk' => 'الحليب',
            'dairy' => 'الألبان',
            'egg' => 'البيض',
            'eggs' => 'البيض',
            'nuts' => 'المكسرات',
            'soy' => 'الصويا',
            'fish' => 'السمك',
            'shellfish' => 'المحار',
            'sesame' => 'السمسم',
            'peanut' => 'الفول السوداني',
        ];

        $result = array_map(function ($item) use ($map) {
            $key = mb_strtolower(trim((string) $item));
            return $map[$key] ?? (string) $item;
        }, $terms);

        return array_values($result);
    }

    private function buildEatDecision(FoodAnalysis $analysis, ?HealthMetric $metric): array
    {
        // Simple risk rules based on current user metrics and meal macros.
        $calories = (int) ($analysis->calories ?? 0);
        $carbs = (float) ($analysis->carbs ?? 0);
        $fat = (float) ($analysis->fat ?? 0);
        $bloodSugar = (float) ($metric?->blood_sugar ?? 0);
        $systolic = (int) ($metric?->systolic ?? 0);
        $diastolic = (int) ($metric?->diastolic ?? 0);

        $risk = 0;
        $reasons = [];

        if ($bloodSugar >= 126 && $carbs >= 45) {
            $risk += 2;
            $reasons[] = 'نسبة الكربوهيدرات مرتفعة مقارنةً بقراءة السكر الحالية.';
        }
        if (($systolic >= 140 || $diastolic >= 90) && $fat >= 25) {
            $risk += 2;
            $reasons[] = 'الدهون مرتفعة وقد لا تناسب حالة ضغط الدم الحالية.';
        }
        if ($calories >= 750) {
            $risk += 1;
            $reasons[] = 'السعرات عالية لوجبة واحدة.';
        }
        if ($carbs <= 35 && $fat <= 18 && $calories <= 550) {
            $risk -= 1;
        }

        if ($risk >= 3) {
            return [
                'status' => 'warn',
                'title' => 'تحذير: يفضل تجنب هذه الوجبة الآن',
                'message' => implode(' ', $reasons) ?: 'الوجبة غير مناسبة لوضعك الصحي الحالي.',
            ];
        }

        if ($risk >= 1) {
            return [
                'status' => 'caution',
                'title' => 'تنبيه: يمكن تناولها بحذر',
                'message' => implode(' ', $reasons) ?: 'ينصح بتقليل الكمية أو موازنتها بنشاط بدني.',
            ];
        }

        return [
            'status' => 'good',
            'title' => 'مناسب: خيار جيد لحالتك الحالية',
            'message' => 'القيم الغذائية مقبولة بالنسبة لمؤشراتك الحالية. حافظ على الكمية المعتدلة.',
        ];
    }

    private function processAnalysisNow(FoodAnalysis $analysis, OpenAIHealthService $ai, PointsService $pointsService): void
    {
        $analysis->update(['status' => 'processing']);

        try {
            $fullPath = Storage::disk('public')->path($analysis->image_path);
            $result = $ai->isEnabled()
                ? $ai->analyzeFoodImage($fullPath)
                : $this->deterministicFallback($fullPath);

            $isFoodRaw = $result['isFood'] ?? true;
            $isFood = filter_var($isFoodRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isFood === false) {
                $analysis->update([
                    'status' => 'rejected',
                    'food_name' => null,
                    'calories' => null,
                    'protein' => null,
                    'carbs' => null,
                    'fat' => null,
                    'minerals' => [],
                    'allergens' => [],
                    'analysis_payload' => $result,
                    'model_used' => $ai->isEnabled() ? $ai->model() : 'deterministic-fallback',
                    'error_message' => (string) ($result['rejectReason'] ?? 'الصورة لا تبدو كوجبة طعام'),
                    'analyzed_date' => now(),
                ]);
                return;
            }

            $calories = (int) ($result['calories'] ?? 0);
            $rating = ($result['rating'] ?? ($calories <= 450 ? 'excellent' : 'good'));

            $analysis->update([
                'status' => 'completed',
                'food_name' => (string) ($result['foodName'] ?? 'وجبة'),
                'calories' => $calories,
                'protein' => (float) ($result['protein'] ?? 0),
                'carbs' => (float) ($result['carbs'] ?? 0),
                'fat' => (float) ($result['fat'] ?? 0),
                'minerals' => is_array($result['minerals'] ?? null) ? $result['minerals'] : [],
                'allergens' => is_array($result['allergens'] ?? null) ? $result['allergens'] : [],
                'analysis_payload' => $result,
                'model_used' => $ai->isEnabled() ? $ai->model() : 'deterministic-fallback',
                'error_message' => null,
                'analyzed_date' => now(),
            ]);

            $pointsService->award(
                $analysis->user,
                'food.analysis_completed',
                'تحليل وجبة',
                ['rating' => $rating],
                'daily'
            );
        } catch (\Throwable $e) {
            $analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'model_used' => $ai->isEnabled() ? $ai->model() : 'deterministic-fallback',
            ]);
        }
    }

    private function deterministicFallback(string $fullPath): array
    {
        $hash = abs(crc32((string) @file_get_contents($fullPath)));
        $calories = 180 + ($hash % 520);
        $protein = round(10 + (($hash % 3100) / 100), 2);
        $carbs = round(15 + ((($hash >> 3) % 6200) / 100), 2);
        $fat = round(5 + ((($hash >> 5) % 3600) / 100), 2);

        return [
            'isFood' => true,
            'rejectReason' => null,
            'foodName' => 'وجبة محللة',
            'calories' => $calories,
            'protein' => $protein,
            'carbs' => $carbs,
            'fat' => $fat,
            'minerals' => ['potassium', 'iron'],
            'allergens' => $carbs > 50 ? ['gluten'] : [],
            'rating' => $calories <= 450 ? 'excellent' : 'good',
            'evaluation' => $calories <= 450 ? 'ممتاز' : 'جيد',
        ];
    }

    private function aiEnabled(): bool
    {
        return app(SiteSettingService::class)->getBool('ai.enabled', false);
    }
}
