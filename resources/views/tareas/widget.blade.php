@if($tareas->isEmpty())
    <div class="widget-empty">
        <div class="widget-empty-icon">📌</div>
        <p>No tienes tareas ancladas.</p>
        <small>Ancla tareas desde la lista para tenerlas a mano aquí.</small>
    </div>
@else
    <ul class="widget-lista">
        @foreach($tareas as $t)
            @php
                $vencida = $t->fecha_limite && $t->fecha_limite->isPast();
                $deptoNombre = \App\Models\Tarea::DEPARTAMENTOS[$t->departamento] ?? $t->departamento;
            @endphp
            <li class="widget-item {{ $vencida ? 'vencida' : '' }}">
                <form method="POST" action="{{ route('tareas.toggle-hecha', $t) }}" class="widget-check-form">
                    @csrf
                    <button type="submit" class="widget-check" title="Marcar hecha"></button>
                </form>
                <div class="widget-cuerpo">
                    <div class="widget-titulo">{{ $t->titulo }}</div>
                    <div class="widget-meta">
                        <span class="widget-tag-depto">{{ $deptoNombre }}</span>
                        @if($t->prioridad === 'alta')
                            <span class="widget-tag-prio">Alta</span>
                        @endif
                        @if($t->fecha_limite)
                            <span class="widget-tag-fecha {{ $vencida ? 'vencida' : '' }}">
                                {{ $t->fecha_limite->format('d M') }}
                            </span>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('tareas.anclar', $t) }}" class="widget-anclar-form">
                    @csrf
                    <button type="submit" class="widget-desanclar" title="Quitar anclaje">×</button>
                </form>
            </li>
        @endforeach
    </ul>
@endif
