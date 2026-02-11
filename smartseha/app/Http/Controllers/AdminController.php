<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SiteSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(SiteSettingService $settings): View
    {
        return view('admin.dashboard', [
            'users' => User::query()->latest()->paginate(20),
            'settings' => [
                'site_name' => $settings->get('site.name', 'Smart Seha'),
                'ai_enabled' => $settings->getBool('ai.enabled', false),
                'ai_model' => $settings->get('ai.model', 'gpt-4o-mini'),
                'points_profile' => $settings->getInt('points.action.profile.completed', 40),
                'points_health' => $settings->getInt('points.action.health.metrics_logged', 20),
                'points_recommendation' => $settings->getInt('points.action.recommendations.generated', 15),
                'points_food' => $settings->getInt('points.action.food.analysis_completed', 25),
                'level_silver' => $settings->getInt('points.level.silver', 100),
                'level_gold' => $settings->getInt('points.level.gold', 300),
                'level_platinum' => $settings->getInt('points.level.platinum', 700),
            ],
            'stats' => [
                'total_users' => User::count(),
                'admin_users' => User::where('is_admin', true)->count(),
            ],
        ]);
    }

    public function updateSettings(Request $request, SiteSettingService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'ai_enabled' => ['nullable', 'boolean'],
            'ai_model' => ['required', 'string', 'max:80'],
            'ai_api_key' => ['nullable', 'string', 'max:255'],
            'points_profile' => ['required', 'integer', 'min:0', 'max:500'],
            'points_health' => ['required', 'integer', 'min:0', 'max:500'],
            'points_recommendation' => ['required', 'integer', 'min:0', 'max:500'],
            'points_food' => ['required', 'integer', 'min:0', 'max:500'],
            'level_silver' => ['required', 'integer', 'min:1'],
            'level_gold' => ['required', 'integer', 'min:2'],
            'level_platinum' => ['required', 'integer', 'min:3'],
        ]);

        if ($validated['level_silver'] >= $validated['level_gold'] || $validated['level_gold'] >= $validated['level_platinum']) {
            return back()->withErrors([
                'level_silver' => 'تأكد من ترتيب مستويات النقاط: Silver < Gold < Platinum',
            ])->withInput();
        }

        $settings->set('site.name', $validated['site_name']);
        $settings->set('ai.enabled', $request->boolean('ai_enabled') ? '1' : '0');
        $settings->set('ai.model', $validated['ai_model']);

        if (!empty($validated['ai_api_key'])) {
            $settings->set('ai.api_key', $validated['ai_api_key'], true);
        }

        $settings->set('points.action.profile.completed', $validated['points_profile']);
        $settings->set('points.action.health.metrics_logged', $validated['points_health']);
        $settings->set('points.action.recommendations.generated', $validated['points_recommendation']);
        $settings->set('points.action.food.analysis_completed', $validated['points_food']);

        $settings->set('points.level.silver', $validated['level_silver']);
        $settings->set('points.level.gold', $validated['level_gold']);
        $settings->set('points.level.platinum', $validated['level_platinum']);

        return back()->with('status', 'تم تحديث إعدادات النظام بنجاح');
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);

        if ((int) $validated['is_admin'] === 0 && (int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['is_admin' => 'لا يمكنك إزالة صلاحية الإدارة من نفسك'])->withInput();
        }

        if ((int) $validated['is_admin'] === 0 && $user->is_admin) {
            $adminsCount = User::query()->where('is_admin', true)->count();
            if ($adminsCount <= 1) {
                return back()->withErrors(['is_admin' => 'لا يمكن إزالة آخر مدير في النظام'])->withInput();
            }
        }

        $user->update(['is_admin' => $validated['is_admin']]);

        return back()->with('status', 'تم تحديث صلاحيات المستخدم');
    }
}
