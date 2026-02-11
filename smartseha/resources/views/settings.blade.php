@extends('layouts.app', ['title' => 'الإعدادات'])

@section('content')
<h1 class="page-title mb-6">الإعدادات الشخصية</h1>

<div class="mb-5 flex flex-wrap items-center gap-4 rounded-3xl bg-[linear-gradient(135deg,#2a9d8f,#38b2a3)] p-6 text-white shadow-lg">
    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-white/20 text-4xl"><i class="fa-solid fa-circle-user"></i></div>
    <div>
        <p class="text-xl font-black" id="displayName">المستخدم</p>
        <p class="text-sm text-white/90" id="displayEmail">--</p>
    </div>
</div>

<div class="glass-card p-6">
    <form id="settingsForm" class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-semibold">الاسم</label>
            <input id="name" class="form-input" readonly>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">البريد الإلكتروني</label>
            <input id="email" class="form-input" readonly>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">العمر</label>
            <input id="age" type="number" min="10" max="100" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الجنس</label>
            <select id="gender" class="form-input" required>
                <option value="male">ذكر</option>
                <option value="female">أنثى</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الطول</label>
            <input id="height" type="number" min="100" max="250" step="0.1" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الوزن</label>
            <input id="weight" type="number" min="20" max="300" step="0.1" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الهدف</label>
            <select id="goal" class="form-input" required>
                <option value="lose">إنقاص الوزن</option>
                <option value="gain">زيادة الوزن</option>
                <option value="lifestyle">نمط صحي</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الضغط الانقباضي</label>
            <input id="systolic" type="number" min="0" max="300" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الضغط الانبساطي</label>
            <input id="diastolic" type="number" min="0" max="200" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">سكر الدم</label>
            <input id="bloodSugar" type="number" min="0" max="600" step="0.1" class="form-input">
        </div>

        <div class="md:col-span-2">
            <button type="submit" class="primary-btn w-full">حفظ التغييرات</button>
        </div>
    </form>

    <p id="settingsMsg" class="mt-3 hidden text-sm"></p>
</div>
@endsection

@push('scripts')
<script>
async function loadSettings() {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/api/settings', {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
    });

    if (!res.ok) return;
    const data = await res.json();

    Object.entries({
        name: data.name,
        email: data.email,
        age: data.age,
        gender: data.gender,
        height: data.height,
        weight: data.weight,
        goal: data.goal,
        systolic: data.systolic,
        diastolic: data.diastolic,
        bloodSugar: data.bloodSugar,
    }).forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el && value !== null && value !== undefined) el.value = value;
    });

    document.getElementById('displayName').textContent = data.name || 'المستخدم';
    document.getElementById('displayEmail').textContent = data.email || '--';
}

document.getElementById('settingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const payload = {
        age: Number(document.getElementById('age').value),
        gender: document.getElementById('gender').value,
        height: Number(document.getElementById('height').value),
        weight: Number(document.getElementById('weight').value),
        goal: document.getElementById('goal').value,
        systolic: document.getElementById('systolic').value || null,
        diastolic: document.getElementById('diastolic').value || null,
        bloodSugar: document.getElementById('bloodSugar').value || null,
    };

    const res = await fetch('/api/settings', {
        method: 'PUT',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify(payload),
    });

    const msg = document.getElementById('settingsMsg');
    msg.classList.remove('hidden', 'text-rose-600', 'text-emerald-600');

    if (!res.ok) {
        msg.classList.add('text-rose-600');
        msg.textContent = 'فشل حفظ الإعدادات';
        return;
    }

    msg.classList.add('text-emerald-600');
    msg.textContent = 'تم حفظ الإعدادات بنجاح';
});

loadSettings();
</script>
@endpush
