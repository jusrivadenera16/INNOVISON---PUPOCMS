@php
    $totalAppointments = $appointments->count();
    $upcomingAppointments = $appointments->filter(function ($appointment) {
        $status = strtolower(trim((string) $appointment->status));
        if (!in_array($status, ['pending', 'approved'], true)) {
            return false;
        }

        try {
            return \Carbon\Carbon::parse($appointment->date . ' ' . $appointment->time)->isFuture();
        } catch (\Throwable $exception) {
            return false;
        }
    })->count();
    $completedAppointments = $appointments->filter(fn ($appointment) => strtolower(trim((string) $appointment->status)) === 'completed')->count();
    $missedAppointments = $appointments->filter(fn ($appointment) => in_array(strtolower(trim((string) $appointment->status)), ['missed', 'expired'], true))->count();
    $cancelledAppointments = $appointments->filter(fn ($appointment) => strtolower(trim((string) $appointment->status)) === 'cancelled')->count();
    $lastVisitAppointment = $appointments
        ->filter(function ($appointment) {
            return strtolower(trim((string) $appointment->status)) === 'completed';
        })
        ->sortByDesc(function ($appointment) {
            try {
                return \Carbon\Carbon::parse($appointment->date . ' ' . $appointment->time)->timestamp;
            } catch (\Throwable $exception) {
                return 0;
            }
        })
        ->first();
    $lastVisitLabel = 'No visits yet';
    if ($lastVisitAppointment) {
        try {
            $lastVisitLabel = \Carbon\Carbon::parse($lastVisitAppointment->date . ' ' . $lastVisitAppointment->time)->format('M d, Y g:i A');
        } catch (\Throwable $exception) {
            $lastVisitLabel = 'Recorded appointment';
        }
    }
@endphp

<div class="history-modern-page">
    <section class="history-modern-hero" aria-labelledby="appointmentHistoryTitle">
        <div class="history-modern-hero-main">
            <span class="history-modern-emblem" aria-hidden="true">
                <x-outline-icon name="clock" />
            </span>
            <div>
                <div class="history-modern-kicker">
                    <x-outline-icon name="sparkles" />
                    Clinic Timeline
                </div>
                <h1 class="history-modern-title" id="appointmentHistoryTitle">Appointment History</h1>
                <p class="history-modern-description">Review your appointment records, completed consultations, and upcoming schedules.</p>
                <div class="history-modern-chips" aria-label="Appointment history features">
                    <span class="history-modern-chip"><x-outline-icon name="calendar-days" /> Recent Appointments</span>
                    <span class="history-modern-chip"><x-outline-icon name="clock" /> Current Status</span>
                    <span class="history-modern-chip"><x-outline-icon name="clipboard-document-list" /> Upcoming Bookings</span>
                </div>
            </div>
        </div>
        <div class="history-modern-hero-overview">
            <div class="history-modern-overview-item">
                <span class="history-modern-overview-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
                <span>
                    <span class="history-modern-overview-label">Upcoming</span>
                    <strong class="history-modern-overview-value">{{ $upcomingAppointments }} {{ \Illuminate\Support\Str::plural('Appointment', $upcomingAppointments) }}</strong>
                </span>
            </div>
            <div class="history-modern-overview-item">
                <span class="history-modern-overview-icon" aria-hidden="true"><x-outline-icon name="clock" /></span>
                <span>
                    <span class="history-modern-overview-label">Last Visit</span>
                    <strong class="history-modern-overview-value">{{ $lastVisitLabel }}</strong>
                </span>
            </div>
        </div>
    </section>

    <section class="history-modern-stat-grid" aria-label="Appointment totals">
        <article class="history-modern-stat-card">
            <span class="history-modern-stat-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
            <span class="history-modern-stat-copy">
                <span class="history-modern-stat-label">Total Appointments</span>
                <strong class="history-modern-stat-value">{{ $totalAppointments }}</strong>
                <span class="history-modern-stat-note">All appointment records</span>
            </span>
        </article>
        <article class="history-modern-stat-card is-upcoming">
            <span class="history-modern-stat-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
            <span class="history-modern-stat-copy">
                <span class="history-modern-stat-label">Upcoming</span>
                <strong class="history-modern-stat-value">{{ $upcomingAppointments }}</strong>
                <span class="history-modern-stat-note">Scheduled appointments</span>
            </span>
        </article>
        <article class="history-modern-stat-card is-completed">
            <span class="history-modern-stat-icon" aria-hidden="true"><x-outline-icon name="check" /></span>
            <span class="history-modern-stat-copy">
                <span class="history-modern-stat-label">Completed</span>
                <strong class="history-modern-stat-value">{{ $completedAppointments }}</strong>
                <span class="history-modern-stat-note">Finished consultations</span>
            </span>
        </article>
        <article class="history-modern-stat-card is-missed">
            <span class="history-modern-stat-icon" aria-hidden="true"><x-outline-icon name="x-mark" /></span>
            <span class="history-modern-stat-copy">
                <span class="history-modern-stat-label">Missed</span>
                <strong class="history-modern-stat-value">{{ $missedAppointments }}</strong>
                <span class="history-modern-stat-note">Not attended</span>
            </span>
        </article>
    </section>

    <div class="history-modern-content-grid">
        <main class="history-modern-list-panel">
            @if($appointments->isNotEmpty())
                <div class="history-modern-filter-row" id="historyFilterRow" aria-label="Filter appointment history">
                    <button type="button" class="history-modern-filter-btn is-active" data-filter="all">All</button>
                    <button type="button" class="history-modern-filter-btn" data-filter="upcoming"><x-outline-icon name="calendar-days" /> Upcoming</button>
                    <button type="button" class="history-modern-filter-btn" data-filter="completed"><x-outline-icon name="check" /> Completed</button>
                    <button type="button" class="history-modern-filter-btn" data-filter="missed"><x-outline-icon name="x-mark" /> Missed</button>
                    <button type="button" class="history-modern-filter-btn" data-filter="cancelled"><x-outline-icon name="clock" /> Cancelled</button>
                </div>
            @endif

            <div class="history-appointment-list" id="historyAppointmentList">
                @forelse($appointments as $appointment)
                    @php
                        $statusNormalized = strtolower(trim((string) $appointment->status));
                        $statusClass = in_array($statusNormalized, ['pending', 'approved', 'completed', 'missed', 'cancelled', 'expired'], true)
                            ? 'status-' . $statusNormalized
                            : 'status-default';
                        try {
                            $appointmentAt = \Carbon\Carbon::parse($appointment->date . ' ' . $appointment->time);
                        } catch (\Throwable $exception) {
                            $appointmentAt = now();
                        }
                        $isUpcoming = $appointmentAt->isFuture() && in_array($statusNormalized, ['pending', 'approved'], true);
                        $historyGroup = $isUpcoming
                            ? 'upcoming'
                            : (in_array($statusNormalized, ['missed', 'expired'], true) ? 'missed' : $statusNormalized);
                        $serviceKey = strtolower(trim((string) $appointment->service));
                        $isGeneralConsultation = str_contains($serviceKey, 'general consultation');
                        $isBloodPressureMonitoring = str_contains($serviceKey, 'blood pressure') || str_contains($serviceKey, 'bp monitoring');
                        $serviceIcon = $isBloodPressureMonitoring
                            ? 'heart-pulse'
                            : ($isGeneralConsultation ? 'clipboard-document-list' : 'calendar-days');
                        $serviceIconClass = $isBloodPressureMonitoring ? 'is-bp' : ($isGeneralConsultation ? 'is-general' : 'is-other');
                        $serviceStatusClass = match ($statusNormalized) {
                            'approved', 'completed' => 'is-status-approved',
                            'pending' => 'is-status-pending',
                            'missed', 'cancelled', 'expired' => 'is-status-alert',
                            default => 'is-status-default',
                        };
                        $consultationRemarks = $appointment->relationLoaded('historyConsultation')
                            ? trim((string) optional($appointment->getRelation('historyConsultation'))->comments)
                            : '';
                        $studentNumber = trim((string) (
                            $appointment->student_number
                            ?: optional(optional($appointment->user)->healthProfile)->student_number
                            ?: optional($appointment->user)->student_number
                            ?: ($studentContext['student_number'] ?? $appointment->student_id)
                        ));
                    @endphp
                    <article
                        class="history-modern-entry"
                        data-history-status="{{ $statusNormalized }}"
                        data-history-group="{{ $historyGroup }}"
                        data-history-month="{{ $appointmentAt->format('F Y') }}"
                    >
                        <span class="history-entry-dot {{ $statusClass }}" aria-hidden="true">
                            @if(in_array($statusNormalized, ['completed', 'missed', 'expired'], true))
                                <x-outline-icon name="{{ $statusNormalized === 'completed' ? 'check' : 'x-mark' }}" />
                            @endif
                        </span>
                        <div class="history-entry-month">{{ $appointmentAt->format('F Y') }}</div>
                        <div class="history-modern-appointment-card">
                            <span class="history-service-icon {{ $serviceIconClass }} {{ $serviceStatusClass }}" aria-hidden="true">
                                <x-outline-icon name="{{ $serviceIcon }}" />
                            </span>
                            <span class="history-entry-copy">
                                <strong class="history-entry-service">{{ $appointment->service }}</strong>
                                <span class="history-entry-meta">
                                    <span><x-outline-icon name="clipboard-document-list" /> {{ $appointment->apt_id ?: 'N/A' }}</span>
                                    <span><x-outline-icon name="calendar-days" /> {{ $appointmentAt->format('M d, Y') }}</span>
                                    <span><x-outline-icon name="clock" /> {{ $appointmentAt->format('g:i A') }}</span>
                                </span>
                                @if(trim((string) $appointment->problem) !== '')
                                    <span class="history-entry-problem">{{ $appointment->problem }}</span>
                                @endif
                            </span>
                            <span class="history-entry-actions">
                                <span class="history-entry-status {{ $statusClass }}">{{ $appointment->status }}</span>
                                <button type="button" class="history-view-btn js-toggle-history-details" aria-expanded="false">
                                    View Details
                                    <x-outline-icon name="chevron-right" />
                                </button>
                            </span>
                            <div class="history-entry-details" aria-hidden="true">
                                <span class="history-detail-item">
                                    <span>Student Number</span>
                                    <strong>{{ $studentNumber !== '' ? $studentNumber : '-' }}</strong>
                                </span>
                                <span class="history-detail-item">
                                    <span>Email</span>
                                    <strong>{{ $appointment->email ?: '-' }}</strong>
                                </span>
                                <span class="history-detail-item">
                                    <span>Appointment Source</span>
                                    <strong>{{ ucfirst((string) ($appointment->type ?: 'Online')) }}</strong>
                                </span>
                                @if(trim((string) $appointment->notes) !== '')
                                    <span class="history-detail-item">
                                        <span>Notes</span>
                                        <strong>{{ $appointment->notes }}</strong>
                                    </span>
                                @endif
                                @if($statusNormalized === 'completed')
                                    <span class="history-detail-item">
                                        <span>Comments / Remarks</span>
                                        <strong>{{ $consultationRemarks !== '' ? $consultationRemarks : 'No consultation remarks recorded.' }}</strong>
                                    </span>
                                @endif
                                @if(in_array($statusNormalized, ['pending', 'approved'], true))
                                    <span class="history-detail-actions">
                                        <button
                                            type="button"
                                            class="cancel-appointment-btn js-open-cancel-dialog"
                                            data-cancel-url="{{ url('/student/appointments/' . $appointment->id . '/cancel') }}"
                                            data-cancel-service="{{ $appointment->service }}"
                                            data-cancel-date="{{ $appointmentAt->format('M d, Y') }}"
                                            data-cancel-time="{{ $appointmentAt->format('g:i A') }}"
                                            data-cancel-name="{{ $appointment->name }}"
                                        >
                                            <x-outline-icon name="x-mark" />
                                            Cancel Appointment
                                        </button>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state" id="emptyHistoryState">
                        <div class="empty-illustration" aria-hidden="true">
                            <div class="empty-dot-wave"><span></span><span></span><span></span></div>
                        </div>
                        <h2 class="empty-title">You have no appointment history yet</h2>
                        <a href="{{ url('/student/booking') }}" class="btn-outline empty-cta" id="emptyHistoryCta">Book your first appointment</a>
                    </div>
                @endforelse
            </div>
            <div class="history-filter-empty" id="historyFilterEmpty">No appointments match this filter.</div>
            <nav class="history-modern-pagination" id="historyPagination" aria-label="Appointment history pages"></nav>
        </main>

        <aside class="history-modern-sidebar">
            <section class="history-modern-side-card">
                <h2 class="history-modern-side-title">
                    <span><x-outline-icon name="chart-bar" /></span>
                    Appointment Summary
                </h2>
                <dl class="history-summary-list">
                    <div><dt><i class="is-upcoming"></i>Upcoming</dt><dd>{{ $upcomingAppointments }}</dd></div>
                    <div><dt><i class="is-completed"></i>Completed</dt><dd>{{ $completedAppointments }}</dd></div>
                    <div><dt><i></i>Cancelled</dt><dd>{{ $cancelledAppointments }}</dd></div>
                    <div><dt><i class="is-missed"></i>Missed</dt><dd>{{ $missedAppointments }}</dd></div>
                </dl>
                <div class="history-summary-total"><span>Total Appointments</span><strong>{{ $totalAppointments }}</strong></div>
            </section>

            <section class="history-modern-side-card">
                <h2 class="history-modern-side-title">
                    <span><x-outline-icon name="sparkles" /></span>
                    Quick Actions
                </h2>
                <div class="history-quick-actions">
                    <a href="{{ url('/student/booking') }}">
                        <x-outline-icon name="calendar-days" />
                        <span>Book New Appointment</span>
                        <x-outline-icon name="chevron-right" />
                    </a>
                    <a href="{{ url('/student/account') }}?view=notifications">
                        <x-outline-icon name="bell" />
                        <span>Notifications</span>
                        <x-outline-icon name="chevron-right" />
                    </a>
                </div>
            </section>

            <section class="history-modern-side-card history-help-card">
                <h2 class="history-modern-side-title">
                    <span><x-outline-icon name="information-circle" /></span>
                    Need Help?
                </h2>
                <p>Our clinic staff is here to assist you with appointment questions and schedule concerns.</p>
                <a href="mailto:puptclinic@gmail.com" class="history-contact-btn">Contact Us <x-outline-icon name="chevron-right" /></a>
            </section>
        </aside>
    </div>

    <div class="cancel-dialog-backdrop" id="cancelDialogBackdrop" aria-hidden="true">
        <div class="cancel-dialog" role="dialog" aria-modal="true" aria-labelledby="cancelDialogTitle">
            <div class="cancel-dialog-header">
                <div class="cancel-dialog-kicker">Appointment Control</div>
                <h2 class="cancel-dialog-title" id="cancelDialogTitle">Cancel appointment</h2>
                <div class="cancel-dialog-copy">Please review the appointment details before submitting the cancellation.</div>
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
                <div class="cancel-dialog-warning">Once cancelled, this appointment will move to your history as cancelled and you will need to book again if you still need the service.</div>
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
</div>
