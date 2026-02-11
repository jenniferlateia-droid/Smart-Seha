@extends('layouts.app', ['title' => 'النقاط'])

@section('content')
<h1 class="page-title mb-6">نظام النقاط</h1>

<div class="mb-5 grid gap-4 md:grid-cols-3">
    <div class="glass-card p-5 text-center"><p class="text-sm text-slate-500">إجمالي النقاط</p><p id="totalPoints" class="mt-2 text-4xl font-black text-emerald-700">--</p></div>
    <div class="glass-card p-5 text-center"><p class="text-sm text-slate-500">المستوى</p><p id="levelName" class="mt-2 text-3xl font-black text-slate-900">--</p></div>
    <div class="glass-card p-5 text-center"><p class="text-sm text-slate-500">المستوى التالي</p><p id="nextLevel" class="mt-2 text-xl font-black text-slate-900">--</p></div>
</div>

<div class="mb-4 grid gap-4 md:grid-cols-3">
    <div class="glass-card p-5 md:col-span-2">
        <h2 class="mb-3 text-lg font-bold">نقاط آخر 7 أيام</h2>
        <canvas id="pointsChart" height="95"></canvas>
    </div>
    <div class="glass-card p-5 text-center">
        <p class="text-sm text-slate-500">الالتزام الأسبوعي</p>
        <p id="consistency" class="mt-2 text-4xl font-black text-emerald-700">--</p>
        <p id="activeDays" class="mt-1 text-sm text-slate-500">--</p>
    </div>
</div>

<div class="glass-card p-6">
    <h2 class="mb-3 text-lg font-bold">سجل الأنشطة</h2>
    <ul id="activities" class="space-y-2 text-sm"></ul>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let pointsChart;

(async function loadPoints() {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/api/points', {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
    });

    if (!res.ok) return;

    const data = await res.json();
    document.getElementById('totalPoints').textContent = data.totalPoints ?? 0;
    document.getElementById('levelName').textContent = data.level?.name ?? '--';
    document.getElementById('nextLevel').textContent = data.level?.nextLevelPoints ?? 'تم الوصول لأعلى مستوى';
    document.getElementById('consistency').textContent = `${data.consistency?.percentage ?? 0}%`;
    document.getElementById('activeDays').textContent = `${data.consistency?.activeDays ?? 0} أيام نشطة`;

    const activities = document.getElementById('activities');
    activities.innerHTML = '';

    (data.activities || []).forEach((row) => {
        const li = document.createElement('li');
        li.className = 'rounded-xl border border-slate-200 bg-slate-50 px-3 py-2';
        li.textContent = `${row.date} - ${row.activity} (+${row.points})`;
        activities.appendChild(li);
    });

    if (pointsChart) pointsChart.destroy();
    pointsChart = new Chart(document.getElementById('pointsChart'), {
        type: 'bar',
        data: {
            labels: data.trend?.labels || [],
            datasets: [{ label: 'النقاط', data: data.trend?.points || [], backgroundColor: '#2a9d8f' }],
        },
    });
})();
</script>
@endpush
