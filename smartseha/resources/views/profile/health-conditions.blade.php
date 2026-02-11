@extends('layouts.app', ['title' => 'القياسات الصحية'])

@section('content')
<div class="mx-auto w-full max-w-[560px] glass-card p-8">
    <div class="mb-6 text-center">
        <h1 class="text-3xl font-black text-slate-900">القياسات الصحية</h1>
        <p class="text-sm text-slate-500">أدخل مؤشراتك الصحية لليوم</p>
    </div>

    <form method="POST" action="{{ route('profile.health.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold">الوزن (كغ)</label>
            <input name="weight" type="number" step="0.1" min="20" max="300" value="{{ old('weight', $latest?->weight) }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الضغط الانقباضي</label>
            <input name="systolic" type="number" min="0" max="300" value="{{ old('systolic', $latest?->systolic) }}" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الضغط الانبساطي</label>
            <input name="diastolic" type="number" min="0" max="200" value="{{ old('diastolic', $latest?->diastolic) }}" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">سكر الدم</label>
            <input name="blood_sugar" type="number" step="0.1" min="0" max="600" value="{{ old('blood_sugar', $latest?->blood_sugar) }}" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">عدد الخطوات</label>
            <input name="steps" type="number" min="0" max="100000" value="{{ old('steps', $latest?->steps) }}" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">شرب الماء (لتر)</label>
            <input name="water_intake_liters" type="number" step="0.1" min="0" max="20" value="{{ old('water_intake_liters', $latest?->water_intake_liters) }}" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">ساعات النوم</label>
            <input name="sleep_hours" type="number" step="0.5" min="0" max="24" value="{{ old('sleep_hours', $latest?->sleep_hours) }}" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">دقائق التمرين</label>
            <input name="exercise_minutes" type="number" min="0" max="600" value="{{ old('exercise_minutes', $latest?->exercise_minutes) }}" class="form-input">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">المزاج (1-10)</label>
            <input name="mood_score" type="number" min="1" max="10" value="{{ old('mood_score', $latest?->mood_score) }}" class="form-input">
        </div>

        <button type="submit" class="primary-btn w-full">حفظ ومتابعة</button>
    </form>
</div>
@endsection
