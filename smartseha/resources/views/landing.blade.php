@extends('layouts.app', ['title' => 'Smart Seha'])

@section('content')
<style>
    .landing-shell {
        width: 100%;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #0b1220, #164f4b);
    }

    .landing-grid {
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255, 255, 255, .06) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .06) 1px, transparent 1px);
        background-size: 34px 34px;
        opacity: .45;
        mask-image: radial-gradient(circle at 50% 8%, black, transparent 85%);
        pointer-events: none;
    }

    .landing-scroll {
        position: relative;
        z-index: 6;
        scroll-behavior: smooth;
        scroll-snap-type: y proximity;
    }

    .snap-section {
        scroll-snap-align: start;
        min-height: 86vh;
        padding: 4.5rem 1.2rem;
        position: relative;
    }

    @media (min-width: 900px) {
        .snap-section {
            padding: 5.4rem 3.5rem;
        }
    }

    .section-inner {
        margin: 0 auto;
        width: 100%;
        max-width: 1220px;
    }

    .soft-card {
        border: 1px solid rgba(255, 255, 255, .12);
        background: rgba(255, 255, 255, .08);
        backdrop-filter: blur(9px);
        border-radius: 1rem;
    }

    .white-card {
        background: #ffffff;
        border-radius: 1rem;
        box-shadow: 0 12px 30px rgba(2, 6, 23, .14);
    }

    .orb {
        position: absolute;
        border-radius: 9999px;
        filter: blur(2px);
        opacity: .22;
        pointer-events: none;
    }

    .orb-a { width: 300px; height: 300px; background: #2a9d8f; top: -90px; left: -80px; animation: float 10s ease-in-out infinite; }
    .orb-b { width: 220px; height: 220px; background: #38b2a3; bottom: 7%; right: -70px; animation: float 8s ease-in-out infinite .7s; }
    .orb-c { width: 130px; height: 130px; background: #f4a261; top: 38%; right: 48%; animation: float 7s ease-in-out infinite 1.3s; }

    .reveal {
        opacity: 0;
        transform: translateY(28px);
        transition: opacity .7s cubic-bezier(.2,.9,.2,1), transform .7s cubic-bezier(.2,.9,.2,1);
    }

    .reveal.in-view {
        opacity: 1;
        transform: translateY(0);
    }

    .float-icon {
        animation: bob 4s ease-in-out infinite;
    }

    .timeline-line {
        position: absolute;
        top: 30px;
        bottom: 30px;
        right: 22px;
        width: 2px;
        background: linear-gradient(to bottom, #2a9d8f, #38b2a3, #f4a261);
        opacity: .4;
    }

    .progress-bar {
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        height: 4px;
        background: linear-gradient(90deg, #2a9d8f, #38b2a3, #f4a261);
        width: 0;
        z-index: 60;
        transition: width .08s linear;
    }

    .travel-stage {
        position: fixed;
        inset: 0;
        z-index: 1;
        pointer-events: none;
    }

    .travel-wrap {
        position: sticky;
        top: 16vh;
        height: 0;
    }

    .travel-node {
        position: absolute;
        width: 138px;
        height: 138px;
        border-radius: 9999px;
        background: radial-gradient(circle at 30% 30%, rgba(125,243,191,.42), rgba(42,157,143,.3) 60%, rgba(26,111,102,.18));
        box-shadow: 0 20px 40px rgba(9, 125, 97, .2), 0 0 0 10px rgba(125, 243, 191, .05);
        transform: translate3d(0,0,0);
        opacity: .28;
        mix-blend-mode: screen;
    }

    .travel-node::before {
        content: '';
        position: absolute;
        inset: -26px;
        border-radius: 9999px;
        border: 1px dashed rgba(164, 255, 220, .18);
        animation: spin 11s linear infinite;
    }

    .travel-node::after {
        content: none;
    }

    .travel-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 52px;
        color: rgba(220, 252, 231, .88);
        text-shadow: 0 0 24px rgba(167, 243, 208, .32);
        animation: pulseIcon 2.8s ease-in-out infinite;
    }

    @media (max-width: 900px) {
        .travel-stage { display: none; }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-14px); }
    }

    @keyframes bob {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0%,100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.25); opacity: .7; }
    }

    @keyframes pulseIcon {
        0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        50% { transform: translate(-50%, -50%) scale(1.16); opacity: .78; }
    }
</style>

<div class="landing-shell">
    <div class="progress-bar" id="scrollProgress"></div>
    <div class="landing-grid"></div>
    <div class="orb orb-a"></div>
    <div class="orb orb-b"></div>
    <div class="orb orb-c"></div>

    <div class="travel-stage" aria-hidden="true">
        <div class="travel-wrap">
            <div id="travelNode" class="travel-node">
                <i class="fa-solid fa-heart-pulse travel-icon"></i>
            </div>
        </div>
    </div>

    <div class="landing-scroll" id="landingScroll">
        <section id="hero" class="snap-section text-white" data-label="الانطلاقة">
            <div class="section-inner grid items-center gap-8 lg:grid-cols-2">
                <div class="reveal">
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-xs font-bold">
                        <i class="fa-solid fa-leaf"></i>
                        منصة صحة ذكية باللغة العربية
                    </p>
                    <h1 class="text-4xl font-black leading-tight md:text-6xl">Smart Seha
                        <span class="mt-2 block text-emerald-200">صحة أوضح. قرارات أسرع.</span>
                    </h1>
                    <p class="mt-5 text-sm leading-7 text-emerald-50/90 md:text-base">من تسجيل القياسات اليومية إلى خطط متابعة واقعية، كل شيء في مكان واحد بأسلوب عملي وسلس.</p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="rounded-xl bg-[linear-gradient(90deg,#2a9d8f,#38b2a3)] px-5 py-3 text-sm font-black text-white shadow-lg hover:-translate-y-0.5 transition">ابدأ الآن</a>
                        <a href="{{ route('login') }}" class="rounded-xl border border-white/35 bg-white/10 px-5 py-3 text-sm font-bold">تسجيل الدخول</a>
                    </div>
                </div>

                <div class="reveal soft-card p-5">
                    <div class="grid gap-3">
                        <div class="rounded-xl bg-white/10 p-4">
                            <p class="text-xs text-emerald-100">لوحة يومية</p>
                            <p class="mt-1 text-lg font-black">وزن • ضغط • سكر</p>
                        </div>
                        <div class="rounded-xl bg-white/10 p-4">
                            <p class="text-xs text-emerald-100">تحليل الطعام</p>
                            <p class="mt-1 text-lg font-black">نتائج تغذوية مباشرة</p>
                        </div>
                        <div class="rounded-xl bg-white/10 p-4">
                            <p class="text-xs text-emerald-100">اقتراحات دقيقة</p>
                            <p class="mt-1 text-lg font-black">AI يساعدك عند الحاجة</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="why" class="snap-section text-white" data-label="القيمة">
            <div class="section-inner">
                <div class="reveal mb-8 text-center">
                    <h2 class="text-3xl font-black">لماذا Smart Seha؟</h2>
                    <p class="mt-3 text-sm text-emerald-50/85">لأن الالتزام الصحي يحتاج وضوح وتجربة ممتعة، وليس تعقيد.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <article class="reveal soft-card p-5">
                        <i class="fa-solid fa-chart-line float-icon mb-3 text-2xl text-emerald-200"></i>
                        <h3 class="text-lg font-black">مؤشرات مفهومة</h3>
                        <p class="mt-2 text-sm text-emerald-50/85">تشوف وضعك الحالي بسرعة وتعرف أين تحتاج تركز اليوم.</p>
                    </article>
                    <article class="reveal soft-card p-5">
                        <i class="fa-solid fa-bullseye float-icon mb-3 text-2xl text-emerald-200"></i>
                        <h3 class="text-lg font-black">أهداف قابلة للتنفيذ</h3>
                        <p class="mt-2 text-sm text-emerald-50/85">مهام يومية قصيرة تساعدك تبني عادة مستمرة.</p>
                    </article>
                    <article class="reveal soft-card p-5">
                        <i class="fa-solid fa-shield-heart float-icon mb-3 text-2xl text-emerald-200"></i>
                        <h3 class="text-lg font-black">رحلة صحية آمنة</h3>
                        <p class="mt-2 text-sm text-emerald-50/85">واجهة عربية واضحة وإعدادات شخصية متكاملة.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="how" class="snap-section" data-label="الخطوات">
            <div class="section-inner">
                <div class="reveal mb-8 text-center text-white">
                    <h2 class="text-3xl font-black">كيف تعمل المنصة؟</h2>
                    <p class="mt-3 text-sm text-emerald-50/85">رحلة واضحة من أول تسجيل إلى متابعة يومية.</p>
                </div>

                <div class="white-card relative p-6 md:p-8">
                    <div class="timeline-line hidden md:block"></div>
                    <div class="grid gap-5">
                        <div class="reveal grid gap-3 md:grid-cols-[60px_1fr] md:items-start"><div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-black">1</div><div><h3 class="font-black text-slate-900">بناء الملف الصحي</h3><p class="text-sm text-slate-600 mt-1">العمر، الطول، الوزن، والهدف الصحي لتخصيص التجربة.</p></div></div>
                        <div class="reveal grid gap-3 md:grid-cols-[60px_1fr] md:items-start"><div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-black">2</div><div><h3 class="font-black text-slate-900">تسجيل القياسات اليومية</h3><p class="text-sm text-slate-600 mt-1">متابعة سهلة للوزن والضغط وسكر الدم مع تحديث لحظي.</p></div></div>
                        <div class="reveal grid gap-3 md:grid-cols-[60px_1fr] md:items-start"><div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-black">3</div><div><h3 class="font-black text-slate-900">تحليل الوجبات</h3><p class="text-sm text-slate-600 mt-1">تحميل صورة سريعة للحصول على نظرة غذائية واضحة.</p></div></div>
                        <div class="reveal grid gap-3 md:grid-cols-[60px_1fr] md:items-start"><div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-black">4</div><div><h3 class="font-black text-slate-900">توصيات ذكية + نقاط تحفيزية</h3><p class="text-sm text-slate-600 mt-1">قرارات يومية أفضل مع نظام مكافآت يحافظ على الاستمرارية.</p></div></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="ai" class="snap-section text-white" data-label="AI">
            <div class="section-inner grid items-center gap-6 lg:grid-cols-2">
                <div class="reveal soft-card p-6">
                    <p class="text-xs text-emerald-100">AI Layer</p>
                    <h2 class="mt-2 text-3xl font-black">الذكاء الاصطناعي في الأماكن المهمة</h2>
                    <p class="mt-3 text-sm leading-7 text-emerald-50/90">يتم استخدام الذكاء الاصطناعي عند تحليل الطعام وبناء التوصيات الشخصية، حتى تكون الخطوة القادمة أوضح وأسهل.</p>
                    <ul class="mt-4 space-y-2 text-sm text-emerald-50/90">
                        <li><i class="fa-solid fa-check ml-2 text-emerald-200"></i>تلخيص غذائي عملي للصورة</li>
                        <li><i class="fa-solid fa-check ml-2 text-emerald-200"></i>اقتراحات متوازنة حسب الهدف</li>
                        <li><i class="fa-solid fa-check ml-2 text-emerald-200"></i>تجربة سلسة مع fallback ذكي</li>
                    </ul>
                </div>
                <div class="reveal grid gap-4">
                    <div class="soft-card p-5"><p class="text-xs text-emerald-100">Smart Insight</p><p class="mt-1 text-lg font-black">"اليوم ممتاز لتعديل وجبة العشاء"</p></div>
                    <div class="soft-card p-5"><p class="text-xs text-emerald-100">Food Snapshot</p><p class="mt-1 text-lg font-black">"توازن جيد.. خفف السكر المضاف"</p></div>
                    <div class="soft-card p-5"><p class="text-xs text-emerald-100">Action Prompt</p><p class="mt-1 text-lg font-black">"امش 20 دقيقة بعد الوجبة"</p></div>
                </div>
            </div>
        </section>

        <section id="plans" class="snap-section" data-label="الخطط">
            <div class="section-inner">
                <div class="reveal mb-8 text-center text-white">
                    <h2 class="text-3xl font-black">خطط مناسبة لكل هدف</h2>
                    <p class="mt-3 text-sm text-emerald-50/85">خسارة وزن، زيادة وزن، أو نمط حياة متوازن.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <article class="reveal white-card p-5"><div class="mb-3 inline-flex rounded-lg bg-emerald-100 p-3 text-emerald-700"><i class="fa-solid fa-arrow-trend-down"></i></div><h3 class="text-lg font-black text-slate-900">خسارة الوزن</h3><p class="mt-2 text-sm text-slate-600">تدرج محسوب في السعرات مع نشاط يومي مناسب.</p></article>
                    <article class="reveal white-card p-5"><div class="mb-3 inline-flex rounded-lg bg-emerald-100 p-3 text-emerald-700"><i class="fa-solid fa-dumbbell"></i></div><h3 class="text-lg font-black text-slate-900">زيادة الوزن</h3><p class="mt-2 text-sm text-slate-600">تركيز على الكثافة الغذائية وتمارين المقاومة.</p></article>
                    <article class="reveal white-card p-5"><div class="mb-3 inline-flex rounded-lg bg-emerald-100 p-3 text-emerald-700"><i class="fa-solid fa-seedling"></i></div><h3 class="text-lg font-black text-slate-900">نمط حياة صحي</h3><p class="mt-2 text-sm text-slate-600">ثبات يومي وبناء عادات تدوم على المدى البعيد.</p></article>
                </div>
            </div>
        </section>

        <section id="developers" class="snap-section" data-label="Developers">
            <div class="section-inner">
                <div class="reveal mb-8 text-center text-white">
                    <h2 class="text-3xl font-black">Developers</h2>
                    <p class="mt-3 text-sm text-emerald-50/85">فريق تنفيذ مشروع Smart Seha</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <article class="reveal white-card p-5">
                        <h3 class="text-lg font-black text-slate-900">ساره محمد مازن اسبير</h3>
                        <p class="mt-2 text-sm text-slate-600">Student Number: 152385</p>
                    </article>
                    <article class="reveal white-card p-5">
                        <h3 class="text-lg font-black text-slate-900">ماري طلال اسبر</h3>
                        <p class="mt-2 text-sm text-slate-600">Student Number: 128636</p>
                    </article>
                    <article class="reveal white-card p-5">
                        <h3 class="text-lg font-black text-slate-900">رجاء احمد عبد الجواد</h3>
                        <p class="mt-2 text-sm text-slate-600">Student Number: 162658</p>
                    </article>
                    <article class="reveal white-card p-5">
                        <h3 class="text-lg font-black text-slate-900">جينفير انطون لاطيه</h3>
                        <p class="mt-2 text-sm text-slate-600">Student Number: 129266</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="cta" class="snap-section text-white" data-label="ابدأ الآن">
            <div class="section-inner max-w-4xl text-center">
                <div class="reveal soft-card p-8 md:p-10">
                    <h2 class="text-3xl font-black md:text-4xl">جاهز تبدأ رحلتك الصحية؟</h2>
                    <p class="mt-3 text-sm leading-7 text-emerald-50/90">سجل اليوم، وخل بياناتك الصحية في منصة واحدة تساعدك تتحسن يوم بعد يوم.</p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('register') }}" class="rounded-xl bg-[linear-gradient(90deg,#2a9d8f,#38b2a3)] px-6 py-3 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5">إنشاء حساب</a>
                        <a href="{{ route('login') }}" class="rounded-xl border border-white/35 bg-white/10 px-6 py-3 text-sm font-bold">تسجيل الدخول</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const reveals = document.querySelectorAll('.reveal');
    const progress = document.getElementById('scrollProgress');
    const node = document.getElementById('travelNode');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) entry.target.classList.add('in-view');
        });
    }, { threshold: 0.14 });

    reveals.forEach((el) => observer.observe(el));

    const keyframes = [
        { p: 0.00, x: 92, y: 6, s: .94, r: 0 },
        { p: 0.14, x: 70, y: 14, s: .93, r: 10 },
        { p: 0.28, x: 34, y: 24, s: .90, r: -12 },
        { p: 0.44, x: 76, y: 36, s: .93, r: 12 },
        { p: 0.62, x: 28, y: 46, s: .91, r: -10 },
        { p: 0.80, x: 66, y: 56, s: .94, r: 8 },
        { p: 0.92, x: 56, y: 60, s: .96, r: 4 },
        { p: 1.00, x: 50, y: 64, s: .97, r: 0 },
    ];

    const lerp = (a, b, t) => a + (b - a) * t;

    const sampleFrame = (p) => {
        for (let i = 0; i < keyframes.length - 1; i += 1) {
            const a = keyframes[i];
            const b = keyframes[i + 1];
            if (p >= a.p && p <= b.p) {
                const t = (p - a.p) / (b.p - a.p || 1);
                return {
                    x: lerp(a.x, b.x, t),
                    y: lerp(a.y, b.y, t),
                    s: lerp(a.s, b.s, t),
                    r: lerp(a.r, b.r, t),
                };
            }
        }
        return keyframes[keyframes.length - 1];
    };

    let target = { x: 92, y: 12, s: .94, r: 0 };
    let current = { x: 92, y: 12, s: .94, r: 0 };

    const update = () => {
        const h = document.documentElement;
        const max = h.scrollHeight - h.clientHeight;
        const p = max > 0 ? h.scrollTop / max : 0;
        progress.style.width = `${Math.max(0, Math.min(1, p)) * 100}%`;

        if (node) {
            target = sampleFrame(p);
        }
    };

    const animate = () => {
        if (node) {
            const easing = 0.04;
            current.x = lerp(current.x, target.x, easing);
            current.y = lerp(current.y, target.y, easing);
            current.s = lerp(current.s, target.s, easing);
            current.r = lerp(current.r, target.r, easing);

            node.style.left = `${current.x}%`;
            node.style.top = `${current.y}vh`;
            node.style.transform = `translate(-50%, -50%) scale(${current.s}) rotate(${current.r}deg)`;
        }
        requestAnimationFrame(animate);
    };

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
    animate();
})();
</script>
@endpush
