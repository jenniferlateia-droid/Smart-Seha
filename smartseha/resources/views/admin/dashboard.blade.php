@extends('layouts.app', ['title' => 'لوحة تحكم الإدارة'])

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="page-title">لوحة تحكم الإدارة</h1>
    <span class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white">Admin</span>
</div>

<div class="mb-6 grid gap-4 md:grid-cols-2">
    <div class="glass-card p-5">
        <p class="text-sm text-slate-500">إجمالي المستخدمين</p>
        <p class="mt-2 text-3xl font-black text-emerald-700">{{ $stats['total_users'] }}</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-sm text-slate-500">عدد المشرفين</p>
        <p class="mt-2 text-3xl font-black text-emerald-700">{{ $stats['admin_users'] }}</p>
    </div>
</div>

<div class="glass-card p-6 mb-6">
    <h2 class="mb-4 text-lg font-black">إعدادات النظام والذكاء الاصطناعي</h2>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-4 md:grid-cols-2">
        @csrf

        <div>
            <label class="mb-1 block text-sm font-semibold">اسم الموقع</label>
            <input name="site_name" value="{{ $settings['site_name'] }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">موديل AI</label>
            <select name="ai_model" class="form-input" required>
                @foreach (['gpt-4o-mini', 'gpt-4.1-mini', 'gpt-4o'] as $model)
                    <option value="{{ $model }}" @selected($settings['ai_model'] === $model)>{{ $model }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-semibold">AI API Key (اختياري للتحديث)</label>
            <input type="password" name="ai_api_key" class="form-input" placeholder="sk-...">
        </div>

        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm font-semibold">
                <input type="hidden" name="ai_enabled" value="0">
                <input type="checkbox" name="ai_enabled" value="1" @checked($settings['ai_enabled'])>
                تفعيل ميزات AI
            </label>
            <p class="mt-1 text-xs text-slate-500">
                عند التفعيل: النظام يستخدم نموذج الذكاء الاصطناعي لتحليل صور الطعام وإنشاء توصيات صحية أكثر تخصيصًا حسب بيانات المستخدم.
                عند التعطيل: النظام يعمل بقواعد داخلية ثابتة بدون استدعاء AI، وتظل المنصة تعمل بشكل طبيعي لكن بدقة تخصيص أقل.
            </p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">نقاط استكمال الملف</label>
            <input type="number" name="points_profile" value="{{ $settings['points_profile'] }}" class="form-input" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold">نقاط إدخال القياسات</label>
            <input type="number" name="points_health" value="{{ $settings['points_health'] }}" class="form-input" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold">نقاط التوصيات</label>
            <input type="number" name="points_recommendation" value="{{ $settings['points_recommendation'] }}" class="form-input" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold">نقاط تحليل الوجبة</label>
            <input type="number" name="points_food" value="{{ $settings['points_food'] }}" class="form-input" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">مستوى Silver</label>
            <input type="number" name="level_silver" value="{{ $settings['level_silver'] }}" class="form-input" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold">مستوى Gold</label>
            <input type="number" name="level_gold" value="{{ $settings['level_gold'] }}" class="form-input" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold">مستوى Platinum</label>
            <input type="number" name="level_platinum" value="{{ $settings['level_platinum'] }}" class="form-input" required>
        </div>

        <div class="md:col-span-2">
            <button class="primary-btn w-full">حفظ إعدادات النظام</button>
        </div>
    </form>
</div>

<div class="glass-card p-6">
    <h2 class="mb-4 text-lg font-black">إدارة المستخدمين</h2>

    <div class="overflow-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b bg-slate-50 text-right text-slate-500">
                    <th class="p-3">ID</th>
                    <th class="p-3">الاسم</th>
                    <th class="p-3">البريد</th>
                    <th class="p-3">الدور</th>
                    <th class="p-3">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b">
                        <td class="p-3">{{ $user->id }}</td>
                        <td class="p-3">{{ $user->name }}</td>
                        <td class="p-3">{{ $user->email }}</td>
                        <td class="p-3">{{ $user->is_admin ? 'Admin' : 'User' }}</td>
                        <td class="p-3">
                            @if (auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('admin.users.role', $user) }}">
                                    @csrf
                                    <input type="hidden" name="is_admin" value="{{ $user->is_admin ? 0 : 1 }}">
                                    <button class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white">
                                        {{ $user->is_admin ? 'إزالة الإدارة' : 'جعله Admin' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
