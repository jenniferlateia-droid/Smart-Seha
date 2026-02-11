@extends('layouts.app', ['title' => 'تحليل الطعام'])

@section('content')
<h1 class="page-title mb-6">تحليل صورة الطعام</h1>

<div class="grid gap-4 md:grid-cols-2">
    <div class="glass-card p-6">
        <h2 class="mb-4 text-lg font-bold">ارفع صورة الوجبة</h2>
        <div class="rounded-2xl border-2 border-dashed border-emerald-400 bg-emerald-50 p-6 text-center">
            <i class="fa-solid fa-cloud-arrow-up mb-2 text-4xl text-emerald-600"></i>
            <p class="text-sm text-slate-600">PNG / JPG / JPEG</p>
            <input id="foodImage" type="file" accept="image/*" class="form-input mt-3">
        </div>
        <button id="analyzeBtn" class="primary-btn mt-4 w-full">تحليل الصورة</button>
        <p id="analysisStatus" class="mt-3 text-sm text-slate-600"></p>
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-4 text-lg font-bold">نتائج التحليل</h2>
        <div class="space-y-3 text-sm">
            <div class="rounded-xl bg-slate-50 p-3"><strong>اسم الوجبة:</strong> <span id="foodName">--</span></div>
            <div class="rounded-xl bg-slate-50 p-3"><strong>السعرات:</strong> <span id="calories">--</span></div>
            <div class="rounded-xl bg-slate-50 p-3"><strong>العناصر الغذائية:</strong> <span id="nutrition">--</span></div>
            <div class="rounded-xl bg-slate-50 p-3"><strong>التقييم:</strong> <span id="evaluation">--</span></div>
            <div id="eatDecisionBox" class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <strong id="eatDecisionTitle">قرار الوجبة:</strong>
                <p id="eatDecisionMsg" class="mt-1 text-xs text-slate-600">--</p>
            </div>
        </div>
        <div class="mt-4">
            <canvas id="macroChart" height="200"></canvas>
        </div>
    </div>
</div>

<div class="glass-card mt-4 p-6">
    <h2 class="mb-4 text-lg font-bold">سجل تحاليل الطعام</h2>
    <div id="foodHistory" class="grid gap-3 md:grid-cols-2 lg:grid-cols-3"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const statusEl = document.getElementById('analysisStatus');
const analyzeBtn = document.getElementById('analyzeBtn');
let macroChart;

function setStatus(text, type = 'info') {
    const color = type === 'error' ? 'text-rose-600' : type === 'success' ? 'text-emerald-700' : 'text-slate-600';
    statusEl.className = `mt-3 text-sm ${color}`;
    statusEl.textContent = text;
}

function renderMacroChart(protein, carbs, fat) {
    if (macroChart) macroChart.destroy();
    macroChart = new Chart(document.getElementById('macroChart'), {
        type: 'doughnut',
        data: {
            labels: ['بروتين', 'كربوهيدرات', 'دهون'],
            datasets: [{
                data: [protein || 0, carbs || 0, fat || 0],
                backgroundColor: ['#2a9d8f', '#f4a261', '#e76f51'],
            }],
        },
    });
}

async function loadHistory() {
    const res = await fetch('/api/analyze-food-history', {
        method: 'GET',
        credentials: 'include',
        headers: { Accept: 'application/json' },
    });
    if (!res.ok) return;
    const payload = await res.json();
    const container = document.getElementById('foodHistory');
    container.innerHTML = '';
    (payload.history || []).forEach((item) => {
        const card = document.createElement('div');
        card.className = 'rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm';
        card.innerHTML = `
            <p class="text-xs text-slate-500">${item.date || '--'}</p>
            <p class="mt-1 font-bold text-slate-900">${item.foodName || 'وجبة غير محددة'}</p>
            <p class="text-slate-600">سعرات: ${item.calories ?? '--'} | P:${item.protein ?? '--'} C:${item.carbs ?? '--'} F:${item.fat ?? '--'}</p>
            <p class="mt-1 text-emerald-700">${item.evaluation || ''}</p>
        `;
        container.appendChild(card);
    });
}

async function pollAnalysis(url) {
    for (let i = 0; i < 25; i += 1) {
        const res = await fetch(url, { credentials: 'include', headers: { Accept: 'application/json' } });
        const payload = await res.json();

        if (payload.status === 'completed') {
            const d = payload.data || {};
            document.getElementById('foodName').textContent = d.foodName ?? '--';
            document.getElementById('calories').textContent = d.calories ?? '--';
            document.getElementById('nutrition').textContent = `بروتين ${d.protein ?? '--'}غ | كربوهيدرات ${d.carbs ?? '--'}غ | دهون ${d.fat ?? '--'}غ`;
            document.getElementById('evaluation').textContent = d.evaluation ?? d.rating ?? '--';

            const decision = d.eatDecision || {};
            const decisionBox = document.getElementById('eatDecisionBox');
            const decisionTitle = document.getElementById('eatDecisionTitle');
            const decisionMsg = document.getElementById('eatDecisionMsg');
            decisionTitle.textContent = decision.title || 'قرار الوجبة:';
            decisionMsg.textContent = decision.message || '--';
            decisionBox.className = 'rounded-xl border p-3 ' + (
                decision.status === 'warn'
                    ? 'border-rose-200 bg-rose-50 text-rose-800'
                    : decision.status === 'caution'
                        ? 'border-amber-200 bg-amber-50 text-amber-800'
                        : 'border-emerald-200 bg-emerald-50 text-emerald-800'
            );

            renderMacroChart(d.protein, d.carbs, d.fat);
            loadHistory();
            setStatus('اكتمل التحليل بنجاح', 'success');
            return;
        }

        if (payload.status === 'failed' || payload.status === 'rejected') {
            setStatus(payload.error || 'فشل التحليل', 'error');
            return;
        }

        setStatus('جاري التحليل الذكي للصورة...');
        await new Promise((resolve) => setTimeout(resolve, 1500));
    }

    setStatus('التحليل يحتاج وقت أطول. حاول بعد قليل.', 'error');
}

document.getElementById('analyzeBtn').addEventListener('click', async () => {
    const input = document.getElementById('foodImage');
    const file = input.files[0];

    if (!file) {
        setStatus('اختر صورة أولاً', 'error');
        return;
    }

    analyzeBtn.disabled = true;
    analyzeBtn.classList.add('opacity-60', 'cursor-not-allowed');
    analyzeBtn.textContent = 'جاري التحليل...';

    const fd = new FormData();
    fd.append('image', file);

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const res = await fetch('/api/analyze-food', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: fd,
    });

    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        setStatus(err.message || 'تعذر بدء التحليل', 'error');
        analyzeBtn.disabled = false;
        analyzeBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        analyzeBtn.textContent = 'تحليل الصورة';
        return;
    }

    const data = await res.json();
    setStatus('تم رفع الصورة، بدء التحليل...');
    await pollAnalysis(data.pollUrl);
    analyzeBtn.disabled = false;
    analyzeBtn.classList.remove('opacity-60', 'cursor-not-allowed');
    analyzeBtn.textContent = 'تحليل الصورة';
});

loadHistory();
</script>
@endpush
