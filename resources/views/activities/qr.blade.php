@extends('layouts.app')

@section('content')

<div style="text-align:center; margin-top:50px;">

    <h2>📱 Escanea esta actividad</h2>

    <div style="margin:30px 0;">
        {!! QrCode::size(300)->generate(route('activities.qr', $activity->id)) !!}
    </div>

    <p><strong>{{ $activity->name }}</strong></p>

    <a href="{{ route('activities.index') }}" class="btn-back">
        ⬅️ Volver
    </a>

</div>

@endsection