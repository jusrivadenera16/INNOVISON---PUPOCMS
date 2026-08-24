@once
    <style>
        :root {
            --system-footer-bg: #23272b;
            --system-footer-border: #7a0c10;
            --system-footer-bottom: rgba(255, 255, 255, 0.72);
            --system-footer-text: #f8fafc;
            --system-footer-link: #ffffff;
            --system-footer-accent: #fff3bf;
            --system-footer-separator: rgba(255, 255, 255, 0.28);
        }

        html[data-theme="light"] {
            --system-footer-bg: #1f2937;
            --system-footer-border: #8b0000;
            --system-footer-bottom: rgba(148, 163, 184, 0.78);
            --system-footer-text: #f8fafc;
            --system-footer-link: #ffffff;
            --system-footer-accent: #fde68a;
            --system-footer-separator: rgba(255, 255, 255, 0.3);
        }

        html[data-theme="dark"] {
            --system-footer-bg: #181c20;
            --system-footer-border: #a11a1f;
            --system-footer-bottom: rgba(255, 255, 255, 0.62);
            --system-footer-text: #f8fafc;
            --system-footer-link: #ffffff;
            --system-footer-accent: #fff3bf;
            --system-footer-separator: rgba(255, 255, 255, 0.24);
        }

        .system-footer {
            width: 100%;
            margin: 0;
            background: var(--system-footer-bg);
            border-top: 3px solid var(--system-footer-border);
            border-bottom: 2px solid var(--system-footer-bottom);
            border-radius: 0;
            color: var(--system-footer-text);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .system-footer__inner {
            width: 100%;
            min-height: 42px;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            text-align: center;
            font-size: 14px;
            line-height: 1.4;
        }

        .system-footer strong {
            color: var(--system-footer-accent);
            font-weight: 800;
        }

        .system-footer a {
            color: var(--system-footer-link);
            text-decoration: none;
            font-weight: 700;
        }

        .system-footer a:hover,
        .system-footer a:focus-visible {
            color: #facc15;
            text-decoration: underline;
            outline: none;
        }

        .system-footer__separator {
            width: 2px;
            height: 18px;
            background: var(--system-footer-separator);
            border-radius: 999px;
        }

        @media (max-width: 640px) {
            .system-footer {
                margin-top: 0;
            }

            .system-footer__inner {
                flex-direction: row;
                column-gap: 16px;
                row-gap: 6px;
                min-height: auto;
                padding: 10px 12px;
                font-size: 13px;
            }

            .system-footer__inner > span:first-child {
                flex: 1 0 100%;
            }

            .system-footer__inner > a {
                white-space: nowrap;
            }

            .system-footer__separator {
                display: none;
            }
        }
    </style>
@endonce

<footer class="system-footer" role="contentinfo" aria-label="System footer">
    <div class="system-footer__inner">
        <span>&copy; 1998-{{ now()->year }} <strong>Polytechnic University of the Philippines</strong></span>
        <span class="system-footer__separator" aria-hidden="true"></span>
        <a href="https://www.pup.edu.ph/terms/" target="_blank" rel="noopener noreferrer">Terms of Use</a>
        <span class="system-footer__separator" aria-hidden="true"></span>
        <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">Privacy Statement</a>
    </div>
</footer>
