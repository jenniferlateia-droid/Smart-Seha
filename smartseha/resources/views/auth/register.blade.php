@extends('layouts.app', ['title' => 'إنشاء حساب'])

@section('content')
<div class="glass-card w-full max-w-[460px] p-10">
    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex h-18 w-18 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#2a9d8f,#f4a261)] text-3xl text-white shadow-lg">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900">انضم إلينا</h1>
        <p class="mt-1 text-sm text-slate-500">ابدأ رحلتك نحو حياة صحية وذكية</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold">الاسم الكامل</label>
            <input name="name" value="{{ old('name') }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">البريد الإلكتروني</label>
            <input name="email" type="email" value="{{ old('email') }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">كلمة المرور</label>
            <input name="password" type="password" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">تأكيد كلمة المرور</label>
            <input name="password_confirmation" type="password" class="form-input" required>
        </div>

        <button type="submit" class="primary-btn w-full">إنشاء الحساب</button>
    </form>

    <p class="mt-5 text-center text-sm text-slate-600">لديك حساب؟ <a class="font-bold text-emerald-700" href="{{ route('login') }}">تسجيل الدخول</a></p>
</div>
@endsection
