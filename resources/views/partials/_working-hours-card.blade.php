@php
    $workdays = trim($entity->workdays ?? '');
    $scheduleTime = trim($entity->schedule ?? '');
@endphp

@if($workdays || $scheduleTime)
    <div class="card shadow-sm working-hours-card">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-clock-history working-hours-icon me-2"></i>
                <span class="fw-semibold">Режим работы</span>
            </div>

            <div class="working-hours-row">
                @if($workdays)
                    <span class="working-hours-days">{{ $workdays }}</span>
                @endif
                @if($workdays && $scheduleTime)
                    <span class="working-hours-sep">—</span>
                @endif
                @if($scheduleTime)
                    <span class="working-hours-time">{{ $scheduleTime }}</span>
                @endif
            </div>
        </div>
    </div>
@endif
