@extends('layouts.app')
@section('content')
    <style>
        /* CONTENEDOR TARJETA PRINCIPAL */
        .activities-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
        }

        /* ENCABEZADO DE LA SECCIÓN */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            color: var(--brand-green-dark);
            font-size: 24px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* BOTÓN CREAR NUEVA ACTIVIDAD */
        .btn-create {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--brand-green), var(--brand-green-dark));
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14.5px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 6px 15px rgba(5, 150, 105, 0.2);
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(5, 150, 105, 0.3);
            filter: brightness(1.05);
        }

        /* CONTENEDOR DE LA TABLA (SCROLLABLE EN MÓVILES) */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
            background-color: white;
        }

        /* TABLA */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 15px;
        }

        th {
            background-color: var(--brand-green-soft);
            color: var(--brand-green-dark);
            font-weight: 700;
            padding: 16px 20px;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--brand-green-soft-border);
        }

        td {
            padding: 16px 20px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: var(--surface-muted);
        }

        .activity-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 15px;
        }

        /* BADGE DE PUNTAJE */
        .badge-points {
            background-color: var(--warning-orange-soft);
            color: var(--warning-orange);
            padding: 4px 10px;
            border-radius: 99px;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid var(--warning-orange);
        }

        /* GRUPO DE BOTONES DE ACCIONES */
        .action-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* BOTONES DE ACCIONES */
        .btn-action {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        /* Realizar (Acción Principal de Estudiante) */
        .btn-realizar {
            background-color: var(--brand-green-soft);
            color: var(--text-primary);
            border: 1px solid var(--brand-green-soft-border);
        }

        .btn-realizar:hover {
            background-color: var(--brand-green);
            color: white;
            border-color: var(--brand-green);
            transform: translateY(-1px);
        }

        /* Editar */
        .btn-edit {
            background-color: var(--brand-blue-soft);
            color: var(--brand-blue-dark);
            border: 1px solid var(--brand-blue-soft-border);
        }

        .btn-edit:hover {
            background-color: var(--brand-blue);
            color: white;
            border-color: var(--brand-blue);
            transform: translateY(-1px);
        }

        /* Eliminar */
        .btn-delete {
            background-color: var(--danger-soft);
            color: var(--danger-dark);
            border: 1px solid var(--danger-soft-border);
        }

        .btn-delete:hover {
            background-color: var(--danger);
            color: white;
            border-color: var(--danger);
            transform: translateY(-1px);
        }

        /* MEDIA QUERIES PARA ADAPTABILIDAD MÓVIL */
        @media (max-width: 640px) {
            .activities-card {
                padding: 20px 15px;
                border-radius: 16px;
            }
            .section-header {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-create {
                justify-content: center;
            }
            .action-group {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>





<div class="activities-card">
    
    <!-- CABECERA DE SECCIÓN -->
    <div class="section-header">
        <h2 class="section-title">
            📋 Lista de Actividades Ecológicas
        </h2>

        @if(in_array(auth()->user()?->rol, ['admin', 'profesor'], true))
            <a href="{{ route('activities.create') }}" class="btn-create">
                ➕ Nueva Actividad
            </a>
        @endif
    </div>

    <!-- TABLA DE ACTIVIDADES -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Recompensa</th>
                    <th style="width: 280px; text-align: center;">Acciones</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($activities as $activity)
                <tr>
                    <!-- Actividad -->
                    <td>
                        <span class="activity-name">{{ $activity->name }}</span>
                    </td>

                    <!-- Puntos -->
                    <td>
                        <span class="badge-points">
                            ⭐ {{ $activity->points }} pts
                        </span>
                    </td>

                    <!-- Acciones -->
                    <td>
                        <div class="action-group">

                            <!-- Visible para todos -->
                            <a href="{{ route('activities.qr.show', $activity->id) }}"
                               class="btn-action"
                               style="background:var(--brand-purple); color:var(--surface);">
                                📱 Generar QR
                            </a>

                            <!-- Visible para todos -->
                            <form action="{{ route('activities.complete', $activity->id) }}" method="POST">
                                @csrf
                                <button class="btn-action btn-realizar">
                                    ✅ Realizar
                                </button>
                            </form>

                            <!-- Solo administrador -->
                            @if(in_array(auth()->user()?->rol, ['admin', 'profesor'], true))

                                <a href="{{ route('activities.edit', $activity->id) }}"
                                   class="btn-action btn-edit">
                                    ✏️ Editar
                                </a>

                                <form action="{{ route('activities.destroy', $activity->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-action btn-delete">
                                        🗑️ Eliminar
                                    </button>
                                </form>

                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:var(--text-secondary); padding:30px;">
                        No hay actividades creadas aún. ¡Crea la primera para empezar! 🚀
                    </td>
                </tr>
            @endforelse
            </tbody>

        </table>
    </div>

</div>

@endsection
