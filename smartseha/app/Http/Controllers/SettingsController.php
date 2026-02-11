<?php

namespace App\Http\Controllers;

use App\Models\HealthMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function page(Request $request): View
    {
        return view('settings');
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');
        $latestMetric = $user->healthMetrics()->latest('recorded_date')->first();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'gender' => $user->profile?->gender,
            'age' => $user->profile?->age,
            'height' => $user->profile?->height,
            'weight' => $user->profile?->weight,
            'goal' => $user->profile?->goal,
            'systolic' => $latestMetric?->systolic,
            'diastolic' => $latestMetric?->diastolic,
            'bloodSugar' => $latestMetric?->blood_sugar,
            'steps' => $latestMetric?->steps,
            'waterIntakeLiters' => $latestMetric?->water_intake_liters,
            'sleepHours' => $latestMetric?->sleep_hours,
            'exerciseMinutes' => $latestMetric?->exercise_minutes,
            'moodScore' => $latestMetric?->mood_score,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'age' => ['required', 'integer', 'between:10,100'],
            'height' => ['required', 'numeric', 'between:100,250'],
            'weight' => ['required', 'numeric', 'between:20,300'],
            'goal' => ['required', 'in:lose,gain,lifestyle'],
            'gender' => ['required', 'in:male,female'],
            'systolic' => ['nullable', 'integer', 'between:0,300'],
            'diastolic' => ['nullable', 'integer', 'between:0,200'],
            'bloodSugar' => ['nullable', 'numeric', 'between:0,600'],
            'steps' => ['nullable', 'integer', 'between:0,100000'],
            'waterIntakeLiters' => ['nullable', 'numeric', 'between:0,20'],
            'sleepHours' => ['nullable', 'numeric', 'between:0,24'],
            'exerciseMinutes' => ['nullable', 'integer', 'between:0,600'],
            'moodScore' => ['nullable', 'integer', 'between:1,10'],
            'currentPassword' => ['nullable', 'string'],
            'newPassword' => ['nullable', 'string', 'min:8'],
        ]);

        $user = $request->user();

        if (!empty($validated['newPassword'])) {
            if (empty($validated['currentPassword']) || !Hash::check($validated['currentPassword'], $user->password)) {
                throw ValidationException::withMessages([
                    'currentPassword' => ['كلمة المرور الحالية غير صحيحة'],
                ]);
            }

            $user->password = Hash::make($validated['newPassword']);
        }

        $user->save();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'age' => $validated['age'],
                'height' => $validated['height'],
                'weight' => $validated['weight'],
                'goal' => $validated['goal'],
                'gender' => $validated['gender'],
            ]
        );

        HealthMetric::updateOrCreate(
            ['user_id' => $user->id, 'recorded_date' => Carbon::today()->toDateString()],
            [
                'weight' => $validated['weight'],
                'systolic' => $validated['systolic'] ?? null,
                'diastolic' => $validated['diastolic'] ?? null,
                'blood_sugar' => $validated['bloodSugar'] ?? null,
                'steps' => $validated['steps'] ?? null,
                'water_intake_liters' => $validated['waterIntakeLiters'] ?? null,
                'sleep_hours' => $validated['sleepHours'] ?? null,
                'exercise_minutes' => $validated['exerciseMinutes'] ?? null,
                'mood_score' => $validated['moodScore'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التغييرات بنجاح',
        ]);
    }
}
