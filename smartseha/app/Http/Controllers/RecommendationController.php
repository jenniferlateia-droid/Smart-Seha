<?php

namespace App\Http\Controllers;

use App\Models\RecommendationLog;
use App\Models\HealthMetric;
use App\Models\Task;
use App\Services\AI\OpenAIHealthService;
use App\Services\PointsService;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function page(): View
    {
        abort_unless($this->aiEnabled(), 404);
        return view('recommendations');
    }

    public function generate(Request $request, OpenAIHealthService $ai, PointsService $points): JsonResponse
    {
        abort_unless($this->aiEnabled(), 403);
        $validated = $request->validate([
            'weight' => ['nullable', 'numeric', 'between:20,300'],
            'height' => ['nullable', 'numeric', 'between:100,250'],
            'age' => ['nullable', 'integer', 'between:10,100'],
            'gender' => ['nullable', 'in:male,female'],
            'blood_sugar' => ['nullable', 'numeric', 'between:0,600'],
            'bloodSugar' => ['nullable', 'numeric', 'between:0,600'],
            'systolic' => ['nullable', 'integer', 'between:0,300'],
            'diastolic' => ['nullable', 'integer', 'between:0,200'],
            'goal' => ['nullable', 'in:lose,gain,lifestyle'],
        ]);

        $user = $request->user()->load('profile');
        $latest = $user->healthMetrics()->latest('recorded_date')->first();

        $payload = [
            'weight' => $validated['weight'] ?? $user->profile?->weight,
            'height' => $validated['height'] ?? $user->profile?->height,
            'age' => $validated['age'] ?? $user->profile?->age,
            'gender' => $validated['gender'] ?? $user->profile?->gender,
            'blood_sugar' => $validated['blood_sugar'] ?? $validated['bloodSugar'] ?? $latest?->blood_sugar,
            'systolic' => $validated['systolic'] ?? $latest?->systolic,
            'diastolic' => $validated['diastolic'] ?? $latest?->diastolic,
            'goal' => $validated['goal'] ?? $user->profile?->goal,
        ];

        $recommendations = $this->buildRecommendations($payload, $ai);
        $recommendations = $this->normalizeRecommendations($recommendations);
        $weeklyPlan = $this->buildWeeklyPlan($payload);
        $todayChallenge = $this->todayChallenge($payload);

        foreach ($recommendations as $rec) {
            RecommendationLog::create([
                'user_id' => $user->id,
                'category' => (string) ($rec['category'] ?? 'health'),
                'icon' => (string) ($rec['icon'] ?? 'عام'),
                'title' => (string) ($rec['title'] ?? 'توصية'),
                'description' => (string) ($rec['description'] ?? ''),
            ]);
        }

        $this->syncTasks($user->id, $recommendations);

        $points->award($user, 'recommendations.generated', 'توليد التوصيات', [], 'daily');

        return response()->json([
            'recommendations' => $recommendations,
            'weeklyPlan' => $weeklyPlan,
            'todayChallenge' => $todayChallenge,
            'healthScore' => $this->healthScore($payload, $latest),
            'data' => $recommendations,
            'generatedAt' => Carbon::now()->toIso8601String(),
            'source' => $ai->isEnabled() ? 'openai' : 'rules',
        ]);
    }

    private function buildRecommendations(array $payload, OpenAIHealthService $ai): array
    {
        // Try AI first, then use rule-based fallback if needed.
        if ($ai->isEnabled()) {
            try {
                $result = $ai->generateRecommendations($payload);

                if (!empty($result) && isset($result['recommendations']) && is_array($result['recommendations'])) {
                    return $result['recommendations'];
                }

                if (array_is_list($result)) {
                    return $result;
                }
            } catch (\Throwable) {
            }
        }

        return $this->rulesBasedRecommendations($payload);
    }

    private function rulesBasedRecommendations(array $payload): array
    {
        $list = [];
        $goal = $payload['goal'] ?? 'lifestyle';

        if ($goal === 'lose') {
            $list[] = ['category' => 'food', 'icon' => 'غذاء', 'title' => 'تقليل السعرات تدريجيًا', 'description' => 'ركز على وجبات عالية بالألياف والبروتين مع تقليل السكريات.'];
            $list[] = ['category' => 'exercise', 'icon' => 'نشاط', 'title' => 'نشاط يومي 30 دقيقة', 'description' => 'المشي السريع يساعد على خسارة الدهون وتحسين القلب.'];
        } elseif ($goal === 'gain') {
            $list[] = ['category' => 'food', 'icon' => 'غذاء', 'title' => 'زيادة البروتين والسعرات الصحية', 'description' => 'أضف وجبات متوازنة حول التمرين لرفع الكتلة العضلية.'];
            $list[] = ['category' => 'exercise', 'icon' => 'نشاط', 'title' => 'تمارين مقاومة 3-4 أيام', 'description' => 'حافظ على زيادة تدريجية في الأحمال لتحفيز البناء العضلي.'];
        } else {
            $list[] = ['category' => 'health', 'icon' => 'صحة', 'title' => 'ترطيب منتظم', 'description' => 'قسم شرب الماء خلال اليوم وحافظ على نوم ثابت.'];
            $list[] = ['category' => 'sleep', 'icon' => 'نوم', 'title' => 'نوم 7-8 ساعات', 'description' => 'النوم الجيد يحسن الطاقة والتوازن الهرموني.'];
        }

        if (($payload['blood_sugar'] ?? 0) >= 126) {
            $list[] = ['category' => 'health', 'icon' => 'متابعة', 'title' => 'مراقبة السكر يوميًا', 'description' => 'سجل القياس قبل الفطور واستشر الطبيب عند استمرار الارتفاع.'];
        }

        if (($payload['systolic'] ?? 0) >= 140 || ($payload['diastolic'] ?? 0) >= 90) {
            $list[] = ['category' => 'health', 'icon' => 'قلب', 'title' => 'خفض الملح ومتابعة الضغط', 'description' => 'قلل الأطعمة المصنعة وأضف نشاطًا خفيفًا ثابتًا يوميًا.'];
        }

        return $list;
    }

    private function syncTasks(int $userId, array $recommendations): void
    {
        foreach ($recommendations as $rec) {
            Task::firstOrCreate(
                [
                    'user_id' => $userId,
                    'title' => (string) ($rec['title'] ?? 'توصية'),
                ],
                [
                    'description' => (string) ($rec['description'] ?? ''),
                    'completed' => false,
                    'points' => 10,
                    'due_date' => Carbon::today()->toDateString(),
                ]
            );
        }
    }

    private function normalizeRecommendations(array $recommendations): array
    {
        $clean = [];

        foreach ($recommendations as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));
            if ($title === '' || $description === '') {
                continue;
            }

            $clean[] = [
                'category' => (string) ($item['category'] ?? 'health'),
                'icon' => (string) ($item['icon'] ?? 'عام'),
                'title' => $title,
                'description' => $description,
            ];
        }

        if (empty($clean)) {
            $clean[] = [
                'category' => 'health',
                'icon' => 'عام',
                'title' => 'توصية عامة',
                'description' => 'حافظ على قياساتك اليومية وتابع تقدمك بشكل منتظم.',
            ];
        }

        return array_slice($clean, 0, 8);
    }

    private function buildWeeklyPlan(array $payload): array
    {
        $goal = $payload['goal'] ?? 'lifestyle';

        if ($goal === 'lose') {
            return [
                ['day' => 'السبت', 'food' => 'فطور بروتين + خضار', 'exercise' => 'مشي سريع 35 دقيقة'],
                ['day' => 'الأحد', 'food' => 'تقليل النشويات مساء', 'exercise' => 'تمارين مقاومة منزلية 25 دقيقة'],
                ['day' => 'الاثنين', 'food' => 'طبق سلطة كبير قبل الغداء', 'exercise' => 'مشي 30 دقيقة'],
                ['day' => 'الثلاثاء', 'food' => 'وجبة عشاء خفيفة', 'exercise' => 'تمارين بطن + إطالة 20 دقيقة'],
                ['day' => 'الأربعاء', 'food' => 'تقليل المشروبات المحلاة', 'exercise' => 'مشي 40 دقيقة'],
                ['day' => 'الخميس', 'food' => 'تركيز على البروتين', 'exercise' => 'مقاومة 30 دقيقة'],
                ['day' => 'الجمعة', 'food' => 'يوم توازن بدون إفراط', 'exercise' => 'نشاط خفيف مع العائلة'],
            ];
        }

        if ($goal === 'gain') {
            return [
                ['day' => 'السبت', 'food' => 'وجبة عالية البروتين بعد التمرين', 'exercise' => 'تمارين مقاومة 40 دقيقة'],
                ['day' => 'الأحد', 'food' => 'إضافة وجبة خفيفة صحية', 'exercise' => 'مشي خفيف 20 دقيقة'],
                ['day' => 'الاثنين', 'food' => 'وجبة كربوهيدرات معقدة', 'exercise' => 'مقاومة علوي 35 دقيقة'],
                ['day' => 'الثلاثاء', 'food' => 'زيادة السعرات 250 سعرة', 'exercise' => 'راحة نشطة وتمطيط'],
                ['day' => 'الأربعاء', 'food' => 'بياض بيض + زبادي', 'exercise' => 'مقاومة سفلي 35 دقيقة'],
                ['day' => 'الخميس', 'food' => 'سناك مكسرات', 'exercise' => 'مشي 25 دقيقة'],
                ['day' => 'الجمعة', 'food' => 'توازن العناصر الثلاثة', 'exercise' => 'تمرين شامل 30 دقيقة'],
            ];
        }

        return [
            ['day' => 'السبت', 'food' => 'فطور متوازن', 'exercise' => 'مشي 30 دقيقة'],
            ['day' => 'الأحد', 'food' => 'زيادة الخضار في الغداء', 'exercise' => 'تمارين تمدد 15 دقيقة'],
            ['day' => 'الاثنين', 'food' => 'تقليل المقليات', 'exercise' => 'مشي 30 دقيقة'],
            ['day' => 'الثلاثاء', 'food' => 'ماء 2 لتر', 'exercise' => 'تمارين خفيفة 20 دقيقة'],
            ['day' => 'الأربعاء', 'food' => 'وجبة منزلية صحية', 'exercise' => 'مشي 35 دقيقة'],
            ['day' => 'الخميس', 'food' => 'تقليل السكر المضاف', 'exercise' => 'مقاومة 20 دقيقة'],
            ['day' => 'الجمعة', 'food' => 'يوم توازن', 'exercise' => 'نشاط ترفيهي خفيف'],
        ];
    }

    private function todayChallenge(array $payload): array
    {
        $items = [
            ['title' => 'تحدي الماء', 'description' => 'اشرب 8 أكواب ماء اليوم'],
            ['title' => 'تحدي الحركة', 'description' => 'حقق 7000 خطوة قبل نهاية اليوم'],
            ['title' => 'تحدي السكر', 'description' => 'تجنب الحلويات المصنعة لمدة يوم كامل'],
        ];

        if (($payload['blood_sugar'] ?? 0) >= 126) {
            return ['title' => 'تحدي سكر الدم', 'description' => 'اختر وجبات منخفضة المؤشر السكري وسجل قراءة واحدة إضافية'];
        }

        return $items[array_rand($items)];
    }

    private function healthScore(array $payload, ?HealthMetric $latestMetric): int
    {
        $score = 100.0;

        $bloodSugar = (float) ($payload['blood_sugar'] ?? 100);
        $systolic = (float) ($payload['systolic'] ?? 120);
        $diastolic = (float) ($payload['diastolic'] ?? 80);
        $steps = (float) ($latestMetric?->steps ?? 0);
        $sleep = (float) ($latestMetric?->sleep_hours ?? 0);
        $water = (float) ($latestMetric?->water_intake_liters ?? 0);
        $exercise = (float) ($latestMetric?->exercise_minutes ?? 0);
        $mood = (float) ($latestMetric?->mood_score ?? 0);

        $score -= min(35, abs($bloodSugar - 100) * 0.45);
        $score -= min(20, max(0, $systolic - 120) * 0.6);
        $score -= min(15, max(0, $diastolic - 80) * 0.7);
        $score -= min(15, max(0, 8000 - $steps) / 500);
        $score -= min(10, abs(7.5 - max(0, $sleep)) * 3.2);
        $score -= min(8, max(0, 2.2 - $water) * 4);
        $score -= min(8, max(0, 30 - $exercise) * 0.25);
        $score -= min(8, max(0, 7 - $mood) * 2);

        return (int) max(0, round($score));
    }

    private function aiEnabled(): bool
    {
        return app(SiteSettingService::class)->getBool('ai.enabled', false);
    }
}
