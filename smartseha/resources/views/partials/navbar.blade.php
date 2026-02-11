@php($aiEnabled = app(\App\Services\SiteSettingService::class)->getBool('ai.enabled', false))
<nav class="sticky top-0 z-20 w-full bg-[linear-gradient(135deg,#264653,#2a9d8f)] py-3 shadow-[0_8px_22px_rgba(0,0,0,0.16)]">
    <div class="mx-auto flex w-full max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4">
        <a href="{{ route('dashboard.page') }}" class="flex items-center gap-2 text-lg font-black text-white">
            <i class="fa-solid fa-heart-pulse rounded-lg bg-white/15 p-2"></i>
            <span>Smart Seha</span>
        </a>

        <div class="flex flex-wrap items-center gap-1 text-sm">
            <a class="rounded-lg px-3 py-2 text-white/90 {{ request()->routeIs('dashboard.page') ? 'bg-white/20' : 'hover:bg-white/10' }}" href="{{ route('dashboard.page') }}"><i class="fa-solid fa-house ml-1"></i>الرئيسية</a>
            @if($aiEnabled)
                <a class="rounded-lg px-3 py-2 text-white/90 {{ request()->routeIs('food.page') ? 'bg-white/20' : 'hover:bg-white/10' }}" href="{{ route('food.page') }}"><i class="fa-solid fa-utensils ml-1"></i>تحليل الطعام</a>
                <a class="rounded-lg px-3 py-2 text-white/90 {{ request()->routeIs('recommendations.page') ? 'bg-white/20' : 'hover:bg-white/10' }}" href="{{ route('recommendations.page') }}"><i class="fa-solid fa-lightbulb ml-1"></i>التوصيات</a>
            @endif
            <a class="rounded-lg px-3 py-2 text-white/90 {{ request()->routeIs('points.page') ? 'bg-white/20' : 'hover:bg-white/10' }}" href="{{ route('points.page') }}"><i class="fa-solid fa-star ml-1"></i>النقاط</a>
            <a class="rounded-lg px-3 py-2 text-white/90 {{ request()->routeIs('settings.page') ? 'bg-white/20' : 'hover:bg-white/10' }}" href="{{ route('settings.page') }}"><i class="fa-solid fa-gear ml-1"></i>الإعدادات</a>
            @if (auth()->user()?->is_admin)
                <a class="rounded-lg px-3 py-2 text-white/90 {{ request()->routeIs('admin.*') ? 'bg-white/20' : 'hover:bg-white/10' }}" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-shield-halved ml-1"></i>الإدارة</a>
            @endif
        </div>

        <button id="logoutBtn" class="rounded-lg bg-rose-500 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-600">تسجيل الخروج</button>
    </div>
</nav>

@push('scripts')
<script>
document.getElementById('logoutBtn')?.addEventListener('click', async () => {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    await fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        credentials: 'include',
    });

    window.location.href = '/login';
});
</script>
@endpush
