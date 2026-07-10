<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Disponibilidad PPOC - {{ $congregacion->nombre }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            padding: 1rem;
            color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .card {
            background: #262640;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .header h1 {
            font-size: 1.5rem;
            color: #4a6da7;
            margin-bottom: 0.5rem;
        }
        .header p {
            color: #a3a3a3;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #e5e5e5;
        }

        /* Desplegable buscable */
        .select-search-container {
            position: relative;
        }
        .select-search-input {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border: 2px solid #404040;
            border-radius: 10px;
            background: #1a1a2e;
            color: #f5f5f5;
            cursor: pointer;
        }
        .select-search-input:focus {
            outline: none;
            border-color: #4a6da7;
        }
        .select-search-input::placeholder {
            color: #737373;
        }
        .select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #1a1a2e;
            border: 2px solid #404040;
            border-top: none;
            border-radius: 0 0 10px 10px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 100;
            display: none;
        }
        .select-dropdown.show {
            display: block;
        }
        .select-option {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #333;
            transition: background 0.2s;
        }
        .select-option:hover {
            background: #4a6da7;
            color: #fff;
        }
        .select-option:last-child {
            border-bottom: none;
        }
        .select-option.hidden {
            display: none;
        }
        .no-results {
            padding: 1rem;
            text-align: center;
            color: #737373;
        }

        /* Turnos por dia */
        .dia-grupo {
            margin-bottom: 1.25rem;
        }
        .dia-titulo {
            font-weight: 600;
            color: #4a6da7;
            margin-bottom: 0.5rem;
            padding-bottom: 0.25rem;
            border-bottom: 1px solid #404040;
        }
        .turnos-lista {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .turno-checkbox {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: #1a1a2e;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .turno-checkbox:hover {
            background: #2a2a4e;
        }
        .turno-checkbox.checked {
            border-color: #4a6da7;
            background: rgba(74, 109, 167, 0.15);
        }
        .turno-checkbox input {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            accent-color: #4a6da7;
        }
        .turno-info {
            flex: 1;
        }
        .turno-nombre {
            font-weight: 500;
        }
        .turno-horario {
            font-size: 0.85rem;
            color: #a3a3a3;
        }

        /* Boton submit */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }
        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Alertas */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            color: #4ade80;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #f87171;
        }

        /* Sin turnos */
        .sin-turnos {
            text-align: center;
            padding: 2rem;
            color: #737373;
        }

        /* Cargando */
        .loading {
            text-align: center;
            padding: 1rem;
            color: #4a6da7;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 1rem;
            color: #525252;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Disponibilidad para los carritos</h1>
                <p>{{ $congregacion->nombre }}</p>
            </div>

            <div style="background: rgba(74,109,167,0.12); border: 1px solid rgba(74,109,167,0.35); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.92rem; line-height: 1.5;">
                Indica en qué turnos de predicación pública (carritos) puedes participar:<br>
                <strong>1.</strong> Busca tu nombre &nbsp;·&nbsp; <strong>2.</strong> Marca tus turnos &nbsp;·&nbsp; <strong>3.</strong> Pulsa Guardar
            </div>

            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            @if($turnos->count() === 0)
            <div class="sin-turnos">
                <p>No hay turnos configurados todavia.</p>
                <p>El administrador debe crear los turnos primero.</p>
            </div>
            @elseif($publicadores->count() === 0)
            <div class="sin-turnos">
                <p>No hay publicadores aprobados para PPOC.</p>
                <p>El administrador debe aprobar publicadores primero.</p>
            </div>
            @else
            <form action="{{ route('disponibilidad.store', $congregacion->token_disponibilidad) }}" method="POST" id="formDisponibilidad">
                @csrf

                <div class="form-group">
                    <label>Tu nombre</label>
                    <div class="select-search-container">
                        <input type="text"
                               class="select-search-input"
                               id="searchInput"
                               placeholder="Escribe para buscar tu nombre..."
                               autocomplete="off">
                        <input type="hidden" name="publicador_id" id="publicadorId">
                        <div class="select-dropdown" id="dropdown">
                            @foreach($publicadores as $pub)
                            <div class="select-option" data-id="{{ $pub->id }}" data-nombre="{{ $pub->nombre_completo }}">
                                {{ $pub->nombre_completo }}
                            </div>
                            @endforeach
                            <div class="no-results" style="display:none;">No se encontraron resultados</div>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="turnosContainer" style="display: none;">
                    <label>Selecciona los turnos en los que puedes salir:</label>
                    <div id="loadingIndicator" class="loading" style="display:none;">
                        Cargando tu disponibilidad...
                    </div>

                    @php
                        $diasNombres = [0 => 'Lunes', 1 => 'Martes', 2 => 'Miercoles', 3 => 'Jueves', 4 => 'Viernes', 5 => 'Sabado', 6 => 'Domingo'];
                        $turnosPorDia = $turnos->groupBy('dia_semana');
                    @endphp

                    @foreach($diasNombres as $diaNum => $diaNombre)
                        @if(isset($turnosPorDia[$diaNum]) && $turnosPorDia[$diaNum]->count() > 0)
                        <div class="dia-grupo">
                            <div class="dia-titulo">{{ $diaNombre }}</div>
                            <div class="turnos-lista">
                                @foreach($turnosPorDia[$diaNum] as $turno)
                                <label class="turno-checkbox" id="turnoLabel{{ $turno->id }}">
                                    <input type="checkbox" name="turnos[]" value="{{ $turno->id }}" id="turno{{ $turno->id }}">
                                    <div class="turno-info">
                                        <div class="turno-nombre">{{ $turno->nombre }}</div>
                                        <div class="turno-horario">{{ $turno->horario }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                    Guardar disponibilidad
                </button>
            </form>
            @endif
        </div>

        <div class="footer">
            Territorios - Sistema de gestión
        </div>
    </div>

    <script>
        document.getElementById('formDisponibilidad')?.addEventListener('submit', function(e) {
            const marcados = this.querySelectorAll('input[type="checkbox"]:checked').length;
            if (marcados === 0 && !confirm('No has marcado ningún turno. Se borrará tu disponibilidad anterior y quedarás sin turnos este mes. ¿Continuar?')) {
                e.preventDefault();
            }
        });
    </script>

    <script>
        const searchInput = document.getElementById('searchInput');
        const dropdown = document.getElementById('dropdown');
        const publicadorIdInput = document.getElementById('publicadorId');
        const turnosContainer = document.getElementById('turnosContainer');
        const btnSubmit = document.getElementById('btnSubmit');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const options = document.querySelectorAll('.select-option:not(.no-results)');
        const noResults = document.querySelector('.no-results');
        const token = '{{ $congregacion->token_disponibilidad }}';

        // Mostrar/ocultar dropdown
        searchInput.addEventListener('focus', function() {
            dropdown.classList.add('show');
            filterOptions();
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.select-search-container')) {
                dropdown.classList.remove('show');
            }
        });

        // Filtrar opciones
        searchInput.addEventListener('input', filterOptions);

        function filterOptions() {
            const search = searchInput.value.toLowerCase().trim();
            let hasResults = false;

            options.forEach(option => {
                const nombre = option.dataset.nombre.toLowerCase();
                if (nombre.includes(search)) {
                    option.classList.remove('hidden');
                    hasResults = true;
                } else {
                    option.classList.add('hidden');
                }
            });

            noResults.style.display = hasResults ? 'none' : 'block';
        }

        // Seleccionar opcion
        options.forEach(option => {
            option.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;

                searchInput.value = nombre;
                publicadorIdInput.value = id;
                dropdown.classList.remove('show');

                // Mostrar turnos y cargar disponibilidad
                turnosContainer.style.display = 'block';
                btnSubmit.disabled = false;

                cargarDisponibilidad(id);
            });
        });

        // Cargar disponibilidad existente
        async function cargarDisponibilidad(publicadorId) {
            loadingIndicator.style.display = 'block';

            // Desmarcar todos
            document.querySelectorAll('input[name="turnos[]"]').forEach(cb => {
                cb.checked = false;
                cb.closest('.turno-checkbox').classList.remove('checked');
            });

            try {
                const response = await fetch(`/disponibilidad/${token}/publicador/${publicadorId}`);
                const data = await response.json();

                if (data.turnos && data.turnos.length > 0) {
                    data.turnos.forEach(turnoId => {
                        const checkbox = document.getElementById('turno' + turnoId);
                        if (checkbox) {
                            checkbox.checked = true;
                            checkbox.closest('.turno-checkbox').classList.add('checked');
                        }
                    });
                }
            } catch (error) {
                console.error('Error cargando disponibilidad:', error);
            }

            loadingIndicator.style.display = 'none';
        }

        // Toggle clase checked en labels
        document.querySelectorAll('input[name="turnos[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.closest('.turno-checkbox');
                if (this.checked) {
                    label.classList.add('checked');
                } else {
                    label.classList.remove('checked');
                }
            });
        });
    </script>
</body>
</html>
