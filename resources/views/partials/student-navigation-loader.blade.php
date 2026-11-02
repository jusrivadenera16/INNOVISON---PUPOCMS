<div class="student-navigation-loader" id="studentNavigationLoader" role="status" aria-live="assertive" aria-label="Opening Portal" aria-hidden="true">
    <div class="student-loader-copy">
        <div class="student-loader-heartbeat" aria-hidden="true">
            <svg viewBox="0 0 150 73" focusable="false">
                <polyline points="0,45.486 38.514,45.486 44.595,33.324 50.676,45.486 57.771,45.486 62.838,55.622 71.959,9 80.067,63.729 84.122,45.486 97.297,45.486 103.379,40.419 110.473,45.486 150,45.486" class="student-loader-trace"></polyline>
                <polyline points="0,45.486 38.514,45.486 44.595,33.324 50.676,45.486 57.771,45.486 62.838,55.622 71.959,9 80.067,63.729 84.122,45.486 97.297,45.486 103.379,40.419 110.473,45.486 150,45.486" class="student-loader-pulse" pathLength="500"></polyline>
            </svg>
        </div>
        <div class="student-loader-message">
            <span data-student-loader-title>Opening Portal</span><span class="student-loader-dots" aria-hidden="true"><span>.</span><span>.</span><span>.</span></span>
        </div>
        <p class="student-loader-detail" data-student-loader-detail>Loading your portal information.</p>
    </div>
</div>

<script>
    (function () {
        const loader = document.getElementById('studentNavigationLoader');
        if (!loader) return;

        const handoffKey = 'pup_student_navigation_loader_handoff';
        const titleNode = loader.querySelector('[data-student-loader-title]');
        const detailNode = loader.querySelector('[data-student-loader-detail]');
        const isStudentShellPath = (path) => /^\/(student(?:\/|$)|account(?:\/|$)|health-form(?:\/|$))/.test(String(path || ''));

        const show = function (copy) {
            const title = String(copy?.title || 'Opening Portal').trim();
            const detail = String(copy?.detail || 'Loading your portal information.').trim();
            if (titleNode) titleNode.textContent = title;
            if (detailNode) detailNode.textContent = detail;
            loader.classList.add('is-active');
            loader.setAttribute('aria-hidden', 'false');
            loader.setAttribute('aria-label', title + '...');
            document.body.setAttribute('aria-busy', 'true');
        };

        const clear = function () {
            loader.classList.remove('is-active');
            loader.setAttribute('aria-hidden', 'true');
            loader.setAttribute('aria-label', 'Opening Portal');
            document.body.removeAttribute('aria-busy');
            document.documentElement.removeAttribute('data-student-navigation-pending');
            document.documentElement.style.removeProperty('--student-pending-loader-title');
            document.documentElement.style.removeProperty('--student-pending-loader-detail');
        };

        let handoff = null;
        try {
            const rawHandoff = sessionStorage.getItem(handoffKey);
            if (rawHandoff) {
                sessionStorage.removeItem(handoffKey);
                const parsed = JSON.parse(rawHandoff);
                const age = Date.now() - Number(parsed?.startedAt || 0);
                if (age >= 0 && age <= 90000
                    && isStudentShellPath(parsed?.destinationPath)
                    && isStudentShellPath(window.location.pathname)) {
                    handoff = parsed;
                }
            }
        } catch (error) {
            handoff = null;
        }

        if (handoff) show(handoff);
        clearPendingCssState();

        function clearPendingCssState() {
            document.documentElement.removeAttribute('data-student-navigation-pending');
            document.documentElement.style.removeProperty('--student-pending-loader-title');
            document.documentElement.style.removeProperty('--student-pending-loader-detail');
        }

        window.addEventListener('pageshow', clear);

        const resolveCopy = function (source, mode, destination) {
            const form = source instanceof HTMLFormElement ? source : source?.form;
            const label = [
                source?.textContent,
                source?.value,
                source?.getAttribute?.('aria-label'),
                source?.getAttribute?.('title'),
            ].filter(Boolean).join(' ').replace(/\s+/g, ' ').trim().toLowerCase();
            const path = destination.pathname.toLowerCase();
            const query = destination.searchParams;
            const context = (label + ' ' + path + ' ' + destination.hash).toLowerCase();

            if (mode === 'submit') {
                if (/cancel/.test(context)) return { title: 'Cancelling Appointment', detail: 'Updating your appointment status.' };
                if (/evaluation|satisfaction survey|feedback/.test(context)) return { title: 'Submitting Service Evaluation', detail: 'Sending your feedback to the clinic.' };
                if (/health-form|health_record|health-record|dependent-profile/.test(context)) return { title: 'Submitting Health Information', detail: 'Saving your health form and supporting information.' };
                if (/appointments\/store|booking|book appointment/.test(context)) return { title: 'Booking Appointment', detail: 'Checking the selected schedule and saving your request.' };
                if (/notifications\/preferences/.test(path)) return { title: 'Saving Notification Preferences', detail: 'Updating how the clinic sends your notifications.' };
                if (/notifications\/(?:mark-all-read|read-all)/.test(path)) return { title: 'Updating Notifications', detail: 'Saving your notification read status.' };
                if (/student\/account|update-contact/.test(context)) return { title: 'Saving Profile Information', detail: 'Updating your account details.' };
                return { title: 'Submitting Request', detail: 'Sending your information to the clinic.' };
            }

            if (/student\/account\/?$/.test(path)) {
                if (query.get('view') === 'health-record') {
                    return { title: 'Opening Health Record', detail: 'Loading your health information and submitted documents.' };
                }
                if (query.get('view') === 'notifications') {
                    return { title: 'Opening Notifications', detail: 'Loading messages and updates from the clinic.' };
                }
                return { title: 'Opening Profile', detail: 'Loading your account information.' };
            }
            if (/student\/evaluations|student\/evaluation-form/.test(path)) {
                return { title: 'Opening Service Evaluation', detail: 'Loading the clinic service evaluation form.' };
            }
            if (/health_record|health-record/.test(path)) {
                return { title: 'Opening Health Record', detail: 'Loading your health information and submitted documents.' };
            }
            if (/health-form|dependent-profile/.test(path)) {
                return { title: 'Opening Health Form', detail: 'Loading your health information and form details.' };
            }
            if (/student\/history/.test(path)) {
                return { title: 'Opening History', detail: 'Loading your appointment records and updates.' };
            }
            if (/student\/booking|student\/appointments(?:\/|$)/.test(path)) {
                return { title: 'Opening Appointments', detail: 'Loading clinic services and available appointment schedules.' };
            }
            if (/student\/notifications/.test(path)) {
                return { title: 'Opening Notifications', detail: 'Loading messages and updates from the clinic.' };
            }
            if (/student\/faq/.test(path)) {
                return { title: 'Opening FAQs', detail: 'Loading clinic information and answers to common questions.' };
            }
            if (/student\/home/.test(path)) {
                if (destination.hash === '#about') {
                    return { title: 'Opening About Us', detail: 'Loading clinic information and available services.' };
                }
                return { title: 'Opening Home', detail: 'Loading clinic announcements and your dashboard.' };
            }

            return { title: 'Opening Portal', detail: 'Loading your portal information.' };
        };

        const persist = function (copy, destination) {
            if (!isStudentShellPath(destination.pathname)) return;
            try {
                sessionStorage.setItem(handoffKey, JSON.stringify({
                    title: copy.title,
                    detail: copy.detail,
                    destinationPath: destination.pathname,
                    startedAt: Date.now(),
                }));
            } catch (error) {
                // The current page still shows its loader if browser storage is blocked.
            }
        };

        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const link = event.target.closest('a[href]');
            if (!link || link.closest('#studentNavigationLoader') || link.matches('[download]') || link.target === '_blank') return;
            if ((link.dataset.loaderScope || '').toLowerCase() === 'none') return;

            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:')) return;

            let destination;
            try {
                destination = new URL(link.href, window.location.href);
            } catch (error) {
                return;
            }
            if (destination.origin !== window.location.origin || !isStudentShellPath(destination.pathname)) return;
            if (destination.pathname === window.location.pathname && destination.search === window.location.search) return;

            const copy = resolveCopy(link, 'navigate', destination);
            persist(copy, destination);
            show(copy);
        });

        document.addEventListener('submit', function (event) {
            if (event.defaultPrevented) return;
            const form = event.target;
            if (!(form instanceof HTMLFormElement)
                || form.matches('#logout-form, #termsGateLogoutForm, [data-no-navigation-loader]')
                || form.target === '_blank') return;

            const submitterScope = event.submitter?.dataset?.loaderScope || '';
            const loaderScope = (submitterScope || form.dataset.loaderScope || '').toLowerCase();
            if (loaderScope === 'none') return;

            let destination;
            try {
                destination = new URL(form.getAttribute('action') || window.location.href, window.location.href);
            } catch (error) {
                return;
            }
            if (destination.origin !== window.location.origin || !isStudentShellPath(destination.pathname)) return;

            const method = (form.getAttribute('method') || 'get').toLowerCase();
            if (method === 'get' && destination.pathname === window.location.pathname && destination.search === window.location.search) return;

            const source = event.submitter || form;
            const copy = resolveCopy(source, 'submit', destination);
            persist(copy, destination);
            show(copy);
        });
    })();
</script>
