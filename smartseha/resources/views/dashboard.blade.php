@extends('layouts.app', ['title' => 'لوحة التحكم'])

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <h1 class="page-title">لوحة المتابعة</h1>
    <a href="{{ route('profile.health.form') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">نموذج القياسات الكامل</a>
</div>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-2xl bg-[linear-gradient(135deg,#264653,#2a9d8f)] p-5 text-white shadow-lg"><p class="text-sm text-white/80">نقاط اليوم</p><p id="todaysPoints" class="mt-1 text-3xl font-black">--</p></div>
    <div class="rounded-2xl bg-[linear-gradient(135deg,#264653,#2a9d8f)] p-5 text-white shadow-lg"><p class="text-sm text-white/80">إجمالي النقاط</p><p id="totalPoints" class="mt-1 text-3xl font-black">--</p></div>
    <div class="rounded-2xl bg-[linear-gradient(135deg,#264653,#2a9d8f)] p-5 text-white shadow-lg"><p class="text-sm text-white/80">المستوى</p><p id="level" class="mt-1 text-2xl font-black">--</p></div>
    <div class="rounded-2xl bg-[linear-gradient(135deg,#264653,#2a9d8f)] p-5 text-white shadow-lg"><p class="text-sm text-white/80">مؤشر الالتزام</p><p id="adherenceScoreTop" class="mt-1 text-3xl font-black">--</p></div>
    <div class="rounded-2xl bg-[linear-gradient(135deg,#f4a261,#e76f51)] p-5 text-white shadow-lg"><p class="text-sm text-white/90">مؤشر الصحة</p><p id="healthScoreMain" class="mt-1 text-3xl font-black">--</p></div>
</div>

<div class="mt-4 grid gap-4 md:grid-cols-2">
    <div class="glass-card p-5">
        <p class="text-xs text-slate-500">وضعك الصحي الحالي</p>
        <p id="healthInfo" class="mt-2 text-sm text-slate-700">--</p>
        <p id="adherenceText" class="mt-1 text-sm text-slate-600">--</p>
    </div>
    <div class="glass-card p-5">
        <h2 class="mb-2 text-sm font-bold">تنبيه وتذكير</h2>
        <ul id="remindersList" class="space-y-2 text-sm text-slate-700"></ul>
    </div>
</div>

<div class="mt-4 grid gap-4 md:grid-cols-2">
    <div class="glass-card p-5"><h3 class="mb-2 text-sm font-bold">ضغط الدم</h3><canvas id="pressureChart" height="180"></canvas></div>
    <div class="glass-card p-5"><h3 class="mb-2 text-sm font-bold">سكر الدم</h3><canvas id="sugarChart" height="180"></canvas></div>
    <div class="glass-card p-5"><h3 class="mb-2 text-sm font-bold">الوزن</h3><canvas id="weightChart" height="180"></canvas></div>
    <div class="glass-card p-5"><h3 class="mb-2 text-sm font-bold">الخطوات</h3><canvas id="stepsChart" height="180"></canvas></div>
</div>

<div class="mt-3">
    <button id="toggleExtraCharts" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">عرض مقاييس إضافية</button>
</div>

<div id="extraCharts" class="mt-4 hidden grid gap-4 md:grid-cols-3">
    <div class="glass-card p-5"><h3 class="mb-2 text-sm font-bold">شرب الماء</h3><canvas id="waterChart" height="170"></canvas></div>
    <div class="glass-card p-5"><h3 class="mb-2 text-sm font-bold">ساعات النوم</h3><canvas id="sleepChart" height="170"></canvas></div>
    <div class="glass-card p-5"><h3 class="mb-2 text-sm font-bold">التمرين/المزاج</h3><canvas id="exerciseMoodChart" height="170"></canvas></div>
</div>

<div class="mt-4 glass-card p-5">
    <h2 class="mb-3 text-sm font-bold">المهام اليومية</h2>
    <p id="taskSummary" class="mb-2 text-xs text-slate-500">--</p>
    <ul id="tasksList" class="space-y-2"></ul>
</div>

<div id="metricFabMenu" class="metric-fab-menu">
    <button id="metricFabToggle" class="metric-fab-toggle" type="button" aria-label="إضافة قياس">
        <i class="fa-solid fa-plus"></i>
    </button>
    <button class="metric-fab metric-fab-item" data-type="weight" style="--tx:-88px;--ty:-8px;"><span class="metric-fab-label">الوزن</span><i class="fa-solid fa-weight-scale"></i><span class="metric-fab-mobile-label">وزن</span></button>
    <button class="metric-fab metric-fab-item" data-type="pressure" style="--tx:-69px;--ty:-54px;"><span class="metric-fab-label">الضغط</span><i class="fa-solid fa-heart-pulse"></i><span class="metric-fab-mobile-label">ضغط</span></button>
    <button class="metric-fab metric-fab-item" data-type="sugar" style="--tx:-27px;--ty:-84px;"><span class="metric-fab-label">السكر</span><i class="fa-solid fa-droplet"></i><span class="metric-fab-mobile-label">سكر</span></button>
    <button class="metric-fab metric-fab-item" data-type="steps" style="--tx:-142px;--ty:-10px;"><span class="metric-fab-label">الخطوات</span><i class="fa-solid fa-shoe-prints"></i><span class="metric-fab-mobile-label">خطوات</span></button>
    <button class="metric-fab metric-fab-item" data-type="water" style="--tx:-128px;--ty:-62px;"><span class="metric-fab-label">الماء</span><i class="fa-solid fa-glass-water"></i><span class="metric-fab-mobile-label">ماء</span></button>
    <button class="metric-fab metric-fab-item" data-type="sleep" style="--tx:-95px;--ty:-105px;"><span class="metric-fab-label">النوم</span><i class="fa-solid fa-moon"></i><span class="metric-fab-mobile-label">نوم</span></button>
    <button class="metric-fab metric-fab-item" data-type="exercise" style="--tx:-49px;--ty:-133px;"><span class="metric-fab-label">التمرين</span><i class="fa-solid fa-dumbbell"></i><span class="metric-fab-mobile-label">تمرين</span></button>
    <button class="metric-fab metric-fab-item" data-type="mood" style="--tx:-10px;--ty:-141px;"><span class="metric-fab-label">المزاج</span><i class="fa-solid fa-face-smile"></i><span class="metric-fab-mobile-label">مزاج</span></button>
</div>

<div id="metricPopup" class="fixed inset-0 z-30 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-5">
        <div class="mb-3 flex items-center justify-between">
            <h3 id="popupTitle" class="text-base font-bold">إضافة قياس</h3>
            <button id="popupClose" class="rounded-lg bg-slate-100 px-3 py-1 text-sm">إغلاق</button>
        </div>
        <form id="metricPopupForm" class="space-y-2">
            <div id="popupInputs"></div>
            <button class="primary-btn w-full" type="submit">حفظ القياس</button>
        </form>
        <p id="popupMsg" class="mt-2 text-xs text-slate-500"></p>
    </div>
</div>
<div class="h-28 md:hidden"></div>
@endsection

@push('scripts')
<style>
.metric-fab-menu {
    position: fixed;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    z-index: 25;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.metric-fab-toggle {
    display: none;
    width: 54px;
    height: 54px;
    border-radius: 9999px;
    border: 0;
    background: linear-gradient(135deg, #2a9d8f, #264653);
    color: #fff;
    box-shadow: 0 10px 24px rgba(2, 6, 23, 0.3);
    cursor: pointer;
    font-size: 18px;
}
.metric-fab {
    position: relative;
    width: 46px;
    height: 46px;
    border-radius: 9999px;
    border: 0;
    background: linear-gradient(135deg, #264653, #2a9d8f);
    color: #fff;
    box-shadow: 0 8px 18px rgba(2, 6, 23, 0.24);
    cursor: pointer;
    animation: floatY 2.6s ease-in-out infinite;
}
.metric-fab:hover { filter: brightness(1.06); }
.metric-fab.clicked { animation: pulseClick 0.28s ease-out; }
.metric-fab-mobile-label { display: none; }
.metric-fab-label {
    position: absolute;
    top: -32px;
    right: 50%;
    transform: translateX(50%) translateY(6px);
    background: #0f1722;
    color: #fff;
    border-radius: 8px;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: all 0.2s ease;
}
.metric-fab:hover .metric-fab-label {
    opacity: 1;
    transform: translateX(50%) translateY(0);
}
@keyframes floatY {
    0% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
    100% { transform: translateY(0); }
}
@keyframes pulseClick {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}
@media (max-width: 768px) {
    .metric-fab-menu {
        top: auto;
        right: 12px;
        bottom: calc(10px + env(safe-area-inset-bottom));
        transform: none;
        width: 60px;
        height: 60px;
        display: block;
        z-index: 35;
    }
    .metric-fab-toggle {
        display: block;
    }
    .metric-fab {
        width: 44px;
        height: 44px;
        border-radius: 9999px;
        animation: none;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #2a9d8f, #264653);
        box-shadow: 0 8px 18px rgba(2, 6, 23, 0.26);
        position: absolute;
        right: 4px;
        bottom: 4px;
        opacity: 0;
        pointer-events: none;
        transform: translate(0, 0) scale(0.65);
        transition: transform 0.22s ease, opacity 0.2s ease;
    }
    .metric-fab-menu.is-open .metric-fab-item {
        opacity: 1;
        pointer-events: auto;
        transform: translate(var(--tx), var(--ty)) scale(1);
    }
    .metric-fab-menu.is-open .metric-fab-toggle i {
        transform: rotate(45deg);
        transition: transform 0.18s ease;
    }
    .metric-fab i {
        font-size: 13px;
    }
    .metric-fab-mobile-label {
        display: none;
    }
    .metric-fab-label {
        display: block;
        top: -30px;
        font-size: 10px;
    }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartInstances = {};
let dashboardState = null;
const metricConfig = {
    weight: { title: 'إضافة وزن', fields: [{ key: 'weight', placeholder: 'الوزن (كغ)', step: '0.1' }] },
    pressure: { title: 'إضافة ضغط الدم', fields: [{ key: 'systolic', placeholder: 'الضغط الانقباضي', step: '1' }, { key: 'diastolic', placeholder: 'الضغط الانبساطي', step: '1' }] },
    sugar: { title: 'إضافة سكر الدم', fields: [{ key: 'blood_sugar', placeholder: 'سكر الدم', step: '0.1' }] },
    steps: { title: 'إضافة خطوات', fields: [{ key: 'steps', placeholder: 'عدد الخطوات', step: '1' }] },
    water: { title: 'إضافة شرب الماء', fields: [{ key: 'water_intake_liters', placeholder: 'الماء (لتر)', step: '0.1' }] },
    sleep: { title: 'إضافة ساعات النوم', fields: [{ key: 'sleep_hours', placeholder: 'ساعات النوم', step: '0.5' }] },
    exercise: { title: 'إضافة دقائق التمرين', fields: [{ key: 'exercise_minutes', placeholder: 'دقائق التمرين', step: '1' }] },
    mood: { title: 'إضافة المزاج', fields: [{ key: 'mood_score', placeholder: 'المزاج (1-10)', step: '1' }] },
};

function drawLineChart(id, labels, data, color, label) {
    if (chartInstances[id]) chartInstances[id].destroy();
    chartInstances[id] = new Chart(document.getElementById(id), {
        type: 'line',
        data: { labels, datasets: [{ label, data, borderColor: color, backgroundColor: `${color}22`, tension: 0.35, spanGaps: true }] },
        options: { plugins: { legend: { display: false } }, responsive: true },
    });
}

function drawBarChart(id, labels, data, color) {
    if (chartInstances[id]) chartInstances[id].destroy();
    chartInstances[id] = new Chart(document.getElementById(id), {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor: color }] },
        options: { plugins: { legend: { display: false } }, responsive: true },
    });
}

function drawPressureChart(labels, systolic, diastolic) {
    if (chartInstances.pressureChart) chartInstances.pressureChart.destroy();
    chartInstances.pressureChart = new Chart(document.getElementById('pressureChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'انقباضي', data: systolic, borderColor: '#e76f51', tension: 0.35, spanGaps: true },
                { label: 'انبساطي', data: diastolic, borderColor: '#264653', tension: 0.35, spanGaps: true },
            ],
        },
        options: { responsive: true },
    });
}

function drawExerciseMoodChart(labels, exercise, mood) {
    if (chartInstances.exerciseMoodChart) chartInstances.exerciseMoodChart.destroy();
    chartInstances.exerciseMoodChart = new Chart(document.getElementById('exerciseMoodChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'التمرين', data: exercise, borderColor: '#fb8500', tension: 0.35, spanGaps: true },
                { label: 'المزاج', data: mood, borderColor: '#43aa8b', tension: 0.35, spanGaps: true },
            ],
        },
        options: { responsive: true },
    });
}

function renderCharts(trend) {
    const labels = trend?.labels || [];
    drawPressureChart(labels, trend?.systolic || [], trend?.diastolic || []);
    drawLineChart('sugarChart', labels, trend?.bloodSugar || [], '#e76f51', 'السكر');
    drawLineChart('weightChart', labels, trend?.weight || [], '#2a9d8f', 'الوزن');
    drawBarChart('stepsChart', labels, trend?.steps || [], '#264653');
    drawLineChart('waterChart', labels, trend?.waterIntake || [], '#3a86ff', 'الماء');
    drawLineChart('sleepChart', labels, trend?.sleepHours || [], '#8338ec', 'النوم');
    drawExerciseMoodChart(labels, trend?.exerciseMinutes || [], trend?.moodScore || []);
}

function renderTasks(tasks) {
    const list = document.getElementById('tasksList');
    list.innerHTML = '';
    if (!tasks.length) {
        const li = document.createElement('li');
        li.className = 'rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-500';
        li.textContent = 'لا توجد مهام حالياً';
        list.appendChild(li);
        return;
    }

    tasks.forEach((task) => {
        const row = document.createElement('li');
        row.className = 'flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2';
        row.innerHTML = `
            <div>
                <p class="text-sm font-semibold ${task.completed ? 'line-through text-slate-400' : 'text-slate-800'}">${task.title}</p>
                <p class="text-xs text-slate-500">${task.description || ''}</p>
            </div>
            <button data-id="${task.id}" class="task-btn rounded-lg px-3 py-1 text-xs ${task.completed ? 'bg-slate-200 text-slate-500' : 'bg-emerald-600 text-white'}">${task.completed ? 'مكتملة' : 'إنهاء'}</button>
        `;
        list.appendChild(row);
    });

    list.querySelectorAll('.task-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (btn.textContent.includes('مكتملة')) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            await fetch(`/api/dashboard/tasks/${btn.dataset.id}/complete`, {
                method: 'POST',
                credentials: 'include',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
            });
            loadDashboard();
        });
    });
}

function updateTodayValueInTrend(key, value) {
    if (!dashboardState || !dashboardState.trend || !Array.isArray(dashboardState.trend[key])) return;
    const list = dashboardState.trend[key];
    if (!list.length) return;
    list[list.length - 1] = value;
}

function applyMetricUpdate(metric) {
    if (!dashboardState || !metric) return;

    if (metric.weight !== null && metric.weight !== undefined) {
        dashboardState.health.weight = metric.weight;
        updateTodayValueInTrend('weight', metric.weight);
    }
    if (metric.blood_sugar !== null && metric.blood_sugar !== undefined) {
        dashboardState.health.bloodSugar = metric.blood_sugar;
        updateTodayValueInTrend('bloodSugar', metric.blood_sugar);
    }
    if (metric.systolic !== null && metric.systolic !== undefined) {
        dashboardState.health.systolic = metric.systolic;
        updateTodayValueInTrend('systolic', metric.systolic);
    }
    if (metric.diastolic !== null && metric.diastolic !== undefined) {
        dashboardState.health.diastolic = metric.diastolic;
        updateTodayValueInTrend('diastolic', metric.diastolic);
    }
    if (metric.steps !== null && metric.steps !== undefined) {
        dashboardState.health.steps = metric.steps;
        updateTodayValueInTrend('steps', metric.steps);
    }
    if (metric.water_intake_liters !== null && metric.water_intake_liters !== undefined) {
        dashboardState.health.waterIntake = metric.water_intake_liters;
        updateTodayValueInTrend('waterIntake', metric.water_intake_liters);
    }
    if (metric.sleep_hours !== null && metric.sleep_hours !== undefined) {
        dashboardState.health.sleepHours = metric.sleep_hours;
        updateTodayValueInTrend('sleepHours', metric.sleep_hours);
    }
    if (metric.exercise_minutes !== null && metric.exercise_minutes !== undefined) {
        dashboardState.health.exerciseMinutes = metric.exercise_minutes;
        updateTodayValueInTrend('exerciseMinutes', metric.exercise_minutes);
    }
    if (metric.mood_score !== null && metric.mood_score !== undefined) {
        dashboardState.health.moodScore = metric.mood_score;
        updateTodayValueInTrend('moodScore', metric.mood_score);
    }

    document.getElementById('healthInfo').textContent =
        `BMI: ${dashboardState.health.bmi ?? '--'} | ضغط: ${dashboardState.health.systolic ?? '--'}/${dashboardState.health.diastolic ?? '--'} | سكر: ${dashboardState.health.bloodSugar ?? '--'}`;
    renderCharts(dashboardState.trend || {});
}

async function loadDashboard() {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const [res, tasksRes] = await Promise.all([
        fetch('/api/dashboard', { method: 'GET', credentials: 'include', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token } }),
        fetch('/api/dashboard/tasks', { method: 'GET', credentials: 'include', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token } }),
    ]);
    if (!res.ok) return;

    const data = await res.json();
    const tasksPayload = await tasksRes.json().catch(() => ({ tasks: [] }));
    dashboardState = data;

    document.getElementById('todaysPoints').textContent = data.todaysPoints ?? 0;
    document.getElementById('totalPoints').textContent = data.totalPoints ?? 0;
    document.getElementById('level').textContent = data.level ?? '--';
    document.getElementById('adherenceScoreTop').textContent = `${data.adherenceScore ?? 0}%`;
    document.getElementById('healthScoreMain').textContent = `${data.healthScore ?? 0}%`;
    document.getElementById('healthInfo').textContent = `BMI: ${data.health.bmi ?? '--'} | ضغط: ${data.health.systolic ?? '--'}/${data.health.diastolic ?? '--'} | سكر: ${data.health.bloodSugar ?? '--'}`;
    document.getElementById('adherenceText').textContent = `الالتزام اليومي: ${data.adherenceScore ?? 0}% | توصيات: ${data.recommendations ?? 0}`;
    document.getElementById('taskSummary').textContent = `مكتمل: ${data.taskSummary?.completed ?? 0} | متبقي: ${data.taskSummary?.pending ?? 0}`;

    const remindersList = document.getElementById('remindersList');
    remindersList.innerHTML = '';
    const merged = [...(data.alerts || []), ...(data.reminders || [])].slice(0, 4);
    (merged.length ? merged : ['لا توجد تنبيهات حالياً']).forEach((item) => {
        const li = document.createElement('li');
        li.className = 'rounded-xl border border-slate-200 px-3 py-2';
        li.textContent = item;
        remindersList.appendChild(li);
    });

    renderCharts(data.trend || {});
    renderTasks(tasksPayload.tasks || []);
}

async function submitMetricPayload(payload) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/api/dashboard/quick-log', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify(payload),
    });

    const data = await res.json().catch(() => ({}));
    const msg = document.getElementById('popupMsg');

    if (res.ok) {
        msg.className = 'mt-2 text-xs text-emerald-700';
        msg.textContent = data.message || 'تم الحفظ';
        document.getElementById('metricPopupForm').reset();
        applyMetricUpdate(data.metric);
        setTimeout(() => loadDashboard(), 250);
        return true;
    }

    msg.className = 'mt-2 text-xs text-rose-600';
    msg.textContent = data.message || 'حدث خطأ أثناء الحفظ';
    return false;
}

function openMetricPopup(type) {
    const config = metricConfig[type];
    if (!config) return;

    document.getElementById('popupTitle').textContent = config.title;
    const inputsContainer = document.getElementById('popupInputs');
    inputsContainer.innerHTML = '';

    config.fields.forEach((field) => {
        const input = document.createElement('input');
        input.className = 'form-input';
        input.type = 'number';
        input.name = field.key;
        input.step = field.step;
        input.placeholder = field.placeholder;
        input.required = true;
        if (field.key === 'mood_score') {
            input.min = '1';
            input.max = '10';
        }
        inputsContainer.appendChild(input);
    });

    document.getElementById('popupMsg').textContent = '';
    const popup = document.getElementById('metricPopup');
    popup.classList.remove('hidden');
    popup.classList.add('flex');
}

function closeMetricPopup() {
    const popup = document.getElementById('metricPopup');
    popup.classList.add('hidden');
    popup.classList.remove('flex');
}

document.querySelectorAll('.metric-fab').forEach((btn) => {
    btn.addEventListener('click', () => {
        btn.classList.add('clicked');
        setTimeout(() => btn.classList.remove('clicked'), 280);
        openMetricPopup(btn.dataset.type);
        document.getElementById('metricFabMenu').classList.remove('is-open');
    });
});

document.getElementById('metricFabToggle').addEventListener('click', () => {
    document.getElementById('metricFabMenu').classList.toggle('is-open');
});

document.addEventListener('click', (event) => {
    const menu = document.getElementById('metricFabMenu');
    if (!menu.classList.contains('is-open')) return;
    if (!menu.contains(event.target)) {
        menu.classList.remove('is-open');
    }
});

document.getElementById('popupClose').addEventListener('click', closeMetricPopup);

document.getElementById('metricPopup').addEventListener('click', (event) => {
    if (event.target.id === 'metricPopup') closeMetricPopup();
});

document.getElementById('metricPopupForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const payload = {};
    for (const [key, value] of formData.entries()) {
        if (value !== '') payload[key] = Number(value);
    }
    const success = await submitMetricPayload(payload);
    if (success) closeMetricPopup();
});

document.getElementById('toggleExtraCharts').addEventListener('click', () => {
    const section = document.getElementById('extraCharts');
    const btn = document.getElementById('toggleExtraCharts');
    const open = section.classList.contains('hidden');
    section.classList.toggle('hidden');
    btn.textContent = open ? 'إخفاء المقاييس الإضافية' : 'عرض مقاييس إضافية';
});

loadDashboard();
</script>
@endpush
