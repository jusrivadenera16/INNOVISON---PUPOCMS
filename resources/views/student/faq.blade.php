@extends('layouts.student')

@section('title', 'FAQ')

@push('styles')
<style>
    /* ... (Keep Hero Section Styles as is) ... */
    .faq-hero {
        background: linear-gradient(135deg, #8B0000 0%, #4a0a10 100%);
        padding: 60px 20px;
        text-align: center;
        color: white;
        margin-bottom: 40px;
        background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    
    .faq-title { font-size: 36px; font-weight: 800; margin: 0 0 10px 0; letter-spacing: -0.5px; }
    
    .faq-subtitle {
        font-size: 16px;
        color: #8e8e8e; 
        font-weight: 500;
        max-width: 600px;
        margin: 0 auto 30px auto;
        line-height: 1.6;
    }

    .hero-search-wrapper { position: relative; max-width: 500px; margin: 0 auto; }
    
    /* UPDATED: Removed icon padding, text starts left */
    .hero-search-input {
        width: 100%; 
        padding: 16px 20px; /* Removed the 50px left padding */
        border-radius: 50px; 
        border: none; 
        font-size: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
        outline: none; 
        transition: transform 0.2s;
    }
    .hero-search-input:focus { transform: scale(1.02); }

    /* --- LAYOUT GRID --- */
    .faq-layout {
        display: grid;
        grid-template-columns: 2.5fr 1fr;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px 60px 20px;
    }

    /* --- CATEGORY CARDS --- */
    .category-card {
        background: #fff; border-radius: 16px; padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 30px; border-top: 5px solid #8B0000; 
    }
    
    /* Removed gap since icon is gone */
    .category-header { margin-bottom: 24px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9; }
    .category-title { font-size: 20px; font-weight: 800; color: #20343a; margin: 0; }

    details { margin-bottom: 12px; border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden; transition: all 0.3s; }
    details[open] { border-color: #cbd5e1; background: #fcfcfc; }
    summary { padding: 16px 20px; cursor: pointer; font-weight: 600; color: #334155; list-style: none; position: relative; padding-right: 40px; transition: color 0.2s; }
    summary::-webkit-details-marker { display: none; }
    summary::after { content: '+'; position: absolute; right: 20px; font-weight: 800; color: #8B0000; font-size: 18px; }
    details[open] summary::after { content: '-'; }
    details[open] summary { color: #8B0000; } 
    .faq-answer { padding: 0 20px 20px 20px; color: #64748b; line-height: 1.6; font-size: 14px; }

    /* --- RIGHT SIDEBAR WRAPPER --- */
    .sticky-sidebar {
        position: -webkit-sticky; 
        position: sticky;
        top: 30px; 
        height: fit-content; 
        z-index: 10;
    }

    .sidebar-widget {
        background: #fff; border-radius: 16px; padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 20px;
    }
    .widget-title { font-size: 16px; font-weight: 800; color: #20343a; margin: 0 0 15px 0; }
    .stat-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #e2e8f0; font-size: 14px; color: #475569; }
    .stat-row:last-child { border-bottom: none; }
    .stat-val { font-weight: 700; color: #1e293b; }

    .btn-action { position: relative; overflow: hidden; display: block; width: 100%; text-align: center; background: #8B0000; color: white; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 20px; transition: background .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease; box-shadow: 0 4px 6px rgba(139, 0, 0, 0.2); }
    .btn-action::after { content: ""; position: absolute; inset: 0; background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.18) 28%, rgba(255,255,255,.58) 50%, rgba(255,255,255,.18) 72%, transparent 100%); transform: translateX(-135%); transition: transform .72s ease; pointer-events: none; }
    .btn-action:hover { background: #facc15; color: #8B0000; transform: translateY(-2px); box-shadow: 0 10px 22px rgba(139, 0, 0, 0.18); }
    .btn-action:hover::after { transform: translateX(135%); }

    @media (max-width: 900px) {
        .faq-layout { grid-template-columns: 1fr; }
        .sticky-sidebar { position: static; }
    }
</style>
@endpush

@section('content')

    <div class="faq-hero">
        <h1 class="faq-title">How can we help you?</h1>
        <p class="faq-subtitle">Find answers to common questions about clinic appointments, medical records, and health services.</p>
        
        <div class="hero-search-wrapper">
            <input type="text" class="hero-search-input" placeholder="Search for a question..." id="faqSearch">
        </div>
    </div>

    <div class="faq-layout">
        
        <div>
            <div class="category-card">
                <div class="category-header">
                    <h3 class="category-title">Clinic Status & Services</h3>
                </div>
                <details>
                    <summary>What medical services are available?</summary>
                    <div class="faq-answer">We offer General Consultations, Dental Checkups, Blood Pressure Monitoring, and issuance of Medical Certificates.</div>
                </details>
                <details>
                    <summary>What are the clinic operating hours?</summary>
                    <div class="faq-answer">The PUP Taguig Clinic is open from <strong>8:00 AM to 5:00 PM</strong>, Mondays to Fridays.</div>
                </details>
                <details>
                    <summary>Do I need an appointment for emergency cases?</summary>
                    <div class="faq-answer">No. Emergency cases are prioritized and do not require an online appointment.</div>
                </details>
            </div>

            <div class="category-card">
                <div class="category-header">
                    <h3 class="category-title">Appointments & Requests</h3>
                </div>
                <details><summary>How do I book an appointment?</summary><div class="faq-answer">Go to the "Appointments" tab and fill out the form.</div></details>
                <details><summary>Can I cancel or reschedule?</summary><div class="faq-answer">Yes, via the My Account page.</div></details>
            </div>

            <div class="category-card">
                <div class="category-header">
                    <h3 class="category-title">Medical Documents</h3>
                </div>
                <details><summary>How do I get a Medical Certificate?</summary><div class="faq-answer">Book a checkup first.</div></details>
                <details><summary>Where can I see my records?</summary><div class="faq-answer">In the My Account history tab.</div></details>
            </div>
        </div>

        <div>
            <div class="sticky-sidebar">
                
                @auth('student')
                <div class="sidebar-widget">
                    <h4 class="widget-title">My Activity</h4>
                    <div class="stat-row"><span>Pending Requests</span><span class="stat-val" style="color:#b45309;">{{ $pendingCount ?? 0 }}</span></div>
                    <div class="stat-row"><span>Upcoming</span><span class="stat-val" style="color:#15803d;">{{ $upcomingCount ?? 0 }}</span></div>
                    <div class="stat-row"><span>Completed</span><span class="stat-val">{{ $completedCount ?? 0 }}</span></div>
                    <div class="stat-row"><span>Cancelled</span><span class="stat-val" style="color:#b91c1c;">{{ $cancelledCount ?? 0 }}</span></div>
                    <a href="{{ url('/student/history') }}" class="btn-action">View Full History ➜</a>
                </div>

                @else
                <div class="sidebar-widget">
                    <h4 class="widget-title">Student Portal</h4>
                    <p style="font-size: 13px; color: #64748b; line-height: 1.6; margin: 0;">
                        Log in through One Portal to view your appointments, history, and health record.
                    </p>
                    <a href="{{ route('login.portal') }}" class="btn-action">Log In via One Portal</a>
                </div>
                @endauth

                <div class="sidebar-widget" style="background: #20343a; color: white; border: none;">
                    <h4 class="widget-title" style="color: white;">Contact Us</h4>
                    <p style="font-size: 13px; opacity: 0.8; margin-bottom: 15px;">Need urgent help?</p>
                    <div style="font-size: 14px; font-weight: 600;">(02) 8837-5858<br>puptclinic@gmail.com</div>
                </div>

            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    document.getElementById('faqSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let details = document.querySelectorAll('details');
        details.forEach(detail => {
            let question = detail.querySelector('summary').innerText.toLowerCase();
            let answer = detail.querySelector('.faq-answer').innerText.toLowerCase();
            if (question.includes(filter) || answer.includes(filter)) {
                detail.style.display = "";
            } else {
                detail.style.display = "none";
            }
        });
    });
</script>
@endpush
