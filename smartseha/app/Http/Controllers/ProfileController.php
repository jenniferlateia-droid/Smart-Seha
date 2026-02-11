<?php

namespace App\Http\Controllers;

use App\Models\HealthMetric;
use App\Models\Task;
use App\Services\PointsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function showForm(Request $request): View
    {
        return view('profile.form', [
            'profile' => $request->user()->profile,
        ]);
    }

    public function store(Request $request, PointsService $points): RedirectResponse
    {
        $validated = $request->validate([
            'age' => ['required', 'integer', 'between:10,100'],
            'gender' => ['required', 'in:male,female'],
            'height' => ['required', 'numeric', 'between:100,250'],
            'weight' => ['required', 'numeric', 'between:20,300'],
            'goal' => ['required', 'in:lose,gain,lifestyle'],
            'target_weight' => ['nullable', 'numeric', 'between:20,300'],
        ]);

        $user = $request->user();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $points->award($user, 'profile.completed', 'استكمال الملف الشخصي', [], 'once');

        Task::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'إدخال القياسات اليومية'],
            [
                'description' => 'سجل الوزن والضغط والسكر لليوم',
                'completed' => false,
                'points' => 20,
                'due_date' => Carbon::today()->toDateString(),
            ]
        );

        return redirect()->route('profile.health.form');
    }

    public function showHealthForm(Request $request): View
    {
        return view('profile.health-conditions', [
            'latest' => $request->user()->healthMetrics()->latest('recorded_date')->first(),
        ]);
    }

    public function storeHealth(Request $request, PointsService $points): RedirectResponse
    {
        $validated = $request->validate([
            'weight' => ['required', 'numeric', 'between:20,300'],
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

        HealthMetric::updateOrCreate(
            ['user_id' => $user->id, 'recorded_date' => $today],
            [
                'weight' => $validated['weight'],
                'systolic' => $validated['systolic'] ?? null,
                'diastolic' => $validated['diastolic'] ?? null,
                'blood_sugar' => $validated['blood_sugar'] ?? null,
                'steps' => $validated['steps'] ?? null,
                'water_intake_liters' => $validated['water_intake_liters'] ?? null,
                'sleep_hours' => $validated['sleep_hours'] ?? null,
                'exercise_minutes' => $validated['exercise_minutes'] ?? null,
                'mood_score' => $validated['mood_score'] ?? null,
            ]
        );

        if ($user->profile) {
            $user->profile()->update(['weight' => $validated['weight']]);
        } else {
            $user->profile()->create([
                'age' => 18,
                'gender' => 'male',
                'height' => 170,
                'weight' => $validated['weight'],
                'goal' => 'lifestyle',
            ]);
        }

        $points->award($user, 'health.metrics_logged', 'تسجيل المؤشرات الصحية', [], 'daily');

        Task::where('user_id', $user->id)
            ->where('title', 'إدخال القياسات اليومية')
            ->update(['completed' => true]);

        return redirect()->route('dashboard.page');
    }
}
