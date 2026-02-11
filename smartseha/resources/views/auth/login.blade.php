@extends('layouts.app', ['title' => 'تسجيل الدخول'])

@section('content')
<div class="glass-card w-full max-w-[460px] p-10">
    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex h-18 w-18 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#2a9d8f,#f4a261)] text-3xl text-white shadow-lg">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900">Smart Seha</h1>
        <p class="mt-1 text-sm text-slate-500">تسجيل الدخول إلى حسابك</p>
    </div>

    <form id="loginForm" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold">البريد الإلكتروني</label>
            <input id="email" type="email" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">كلمة المرور</label>
            <input id="password" type="password" class="form-input" required>
        </div>

        <button id="submitBtn" type="submit" class="primary-btn w-full">تسجيل الدخول</button>
        <p id="loginError" class="hidden text-sm font-semibold text-rose-600"></p>
    </form>

    <p class="mt-5 text-center text-sm text-slate-600">لا تملك حسابًا؟ <a class="font-bold text-emerald-700" href="{{ route('register') }}">إنشاء حساب</a></p>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const errorEl = document.getElementById('loginError');

    errorEl.classList.add('hidden');

    const res = await fetch('/api/login', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({ email, password }),
    });

    const payload = await res.json();

    if (!res.ok) {
        errorEl.textContent = payload.message || 'فشل تسجيل الدخول';
        errorEl.classList.remove('hidden');
        return;
    }

    window.location.href = payload.redirect || '/dashboard';
});
</script>
@endpush
