<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\HealthMetric;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function page(): View
    {
        return view('dashboard');
    }

    public function data(Request $request, PointsService $pointsService): JsonResponse
    {
        $user = $request->user()->load('profile');
        $latestMetric = $user->healthMetrics()->latest('recorded_date')->first();
        $metrics = $user->healthMetrics()
            ->whereDate('recorded_date', '>=', Carbon::today()->subDays(13)->toDateString())
            ->orderBy('recorded_date')
            ->get()
            ->keyBy(function ($item) {
                return optional($item->recorded_date)->toDateString();
            });

        $todaysPoints = (int) $user->points()->whereDate('recorded_date', now()->toDateString())->sum('points');
        $totalPoints = (int) $user->points()->sum('points');
        $level = $pointsService->levelForTotal($totalPoints);
        $today = Carbon::today()->toDateString();
        $todayMetric = $metrics->get($today);
        $hasTodayLog = (bool) $todayMetric;
        $completedTasks = (int) Task::query()->where('user_id', $user->id)->where('completed', true)->count();
        $pendingTasks = (int) Task::query()->where('user_id', $user->id)->where('completed', false)->count();

        $adherenceScore = $this->adherenceScore($todayMetric);
        $labels = [];
        $weight = [];
        $bloodSugar = [];
        $systolic = [];
        $diastolic = [];
        $steps = [];
        $water = [];
        $sleep = [];
        $exercise = [];
        $mood = [];

        foreach (range(13, 0) as $offset) {
            $date = Carbon::today()->subDays($offset);
            $key = $date->toDateString();
            $row = $metrics->get($key);

            $labels[] = $date->format('m/d');
            $weight[] = $row?->weight;
            $bloodSugar[] = $row?->blood_sugar;
            $systolic[] = $row?->systolic;
            $diastolic[] = $row?->diastolic;
            $steps[] = $row?->steps;
            $water[] = $row?->water_intake_liters;
            $sleep[] = $row?->sleep_hours;
            $exercise[] = $row?->exercise_minutes;
            $mood[] = $row?->mood_score;
        }

        $trend = [
            'labels' => $labels,
            'weight' => $weight,
            'bloodSugar' => $bloodSugar,
            'systolic' => $systolic,
            'diastolic' => $diastolic,
            'steps' => $steps,
            'waterIntake' => $water,
            'sleepHours' => $sleep,
            'exerciseMinutes' => $exercise,
            'moodScore' => $mood,
        ];

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->profile?->age,
                'gender' => $user->profile?->gender,
            ],
            'health' => [
                'weight' => $user->profile?->weight,
                'height' => $user->profile?->height,
                'bmi' => $this->bmi($user->profile?->weight, $user->profile?->height),
                'bloodSugar' => $latestMetric?->blood_sugar,
                'systolic' => $latestMetric?->systolic,
                'diastolic' => $latestMetric?->diastolic,
                'steps' => $latestMetric?->steps,
                'waterIntake' => $latestMetric?->water_intake_liters,
                'sleepHours' => $latestMetric?->sleep_hours,
                'exerciseMinutes' => $latestMetric?->exercise_minutes,
                'moodScore' => $latestMetric?->mood_score,
            ],
            'todaysPoints' => $todaysPoints,
            'totalPoints' => $totalPoints,
            'recommendations' => $user->recommendationLogs()->count(),
            'level' => $level['name'],
            'hasTodayLog' => $hasTodayLog,
            'adherenceScore' => $adherenceScore,
            'healthScore' => $this->healthScoreFromMetric($latestMetric),
            'taskSummary' => [
                'completed' => $completedTasks,
                'pending' => $pendingTasks,
            ],
            'trend' => $trend,
            'alerts' => $this->healthAlerts($latestMetric),
            'reminders' => $this->smartReminders($hasTodayLog, $todayMetric),
        ]);
    }

    public function tasks(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'description', 'completed', 'points']);

        return response()->json(['tasks' => $tasks]);
    }

    public function completeTask(Request $request, Task $task, PointsService $points): JsonResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        if (!$task->completed) {
            $task->update(['completed' => true]);
            $points->award($request->user(), 'task.completed', 'إكمال مهمة: '.$task->title, ['scope_id' => 'task-'.$task->id], 'custom');
        }

        return response()->json([
            'success' => true,
            'task' => $task->fresh(),
        ]);
    }

    public function quickLog(Request $request, PointsService $points): JsonResponse
    {
        $validated = $request->validate([
            'weight' => ['nullable', 'numeric', 'between:20,300'],
            'systolic' => ['nullable', 'integer', 'between:0,300'],
            'diastolic' => ['nullable', 'integer', 'between:0,200'],
            'blood_sugar' => ['nullable', 'numeric', 'between:0,600'],
            'steps' => ['nullable', 'integer', 'between:0,100000'],
            'water_intake_liters' => ['nullable', 'numeric', 'between:0,20'],
            'sleep_hours' => ['nullable', 'numeric', 'between:0,24'],
            'exercise_minutes' => ['nullable', 'integer', 'between:0,600'],
            'mood_score' => ['nullable', 'integer', 'between:1,10'],
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();
        $existing = HealthMetric::query()->where('user_id', $user->id)->whereDate('recorded_date', $today)->first();

        $metric = HealthMetric::updateOrCreate(
            ['user_id' => $user->id, 'recorded_date' => $today],
            [
                'weight' => $validated['weight'] ?? $existing?->weight ?? $user->profile?->weight ?? 70,
                'systolic' => $validated['systolic'] ?? $existing?->systolic,
                'diastolic' => $validated['diastolic'] ?? $existing?->diastolic,
                'blood_sugar' => $validated['blood_sugar'] ?? $existing?->blood_sugar,
                'steps' => $validated['steps'] ?? $existing?->steps,
                'water_intake_liters' => $validated['water_intake_liters'] ?? $existing?->water_intake_liters,
                'sleep_hours' => $validated['sleep_hours'] ?? $existing?->sleep_hours,
                'exercise_minutes' => $validated['exercise_minutes'] ?? $existing?->exercise_minutes,
                'mood_score' => $validated['mood_score'] ?? $existing?->mood_score,
            ]
        );

        if (isset($validated['weight']) && $user->profile) {
            $user->profile()->update(['weight' => $validated['weight']]);
        }

        $points->award($user, 'health.quick_log', 'تسجيل يومي سريع', [], 'daily');

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ القياس اليومي',
            'metric' => $metric,
        ]);
    }

    private function bmi(?float $weight, ?float $height): ?float
    {
        if (!$weight || !$height) {
            return null;
        }

        $heightInMeters = $height / 100;

        if ($heightInMeters <= 0) {
            return null;
        }

        return round($weight / ($heightInMeters * $heightInMeters), 2);
    }

    private function adherenceScore(?HealthMetric $metric): int
    {
        if (!$metric) {
            return 0;
        }

        $score = 0;
        $score += $metric->steps && $metric->steps >= 6000 ? 20 : 0;
        $score += $metric->water_intake_liters && $metric->water_intake_liters >= 1.8 ? 20 : 0;
        $score += $metric->sleep_hours && $metric->sleep_hours >= 7 ? 20 : 0;
        $score += $metric->exercise_minutes && $metric->exercise_minutes >= 20 ? 20 : 0;
        $score += $metric->mood_score && $metric->mood_score >= 6 ? 20 : 0;

        return $score;
    }

    private function healthAlerts(?HealthMetric $metric): array
    {
        if (!$metric) {
            return [];
        }

        $alerts = [];
        if (($metric->blood_sugar ?? 0) >= 126) {
            $alerts[] = 'مستوى السكر مرتفع نسبيًا. حاول تقليل السكريات السريعة ومتابعة القياس.';
        }
        if (($metric->systolic ?? 0) >= 140 || ($metric->diastolic ?? 0) >= 90) {
            $alerts[] = 'قراءة ضغط الدم تحتاج متابعة. قلل الملح وراجع الطبيب عند استمرار الارتفاع.';
        }
        if (($metric->sleep_hours ?? 0) > 0 && ($metric->sleep_hours ?? 0) < 6) {
            $alerts[] = 'النوم أقل من المطلوب. استهدف 7-8 ساعات لتحسين التعافي والطاقة.';
        }

        return $alerts;
    }

    private function smartReminders(bool $hasTodayLog, ?HealthMetric $todayMetric): array
    {
        $items = [];

        if (!$hasTodayLog) {
            $items[] = 'سجل قياسات اليوم الآن حتى نحافظ على دقة المتابعة.';
        }
        if (!$todayMetric?->water_intake_liters || $todayMetric->water_intake_liters < 1.8) {
            $items[] = 'تذكير: اشرب كوب ماء كل ساعتين للوصول إلى الهدف اليومي.';
        }
        if (!$todayMetric?->steps || $todayMetric->steps < 6000) {
            $items[] = 'تذكير: 15 دقيقة مشي إضافية سترفع خطواتك بشكل ممتاز.';
        }
        if (!$todayMetric?->exercise_minutes || $todayMetric->exercise_minutes < 20) {
            $items[] = 'تذكير: نفذ تمرينًا منزليًا قصيرًا لمدة 20 دقيقة.';
        }

        return array_slice($items, 0, 4);
    }

    private function healthScoreFromMetric(?HealthMetric $metric): int
    {
        if (!$metric) {
            return 0;
        }

        // Score starts from 100 and drops when key health values move away from target.
        $score = 100.0;

        $bloodSugar = (float) ($metric->blood_sugar ?? 100);
        $systolic = (float) ($metric->systolic ?? 120);
        $diastolic = (float) ($metric->diastolic ?? 80);
        $steps = (float) ($metric->steps ?? 0);
        $sleep = (float) ($metric->sleep_hours ?? 0);
        $water = (float) ($metric->water_intake_liters ?? 0);
        $exercise = (float) ($metric->exercise_minutes ?? 0);
        $mood = (float) ($metric->mood_score ?? 0);

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
}
