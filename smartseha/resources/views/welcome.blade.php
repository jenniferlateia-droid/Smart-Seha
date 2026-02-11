@extends('layouts.app', ['title' => 'Smart Seha'])

@section('content')
<div class="mx-auto max-w-3xl rounded-2xl bg-white p-8 shadow-lg">
    <h1 class="text-3xl font-black text-slate-900">Smart Seha</h1>
    <p class="mt-3 text-slate-600">مرحباً بك في منصة Smart Seha.</p>
    <div class="mt-6">
        <a href="{{ route('landing') }}" class="primary-btn inline-block">الانتقال إلى الصفحة الرئيسية</a>
    </div>
</div>
@endsection
