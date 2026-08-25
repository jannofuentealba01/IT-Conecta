@php
    $identityParticipant = $participant ?? $currentParticipant ?? null;
    $identityName = $identityParticipant?->name ?? session('participant_name');
    $identityCourse = $identityParticipant?->room?->course?->name
        ?? $identityParticipant?->course
        ?? session('participant_course');
    $identityRoom = $identityParticipant?->room?->code ?? session('room_code');
@endphp

@once
<style>
    .student-identity-bar{display:flex;align-items:center;justify-content:center;width:100%;margin:0 0 16px;padding:8px 12px;border:1px solid var(--brand-blue-soft-border);border-radius:11px;background:var(--info-soft);color:var(--text-secondary);font-size:13px;line-height:1.35;text-align:center}
    .student-identity-line{font-weight:650;overflow-wrap:anywhere}
    .student-identity-line strong{color:var(--text-primary);font-weight:800}
    .student-identity-separator{color:var(--brand-blue);padding:0 5px}
    @media(max-width:600px){.student-identity-bar{margin-bottom:13px;padding:7px 9px;font-size:12px;border-radius:9px}}
</style>
@endonce

@if($identityName || $identityCourse || $identityRoom)
<aside class="student-identity-bar" aria-label="Identificación del estudiante">
    <span class="student-identity-line">👤 <strong>{{ $identityName ?? 'Sin registrar' }}</strong><span class="student-identity-separator">·</span>{{ $identityCourse ?? 'Curso sin registrar' }}<span class="student-identity-separator">·</span>Sala {{ $identityRoom ?? 'sin registrar' }}</span>
</aside>
@endif
