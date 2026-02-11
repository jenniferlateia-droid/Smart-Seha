<?php

namespace App\Http\Controllers;

use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PointsController extends Controller
{
    public function page(): View
    {
        return view('points');
    }

    public function data(Request $request, PointsService $pointsService): JsonResponse
    {
        $user = $request->user();
        $totalPoints = (int) $user->points()->sum('points');

        $level = $pointsService->levelForTotal($totalPoints);

        $activities = $user->points()
            ->latest('recorded_date')
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(fn ($item): array => [
                'date' => optional($item->recorded_date)->format('Y-m-d'),
                'activity' => $item->activity,
                'points' => $item->points,
                'action' => $item->action_key,
            ])
            ->values();

        $from = Carbon::today()->subDays(6)->toDateString();
        $dailyRows = $user->points()
            ->selectRaw('recorded_date, SUM(points) as total')
            ->whereDate('recorded_date', '>=', $from)
            ->groupBy('recorded_date')
            ->orderBy('recorded_date')
            ->get()
            ->keyBy(fn ($row) => optional($row->recorded_date)->format('Y-m-d'));

        $labels = [];
        $totals = [];
        $activeDays = 0;
        foreach (range(6, 0) as $offset) {
            $date = Carbon::today()->subDays($offset);
            $key = $date->format('Y-m-d');
            $value = (int) ($dailyRows[$key]->total ?? 0);
            $labels[] = $date->format('m/d');
            $totals[] = $value;
            if ($value > 0) {
                $activeDays += 1;
            }
        }

        return response()->json([
            'totalPoints' => $totalPoints,
            'total_points' => $totalPoints,
            'level' => $level,
            'activities' => $activities,
            'trend' => [
                'labels' => $labels,
                'points' => $totals,
            ],
            'consistency' => [
                'activeDays' => $activeDays,
                'percentage' => (int) round(($activeDays / 7) * 100),
            ],
        ]);
    }
}
