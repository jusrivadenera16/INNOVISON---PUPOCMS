        .requirement-grid {
            align-items: stretch;
            gap: 10px;
        }

        .requirement-card {
            min-width: 0;
            min-height: auto;
            padding: 10px;
            border-radius: 12px;
            background: linear-gradient(160deg, #ffffff 0%, #fffaf8 100%);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.045);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .requirement-card.file-selected {
            box-shadow: 0 8px 20px rgba(127, 29, 45, 0.1);
            transform: none;
        }

        .requirement-card.has-invalid-file {
            border-color: #c43138;
            box-shadow: 0 0 0 2px rgba(196, 49, 56, 0.1);
        }

        .requirement-card-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            gap: 8px;
            margin-bottom: 8px;
        }

        .requirement-heading-main {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            min-width: 0;
        }

        .requirement-icon {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            border-radius: 8px;
            background: #fff0ef;
            color: var(--clinic-maroon);
        }

        .requirement-icon svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.8;
        }

        .requirement-heading-copy {
            min-width: 0;
        }

        .requirement-card-header strong {
            display: block;
            margin: 0;
            color: #70131b;
            font-size: 0.98rem;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .requirement-description {
            display: block;
            margin: 3px 0 0;
            color: #647084;
            font-size: 0.78rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .requirement-badge {
            align-self: start;
            padding: 4px 8px;
            border: 1px solid rgba(100, 116, 139, 0.08);
            border-radius: 999px;
            background: #f1f2f4;
            color: #596170;
            font-size: 0.68rem;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
        }

        .requirement-badge.is-required {
            background: #ffe2e0;
            color: #a2232e;
        }

        .requirement-badge.is-on-file {
            background: #e7f5ec;
            color: #237345;
        }

        .requirement-guideline,
        .upload-example-grid,
        .requirement-card > small {
            display: none !important;
        }

        .requirement-file-input {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
            opacity: 0 !important;
        }

        .requirement-dropzone {
            display: flex;
            min-height: 106px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            margin-top: 7px;
            padding: 8px;
            border: 1px dashed rgba(127, 29, 45, 0.24);
            border-radius: 8px;
            background: rgba(255, 249, 246, 0.85);
            color: #677084;
            text-align: center;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .requirement-file-input:focus-visible + .requirement-dropzone,
        .requirement-dropzone.is-dragging {
            outline: 2px solid rgba(127, 29, 45, 0.22);
            outline-offset: 2px;
            border-color: var(--clinic-maroon);
            background: #fff3ef;
        }

        .requirement-dropzone-icon {
            width: 26px;
            height: 26px;
            color: var(--clinic-maroon);
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.7;
        }

        .requirement-dropzone strong {
            color: #636d7d;
            font-size: 0.74rem;
            line-height: 1.3;
        }

        .requirement-or {
            font-size: 0.68rem;
        }

        .requirement-pick-button {
            min-width: 98px;
            min-height: 30px;
            padding: 4px 12px;
            border: 0;
            border-radius: 6px;
            background: var(--clinic-maroon);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            cursor: pointer;
        }

        .requirement-pick-button:hover,
        .requirement-pick-button:focus-visible {
            background: var(--clinic-maroon-dark);
        }

        .requirement-pick-button:disabled {
            background: #b7bdc7;
            cursor: not-allowed;
        }

        .requirement-file-meta {
            margin-top: 2px;
            color: #727d8d;
            font-size: 0.68rem;
            line-height: 1.2;
        }

        .requirement-upload-error {
            color: #a2232e;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .requirement-sample-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 28px;
            margin-top: 5px;
            padding: 3px 9px;
            border: 1px solid rgba(127, 29, 45, 0.28);
            border-radius: 999px;
            background: #ffffff;
            color: var(--clinic-maroon);
            font-size: 0.68rem;
            font-weight: 800;
            cursor: pointer;
        }

        .requirement-sample-button:hover,
        .requirement-sample-button:focus-visible {
            background: #fff1ef;
            border-color: rgba(127, 29, 45, 0.55);
        }

        .requirement-sample-button svg {
            width: 13px;
            height: 13px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.8;
        }

        .requirement-sample-dialog {
            width: min(680px, calc(100% - 28px));
            max-width: none;
            max-height: min(90dvh, 760px);
            padding: 0;
            overflow: hidden;
            border: 1px solid rgba(127, 29, 45, 0.18);
            border-radius: 14px;
            background: transparent;
            color: #111827;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        }

        .requirement-sample-dialog::backdrop {
            background: rgba(19, 13, 16, 0.64);
            backdrop-filter: blur(3px);
        }

        .requirement-sample-card {
            display: flex;
            max-height: min(90dvh, 760px);
            flex-direction: column;
            background: #ffffff;
        }

        .requirement-sample-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #f0dfe0;
            background: #fffafa;
        }

        .requirement-sample-heading {
            display: flex;
            gap: 9px;
            min-width: 0;
        }

        .requirement-sample-heading .requirement-icon {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
        }

        .requirement-sample-heading h3 {
            margin: 0;
            color: #70131b;
            font-size: 1rem;
            font-weight: 850;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .requirement-sample-heading p {
            margin: 3px 0 0;
            color: #687386;
            font-size: 0.78rem;
            line-height: 1.35;
        }

        .requirement-sample-close {
            display: grid;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            place-items: center;
            border: 1px solid #e4d6d8;
            border-radius: 50%;
            background: #ffffff;
            color: #667085;
            cursor: pointer;
        }

        .requirement-sample-close svg {
            width: 15px;
            height: 15px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-width: 2;
        }

        .requirement-sample-body {
            overflow-y: auto;
            padding: 12px 14px 14px;
        }

        .requirement-sample-examples {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .requirement-sample-examples[hidden] {
            display: none !important;
        }

        .requirement-sample-example {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #d9e7dc;
            border-radius: 9px;
            background: #f7fcf8;
        }

        .requirement-sample-example.is-wrong {
            border-color: #f1d6d5;
            background: #fff8f7;
        }

        .requirement-sample-example h4 {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 7px 9px;
            color: #267447;
            font-size: 0.78rem;
            font-weight: 850;
        }

        .requirement-sample-example.is-wrong h4 {
            color: #a2232e;
        }

        .requirement-sample-example h4 span {
            display: grid;
            width: 16px;
            height: 16px;
            place-items: center;
            border-radius: 50%;
            background: #27844b;
            color: #ffffff;
            font-size: 0.65rem;
            line-height: 1;
        }

        .requirement-sample-example.is-wrong h4 span {
            background: #c43138;
        }

        .requirement-sample-example img {
            display: block;
            width: calc(100% - 16px);
            height: 184px;
            margin: 0 8px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 5px;
            background: #ffffff;
            object-fit: contain;
        }

        .requirement-sample-example p {
            min-height: 34px;
            margin: 0;
            padding: 7px 9px;
            color: #596273;
            font-size: 0.72rem;
            line-height: 1.35;
        }

        .requirement-sample-reminders {
            display: grid;
            grid-template-columns: 20px minmax(0, 1fr);
            gap: 7px;
            margin-top: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            background: #edf5fc;
            color: #3e5368;
        }

        .requirement-sample-reminders > span {
            display: grid;
            width: 18px;
            height: 18px;
            place-items: center;
            border-radius: 50%;
            background: #2584c5;
            color: #ffffff;
            font-size: 0.68rem;
            font-weight: 900;
        }

        .requirement-sample-reminders strong {
            display: block;
            color: #344b63;
            font-size: 0.74rem;
        }

        .requirement-sample-reminders p {
            margin: 3px 0 0;
            color: #53677d;
            font-size: 0.7rem;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        @media (max-width: 768px) {
            .requirement-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 430px) {
            .requirement-card {
                padding: 9px;
            }

            .requirement-sample-dialog {
                width: calc(100% - 16px);
                max-height: 92dvh;
                border-radius: 11px;
            }

            .requirement-sample-card {
                max-height: 92dvh;
            }

            .requirement-sample-header {
                padding: 11px 12px;
            }

            .requirement-sample-body {
                padding: 10px;
            }

            .requirement-sample-examples {
                grid-template-columns: 1fr;
            }

            .requirement-sample-example img {
                height: 190px;
            }
        }
