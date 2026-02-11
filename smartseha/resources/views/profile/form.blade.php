@extends('layouts.app', ['title' => 'البيانات الصحية'])

@section('content')
<div class="mx-auto w-full max-w-[560px] glass-card p-8">
    <div class="mb-6 text-center">
        <h1 class="text-3xl font-black text-slate-900">أكمل بياناتك الصحية</h1>
        <p class="text-sm text-slate-500">هذه البيانات تساعدنا على تقديم توصيات أدق</p>
    </div>

    <form method="POST" action="{{ route('profile.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold">العمر</label>
            <input name="age" type="number" min="10" max="100" value="{{ old('age', $profile?->age) }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الجنس</label>
            <select name="gender" class="form-input" required>
                <option value="">اختر</option>
                <option value="male" @selected(old('gender', $profile?->gender) === 'male')>ذكر</option>
                <option value="female" @selected(old('gender', $profile?->gender) === 'female')>أنثى</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الطول (سم)</label>
            <input name="height" type="number" step="0.1" min="100" max="250" value="{{ old('height', $profile?->height) }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الوزن (كغ)</label>
            <input name="weight" type="number" step="0.1" min="20" max="300" value="{{ old('weight', $profile?->weight) }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الهدف الصحي</label>
            <select name="goal" class="form-input" required>
                <option value="">اختر</option>
                <option value="lose" @selected(old('goal', $profile?->goal) === 'lose')>إنقاص الوزن</option>
                <option value="gain" @selected(old('goal', $profile?->goal) === 'gain')>زيادة الوزن</option>
                <option value="lifestyle" @selected(old('goal', $profile?->goal) === 'lifestyle')>نمط حياة صحي</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">الوزن المستهدف (اختياري)</label>
            <input name="target_weight" type="number" step="0.1" min="20" max="300" value="{{ old('target_weight', $profile?->target_weight) }}" class="form-input">
        </div>

        <button type="submit" class="primary-btn w-full">التالي</button>
    </form>
</div>
@endsection
