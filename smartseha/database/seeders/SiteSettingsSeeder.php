<?php

namespace Database\Seeders;

use App\Services\SiteSettingService;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SiteSettingService::class);

        $defaults = [
            'site.name' => 'Smart Seha',
            'ai.enabled' => '0',
            'ai.model' => 'gpt-4o-mini',
            'points.action.profile.completed' => '40',
            'points.action.health.metrics_logged' => '20',
            'points.action.recommendations.generated' => '15',
            'points.action.food.analysis_completed' => '25',
            'points.action.task.completed' => '10',
            'points.level.silver' => '100',
            'points.level.gold' => '300',
            'points.level.platinum' => '700',
        ];

        foreach ($defaults as $key => $value) {
            if ($settings->get($key) === null) {
                $settings->set($key, $value);
            }
        }
    }
}
