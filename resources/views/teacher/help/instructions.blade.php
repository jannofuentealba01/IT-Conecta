@extends('layouts.app')

@section('content')
@include('teacher.partials.styles')
<style>
    .help-index{display:grid;gap:18px;counter-reset:guide}.flow-card{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:20px;box-shadow:0 10px 28px color-mix(in srgb,var(--text-primary) 6%,transparent)}.flow-heading{display:flex;gap:12px;align-items:center;margin-bottom:14px}.flow-icon{display:flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:12px;background:var(--info-soft);font-size:21px}.flow-heading p{margin:3px 0 0;color:var(--text-secondary);font-size:13px}.flow-steps{display:grid;gap:10px}.help-step{display:grid;grid-template-columns:42px 1fr;gap:13px;align-items:start;padding:14px;border:1px solid var(--border);border-radius:12px;background:var(--surface-muted)}.help-step::before{counter-increment:guide;content:counter(guide);display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:var(--brand-blue);color:var(--surface);font-weight:900}.help-step h3{margin:1px 0 4px;color:var(--brand-blue-dark);font-size:16px}.help-step p{margin:0;color:var(--text-secondary);line-height:1.5;font-size:14px}.help-callout{padding:18px;border-radius:15px;background:var(--info-soft);border:1px solid var(--brand-blue-soft-border);margin-bottom:18px}.help-callout p{margin:5px 0 0;color:var(--text-secondary);line-height:1.5}@media(max-width:640px){.flow-card{padding:15px}.help-step{grid-template-columns:36px 1fr;padding:12px}.help-step::before{width:34px;height:34px}}
</style>
<div class="teacher-shell">
    <x-breadcrumbs :items="[
        ['label' => 'Área docente', 'url' => route('teacher.dashboard')],
        ['label' => 'Instrucciones'],
    ]" />
    <div class="teacher-header"><div><p class="teacher-eyebrow">Ayuda docente</p><h1 class="teacher-title">Cómo utilizar IT Conecta</h1><p class="teacher-subtitle">Guía rápida del flujo de clase y del Juego del Impostor.</p></div><a class="teacher-btn teacher-btn-secondary" href="{{ route('teacher.instructions.faq') }}">Preguntas frecuentes</a></div>
    <div class="help-callout"><strong>Antes de comenzar</strong><p>IT Conecta es una experiencia educativa. La huella y sus equivalencias son aproximadas y no constituyen una medición certificada ni una calificación del estudiante.</p></div>
    <div class="help-index">
        <section class="flow-card"><div class="flow-heading"><span class="flow-icon">🔐</span><div><h2 class="teacher-section-title teacher-section-title--brand">Flujo 1: Acceso del profesor</h2><p>Registro, aprobación e ingreso al sistema.</p></div></div><div class="flow-steps">
        <article class="help-step"><div><h3>Registrar la cuenta</h3><p>Crea tu cuenta docente con nombre, correo y contraseña.</p></div></article>
        <article class="help-step"><div><h3>Esperar la aprobación</h3><p>Un administrador debe aprobar tu registro antes del primer ingreso.</p></div></article>
        <article class="help-step"><div><h3>Iniciar sesión</h3><p>Ingresa con tu correo y contraseña para abrir el panel docente.</p></div></article>
        </div></section>
        <section class="flow-card"><div class="flow-heading"><span class="flow-icon">🏫</span><div><h2 class="teacher-section-title teacher-section-title--brand">Flujo 2: Curso, sala e ingreso</h2><p>Preparación de la sesión y acceso de los estudiantes.</p></div></div><div class="flow-steps">
        <article class="help-step"><div><h3>Crear el curso</h3><p>Registra el curso una sola vez; los estudiantes no tendrán que escribirlo.</p></div></article>
        <article class="help-step"><div><h3>Crear una sala</h3><p>Crea una sesión para el curso y ábrela cuando la clase esté preparada.</p></div></article>
        <article class="help-step"><div><h3>Compartir el código</h3><p>Muestra o dicta el código de seis dígitos generado por la sala.</p></div></article>
        <article class="help-step"><div><h3>Ingreso estudiantil</h3><p>Cada estudiante escribe el código y solamente su nombre la primera vez.</p></div></article>
        <article class="help-step"><div><h3>Reingreso</h3><p>En el mismo dispositivo, el código permite recuperar la identidad mientras la sala siga abierta.</p></div></article>
        </div></section>
        <section class="flow-card"><div class="flow-heading"><span class="flow-icon">🌍</span><div><h2 class="teacher-section-title teacher-section-title--brand">Flujo 3: Cálculo de la huella</h2><p>Encuesta, resultado e interpretación cotidiana.</p></div></div><div class="flow-steps">
        <article class="help-step"><div><h3>Calcular la huella</h3><p>Los estudiantes responden diez preguntas sobre sus hábitos cotidianos.</p></div></article>
        <article class="help-step"><div><h3>Intentos de cálculo</h3><p>Cada estudiante dispone de un cálculo inicial y una sola repetición para corregir errores.</p></div></article>
        <article class="help-step"><div><h3>Interpretar el color</h3><p>Verde representa impacto bajo, amarillo impacto medio y rojo impacto alto.</p></div></article>
        <article class="help-step"><div><h3>Ver el impacto real</h3><p>La aplicación convierte la huella en una comparación cotidiana y muestra su fuente.</p></div></article>
        </div></section>
        <section class="flow-card"><div class="flow-heading"><span class="flow-icon">📱</span><div><h2 class="teacher-section-title teacher-section-title--brand">Flujo 4: Actividades y códigos QR</h2><p>Preparación, escaneo, validación y puntos.</p></div></div><div class="flow-steps">
        <article class="help-step"><div><h3>Preparar la EcoBúsqueda</h3><p>Desde la sala, selecciona las misiones del catálogo oficial de 20 actividades. La modalidad es individual y la duración definida es de 15 minutos.</p></div></article>
        <article class="help-step"><div><h3>Preparar, descargar e iniciar</h3><p>Al guardar la selección, la EcoBúsqueda queda preparada y los estudiantes esperan sin poder puntuar. Descarga el PDF, distribuye los QR y pulsa “Iniciar EcoBúsqueda” para comenzar los 15 minutos.</p></div></article>
        <article class="help-step"><div><h3>Escanear una misión</h3><p>El estudiante entra en EcoBúsqueda, pulsa “Abrir cámara”, encuentra un QR permanente activo y lee la instrucción. La aplicación intenta usar la cámara trasera; si el permiso está bloqueado o hay poca luz, permite pulsar “Reintentar cámara”, encender la linterna en equipos compatibles o tomar una foto.</p></div></article>
        <article class="help-step"><div><h3>Comprobar la actividad</h3><p>Debe responder correctamente dos preguntas antes de registrar la misión.</p></div></article>
        <article class="help-step"><div><h3>Registrar puntos</h3><p>Una respuesta aprobada entrega el puntaje definido por el servidor. Durante la partida se muestran progreso y tiempo, nunca el ranking.</p></div></article>
        <article class="help-step"><div><h3>Finalizar y mostrar resultados</h3><p>Cada QR puntúa una sola vez. Al llegar a cero o al pulsar “Finalizar actividad”, se bloquean los registros y aparece el ranking. Si hace falta, el profesor puede reabrir una sola vez por 5 minutos y cerrarla manualmente antes.</p></div></article>
        </div></section>
        <section class="flow-card"><div class="flow-heading"><span class="flow-icon">🎭</span><div><h2 class="teacher-section-title teacher-section-title--game">Flujo 5: Juego del Impostor</h2><p>Roles, pistas, votación y presentación del resultado.</p></div></div><div class="flow-steps">
        <article class="help-step"><div><h3>Preparar el Impostor</h3><p>Pulsa “Preparar o continuar juego”, explica las reglas y comprueba en la pantalla de preparación quiénes están conectados. El contador todavía no comienza.</p></div></article>
        <article class="help-step"><div><h3>Iniciar la partida</h3><p>Cuando haya al menos tres estudiantes, pulsa “Iniciar partida”. En ese momento se asignan al azar la palabra y los impostores y comienza el contador de cinco minutos.</p></div></article>
        <article class="help-step"><div><h3>Cantidad de impostores</h3><p>Se asignan automáticamente entre uno y cinco según el total de estudiantes.</p></div></article>
        <article class="help-step"><div><h3>Enviar pistas</h3><p>Durante los primeros cuatro minutos, cada participante envía una pista breve.</p></div></article>
        <article class="help-step"><div><h3>Votación automática</h3><p>Al minuto cuatro comienza un minuto obligatorio de votación.</p></div></article>
        <article class="help-step"><div><h3>Adelantar la votación</h3><p>“Votar ahora” termina las pistas y entrega inmediatamente un minuto completo para votar.</p></div></article>
        <article class="help-step"><div><h3>Emitir el voto</h3><p>Cada estudiante vota una vez y no puede seleccionarse a sí mismo.</p></div></article>
        <article class="help-step"><div><h3>Cerrar la votación</h3><p>Al terminar el minuto dejan de aceptarse votos y comienza una espera de 30 segundos.</p></div></article>
        <article class="help-step"><div><h3>Mostrar resultados</h3><p>Durante esos 30 segundos puedes mostrarlos; si no lo haces, aparecerán automáticamente.</p></div></article>
        </div></section>
        <section class="flow-card"><div class="flow-heading"><span class="flow-icon">📊</span><div><h2 class="teacher-section-title teacher-section-title--brand">Flujo 6: Resultados y cierre</h2><p>Revisión final y conservación de los registros.</p></div></div><div class="flow-steps">
        <article class="help-step"><div><h3>Revisar resultados</h3><p>Consulta impostores, votos, puntos, actividades y huellas registradas.</p></div></article>
        <article class="help-step"><div><h3>Cerrar la sala</h3><p>Finaliza la sesión al terminar la clase. Los registros permanecen guardados.</p></div></article>
        </div></section>
    </div>
    <div class="teacher-card" style="margin-top:18px;text-align:center"><h2 class="teacher-section-title teacher-section-title--brand">¿Necesitas más detalle?</h2><p class="teacher-meta" style="margin-bottom:14px">Consulta preparación de clase, reglas, permisos de cámara y solución de problemas.</p><a class="teacher-btn teacher-btn-primary" href="{{ route('teacher.instructions.faq') }}">Ir a preguntas frecuentes</a></div>
</div>
@endsection
