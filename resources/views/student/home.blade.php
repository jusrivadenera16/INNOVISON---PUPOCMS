@extends('layouts.student')

@section('title', 'Home')

@push('styles')
<style>
    /* --- CRITICAL FIX: REMOVE TOP PADDING FOR HOME PAGE ONLY --- */
    main { padding-top: 0 !important; }

    /* --- HERO SECTION STYLES --- */
    .PUPBG { position:relative; min-height:520px; display:flex; align-items:center; }
    .PUPBG::before {
        content:""; position:absolute; inset:0;
        /* Background Image with Overlay */
        background: linear-gradient(180deg,rgba(15,27,38,0.72),rgba(15,27,38,0.72)), url('{{ asset("images/PUPBG.jpg") }}') center/cover no-repeat;
        z-index:0;
    }
    .PUPBG-overlay { position:absolute; inset:0; background:linear-gradient(180deg,rgba(0,0,0,0.15),rgba(0,0,0,0.45)); z-index:1; }
    .PUPBG-inner { position:relative; z-index:2; padding:90px 0; color:#fff; text-align:center; }
    
    .kicker { letter-spacing:2px; font-weight:700; margin:0 0 12px 0; opacity:0.95; color:#ff7b73; }
    .PUPBG-title { font-size:56px; margin:6px 0 18px 0; line-height:1.05; font-weight:800; text-shadow:0 2px 10px rgba(0,0,0,0.5); color: #fff; }
    .PUPBG-lead { color:#bfcfd6; margin:0 auto 28px; max-width:720px; font-size:18px; }

    .hero-actions { display:flex; gap:16px; justify-content:center; margin-top:18px; }
    .btn { position:relative; overflow:hidden; display:inline-flex; align-items:center; justify-content:center; gap:10px; padding:12px 24px; border-radius:8px; text-decoration:none; font-weight:700; cursor: pointer; border: none; transition:transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .btn::after {
        content:"";
        position:absolute;
        inset:0;
        background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.18) 28%, rgba(255,255,255,.58) 50%, rgba(255,255,255,.18) 72%, transparent 100%);
        transform:translateX(-135%);
        transition:transform .72s ease;
        pointer-events:none;
    }
    .btn:hover::after,
    .btn:focus-visible::after { transform:translateX(135%); }
    .btn svg { width:18px; height:18px; flex:0 0 auto; stroke-width:1.8; }
    .btn-primary { background:#8B0000; color:#fff; border:2px solid rgba(0,0,0,0.08); box-shadow:0 6px 18px rgba(0,0,0,0.25); }
    .btn-secondary { background:#fff; color:#15222a; border:0; }
    .btn-primary:hover,
    .btn-primary:focus-visible,
    .btn-secondary:hover,
    .btn-secondary:focus-visible {
        background:#facc15;
        color:#8B0000;
        border-color:#facc15;
        transform:translateY(-2px);
        box-shadow:0 12px 28px rgba(139,0,0,.22);
        outline:none;
    }

    /* --- WELCOME SECTION --- */
    .welcome { padding:64px 0; background:#fff; }
    .welcome-inner { display:flex; gap:40px; align-items:center; }
    .welcome-text { flex:1; }
    .welcome-text h2 { margin-top:0; font-size: 28px; color: #2d3748; }
    .welcome-text p { color:#666; line-height:1.6; }
    .btn-outline { position:relative; overflow:hidden; display:inline-flex; align-items:center; gap:10px; padding:10px 14px; border-radius:8px; border:1px solid #8B0000; color:#8B0000; text-decoration:none; margin-top:14px; transition:transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .btn-outline::after {
        content:"";
        position:absolute;
        inset:0;
        background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.16) 28%, rgba(255,255,255,.55) 50%, rgba(255,255,255,.16) 72%, transparent 100%);
        transform:translateX(-135%);
        transition:transform .72s ease;
        pointer-events:none;
    }
    .btn-outline:hover,
    .btn-outline:focus-visible {
        background:#facc15;
        border-color:#facc15;
        color:#8B0000;
        transform:translateY(-2px);
        box-shadow:0 10px 24px rgba(139,0,0,.16);
        outline:none;
    }
    .btn-outline:hover::after,
    .btn-outline:focus-visible::after { transform:translateX(135%); }
    .btn-outline svg { width:18px; height:18px; flex:0 0 auto; stroke-width:1.8; }
    .welcome-art { width:320px; flex:0 0 320px; }

    /* --- TESTIMONIALS / COMMENTS --- */
    .comments-section { padding:56px 0; background:#f3f6f5; }
    .comments-section .section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:22px; }
    .comments-section h3 { margin:0; font-size:22px; color: #2d3748; }
    .comments-section p.lead { margin:6px 0 0; color:#5b6d70; }
    .feedback-more {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        background: #ffffff;
        border: 1px solid #ead7d7;
        color: #8B0000;
        box-shadow: 0 8px 20px rgba(16,24,28,0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .feedback-more svg { width:18px; height:18px; stroke-width:2; }
    .feedback-more:hover {
        transform: translateX(2px);
        background: #fff7f7;
        box-shadow: 0 14px 28px rgba(16,24,28,0.10);
    }
    
    .comments-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:18px; }
    .comment-card { background:#ffffff; padding:18px; border-radius:12px; box-shadow:0 8px 28px rgba(16,24,28,0.06); display:flex; gap:12px; align-items:flex-start; transition:transform 220ms ease; }
    .comment-card:hover { transform:translateY(-8px); box-shadow:0 20px 48px rgba(16,24,28,0.14); }
    
    .avatar { width:56px; height:56px; border-radius:50%; flex:0 0 56px; overflow:hidden; fill: #cbd5e0; }
    .comment-body { flex:1; }
    .comment-body h4 { margin:0; font-size:15px; color: #2d3748; }
    .comment-meta { color:#7f8b8e; font-size:13px; font-weight: normal; }
    .comment-body p { margin:10px 0 0; color:#345; line-height:1.5; font-size: 14px; }
    .comment-footer { display:flex; align-items:center; gap:8px; margin-top:12px; }
    .comment-chip { background:rgba(15,27,38,0.04); padding:6px 10px; border-radius:999px; font-size:13px; color:#334; }

    /* --- FOOTER STYLES --- */
    .site-footer { background:#0f1b26; color:#c9d6df; padding:48px 0 20px; font-size:15px; margin-top: 0; }
    .footer-grid { display:grid; grid-template-columns:1.4fr 1fr 1fr 1fr; gap:32px; align-items:start; padding-bottom:18px; }
    
    .brand { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
    .brand-logo img { width:56px; height:56px; border-radius:50%; object-fit:cover; border: 2px solid #8B0000; }
    .brand-name { font-weight:700; color:#fff; }
    .brand-sub { display:block; font-size:12px; color:#91a0ad; font-weight:600; }
    .brand-desc { color:#9fb0bd; max-width:420px; line-height:1.6; margin:12px 0; }
    
    .social { display:flex; gap:10px; margin-top:8px; }
    .social-link { width:40px; height:40px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.18); background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.92); transition:background 0.2s ease, transform 0.2s ease, border-color 0.2s ease; }
    .social-link:hover { background:rgba(255,255,255,0.12); border-color:rgba(255,255,255,0.32); transform:translateY(-1px); }
    .social-link svg { width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:1.8; }

    .site-footer h4 { color:#fff; margin:0 0 12px 0; font-size:16px; }
    .footer-links { list-style:none; padding:0; margin:0; }
    .footer-links li { margin:10px 0; color:#b9c8d2; }
    .footer-links a { color:#b9c8d2; text-decoration:none; display:inline-flex; align-items:center; gap:10px; }
    .footer-links a:hover { color:#fff; text-decoration:underline; }
    .footer-link-icon { width:18px; height:18px; flex:0 0 auto; stroke-width:1.8; }

    .contact-list { list-style:none; padding:0; margin:0; }
    .contact-list li { display:flex; align-items:flex-start; gap:12px; color:#b9c8d2; margin:12px 0; line-height:1.4; }
    .contact-icon { width:20px; height:20px; stroke:#ffb3a9; fill:none; stroke-width:1.8; flex:0 0 20px; margin-top:4px; }
    .footer-bottom { border-top:1px solid rgba(255,255,255,0.04); padding-top:22px; text-align:center; color:rgba(255,255,255,0.6); font-size:14px; margin-top:18px; }

    .learn-more-modal {
        display:none;
        position:fixed;
        inset:0;
        z-index:1200;
        align-items:center;
        justify-content:center;
        padding:24px;
        background:transparent;
        backdrop-filter:none;
        -webkit-backdrop-filter:none;
        overflow-y:auto;
        pointer-events:none;
    }
    html.learn-more-open,
    body.learn-more-open {
        overflow:hidden;
    }
    .learn-more-modal.is-open { display:flex; }
    .learn-more-card {
        width:min(560px,100%);
        overflow:hidden;
        border-radius:18px;
        background:#fff;
        border-top:4px solid #facc15;
        border-bottom:4px solid #facc15;
        box-shadow:
            0 28px 80px rgba(15, 23, 42, 0.34),
            0 0 0 1px rgba(112, 19, 27, 0.08),
            0 18px 36px rgba(0, 0, 0, 0.18);
        pointer-events:auto;
    }
    .learn-more-head {
        display:flex;
        justify-content:space-between;
        gap:16px;
        padding:20px 22px;
        background:linear-gradient(135deg,#70131B,#8f2230);
        color:#fff;
    }
    .learn-more-head h3 { margin:0; color:#fff; font-size:22px; font-weight:900; }
    .learn-more-head p { margin:5px 0 0; color:rgba(255,255,255,.78); font-size:13px; line-height:1.5; }
    .learn-more-close {
        position:relative;
        overflow:hidden;
        isolation:isolate;
        width:42px;
        height:42px;
        border-radius:999px;
        border:1px solid rgba(255,255,255,.24);
        background:rgba(255,255,255,.12);
        color:#fff;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        transition:background .22s ease, border-color .22s ease, color .22s ease, transform .22s ease, box-shadow .22s ease;
    }
    .learn-more-close::before {
        content:"";
        position:absolute;
        inset:0;
        background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.14) 28%, rgba(255,255,255,.5) 50%, rgba(255,255,255,.14) 72%, transparent 100%);
        transform:translateX(-140%);
        transition:transform .72s ease;
        z-index:-1;
        pointer-events:none;
    }
    .learn-more-close:hover,
    .learn-more-close:focus-visible {
        background:#5e0f17;
        border-color:#facc15;
        color:#fff;
        transform:translateY(-1px);
        box-shadow:0 12px 24px rgba(94,15,23,.26);
        outline:none;
    }
    .learn-more-close:hover::before,
    .learn-more-close:focus-visible::before {
        transform:translateX(140%);
    }
    .learn-more-close svg { width:18px; height:18px; }
    .learn-more-body {
        display:grid;
        gap:12px;
        padding:22px;
    }
    .learn-more-info {
        padding:14px 16px;
        border-radius:14px;
        border:1px solid rgba(112,19,27,.12);
        background:#fffaf7;
        color:#334155;
        line-height:1.65;
    }
    html[data-theme="dark"] .learn-more-card { background:#111827; }
    html[data-theme="dark"] .learn-more-info {
        background:rgba(31,41,55,.92);
        border-color:rgba(250,204,21,.18);
        color:#f8fafc;
    }

   
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }


    @media (max-width:900px){
        .comments-grid { grid-template-columns:repeat(2,1fr); }
        .footer-grid { grid-template-columns:repeat(2,1fr); }
        .PUPBG-title { font-size:36px; }
    }
    @media (max-width:600px){
        .comments-grid { grid-template-columns:1fr; }
        .footer-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')
    <svg style="display: none;">
      <symbol id="avatar-placeholder" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="12" fill="#e2e8f0"/>
        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z" fill="#cbd5e0"/>
      </symbol>
    </svg>

    <section class="PUPBG">
      <div class="PUPBG-overlay"></div>
      <div class="container PUPBG-inner">
        <p class="kicker">PUP TAGUIG HEALTH SERVICES</p>
        <h1 class="PUPBG-title">Your Health, Our Priority</h1>
        <p class="PUPBG-lead">Access quality healthcare services online, anytime, anywhere.</p>

        <div class="hero-actions">
          <a href="{{ url('/student/booking') }}" class="btn btn-primary">
            <x-outline-icon name="calendar-days" />
            <span>Book Appointment</span>
          </a>
          <a href="{{ url('/student/history') }}" class="btn btn-secondary">
            <x-outline-icon name="clock" />
            <span>View Appointments</span>
          </a>
        </div>
      </div>
    </section>

    <section id="about" class="container" style="padding: 60px 20px; text-align: center; max-width: 800px; margin: 0 auto; scroll-margin-top: 100px;">
        <h2 style="color: #20343a; font-weight: 800; margin-bottom: 16px;">Welcome to the Official PUPT Clinic</h2>
        <p style="color: #64748b; line-height: 1.8; margin-bottom: 24px;">
            Welcome to the official website for the PUP - Taguig Branch school clinic. If you ever feel unwell,
            you can make a consultation request here online. Additionally, you can check and evaluate all the
            information and status related to your appointment.
        </p>
        <a href="#" class="btn-outline" id="learnMoreBtn">
            <x-outline-icon name="arrow-long-right" />
            <span>Learn More</span>
        </a>
    </section>  

    <section class="comments-section">
      <div class="container">
        <div class="section-head">
          <div>
            <h3>What people are saying</h3>
            <p class="lead">Latest feedback on clinic services...</p>
          </div>
          @if(($feedbackCount ?? 0) > 3)
            <a href="{{ route('student.feedback.index') }}" class="feedback-more" aria-label="View more feedback" title="View more feedback">
              <x-outline-icon name="arrow-long-right" />
            </a>
          @endif
        </div>

        <div class="comments-grid">
          @forelse(($recentFeedback ?? []) as $feedback)
          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>{{ $feedback['name'] }} <span class="comment-meta">· {{ $feedback['role'] }} · {{ $feedback['time'] }}</span></h4>
              <p>{{ $feedback['message'] }}</p>
              <div class="comment-footer"><span class="comment-chip">{{ $feedback['service'] }}</span></div>
            </div>
          </article>
          @empty
          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>Jane D. <span class="comment-meta">· Student · 2 days ago</span></h4>
              <p>Quick and easy booking process — staff were helpful and the consultation was very informative. Highly recommended.</p>
              <div class="comment-footer"><span class="comment-chip">General Consultation</span></div>
            </div>
          </article>

          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>Mark R. <span class="comment-meta">· Faculty · 1 week ago</span></h4>
              <p>Received my medical certificate quickly. The online system saved me a lot of time.</p>
              <div class="comment-footer"><span class="comment-chip">Medical Certificates</span></div>
            </div>
          </article>

          <article class="comment-card" tabindex="0">
            <svg class="avatar" role="img" aria-label="User avatar"><use href="#avatar-placeholder"></use></svg>
            <div class="comment-body">
              <h4>Anna L. <span class="comment-meta">· Student · 3 weeks ago</span></h4>
              <p>Staff were friendly and the mental health support session was very helpful. Thank you!</p>
              <div class="comment-footer"><span class="comment-chip">Mental Health</span></div>
            </div>
          </article>
          @endforelse
        </div>
      </div>
    </section>

    <footer class="site-footer">
      <div class="footer-top">
        <div class="container footer-grid">
          <div class="footer-col footer-brand">
            <div class="brand">
              <div class="brand-logo">
                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Taguig logo" />
              </div>
              <div>
                <div class="brand-name">PUP TAGUIG <span class="brand-sub">ONLINE CLINIC</span></div>
              </div>
            </div>
            <p class="brand-desc">Providing quality healthcare services to the PUP Taguig community.</p>

            <div class="social">
              <a class="social-link" href="#" aria-label="Official clinic site">
                <x-outline-icon name="globe-alt" />
              </a>
              <a class="social-link" href="#" aria-label="Clinic announcements">
                <x-outline-icon name="megaphone" />
              </a>
            </div>
          </div>

          <div class="footer-col">
            <h4>Quick Links</h4>
            <ul class="footer-links">
              <li><a href="{{ url('/student/home') }}"><x-outline-icon name="home" class="footer-link-icon" /><span>Home</span></a></li>
              <li><a href="#"><x-outline-icon name="information-circle" class="footer-link-icon" /><span>About Us</span></a></li>
              <li><a href="{{ url('/student/booking') }}"><x-outline-icon name="calendar-days" class="footer-link-icon" /><span>Book Appointment</span></a></li>
              <li><a href="{{ url('/student/faq') }}"><x-outline-icon name="question-mark-circle" class="footer-link-icon" /><span>FAQ</span></a></li>
            </ul>
          </div>

          <div class="footer-col">
            <h4>Services</h4>
            <ul class="footer-links">
              <li>General Consultation</li>
              <li>Mental Health Support</li>
              <li>Prescription Services</li>
              <li></li>
            </ul>
          </div>

          <div class="footer-col">
            <h4>Contact Us</h4>
            <ul class="contact-list">
              <li>
                <x-outline-icon name="map-pin" class="contact-icon" />
                General Santos Ave, Taguig City
              </li>
              <li>
                <x-outline-icon name="envelope" class="contact-icon" />
                pupt_clinic@pup.edu.ph
              </li>
              <li>
                <x-outline-icon name="phone" class="contact-icon" />
                (02) 8837-5858
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="container">© 2025 PUP Taguig Online Clinic. All rights reserved.</div>
      </div>
    </footer>

    <div class="learn-more-modal" id="learnMoreModal" aria-hidden="true">
      <section class="learn-more-card" role="dialog" aria-modal="true" aria-labelledby="learnMoreTitle">
        <div class="learn-more-head">
          <div>
            <h3 id="learnMoreTitle">About PUPT Clinic</h3>
            <p>Student-centered care through online access and campus clinic support.</p>
          </div>
          <button type="button" class="learn-more-close" id="learnMoreCloseBtn" aria-label="Close learn more modal">
            <x-outline-icon name="x-mark" />
          </button>
        </div>
        <div class="learn-more-body">
          <div class="learn-more-info">The PUP Taguig Clinic supports students with consultations, medical record processing, health clearance workflows, and appointment coordination.</div>
          <div class="learn-more-info">Public guests can explore clinic information. Students should sign in through One Portal to book appointments and access private records.</div>
        </div>
      </section>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const learnMoreBtn = document.getElementById('learnMoreBtn');
        const learnMoreModal = document.getElementById('learnMoreModal');
        const learnMoreCloseBtn = document.getElementById('learnMoreCloseBtn');

        if (learnMoreModal && learnMoreModal.parentElement !== document.body) {
          document.body.appendChild(learnMoreModal);
        }

        function setLearnMoreOpen(isOpen) {
          if (!learnMoreModal) return;
          learnMoreModal.classList.toggle('is-open', isOpen);
          learnMoreModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
          document.documentElement.classList.toggle('learn-more-open', isOpen);
          document.body.classList.toggle('learn-more-open', isOpen);
        }

        if (learnMoreBtn) {
          learnMoreBtn.addEventListener('click', function (event) {
            event.preventDefault();
            setLearnMoreOpen(true);
          });
        }

        if (learnMoreCloseBtn) {
          learnMoreCloseBtn.addEventListener('click', function () {
            setLearnMoreOpen(false);
          });
        }

        if (learnMoreModal) {
          learnMoreModal.addEventListener('click', function (event) {
            if (event.target === learnMoreModal) {
              setLearnMoreOpen(false);
            }
          });
        }
      });
    </script>

@endsection
