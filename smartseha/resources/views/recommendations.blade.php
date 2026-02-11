@extends('layouts.app', ['title' => 'التوصيات'])

@section('content')
<div class="mb-5 flex items-center justify-between">
    <h1 class="page-title">التوصيات والخطط الصحية</h1>
    <button id="refreshRecs" class="primary-btn px-4">تحديث</button>
</div>

<div class="mb-4 grid gap-4 md:grid-cols-3">
    <div class="glass-card p-5 md:col-span-2">
        <p class="text-xs text-slate-500">تحدي اليوم</p>
        <h2 id="challengeTitle" class="mt-1 text-xl font-black text-slate-900">--</h2>
        <p id="challengeDesc" class="mt-2 text-sm text-slate-600">--</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-xs text-slate-500">مؤشر الصحة الحالي</p>
        <p id="healthScore" class="mt-2 text-4xl font-black text-emerald-700">--</p>
    </div>
</div>

<div id="recs" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"></div>

<div class="glass-card mt-4 p-5">
    <h2 class="mb-3 text-lg font-bold">الخطة الأسبوعية المقترحة</h2>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-right text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-slate-500">
                    <th class="p-2">اليوم</th>
                    <th class="p-2">الخطة الغذائية</th>
                    <th class="p-2">خطة النشاط</th>
                </tr>
            </thead>
            <tbody id="weeklyPlanRows"></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadRecommendations() {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/api/recommendations', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({}),
    });

    const payload = await res.json();
    const list = payload.recommendations || payload.data || payload || [];
    const container = document.getElementById('recs');
    container.innerHTML = '';

    list.forEach((item) => {
        const el = document.createElement('div');
        el.className = 'glass-card p-5';
        el.innerHTML = `<p class="mb-1 text-xs text-emerald-700">${item.category || ''}</p><h3 class="mb-2 text-lg font-bold text-slate-900">${item.icon || ''} ${item.title || ''}</h3><p class="text-sm text-slate-600">${item.description || ''}</p>`;
        container.appendChild(el);
    });

    document.getElementById('healthScore').textContent = `${payload.healthScore ?? '--'}%`;
    document.getElementById('challengeTitle').textContent = payload.todayChallenge?.title ?? 'لا يوجد تحدي';
    document.getElementById('challengeDesc').textContent = payload.todayChallenge?.description ?? '';

    const rows = document.getElementById('weeklyPlanRows');
    rows.innerHTML = '';
    (payload.weeklyPlan || []).forEach((item) => {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100';
        tr.innerHTML = `<td class="p-2 font-semibold">${item.day || ''}</td><td class="p-2 text-slate-600">${item.food || ''}</td><td class="p-2 text-slate-600">${item.exercise || ''}</td>`;
        rows.appendChild(tr);
    });
}

document.getElementById('refreshRecs').addEventListener('click', loadRecommendations);
loadRecommendations();
</script>
@endpush
