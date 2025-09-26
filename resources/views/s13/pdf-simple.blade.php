<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>S13 - Registro de Asignación de Territorio</title>
    <style>
        @page { margin: 5mm 10mm; size: A4 portrait; }
        
        body { font-family: Arial, sans-serif; font-size: 7pt; margin: 0; padding: 0; }
        
        .header { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px; }
        
        .year-info { text-align: left; margin-bottom: 15px; font-size: 10pt; }
        
        .page-info { text-align: right; font-size: 8pt; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        
        th, td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        
        th { background-color: #f0f0f0; font-weight: bold; font-size: 7pt; }
        
        .territory-col { width: 45px; font-weight: bold; background-color: #f8f8f8; font-size: 8pt; }
        
        .date-col { width: 70px; font-size: 6pt; background-color: #f8f8f8; }
        
        .assign-col { width: 120px; font-size: 7pt; height: 35px; }
        
        .sub-col { width: 60px; font-size: 7pt; }
        
        .name-row { font-size: 6pt; }
        
        .name { font-weight: bold; margin-bottom: 2px; font-size: 7pt; }
        
        .date-out { border-bottom: 1px solid #000; padding: 1px; margin-bottom: 2px; font-size: 6pt; }
        
        .date-in { padding: 1px; font-size: 6pt; }
    </style>
</head>
<body>
    @foreach($paginas as $indicePagina => $territoriosPagina)
        @if($indicePagina > 0)<div style="page-break-before: always;"></div>@endif
        
        <div class="header">REGISTRO DE ASIGNACIÓN DE TERRITORIO</div>
        <div class="year-info"><strong>Año de servicio:</strong> {{ $añoServicio }}-{{ $añoSiguiente }}</div>
        <div class="page-info">Página {{ $indicePagina + 1 }} de {{ $paginas->count() }} | Territorios {{ $territoriosPagina->first()->numero }} - {{ $territoriosPagina->last()->numero }}</div>
        
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="territory-col">Núm.<br>de terr.</th>
                    <th rowspan="2" class="date-col">Última fecha<br>en que se<br>completó*</th>
                    <th colspan="2">Asignado a</th>
                    <th colspan="2">Asignado a</th>
                    <th colspan="2">Asignado a</th>
                    <th colspan="2">Asignado a</th>
                </tr>
                <tr>
                    <th class="sub-col">Fecha en que<br>se asignó</th>
                    <th class="sub-col">Fecha en que<br>se completó</th>
                    <th class="sub-col">Fecha en que<br>se asignó</th>
                    <th class="sub-col">Fecha en que<br>se completó</th>
                    <th class="sub-col">Fecha en que<br>se asignó</th>
                    <th class="sub-col">Fecha en que<br>se completó</th>
                    <th class="sub-col">Fecha en que<br>se asignó</th>
                    <th class="sub-col">Fecha en que<br>se completó</th>
                </tr>
            </thead>
            <tbody>
                @foreach($territoriosPagina as $territorio)
                    @php
                        $ultimaFecha = null;
                        if($territorio->registros && $territorio->registros->count() > 0) {
                            // CORREGIDO: Solo mostrar fecha si el registro MÁS RECIENTE está completado
                            $ultimoRegistro = $territorio->registros->sortByDesc('fecha_salida')->first();
                            if($ultimoRegistro && $ultimoRegistro->fecha_entrada) {
                                $ultimaFecha = $ultimoRegistro->fecha_entrada;
                            }
                        }
                    @endphp
                    <!-- Fila 1: Nombres de publicadores -->
                    <tr>
                        <td rowspan="2" class="territory-col">{{ $territorio->numero }}</td>
                        <td rowspan="2" class="date-col">@if($ultimaFecha){{ \Carbon\Carbon::parse($ultimaFecha)->format('d-m-y') }}@endif</td>
                        
                        <!-- Asignación 1 -->
                        <td colspan="2" class="sub-col">
                            @if(isset($territorio->registros[0]))
                                {{ $territorio->registros[0]->publicador->nombre }} {{ $territorio->registros[0]->publicador->apellidos }}
                            @endif
                        </td>
                        
                        <!-- Asignación 2 -->
                        <td colspan="2" class="sub-col">
                            @if(isset($territorio->registros[1]))
                                {{ $territorio->registros[1]->publicador->nombre }} {{ $territorio->registros[1]->publicador->apellidos }}
                            @endif
                        </td>
                        
                        <!-- Asignación 3 -->
                        <td colspan="2" class="sub-col">
                            @if(isset($territorio->registros[2]))
                                {{ $territorio->registros[2]->publicador->nombre }} {{ $territorio->registros[2]->publicador->apellidos }}
                            @endif
                        </td>
                        
                        <!-- Asignación 4 -->
                        <td colspan="2" class="sub-col">
                            @if(isset($territorio->registros[3]))
                                {{ $territorio->registros[3]->publicador->nombre }} {{ $territorio->registros[3]->publicador->apellidos }}
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Fila 2: Fechas -->
                    <tr>
                        <!-- Asignación 1: Fechas -->
                        <td class="sub-col">
                            @if(isset($territorio->registros[0]))
                                {{ $territorio->registros[0]->fecha_salida->format('d-m-y') }}
                            @endif
                        </td>
                        <td class="sub-col">
                            @if(isset($territorio->registros[0]) && $territorio->registros[0]->fecha_entrada)
                                {{ $territorio->registros[0]->fecha_entrada->format('d-m-y') }}
                            @endif
                        </td>
                        
                        <!-- Asignación 2: Fechas -->
                        <td class="sub-col">
                            @if(isset($territorio->registros[1]))
                                {{ $territorio->registros[1]->fecha_salida->format('d-m-y') }}
                            @endif
                        </td>
                        <td class="sub-col">
                            @if(isset($territorio->registros[1]) && $territorio->registros[1]->fecha_entrada)
                                {{ $territorio->registros[1]->fecha_entrada->format('d-m-y') }}
                            @endif
                        </td>
                        
                        <!-- Asignación 3: Fechas -->
                        <td class="sub-col">
                            @if(isset($territorio->registros[2]))
                                {{ $territorio->registros[2]->fecha_salida->format('d-m-y') }}
                            @endif
                        </td>
                        <td class="sub-col">
                            @if(isset($territorio->registros[2]) && $territorio->registros[2]->fecha_entrada)
                                {{ $territorio->registros[2]->fecha_entrada->format('d-m-y') }}
                            @endif
                        </td>
                        
                        <!-- Asignación 4: Fechas -->
                        <td class="sub-col">
                            @if(isset($territorio->registros[3]))
                                {{ $territorio->registros[3]->fecha_salida->format('d-m-y') }}
                            @endif
                        </td>
                        <td class="sub-col">
                            @if(isset($territorio->registros[3]) && $territorio->registros[3]->fecha_entrada)
                                {{ $territorio->registros[3]->fecha_entrada->format('d-m-y') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 15px; font-size: 7pt; color: #333; font-style: italic;">
            *Cuando comience una nueva página, anote en esta columna la última fecha en que los territorios se completaron.
        </div>
        
        @if($indicePagina == $paginas->count() - 1)
            <div style="margin-top: 30px; font-size: 8pt; color: #666;">
                <strong>S-13-S 1/22</strong> - Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }}
            </div>
        @endif
    @endforeach
</body>
</html>
