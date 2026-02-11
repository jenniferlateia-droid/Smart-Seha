<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Support\Carbon;

class PointsService
{
    public function __construct(private readonly SiteSettingService $settings)
    {
    }

    public function award(User $user, string $actionKey, string $activity, array $meta = [], ?string $scope = 'daily'): UserPoint
    {
        $today = Carbon::today()->toDateString();
        $uniqueKey = $this->buildUniqueKey($actionKey, $scope, $today, $meta);

        $points = $this->pointsForAction($actionKey);

        return UserPoint::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'unique_key' => $uniqueKey,
            ],
            [
                'action_key' => $actionKey,
                'activity' => $activity,
                'points' => $points,
                'recorded_date' => $today,
                'meta' => $meta,
            ]
        );
    }

    public function levelForTotal(int $totalPoints): array
    {
        $silver = $this->settings->getInt('points.level.silver', 100);
        $gold = $this->settings->getInt('points.level.gold', 300);
        $platinum = $this->settings->getInt('points.level.platinum', 700);

        if ($silver < 1) {
            $silver = 100;
        }
        if ($gold <= $silver) {
            $gold = $silver + 200;
        }
        if ($platinum <= $gold) {
            $platinum = $gold + 400;
        }

        if ($totalPoints >= $platinum) {
            return ['name' => 'platinum', 'minPoints' => $platinum, 'maxPoints' => null, 'nextLevelPoints' => null];
        }

        if ($totalPoints >= $gold) {
            return ['name' => 'gold', 'minPoints' => $gold, 'maxPoints' => $platinum - 1, 'nextLevelPoints' => $platinum];
        }

        if ($totalPoints >= $silver) {
            return ['name' => 'silver', 'minPoints' => $silver, 'maxPoints' => $gold - 1, 'nextLevelPoints' => $gold];
        }

        return ['name' => 'bronze', 'minPoints' => 0, 'maxPoints' => $silver - 1, 'nextLevelPoints' => $silver];
    }

    private function pointsForAction(string $actionKey): int
    {
        $defaults = [
            'profile.completed' => 40,
            'health.metrics_logged' => 20,
            'recommendations.generated' => 15,
            'food.analysis_completed' => 25,
            'task.completed' => 10,
        ];

        return $this->settings->getInt('points.action.'.$actionKey, $defaults[$actionKey] ?? 5);
    }

    private function buildUniqueKey(string $actionKey, ?string $scope, string $date, array $meta): string
    {
        if ($scope === 'once') {
            return $actionKey;
        }

        if ($scope === 'custom' && isset($meta['scope_id'])) {
            return $actionKey.':'.$meta['scope_id'];
        }

        return $actionKey.':'.$date;
    }
}
