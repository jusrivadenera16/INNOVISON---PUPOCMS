@extends('layouts.student')

@section('title', 'My History')

@push('styles')
<style>
      body:has(.history-hero-icon),
      body:has(.history-modern-page) {
          background:
              linear-gradient(180deg, rgba(255, 250, 250, 0.70), rgba(255, 255, 255, 0.58) 42%, rgba(245, 248, 247, 0.72) 100%),
              url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat fixed !important;
      }
      html[data-theme="dark"] body:has(.history-hero-icon),
      html[data-theme="dark"] body:has(.history-modern-page) {
          background:
              linear-gradient(180deg, rgba(2, 6, 23, 0.82), rgba(15, 23, 42, 0.74) 42%, rgba(2, 6, 23, 0.84) 100%),
              url('{{ asset("images/student-bg.png") }}') center top / cover no-repeat fixed !important;
      }
      /* --- PAGE SPECIFIC STYLES --- */
      .page-header {
          position: relative;
          margin: -12px auto 22px;
          padding: 18px 22px;
          border-radius: 24px;
          border: 1px solid rgba(139, 0, 0, 0.12);
          background:
              radial-gradient(circle at top right, rgba(255, 244, 194, 0.68), transparent 30%),
              linear-gradient(135deg, #fffef4 0%, #fff8fb 36%, #ffffff 100%);
          box-shadow:
              0 20px 40px rgba(15, 23, 42, 0.09),
              0 0 0 1px rgba(255,255,255,0.78) inset;
          overflow: hidden;
          max-width: 980px;
      }
      .page-header::before {
          content: "";
          position: absolute;
          inset: auto -60px -80px auto;
          width: 220px;
          height: 220px;
          background: radial-gradient(circle, rgba(139, 0, 0, 0.10) 0%, rgba(139, 0, 0, 0) 70%);
          pointer-events: none;
      }
      .history-hero-icon {
          position: absolute;
          top: -12px;
          right: -8px;
          width: 180px;
          height: 180px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          color: rgba(112, 19, 27, 0.10);
          transform: rotate(-12deg);
          pointer-events: none;
          z-index: 0;
      }
      .history-hero-icon svg {
          width: 100%;
          height: 100%;
          stroke-width: 1.7;
      }
      .history-hero-kicker,
      .history-hero-title,
      .history-hero-text,
      .history-hero-steps {
          position: relative;
          z-index: 1;
      }
      .history-hero-kicker {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 6px 10px;
          border-radius: 999px;
          background: rgba(139, 0, 0, 0.08);
          color: #8B0000;
          font-size: 11px;
          font-weight: 800;
          letter-spacing: 0.08em;
          text-transform: uppercase;
          margin-bottom: 10px;
      }
      .history-hero-title {
          margin: 0;
          font-size: 28px;
          color: #8B0000;
          font-weight: 800;
          letter-spacing: -0.03em;
      }
      .history-hero-text {
          color: #6b7b7d;
          margin-top: 8px;
          font-size: 14px;
          line-height: 1.6;
          max-width: 620px;
      }
      .history-hero-steps {
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
          margin-top: 14px;
      }
      .history-hero-step {
          display: inline-flex;
          align-items: center;
          gap: 10px;
          padding: 8px 12px;
          border-radius: 14px;
          background: rgba(255, 255, 255, 0.82);
          border: 1px solid rgba(148, 163, 184, 0.18);
          color: #334155;
          font-size: 12px;
          font-weight: 700;
          box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
      }
      .history-hero-step::before {
          content: "";
          width: 8px;
          height: 8px;
          border-radius: 999px;
          background: #8B0000;
          flex: 0 0 auto;
          box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.08);
      }
      
      .card-history {
          background: #fff; 
          padding: 24px; 
          border-radius: 22px; 
          box-shadow: 0 18px 36px rgba(16,24,28,0.08); 
          min-height: 400px;
          border: 1px solid rgba(30, 41, 59, 0.08);
          position: relative;
          overflow: hidden;
          max-width: 980px;
          margin: 0 auto;
      }
      .card-history::before {
          content: "";
          position: absolute;
          top: 0;
          left: 20px;
          right: 20px;
          height: 4px;
          border-radius: 999px;
          background: linear-gradient(90deg, #8B0000 0%, #facc15 100%);
      }
      .history-summary-grid {
          display: grid;
          grid-template-columns: repeat(5, minmax(0, 1fr));
          gap: 14px;
          margin-bottom: 18px;
      }
      .history-summary-card {
          padding: 16px 16px 14px;
          border-radius: 18px;
          background: linear-gradient(180deg, #ffffff 0%, #fcfcfe 100%);
          border: 1px solid rgba(30, 41, 59, 0.08);
          box-shadow:
              0 12px 24px rgba(15, 23, 42, 0.06),
              0 0 0 1px rgba(255,255,255,0.76) inset;
      }
      .history-summary-label {
          display: block;
          font-size: 11px;
          font-weight: 800;
          letter-spacing: 0.08em;
          text-transform: uppercase;
          color: #64748b;
          margin-bottom: 8px;
      }
      .history-summary-value {
          font-size: 28px;
          line-height: 1;
          font-weight: 800;
          color: #8B0000;
          display: block;
      }
      .history-summary-note {
          margin-top: 8px;
          font-size: 12px;
          color: #64748b;
      }
      .history-toolbar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 14px;
          flex-wrap: wrap;
          margin-bottom: 12px;
      }
      .history-toolbar-copy {
          color: #64748b;
          font-size: 13px;
          font-weight: 600;
      }
      .history-filter-row {
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
      }
      .history-filter-btn {
          min-height: 40px;
          padding: 0 16px;
          border-radius: 999px;
          border: 1px solid rgba(139, 0, 0, 0.16);
          background: #ffffff;
          color: #8B0000;
          font-size: 13px;
          font-weight: 800;
          cursor: pointer;
          transition: all 0.18s ease;
          box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
      }
      .history-filter-btn:hover,
      .history-filter-btn.is-active {
          transform: translateY(-1px);
          background: #8B0000;
          color: #facc15;
          border-color: #8B0000;
          box-shadow: 0 14px 24px rgba(139, 0, 0, 0.16);
      }
      
      .btn-outline { 
          border: 1px solid #8B0000; 
          color: #8B0000; 
          background: transparent; 
          padding: 7.9px 14px; 
          border-radius: 8px; 
          text-decoration: none; 
          font-weight: 600; 
          font-size: 14px; 
          display: inline-block;
      }
      .btn-outline:hover { background: #fdf2f2; }
      
      .history-grid {
          display: grid;
          gap: 18px;
          margin-top: 18px;
          position: relative;
          padding-left: 18px;
      }
      .history-grid::before {
          content: "";
          position: absolute;
          left: 6px;
          top: 6px;
          bottom: 6px;
          width: 2px;
          border-radius: 999px;
          background: linear-gradient(180deg, rgba(139, 0, 0, 0.24) 0%, rgba(250, 204, 21, 0.22) 100%);
      }
      
      .apt-card {
          position: relative;
          padding: 18px 18px 18px 22px;
          border-radius: 18px;
          background: linear-gradient(180deg, #ffffff 0%, #fcfcfe 100%);
          border: 1px solid rgba(30, 41, 59, 0.08);
          display: flex;
          flex-direction: column;
          gap: 12px; 
          transition: box-shadow 0.2s, transform 0.2s, border-color 0.2s; 
          box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
      }
      .apt-card::before {
          content: "";
          position: absolute;
          left: -21px;
          top: 26px;
          width: 12px;
          height: 12px;
          border-radius: 999px;
          background: #ffffff;
          border: 3px solid #8B0000;
          box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.08);
      }
      .apt-card.status-pending { border-left: 4px solid #d97706; }
      .apt-card.status-approved { border-left: 4px solid #15803d; }
      .apt-card.status-completed { border-left: 4px solid #8B0000; }
      .apt-card.status-missed { border-left: 4px solid #c2410c; }
      .apt-card.status-cancelled { border-left: 4px solid #64748b; }
      .apt-card.status-expired { border-left: 4px solid #6b7280; }
      .apt-card.status-default { border-left: 4px solid #8B0000; }
      .apt-card:hover { box-shadow: 0 18px 34px rgba(0,0,0,0.08); border-color: rgba(139, 0, 0, 0.12); transform: translateY(-2px); }
      .apt-card.is-upcoming {
          background: linear-gradient(180deg, #fffdfa 0%, #ffffff 100%);
      }
      
      .apt-header { display: flex; justify-content: space-between; align-items: flex-start; }
      .apt-service { font-size: 17px; font-weight: 800; color: #20343a; letter-spacing: -0.01em; }
      .apt-date {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          min-height: 36px;
          padding: 0 14px;
          border-radius: 999px;
          background: rgba(139, 0, 0, 0.07);
          font-weight: 700;
          color: #8B0000;
      }
      
      .apt-meta {
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
          margin-top: 4px;
      }
      .apt-meta-pill {
          display: inline-flex;
          align-items: center;
          min-height: 32px;
          padding: 0 12px;
          border-radius: 999px;
          background: #f8fafc;
          border: 1px solid #e2e8f0;
          color: #475569;
          font-size: 12px;
          font-weight: 700;
      }
      .apt-meta-pill.is-status {
          font-weight: 800;
          text-transform: uppercase;
          letter-spacing: 0.04em;
      }
      .apt-meta-pill.is-status.status-pending { background: #fff3cd; color: #856404; border-color: rgba(217, 119, 6, 0.18); }
      .apt-meta-pill.is-status.status-approved { background: #d4edda; color: #155724; border-color: rgba(21, 128, 61, 0.18); }
      .apt-meta-pill.is-status.status-completed { background: #f3e8ea; color: #7f1d2d; border-color: rgba(139, 0, 0, 0.14); }
      .apt-meta-pill.is-status.status-missed { background: #ffedd5; color: #9a3412; border-color: rgba(194, 65, 12, 0.16); }
      .apt-meta-pill.is-status.status-cancelled { background: #e5e7eb; color: #4b5563; border-color: rgba(100, 116, 139, 0.18); }
      .apt-meta-pill.is-status.status-expired { background: #f3f4f6; color: #4b5563; border-color: rgba(107, 114, 128, 0.18); }
      .apt-meta-pill.is-status.status-default { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
      .apt-notes {
          display: inline-block;
          width: fit-content;
          max-width: min(100%, 520px);
          background: linear-gradient(180deg, #fffdf0 0%, #fff7cc 100%);
          padding: 12px 14px;
          border-radius: 14px;
          font-size: 14px;
          color: #445;
          margin-top: 2px;
          border: 1px solid rgba(245, 158, 11, 0.22);
          box-shadow:
              0 10px 20px rgba(146, 64, 14, 0.06),
              inset 0 1px 0 rgba(255,255,255,0.7);
          line-height: 1.55;
          word-break: break-word;
      }
      .apt-footer {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 12px;
          flex-wrap: wrap;
      }
      .apt-footer-actions {
          display: flex;
          justify-content: flex-end;
          gap: 10px;
          flex-wrap: wrap;
      }
      .apt-action-note {
          font-size: 12px;
          font-weight: 700;
          color: #94a3b8;
      }
      .cancel-appointment-btn {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
          min-height: 42px;
          padding: 0 16px;
          border-radius: 999px;
          border: 1px solid rgba(139, 0, 0, 0.22);
          background: linear-gradient(180deg, #ffffff 0%, #fff5f5 100%);
          color: #8B0000;
          font-size: 13px;
          font-weight: 800;
          cursor: pointer;
          box-shadow: 0 10px 20px rgba(139, 0, 0, 0.08);
          transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
      }
      .cancel-appointment-btn:hover,
      .cancel-appointment-btn:focus-visible {
          transform: translateY(-1px);
          background: #8B0000;
          color: #facc15;
          box-shadow: 0 16px 24px rgba(139, 0, 0, 0.18);
      }
      .cancel-appointment-btn svg {
          width: 16px;
          height: 16px;
          flex: 0 0 auto;
      }
      .cancel-appointment-btn.secondary {
          background: #f8fafc;
          color: #475569;
          border-color: #e2e8f0;
          box-shadow: none;
      }
      .cancel-appointment-btn.secondary:hover,
      .cancel-appointment-btn.secondary:focus-visible {
          background: #eef2ff;
          color: #1e293b;
          box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
      }
      .cancel-dialog-backdrop {
          position: fixed;
          inset: 0;
          z-index: 1200;
          display: none;
          align-items: center;
          justify-content: center;
          padding: 18px;
          background: rgba(15, 23, 42, 0.56);
          backdrop-filter: blur(10px);
      }
      .cancel-dialog-backdrop.is-open {
          display: flex;
      }
      .cancel-dialog {
          width: min(100%, 560px);
          border-radius: 24px;
          border: 1px solid rgba(139, 0, 0, 0.14);
          background: linear-gradient(180deg, #ffffff 0%, #fffaf7 100%);
          box-shadow: 0 26px 60px rgba(15, 23, 42, 0.22);
          overflow: hidden;
      }
      .cancel-dialog-header {
          position: relative;
          padding: 20px 22px 18px;
          background:
              linear-gradient(135deg, rgba(139, 0, 0, 0.11) 0%, rgba(250, 204, 21, 0.13) 100%),
              linear-gradient(180deg, #fffdf9 0%, #fff6f6 100%);
          border-bottom: 1px solid rgba(139, 0, 0, 0.10);
      }
      .cancel-dialog-kicker {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 6px 10px;
          border-radius: 999px;
          background: rgba(139, 0, 0, 0.08);
          color: #8B0000;
          font-size: 11px;
          font-weight: 800;
          letter-spacing: 0.08em;
          text-transform: uppercase;
          margin-bottom: 10px;
      }
      .cancel-dialog-title {
          margin: 0;
          font-size: 22px;
          line-height: 1.15;
          color: #4c0519;
          font-weight: 800;
      }
      .cancel-dialog-copy {
          margin-top: 8px;
          font-size: 14px;
          line-height: 1.6;
          color: #64748b;
      }
      .cancel-dialog-body {
          padding: 20px 22px 22px;
      }
      .cancel-dialog-summary {
          display: grid;
          gap: 12px;
          padding: 16px;
          border-radius: 18px;
          background: #fff;
          border: 1px solid rgba(30, 41, 59, 0.08);
          box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
      }
      .cancel-dialog-summary-row {
          display: flex;
          justify-content: space-between;
          gap: 12px;
          align-items: flex-start;
      }
      .cancel-dialog-summary-label {
          font-size: 11px;
          font-weight: 800;
          letter-spacing: 0.08em;
          text-transform: uppercase;
          color: #94a3b8;
      }
      .cancel-dialog-summary-value {
          text-align: right;
          font-size: 14px;
          font-weight: 700;
          color: #20343a;
          max-width: 60%;
          word-break: break-word;
      }
      .cancel-dialog-warning {
          margin-top: 14px;
          padding: 12px 14px;
          border-radius: 14px;
          background: #fff7ed;
          border: 1px solid rgba(249, 115, 22, 0.18);
          color: #9a3412;
          font-size: 13px;
          line-height: 1.55;
          font-weight: 600;
      }
      .cancel-dialog-actions {
          display: flex;
          justify-content: flex-end;
          gap: 10px;
          flex-wrap: wrap;
          padding: 0 22px 22px;
      }
      html[data-theme="dark"] .cancel-appointment-btn {
          background: #17171a !important;
          color: #ffffff !important;
          border-color: rgba(250, 204, 21, 0.14) !important;
      }
      html[data-theme="dark"] .cancel-appointment-btn:hover,
      html[data-theme="dark"] .cancel-appointment-btn:focus-visible {
          background: #8B0000 !important;
          color: #facc15 !important;
          border-color: #8B0000 !important;
      }
      html[data-theme="dark"] .cancel-appointment-btn.secondary {
          background: #17171a !important;
          color: #f8fafc !important;
          border-color: rgba(250, 204, 21, 0.14) !important;
      }
      html[data-theme="dark"] .cancel-appointment-btn.secondary:hover,
      html[data-theme="dark"] .cancel-appointment-btn.secondary:focus-visible {
          background: #1f2937 !important;
          color: #ffffff !important;
      }
      html[data-theme="dark"] .cancel-dialog {
          background: linear-gradient(180deg, #0f0f10 0%, #161618 100%) !important;
          border-color: rgba(250, 204, 21, 0.14) !important;
      }
      html[data-theme="dark"] .cancel-dialog-header {
          background:
              linear-gradient(135deg, rgba(139, 0, 0, 0.20) 0%, rgba(250, 204, 21, 0.12) 100%),
              linear-gradient(180deg, #17171a 0%, #1c1c20 100%) !important;
          border-bottom-color: rgba(250, 204, 21, 0.10) !important;
      }
      html[data-theme="dark"] .cancel-dialog-kicker {
          background: rgba(250, 204, 21, 0.10) !important;
          color: #facc15 !important;
      }
      html[data-theme="dark"] .cancel-dialog-title {
          color: #ffffff !important;
      }
      html[data-theme="dark"] .cancel-dialog-copy,
      html[data-theme="dark"] .cancel-dialog-summary-label {
          color: #cbd5e1 !important;
      }
      html[data-theme="dark"] .cancel-dialog-summary {
          background: #111113 !important;
          border-color: rgba(250, 204, 21, 0.10) !important;
      }
      html[data-theme="dark"] .cancel-dialog-summary-value {
          color: #f8fafc !important;
      }
      html[data-theme="dark"] .cancel-dialog-warning {
          background: rgba(146, 64, 14, 0.20) !important;
          border-color: rgba(250, 204, 21, 0.16) !important;
          color: #fde68a !important;
      }
      
      .status-badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; }
      .status-badge.status-pending { background: #fff3cd; color: #856404; }
      .status-badge.status-approved { background: #d4edda; color: #155724; }
      .status-badge.status-completed { background: #f3e8ea; color: #7f1d2d; }
      .status-badge.status-missed { background: #ffedd5; color: #9a3412; }
      .status-badge.status-cancelled { background: #e5e7eb; color: #4b5563; }
      .status-badge.status-expired { background: #f3f4f6; color: #4b5563; }
      .status-badge.status-default { background: #eee; color: #555; }
      
      .empty-state {
          min-height: 360px;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-direction: column;
          gap: 18px;
          padding: 48px 24px;
          color: #667085;
          text-align: center;
      }

      .empty-illustration {
          position: relative;
          width: 230px;
          height: 120px;
          display: flex;
          align-items: center;
          justify-content: center;
      }

      .empty-dot-wave {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 12px;
          min-width: 122px;
          min-height: 64px;
          padding: 8px 12px;
      }

      .empty-dot-wave span {
          width: 14px;
          height: 14px;
          border-radius: 999px;
          background: #8B0000;
          box-shadow: 0 8px 16px rgba(139, 0, 0, 0.18);
          animation: historyDotWave 1.15s ease-in-out infinite;
      }

      .empty-dot-wave span:nth-child(2) {
          animation-delay: 0.16s;
          background: #b91c1c;
      }

      .empty-dot-wave span:nth-child(3) {
          animation-delay: 0.32s;
          background: #facc15;
      }

      .empty-shadow {
          position: absolute;
          bottom: 6px;
          width: 138px;
          height: 18px;
          border-radius: 999px;
          background: rgba(127, 29, 29, 0.12);
          filter: blur(2px);
      }

      .empty-bubble {
          position: absolute;
          top: 2px;
          right: 18px;
          background: #ffffff;
          color: #8B0000;
          border: 2px solid #f3c9c9;
          border-radius: 18px;
          padding: 10px 16px;
          font-size: 16px;
          font-weight: 800;
          box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
          animation: bubbleFloat 2s ease-in-out infinite;
          min-width: 110px;
      }

      .bubble-text {
          display: inline-block;
          transition: opacity 0.18s ease, transform 0.18s ease;
      }

      .bubble-text-yay {
          position: absolute;
          inset: 10px 16px;
          opacity: 0;
          transform: translateY(4px);
      }

      .empty-bubble::after {
          content: "";
          position: absolute;
          left: 50%;
          bottom: -10px;
          width: 18px;
          height: 18px;
          background: #ffffff;
          border-right: 2px solid #f3c9c9;
          border-bottom: 2px solid #f3c9c9;
          transform: translateX(-50%) rotate(45deg);
      }

      .clinic-cartoon {
          position: relative;
          width: 132px;
          height: 172px;
          animation: cartoonBounce 2.6s ease-in-out infinite;
      }

      .cartoon-head {
          position: absolute;
          top: 12px;
          left: 34px;
          width: 64px;
          height: 64px;
          border-radius: 999px;
          background: #ffd7b5;
          border: 3px solid #7c2d12;
          z-index: 2;
      }

      .cartoon-hair {
          position: absolute;
          top: 4px;
          left: 29px;
          width: 74px;
          height: 38px;
          border-radius: 999px 999px 18px 18px;
          background: #5b2c1d;
          z-index: 3;
      }

      .cartoon-eye {
          position: absolute;
          top: 30px;
          width: 7px;
          height: 7px;
          border-radius: 999px;
          background: #3f1d12;
          z-index: 4;
      }

      .cartoon-eye.left { left: 53px; }
      .cartoon-eye.right { right: 53px; }

      .cartoon-smile {
          position: absolute;
          top: 46px;
          left: 52px;
          width: 28px;
          height: 12px;
          border-bottom: 3px solid #b45309;
          border-radius: 0 0 20px 20px;
          z-index: 4;
      }

      .cartoon-body {
          position: absolute;
          top: 74px;
          left: 28px;
          width: 76px;
          height: 70px;
          border-radius: 22px 22px 18px 18px;
          background: #ffffff;
          border: 3px solid #7c2d12;
      }

      .cartoon-cross-v,
      .cartoon-cross-h {
          position: absolute;
          background: #8B0000;
          border-radius: 999px;
          z-index: 2;
      }

      .cartoon-cross-v {
          top: 92px;
          left: 63px;
          width: 8px;
          height: 24px;
      }

      .cartoon-cross-h {
          top: 100px;
          left: 55px;
          width: 24px;
          height: 8px;
      }

      .cartoon-arm {
          position: absolute;
          top: 84px;
          width: 18px;
          height: 58px;
          border-radius: 999px;
          background: #ffd7b5;
          border: 3px solid #7c2d12;
          transform-origin: top center;
          transition: transform 0.28s ease;
      }

      .cartoon-arm.left {
          left: 12px;
          transform: rotate(18deg);
      }

      .cartoon-arm.right {
          right: 12px;
          transform: rotate(-22deg);
      }

      .cartoon-leg {
          position: absolute;
          bottom: 10px;
          width: 18px;
          height: 52px;
          border-radius: 999px;
          background: #f8fafc;
          border: 3px solid #7c2d12;
      }

      .cartoon-leg.left { left: 42px; }
      .cartoon-leg.right { right: 42px; }

      .empty-title {
          margin: 0;
          font-size: 18px;
          color: #7f1d1d;
          font-weight: 800;
      }

      .empty-state .btn-outline {
          padding: 10px 18px;
          border-radius: 999px;
          font-weight: 700;
          box-shadow: 0 10px 24px rgba(139, 0, 0, 0.08);
      }

      .empty-state.is-celebrating .cartoon-arm.left {
          transform: rotate(72deg) translateY(-10px);
      }

      .empty-state.is-celebrating .cartoon-arm.right {
          transform: rotate(-72deg) translateY(-10px);
      }

      .empty-state.is-celebrating .bubble-text-book {
          opacity: 0;
          transform: translateY(-4px);
      }

      .empty-state.is-celebrating .bubble-text-yay {
          opacity: 1;
          transform: translateY(0);
      }

      @keyframes bubbleFloat {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-8px); }
      }

      @keyframes cartoonBounce {
          0%, 100% { transform: translateY(0); }
          50% { transform: translateY(-10px); }
      }

      @keyframes historyDotWave {
          0%, 80%, 100% {
              transform: translateY(0) scale(0.92);
              opacity: 0.62;
          }
          40% {
              transform: translateY(-14px) scale(1.08);
              opacity: 1;
          }
      }
      html[data-theme="dark"] .page-header {
          background: linear-gradient(180deg, #0f0f10 0%, #161618 100%) !important;
          border-color: rgba(250, 204, 21, 0.16) !important;
          box-shadow:
              0 18px 36px rgba(0, 0, 0, 0.42),
              0 0 0 1px rgba(250, 204, 21, 0.05) inset !important;
      }
      html[data-theme="dark"] .history-hero-kicker,
      html[data-theme="dark"] .history-hero-step {
          background: linear-gradient(180deg, #17171a 0%, #1d1d21 100%) !important;
          border-color: rgba(250, 204, 21, 0.14) !important;
          color: #f8fafc !important;
      }
      html[data-theme="dark"] .history-hero-title {
          color: #ffffff !important;
      }
      html[data-theme="dark"] .history-hero-text {
          color: #e5e7eb !important;
      }
      html[data-theme="dark"] .history-hero-icon {
          color: rgba(250, 204, 21, 0.08) !important;
      }
      html[data-theme="dark"] .card-history,
      html[data-theme="dark"] .history-summary-card,
      html[data-theme="dark"] .apt-card {
          background: linear-gradient(180deg, #0f0f10 0%, #161618 100%) !important;
          border-color: rgba(250, 204, 21, 0.14) !important;
          box-shadow:
              0 18px 36px rgba(0, 0, 0, 0.30),
              0 0 0 1px rgba(250, 204, 21, 0.04) inset !important;
      }
      html[data-theme="dark"] .history-summary-label,
      html[data-theme="dark"] .history-toolbar-copy,
      html[data-theme="dark"] .apt-meta-pill,
      html[data-theme="dark"] .apt-notes {
          color: #cbd5e1 !important;
      }
      html[data-theme="dark"] .history-summary-value,
      html[data-theme="dark"] .apt-service {
          color: #ffffff !important;
      }
      html[data-theme="dark"] .history-filter-btn {
          background: #17171a !important;
          color: #ffffff !important;
          border-color: rgba(250, 204, 21, 0.14) !important;
      }
      html[data-theme="dark"] .history-filter-btn:hover,
      html[data-theme="dark"] .history-filter-btn.is-active {
          background: #8B0000 !important;
          color: #facc15 !important;
          border-color: #8B0000 !important;
      }
      html[data-theme="dark"] .apt-date,
      html[data-theme="dark"] .apt-meta-pill {
          background: #17171a !important;
          border-color: rgba(250, 204, 21, 0.14) !important;
          color: #f8fafc !important;
      }
      html[data-theme="dark"] .apt-meta-pill.is-status.status-pending,
      html[data-theme="dark"] .apt-meta-pill.is-status.status-approved,
      html[data-theme="dark"] .apt-meta-pill.is-status.status-completed,
      html[data-theme="dark"] .apt-meta-pill.is-status.status-missed,
      html[data-theme="dark"] .apt-meta-pill.is-status.status-cancelled,
      html[data-theme="dark"] .apt-meta-pill.is-status.status-expired,
      html[data-theme="dark"] .apt-meta-pill.is-status.status-default {
          color: #f8fafc !important;
      }
      html[data-theme="dark"] .apt-action-note {
          color: #94a3b8 !important;
      }
      html[data-theme="dark"] .apt-notes {
          background: linear-gradient(180deg, rgba(133, 77, 14, 0.26) 0%, rgba(146, 64, 14, 0.20) 100%) !important;
          border-color: rgba(250, 204, 21, 0.18) !important;
          color: #f8fafc !important;
      }
      html[data-theme="dark"] .history-grid::before {
          background: linear-gradient(180deg, rgba(250, 204, 21, 0.20) 0%, rgba(139, 0, 0, 0.20) 100%) !important;
      }
      html[data-theme="dark"] .apt-card::before {
          background: #0f0f10 !important;
      }
      @media (max-width: 980px) {
          .history-summary-grid {
              grid-template-columns: repeat(3, minmax(0, 1fr));
          }
      }
      @media (max-width: 680px) {
          .page-header {
              padding: 16px 16px;
              margin: -8px auto 18px;
          }
          .history-summary-grid {
              grid-template-columns: repeat(2, minmax(0, 1fr));
          }
          .history-toolbar {
              align-items: stretch;
          }
          .history-filter-row {
              width: 100%;
          }
          .history-filter-btn {
              flex: 1 1 calc(50% - 10px);
          }
          .apt-header,
          .apt-footer {
              flex-direction: column;
              align-items: flex-start;
          }
          .history-grid {
              padding-left: 14px;
          }
          .history-hero-icon {
              top: 4px;
              right: -10px;
              width: 118px;
              height: 118px;
          }
          .history-hero-step {
              width: 100%;
              justify-content: flex-start;
          }
      }

      /* --- APPOINTMENT HISTORY REDESIGN --- */
      .history-modern-page {
          width: min(1060px, calc(100% - 40px));
          margin: 0 auto;
          padding: 0 0 60px;
      }
      .history-modern-hero {
          position: relative;
          min-height: 205px;
          display: grid;
          grid-template-columns: minmax(0, 1fr) 310px;
          align-items: stretch;
          gap: 28px;
          margin: -8px 0 16px;
          padding: 28px 30px;
          overflow: hidden;
          border: 1px solid rgba(250,204,21,.28);
          border-radius: 10px;
          box-sizing: border-box;
          background: linear-gradient(90deg, rgba(111,0,31,.99), rgba(105,0,29,.97) 52%, rgba(71,0,22,.82));
          box-shadow: 0 18px 38px rgba(76,5,25,.2);
          color: #ffffff;
      }
      .history-modern-hero::before {
          content: "";
          position: absolute;
          inset: 0 0 0 43%;
          background: url('{{ asset("images/PUPBG.jpg") }}') right center / cover no-repeat;
          opacity: .16;
          filter: saturate(.3) contrast(1.1);
          -webkit-mask-image: linear-gradient(90deg, transparent, rgba(0,0,0,.8) 30%, #000);
          mask-image: linear-gradient(90deg, transparent, rgba(0,0,0,.8) 30%, #000);
          pointer-events: none;
      }
      .history-modern-hero::after {
          content: "";
          position: absolute;
          right: 22px;
          top: 12px;
          width: 170px;
          height: 170px;
          border: 8px solid rgba(255,255,255,.035);
          border-radius: 50%;
          box-shadow: 0 0 0 18px rgba(255,255,255,.018);
          pointer-events: none;
      }
      .history-modern-hero-main,
      .history-modern-hero-overview {
          position: relative;
          z-index: 1;
      }
      .history-modern-hero-main {
          display: grid;
          grid-template-columns: 112px minmax(0, 1fr);
          align-items: center;
          gap: 22px;
      }
      .history-modern-emblem {
          width: 106px;
          height: 106px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: 2px solid rgba(255,255,255,.88);
          border-radius: 50%;
          background: linear-gradient(145deg, rgba(255,255,255,.98), rgba(255,239,241,.94));
          color: #8b0018;
          box-shadow: 0 14px 30px rgba(27,0,9,.28);
      }
      .history-modern-emblem svg {
          width: 56px;
          height: 56px;
          stroke-width: 1.45;
      }
      .history-modern-kicker {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          margin-bottom: 5px;
          color: #facc15;
          font-size: 10px;
          font-weight: 900;
          letter-spacing: .08em;
          text-transform: uppercase;
      }
      .history-modern-kicker svg { width: 13px; height: 13px; }
      .history-modern-title {
          margin: 0 0 6px;
          color: #ffffff;
          font-size: clamp(27px, 3vw, 34px);
          font-weight: 900;
          letter-spacing: 0;
      }
      .history-modern-description {
          max-width: 510px;
          margin: 0;
          color: rgba(255,255,255,.88);
          font-size: 13px;
          line-height: 1.55;
      }
      .history-modern-chips {
          display: flex;
          flex-wrap: wrap;
          gap: 7px;
          margin-top: 13px;
      }
      .history-modern-chip {
          min-height: 30px;
          display: inline-flex;
          align-items: center;
          gap: 7px;
          padding: 6px 10px;
          border: 1px solid rgba(255,255,255,.1);
          border-radius: 999px;
          background: rgba(255,255,255,.1);
          color: #ffffff;
          font-size: 10px;
          font-weight: 750;
      }
      .history-modern-chip svg { width: 14px; height: 14px; stroke-width: 2; }
      .history-modern-hero-overview {
          display: grid;
          align-content: center;
          padding-left: 26px;
          border-left: 1px solid rgba(255,255,255,.18);
      }
      .history-modern-overview-item {
          display: grid;
          grid-template-columns: 42px minmax(0, 1fr);
          align-items: center;
          gap: 12px;
          padding: 14px 0;
      }
      .history-modern-overview-item + .history-modern-overview-item {
          border-top: 1px solid rgba(255,255,255,.14);
      }
      .history-modern-overview-icon {
          width: 40px;
          height: 40px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: 1px solid rgba(250,204,21,.3);
          border-radius: 50%;
          background: rgba(27,0,9,.24);
          color: #facc15;
      }
      .history-modern-overview-icon svg { width: 20px; height: 20px; stroke-width: 2; }
      .history-modern-overview-label {
          display: block;
          margin-bottom: 3px;
          color: rgba(255,255,255,.68);
          font-size: 9px;
          font-weight: 850;
          letter-spacing: .06em;
          text-transform: uppercase;
      }
      .history-modern-overview-value {
          display: block;
          color: #ffffff;
          font-size: 14px;
          font-weight: 850;
          line-height: 1.35;
      }
      .history-modern-stat-grid {
          display: grid;
          grid-template-columns: repeat(4, minmax(0, 1fr));
          gap: 10px;
          margin-bottom: 14px;
      }
      .history-modern-stat-card {
          min-height: 98px;
          display: grid;
          grid-template-columns: 48px minmax(0, 1fr);
          align-items: center;
          gap: 10px;
          padding: 13px 14px;
          border: 1px solid rgba(112,19,27,.11);
          border-radius: 8px;
          background: rgba(255,255,255,.97);
          box-shadow: 0 10px 24px rgba(39,13,20,.07);
      }
      .history-modern-stat-icon {
          width: 46px;
          height: 46px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border-radius: 8px;
          background: #fff0f2;
          color: #a40021;
      }
      .history-modern-stat-icon svg { width: 24px; height: 24px; stroke-width: 1.8; }
      .history-modern-stat-card.is-upcoming .history-modern-stat-icon { background: #fff7da; color: #d97706; }
      .history-modern-stat-card.is-completed .history-modern-stat-icon { background: #eaf8e9; color: #199447; }
      .history-modern-stat-card.is-missed .history-modern-stat-icon { background: #fff0f2; color: #dc334d; }
      .history-modern-stat-copy { min-width: 0; display: grid; }
      .history-modern-stat-label {
          color: #64748b;
          font-size: 12px;
          font-weight: 850;
          letter-spacing: .04em;
          text-transform: uppercase;
      }
      .history-modern-stat-value {
          margin: 2px 0 3px;
          color: #182033;
          font-size: 26px;
          font-weight: 900;
          line-height: 1;
      }
      .history-modern-stat-note { color: #718096; font-size: 12px; line-height: 1.4; }
      .history-modern-content-grid {
          display: grid;
          grid-template-columns: minmax(0, 1fr) 280px;
          gap: 14px;
          align-items: start;
      }
      .history-modern-list-panel,
      .history-modern-side-card {
          border: 1px solid rgba(112,19,27,.12);
          border-radius: 8px;
          background: rgba(255,255,255,.97);
          box-shadow: 0 14px 30px rgba(39,13,20,.08);
      }
      .history-modern-list-panel { min-width: 0; padding: 12px; }
      .history-modern-filter-row {
          display: flex;
          align-items: center;
          flex-wrap: wrap;
          gap: 7px;
          padding-bottom: 12px;
      }
      .history-modern-filter-btn {
          min-height: 34px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 6px;
          padding: 8px 13px;
          border: 1px solid rgba(112,19,27,.15);
          border-radius: 999px;
          background: #ffffff;
          color: #6f1624;
          font: inherit;
          font-size: 12px;
          font-weight: 850;
          cursor: pointer;
      }
      .history-modern-filter-btn svg { width: 13px; height: 13px; stroke-width: 2; }
      .history-modern-filter-btn:hover { background: #fff7f8; border-color: rgba(176,0,32,.32); }
      .history-modern-filter-btn.is-active { background: #8b0018; border-color: #8b0018; color: #ffffff; }
      .history-appointment-list {
          position: relative;
          display: grid;
          gap: 8px;
          padding-left: 20px;
      }
      .history-appointment-list::before {
          content: "";
          position: absolute;
          left: 7px;
          top: 22px;
          bottom: 22px;
          width: 1px;
          background: #d8dee7;
      }
      .history-modern-entry {
          position: relative;
          min-width: 0;
      }
      .history-modern-entry[hidden] { display: none !important; }
      .history-entry-dot {
          position: absolute;
          left: -19px;
          top: 43px;
          z-index: 1;
          width: 14px;
          height: 14px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: 2px solid #8b0018;
          border-radius: 50%;
          box-sizing: border-box;
          background: #ffffff;
          color: #ffffff;
      }
      .history-entry-dot svg { width: 8px; height: 8px; stroke-width: 3; }
      .history-entry-dot.status-completed { border-color: #16a34a; background: #16a34a; }
      .history-entry-dot.status-pending,
      .history-entry-dot.status-approved { border-color: #e59600; background: #fff; }
      .history-entry-dot.status-missed,
      .history-entry-dot.status-expired { border-color: #dc334d; background: #dc334d; }
      .history-entry-dot.status-cancelled { border-color: #64748b; background: #fff; }
      .history-entry-month {
          margin: 0 0 5px 8px;
          color: #7f001d;
          font-size: 9px;
          font-weight: 900;
          letter-spacing: .04em;
          text-transform: uppercase;
      }
      .history-modern-appointment-card {
          min-width: 0;
          display: grid;
          grid-template-columns: 48px minmax(0, 1fr) 138px;
          align-items: center;
          gap: 11px;
          padding: 14px;
          border: 1px solid #e4e8ee;
          border-radius: 7px;
          background: #ffffff;
          transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
      }
      .history-modern-appointment-card:hover {
          transform: translateY(-1px);
          border-color: rgba(176,0,32,.24);
          box-shadow: 0 8px 18px rgba(39,13,20,.08);
      }
      .history-service-icon {
          width: 46px;
          height: 46px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
          background: #fff0f2;
          color: #a40021;
      }
      .history-service-icon.is-bp { background: #eaf8e9; color: #17863f; }
      .history-service-icon.is-other { background: #f1f5f9; color: #64748b; }
      .history-service-icon.is-status-approved { background: #eaf8e9; color: #17863f; }
      .history-service-icon.is-status-pending { background: #fff7da; color: #d97706; }
      .history-service-icon.is-status-alert { background: #fff0f2; color: #dc334d; }
      .history-service-icon svg { width: 24px; height: 24px; stroke-width: 1.8; }
      .history-entry-copy { min-width: 0; }
      .history-entry-service {
          display: block;
          margin-bottom: 6px;
          color: #4f0c17;
          font-size: 15px;
          font-weight: 900;
          line-height: 1.3;
      }
      .history-entry-meta {
          display: flex;
          flex-wrap: wrap;
          gap: 5px;
      }
      .history-entry-meta span {
          min-height: 25px;
          display: inline-flex;
          align-items: center;
          gap: 4px;
          padding: 4px 7px;
          border: 1px solid #e5eaf0;
          border-radius: 999px;
          background: #f8fafc;
          color: #526173;
          font-size: 10px;
          font-weight: 700;
      }
      .history-entry-meta svg { width: 10px; height: 10px; stroke-width: 2; }
      .history-entry-problem {
          display: block;
          margin-top: 6px;
          color: #64748b;
          font-size: 13px;
          line-height: 1.55;
      }
      .history-entry-actions {
          display: grid;
          justify-items: end;
          gap: 8px;
      }
      .history-entry-status {
          padding: 5px 8px;
          border-radius: 999px;
          background: #f1f5f9;
          color: #475569;
          font-size: 9px;
          font-weight: 900;
          text-transform: uppercase;
      }
      .history-entry-status.status-completed { background: #eaf8e9; color: #17863f; }
      .history-entry-status.status-pending,
      .history-entry-status.status-approved { background: #fff7da; color: #a75b00; }
      .history-entry-status.status-missed,
      .history-entry-status.status-expired { background: #fff0f2; color: #bd1734; }
      .history-entry-status.status-cancelled { background: #eef2f5; color: #596575; }
      .history-view-btn {
          min-height: 32px;
          display: inline-flex;
          align-items: center;
          gap: 5px;
          padding: 7px 11px;
          border: 1px solid rgba(176,0,32,.17);
          border-radius: 999px;
          background: #ffffff;
          color: #8b0018;
          font: inherit;
          font-size: 12px;
          font-weight: 850;
          cursor: pointer;
      }
      .history-view-btn svg { width: 12px; height: 12px; stroke-width: 2.2; }
      .history-entry-details {
          display: none;
          grid-column: 1 / -1;
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: 8px;
          padding: 11px 12px 0 57px;
          border-top: 1px solid #eef1f4;
      }
      .history-modern-appointment-card.is-expanded .history-entry-details { display: grid; }
      .history-detail-item { min-width: 0; display: grid; gap: 3px; }
      .history-detail-item span { color: #7b8798; font-size: 10px; font-weight: 850; text-transform: uppercase; }
      .history-detail-item strong { color: #263445; font-size: 12px; line-height: 1.45; overflow-wrap: anywhere; }
      .history-detail-actions {
          grid-column: 1 / -1;
          display: flex;
          justify-content: flex-end;
          padding-top: 3px;
      }
      .history-detail-actions .cancel-appointment-btn { min-height: 36px; padding: 0 13px; font-size: 12px; }
      .history-modern-pagination {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 7px;
          padding-top: 13px;
      }
      .history-modern-page-btn {
          width: 31px;
          height: 31px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: 1px solid #e2e8f0;
          border-radius: 7px;
          background: #ffffff;
          color: #64748b;
          font: inherit;
          font-size: 12px;
          font-weight: 850;
          cursor: pointer;
      }
      .history-modern-page-btn.is-active { border-color: #8b0018; background: #8b0018; color: #ffffff; }
      .history-modern-page-btn:disabled { opacity: .35; cursor: default; }
      .history-modern-sidebar { display: grid; gap: 12px; }
      .history-modern-side-card { padding: 16px; }
      .history-modern-side-title {
          display: flex;
          align-items: center;
          gap: 8px;
          margin: 0 0 13px;
          color: #7f001d;
          font-size: 15px;
          font-weight: 900;
      }
      .history-modern-side-title > span {
          width: 32px;
          height: 32px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border-radius: 7px;
          background: #fff0f2;
          color: #a40021;
      }
      .history-modern-side-title svg { width: 18px; height: 18px; stroke-width: 1.9; }
      .history-summary-list { display: grid; gap: 10px; margin: 0; }
      .history-summary-list > div { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
      .history-summary-list dt { display: inline-flex; align-items: center; gap: 8px; color: #475569; font-size: 12px; }
      .history-summary-list dt i { width: 7px; height: 7px; border-radius: 50%; background: #94a3b8; }
      .history-summary-list dt i.is-upcoming { background: #e59600; }
      .history-summary-list dt i.is-completed { background: #16a34a; }
      .history-summary-list dt i.is-missed { background: #ef4760; }
      .history-summary-list dd { margin: 0; color: #182033; font-size: 13px; font-weight: 900; }
      .history-summary-total {
          display: flex;
          justify-content: space-between;
          gap: 10px;
          margin-top: 14px;
          padding-top: 12px;
          border-top: 1px solid #e8edf2;
          color: #64748b;
          font-size: 11px;
      }
      .history-summary-total strong { color: #334155; }
      .history-quick-actions { display: grid; }
      .history-quick-actions a {
          min-height: 42px;
          display: grid;
          grid-template-columns: 19px minmax(0, 1fr) 15px;
          align-items: center;
          gap: 8px;
          padding: 8px 3px;
          border-bottom: 1px solid #eef1f4;
          color: #334155;
          font-size: 12px;
          text-decoration: none;
      }
      .history-quick-actions a:last-child { border-bottom: 0; }
      .history-quick-actions svg { width: 17px; height: 17px; color: #8b0018; stroke-width: 1.8; }
      .history-quick-actions a svg:last-child { color: #64748b; }
      .history-help-card { background: linear-gradient(145deg, #fffdf6, #fff9ed); }
      .history-help-card p { margin: 0 0 12px; color: #64748b; font-size: 12px; line-height: 1.55; }
      .history-contact-btn {
          min-height: 31px;
          display: inline-flex;
          align-items: center;
          gap: 6px;
          padding: 6px 10px;
          border: 1px solid rgba(176,0,32,.17);
          border-radius: 999px;
          color: #8b0018;
          font-size: 12px;
          font-weight: 850;
          text-decoration: none;
      }
      .history-contact-btn svg { width: 12px; height: 12px; }
      .history-filter-empty {
          display: none;
          min-height: 180px;
          align-items: center;
          justify-content: center;
          color: #64748b;
          font-size: 12px;
          text-align: center;
      }
      .history-filter-empty.is-visible { display: flex; }
      html[data-theme="dark"] .history-modern-hero {
          background: linear-gradient(90deg, rgba(43,0,14,.99), rgba(55,0,18,.97) 52%, rgba(14,8,18,.84));
      }
      html[data-theme="dark"] .history-modern-stat-card,
      html[data-theme="dark"] .history-modern-list-panel,
      html[data-theme="dark"] .history-modern-side-card {
          border-color: rgba(250,204,21,.15);
          background: #101722;
          box-shadow: 0 16px 34px rgba(0,0,0,.34);
      }
      html[data-theme="dark"] .history-modern-stat-value,
      html[data-theme="dark"] .history-entry-service,
      html[data-theme="dark"] .history-summary-list dd,
      html[data-theme="dark"] .history-summary-total strong,
      html[data-theme="dark"] .history-detail-item strong { color: #f8fafc; }
      html[data-theme="dark"] .history-modern-stat-label,
      html[data-theme="dark"] .history-modern-stat-note,
      html[data-theme="dark"] .history-entry-problem,
      html[data-theme="dark"] .history-summary-list dt,
      html[data-theme="dark"] .history-summary-total,
      html[data-theme="dark"] .history-help-card p { color: #aeb8c7; }
      html[data-theme="dark"] .history-modern-filter-btn,
      html[data-theme="dark"] .history-modern-appointment-card,
      html[data-theme="dark"] .history-view-btn,
      html[data-theme="dark"] .history-modern-page-btn {
          border-color: rgba(148,163,184,.2);
          background: #151e2d;
          color: #e5e7eb;
      }
      html[data-theme="dark"] .history-modern-filter-btn.is-active,
      html[data-theme="dark"] .history-modern-page-btn.is-active { border-color: #facc15; background: #760018; color: #ffffff; }
      html[data-theme="dark"] .history-entry-meta span { border-color: rgba(148,163,184,.18); background: #182233; color: #cbd5e1; }
      html[data-theme="dark"] .history-entry-details { border-color: rgba(255,255,255,.09); }
      html[data-theme="dark"] .history-appointment-list::before { background: #334155; }
      html[data-theme="dark"] .history-entry-dot { background: #101722; }
      html[data-theme="dark"] .history-help-card { background: #111620; }
      html[data-theme="dark"] .history-quick-actions a { border-color: rgba(255,255,255,.09); color: #e5e7eb; }
      @media (max-width: 900px) {
          .history-modern-hero { grid-template-columns: minmax(0, 1fr) 250px; padding: 24px; }
          .history-modern-hero-main { grid-template-columns: 86px minmax(0, 1fr); gap: 17px; }
          .history-modern-emblem { width: 82px; height: 82px; }
          .history-modern-emblem svg { width: 45px; height: 45px; }
          .history-modern-content-grid { grid-template-columns: minmax(0, 1fr) 230px; }
      }
      @media (max-width: 780px) {
          .history-modern-hero,
          .history-modern-content-grid { grid-template-columns: 1fr; }
          .history-modern-hero-overview { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 6px 0 0; border-top: 1px solid rgba(255,255,255,.18); border-left: 0; }
          .history-modern-overview-item + .history-modern-overview-item { border-top: 0; border-left: 1px solid rgba(255,255,255,.14); padding-left: 18px; }
          .history-modern-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
          .history-modern-sidebar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
          .history-help-card { grid-column: 1 / -1; }
      }
      @media (max-width: 600px) {
          .history-modern-page { width: min(100% - 20px, 1060px); }
          .history-modern-hero { min-height: 0; margin-top: -4px; padding: 19px 16px; }
          .history-modern-hero-main { grid-template-columns: 62px minmax(0, 1fr); align-items: start; gap: 12px; }
          .history-modern-emblem { width: 60px; height: 60px; }
          .history-modern-emblem svg { width: 33px; height: 33px; }
          .history-modern-title { font-size: 25px; }
          .history-modern-chips { grid-column: 1 / -1; display: grid; grid-template-columns: 1fr; }
          .history-modern-hero-overview { grid-template-columns: 1fr; }
          .history-modern-overview-item + .history-modern-overview-item { border-top: 1px solid rgba(255,255,255,.14); border-left: 0; padding-left: 0; }
          .history-modern-appointment-card { grid-template-columns: 42px minmax(0, 1fr); align-items: start; }
          .history-entry-actions { grid-column: 2; display: flex; justify-content: space-between; align-items: center; justify-items: stretch; }
          .history-entry-details { grid-template-columns: 1fr; padding-left: 0; }
          .history-modern-sidebar { grid-template-columns: 1fr; }
          .history-help-card { grid-column: auto; }
      }
      @media (max-width: 420px) {
          .history-modern-stat-grid { grid-template-columns: 1fr; }
      }
    </style>
@endpush

@section('content')
    @include('student.partials.history-modern')

    @if(false)
    @php
        $totalAppointments = $appointments->count();
        $pendingAppointments = $appointments->filter(fn ($appt) => strtolower((string) $appt->status) === 'pending')->count();
        $approvedAppointments = $appointments->filter(fn ($appt) => strtolower((string) $appt->status) === 'approved')->count();
        $completedAppointments = $appointments->filter(fn ($appt) => strtolower((string) $appt->status) === 'completed')->count();
        $missedAppointments = $appointments->filter(fn ($appt) => strtolower((string) $appt->status) === 'missed')->count();
    @endphp
    <div class="container" style="padding-top: 5px; padding-bottom: 60px;">
      <div class="page-header">
        <div class="history-hero-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-3.13-6.838M21 3v6h-6" />
          </svg>
        </div>
        <div class="history-hero-kicker">Clinic Timeline</div>
        <h1 class="history-hero-title">Appointment History</h1>
        <p class="history-hero-text">View and manage your past and upcoming consultations.</p>
        <div class="history-hero-steps">
          <div class="history-hero-step">
            <span>Recent Appointments</span>
          </div>
          <div class="history-hero-step">
            <span>Current Status</span>
          </div>
          <div class="history-hero-step">
            <span>Upcoming Bookings</span>
          </div>
        </div>
      </div>

      <section class="card-history">
        @if($appointments->isNotEmpty())
          <div class="history-summary-grid">
            <div class="history-summary-card">
              <span class="history-summary-label">Total Appointments</span>
              <span class="history-summary-value">{{ $totalAppointments }}</span>
              <div class="history-summary-note">Your complete clinic appointment trail.</div>
            </div>
            <div class="history-summary-card">
              <span class="history-summary-label">Pending</span>
              <span class="history-summary-value">{{ $pendingAppointments }}</span>
              <div class="history-summary-note">Waiting for clinic review.</div>
            </div>
            <div class="history-summary-card">
              <span class="history-summary-label">Approved</span>
              <span class="history-summary-value">{{ $approvedAppointments }}</span>
              <div class="history-summary-note">Confirmed and scheduled.</div>
            </div>
            <div class="history-summary-card">
              <span class="history-summary-label">Completed</span>
              <span class="history-summary-value">{{ $completedAppointments }}</span>
              <div class="history-summary-note">Finished consultations on record.</div>
            </div>
            <div class="history-summary-card">
              <span class="history-summary-label">Missed</span>
              <span class="history-summary-value">{{ $missedAppointments }}</span>
              <div class="history-summary-note">Appointments marked as not attended.</div>
            </div>
          </div>
        @endif

        @if($appointments->isNotEmpty())
          <div class="history-toolbar">
            <div class="history-toolbar-copy">Filter your records by appointment status.</div>
            <div class="history-filter-row" id="historyFilterRow">
              <button type="button" class="history-filter-btn is-active" data-filter="all">All</button>
              <button type="button" class="history-filter-btn" data-filter="pending">Pending</button>
              <button type="button" class="history-filter-btn" data-filter="approved">Approved</button>
              <button type="button" class="history-filter-btn" data-filter="completed">Completed</button>
              <button type="button" class="history-filter-btn" data-filter="missed">Missed</button>
              <button type="button" class="history-filter-btn" data-filter="cancelled">Cancelled</button>
            </div>
          </div>
        @endif
        
        <div class="history-grid">
            @forelse($appointments as $appt)
                @php
                    $statusNormalized = strtolower((string) $appt->status);
                    $statusClass = match (strtolower((string) $appt->status)) {
                        'pending' => 'status-pending',
                        'approved' => 'status-approved',
                        'completed' => 'status-completed',
                        'missed' => 'status-missed',
                        'cancelled' => 'status-cancelled',
                        'expired' => 'status-expired',
                        default => 'status-default',
                    };
                    $appointmentAt = \Carbon\Carbon::parse($appt->date . ' ' . $appt->time);
                    $isUpcoming = $appointmentAt->isFuture() && in_array($statusNormalized, ['pending', 'approved'], true);
                @endphp
                <div class="apt-card {{ $statusClass }} {{ $isUpcoming ? 'is-upcoming' : '' }}" data-history-status="{{ $statusNormalized }}">
                  <div class="apt-header">
                    <div>
                      <div class="apt-service">{{ $appt->service }}</div>
                      <div class="apt-meta">
                        <span class="apt-meta-pill">Appointment No. {{ $appt->apt_id ?: 'N/A' }}</span>
                        <span class="apt-meta-pill">{{ $appt->name }}</span>
                        <span class="apt-meta-pill">{{ $appt->student_number ?: optional(optional($appt->user)->healthProfile)->student_number ?: optional($appt->user)->student_number ?: ($studentContext['student_number'] ?? $appt->student_id) }}</span>
                        <span class="apt-meta-pill">{{ $appt->email }}</span>
                        <span class="apt-meta-pill is-status {{ $statusClass }}">{{ $appt->status }}</span>
                        @if($isUpcoming)
                          <span class="apt-meta-pill">Upcoming Schedule</span>
                        @endif
                      </div>
                    </div>
                    <div class="apt-date">{{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</div>
                  </div>
                  
                  @if($appt->notes)
                      <div class="apt-notes"><strong>Notes:</strong> {{ $appt->notes }}</div>
                  @endif
                  
                  <div class="apt-footer">
                    <div class="apt-footer-actions">
                      @if($appt->status == 'Pending' || $appt->status == 'Approved')
                          <button
                              type="button"
                              class="cancel-appointment-btn js-open-cancel-dialog"
                              data-cancel-url="{{ url('/student/appointments/' . $appt->id . '/cancel') }}"
                              data-cancel-service="{{ $appt->service }}"
                              data-cancel-date="{{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }}"
                              data-cancel-time="{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}"
                              data-cancel-name="{{ $appt->name }}"
                          >
                              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                  <path d="M12 8v4m0 4h.01M10.29 3.86l-7.3 12.71A2 2 0 0 0 4.7 19.5h14.6a2 2 0 0 0 1.71-2.93l-7.3-12.71a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                              Cancel Appointment
                          </button>
                      @else
                          <span class="apt-action-note">No actions available</span>
                      @endif
                    </div>
                  </div>
                </div>
            @empty
                <div class="empty-state" id="emptyHistoryState">
                  <div class="empty-illustration" aria-hidden="true">
                    <div class="empty-dot-wave">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                  </div>
                  <h2 class="empty-title">You have no appointment history yet</h2>
                  <a href="{{ url('/student/booking') }}" class="btn-outline empty-cta" id="emptyHistoryCta">Book your first appointment</a>
                </div>
            @endforelse
        </div>

        <div class="cancel-dialog-backdrop" id="cancelDialogBackdrop" aria-hidden="true">
          <div class="cancel-dialog" role="dialog" aria-modal="true" aria-labelledby="cancelDialogTitle">
            <div class="cancel-dialog-header">
              <div class="cancel-dialog-kicker">Appointment Control</div>
              <h2 class="cancel-dialog-title" id="cancelDialogTitle">Cancel appointment</h2>
              <div class="cancel-dialog-copy">
                Please review the appointment details before submitting the cancellation.
              </div>
            </div>
            <div class="cancel-dialog-body">
              <div class="cancel-dialog-summary">
                <div class="cancel-dialog-summary-row">
                  <span class="cancel-dialog-summary-label">Student</span>
                  <span class="cancel-dialog-summary-value" id="cancelDialogName">-</span>
                </div>
                <div class="cancel-dialog-summary-row">
                  <span class="cancel-dialog-summary-label">Service</span>
                  <span class="cancel-dialog-summary-value" id="cancelDialogService">-</span>
                </div>
                <div class="cancel-dialog-summary-row">
                  <span class="cancel-dialog-summary-label">Schedule</span>
                  <span class="cancel-dialog-summary-value" id="cancelDialogSchedule">-</span>
                </div>
              </div>
              <div class="cancel-dialog-warning">
                Once cancelled, this appointment will move to your history as cancelled and you will need to book again if you still need the service.
              </div>
            </div>
            <form method="POST" id="cancelDialogForm">
              @csrf
              <div class="cancel-dialog-actions">
                <button type="button" class="cancel-appointment-btn secondary" id="cancelDialogClose">Keep Appointment</button>
                <button type="submit" class="cancel-appointment-btn">Yes, Cancel</button>
              </div>
            </form>
          </div>
        </div>
        
      </section>
    </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emptyState = document.getElementById('emptyHistoryState');
    const cta = document.getElementById('emptyHistoryCta');
    const filterRow = document.getElementById('historyFilterRow');
    const historyEntries = Array.from(document.querySelectorAll('.history-modern-entry[data-history-group]'));
    const filterEmpty = document.getElementById('historyFilterEmpty');
    const pagination = document.getElementById('historyPagination');
    const cancelBackdrop = document.getElementById('cancelDialogBackdrop');
    const cancelDialogForm = document.getElementById('cancelDialogForm');
    const cancelDialogName = document.getElementById('cancelDialogName');
    const cancelDialogService = document.getElementById('cancelDialogService');
    const cancelDialogSchedule = document.getElementById('cancelDialogSchedule');
    const cancelDialogTitle = document.getElementById('cancelDialogTitle');
    const cancelDialogClose = document.getElementById('cancelDialogClose');
    const cancelButtons = Array.from(document.querySelectorAll('.js-open-cancel-dialog'));
    const detailsButtons = Array.from(document.querySelectorAll('.js-toggle-history-details'));
    const pageSize = 5;
    let activeFilter = 'all';
    let currentPage = 1;
    let lastFocusedElement = null;

    const matchingEntries = function () {
        return historyEntries.filter(function (entry) {
            return activeFilter === 'all' || entry.dataset.historyGroup === activeFilter;
        });
    };

    const createPageButton = function (label, page, options = {}) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'history-modern-page-btn';
        button.textContent = label;
        button.disabled = Boolean(options.disabled);
        button.classList.toggle('is-active', Boolean(options.active));
        if (options.label) {
            button.setAttribute('aria-label', options.label);
        }
        if (options.active) {
            button.setAttribute('aria-current', 'page');
        }
        button.addEventListener('click', function () {
            currentPage = page;
            renderHistory();
        });
        return button;
    };

    const renderPagination = function (totalPages) {
        if (!pagination) {
            return;
        }

        pagination.replaceChildren();
        if (totalPages <= 1) {
            pagination.hidden = true;
            return;
        }

        pagination.hidden = false;
        pagination.appendChild(createPageButton('\u2039', Math.max(1, currentPage - 1), {
            disabled: currentPage === 1,
            label: 'Previous page'
        }));

        for (let page = 1; page <= totalPages; page += 1) {
            pagination.appendChild(createPageButton(String(page), page, {
                active: page === currentPage,
                label: `Page ${page}`
            }));
        }

        pagination.appendChild(createPageButton('\u203a', Math.min(totalPages, currentPage + 1), {
            disabled: currentPage === totalPages,
            label: 'Next page'
        }));
    };

    const renderHistory = function () {
        const matches = matchingEntries();
        const totalPages = Math.max(1, Math.ceil(matches.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);
        const pageStart = (currentPage - 1) * pageSize;
        const visibleEntries = matches.slice(pageStart, pageStart + pageSize);
        let previousMonth = null;

        historyEntries.forEach(function (entry) {
            entry.hidden = true;
            const monthLabel = entry.querySelector('.history-entry-month');
            if (monthLabel) {
                monthLabel.hidden = true;
            }
        });

        visibleEntries.forEach(function (entry) {
            entry.hidden = false;
            const month = entry.dataset.historyMonth || '';
            const monthLabel = entry.querySelector('.history-entry-month');
            if (monthLabel) {
                monthLabel.hidden = month === previousMonth;
            }
            previousMonth = month;
        });

        if (filterEmpty) {
            filterEmpty.classList.toggle('is-visible', matches.length === 0);
        }

        renderPagination(totalPages);
    };

    const closeCancelDialog = function () {
        if (!cancelBackdrop) {
            return;
        }

        cancelBackdrop.classList.remove('is-open');
        cancelBackdrop.setAttribute('aria-hidden', 'true');

        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }
    };

    const openCancelDialog = function (button) {
        if (!cancelBackdrop || !cancelDialogForm) {
            return;
        }

        lastFocusedElement = button || document.activeElement;
        cancelDialogForm.action = button.dataset.cancelUrl || '#';
        cancelDialogName.textContent = button.dataset.cancelName || '-';
        cancelDialogService.textContent = button.dataset.cancelService || '-';
        cancelDialogSchedule.textContent = `${button.dataset.cancelDate || '-'} at ${button.dataset.cancelTime || '-'}`;
        cancelDialogTitle.textContent = `Cancel ${button.dataset.cancelService || 'appointment'}`;
        cancelBackdrop.classList.add('is-open');
        cancelBackdrop.setAttribute('aria-hidden', 'false');
        cancelDialogClose.focus();
    };

    if (filterRow && historyEntries.length) {
        const filterButtons = Array.from(filterRow.querySelectorAll('.history-modern-filter-btn'));

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activeFilter = button.dataset.filter || 'all';
                currentPage = 1;

                filterButtons.forEach(function (btn) {
                    btn.classList.toggle('is-active', btn === button);
                });
                renderHistory();
            });
        });
    }

    detailsButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const card = button.closest('.history-modern-appointment-card');
            const details = card ? card.querySelector('.history-entry-details') : null;
            if (!card || !details) {
                return;
            }

            const isOpen = card.classList.toggle('is-expanded');
            button.setAttribute('aria-expanded', String(isOpen));
            details.setAttribute('aria-hidden', String(!isOpen));
            button.firstChild.textContent = isOpen ? 'Hide Details ' : 'View Details ';
        });
    });

    cancelButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openCancelDialog(button);
        });
    });

    if (cancelBackdrop) {
        cancelBackdrop.addEventListener('click', function (event) {
            if (event.target === cancelBackdrop) {
                closeCancelDialog();
            }
        });
    }

    if (cancelDialogClose) {
        cancelDialogClose.addEventListener('click', closeCancelDialog);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && cancelBackdrop && cancelBackdrop.classList.contains('is-open')) {
            closeCancelDialog();
        }
    });

    if (historyEntries.length) {
        renderHistory();
    }

    if (!emptyState || !cta) {
        return;
    }

    const activate = () => emptyState.classList.add('is-celebrating');
    const deactivate = () => emptyState.classList.remove('is-celebrating');

    cta.addEventListener('mouseenter', activate);
    cta.addEventListener('mouseleave', deactivate);
    cta.addEventListener('focus', activate);
    cta.addEventListener('blur', deactivate);
});
</script>
@endpush
