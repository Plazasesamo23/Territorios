<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>S13 - Registro de Asignación de Territorio</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.0;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        
        .year-info {
            text-align: left;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        
        .table-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table-container th,
        .table-container td {
            border: 1px solid #000;
            padding: 3px 5px;
            text-align: center;
            vertical-align: top;
            height: 20px;
        }
        
        .table-container th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 8pt;
        }
        
        .territory-number {
            width: 40px;
            font-weight: bold;
            background-color: #f8f8f8;
            text-align: center;
            font-size: 8pt;
        }
        
        .last-date-column {
            width: 60px;
            font-size: 6pt;
            background-color: #f8f8f8;
            text-align: center;
        }
        
        .assignment-cell {
            width: 110px;
            font-size: 6pt;
            text-align: center;
            padding: 1px 2px;
        }
        
        .assignment-row {
            height: 28px;
        }
        
        .assignment-row td {
            vertical-align: top;
            padding: 2px;
        }
        
        .assignment-data {
            display: block;
            line-height: 1.2;
        }
        
        .publisher-name {
            font-weight: bold;
            font-size: 8pt;
        }
        
        .dates {
            font-size: 7pt;
            color: #333;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .page-info {
            text-align: right;
            font-size: 8pt;
            margin-bottom: 10px;
        }
        
        .footer {
            position: fixed;
            bottom: 10mm;
            right: 0;
            font-size: 8pt;
            color: #666;
        }
        
        .no-data {
            color: #ccc;
            font-style: italic;
            font-size: 7pt;
        }
    </style>
</head>
<body>
    @foreach($paginas as $indicePagina => $territoriosPagina)
        @if($indicePagina > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="header">
            REGISTRO DE ASIGNACIÓN DE TERRITORIO
        </div>
        
        <div class="year-info">
            <strong>Año de servicio:</strong> {{ $añoServicio }}-{{ $añoSiguiente }}
        </div>
        
        <div class="page-info">
            Página {{ $indicePagina + 1 }} de {{ $paginas->count() }} | 
            Territorios {{ $territoriosPagina->first()->numero }} - {{ $territoriosPagina->last()->numero }}
        </div>
        
        <table class="table-container">
            <thead>
                <tr>
                    <th rowspan="3" class="territory-number">Núm.<br>de terr.</th>
                    <th rowspan="3" class="last-date-column">Última fecha<br>en que se<br>completó*</th>
                    <th colspan="4">Asignado a</th>
                </tr>
                <tr>
                    <th class="assignment-cell">Fecha en que<br>se asignó</th>
                    <th class="assignment-cell">Fecha en que<br>se asignó</th>
                    <th class="assignment-cell">Fecha en que<br>se asignó</th>
                    <th class="assignment-cell">Fecha en que<br>se asignó</th>
                </tr>
                <tr>
                    <th class="assignment-cell">Fecha en que<br>se completó</th>
                    <th class="assignment-cell">Fecha en que<br>se completó</th>
                    <th class="assignment-cell">Fecha en que<br>se completó</th>
                    <th class="assignment-cell">Fecha en que<br>se completó</th>
                </tr>
            </thead>
            <tbody>
                @foreach($territoriosPagina as $territorio)
                    @php
                        // Calcular la última fecha de completado
                        $ultimaFechaCompleto = null;
                        if($territorio->registros && $territorio->registros->count() > 0) {
                            $ultimaFechaCompleto = $territorio->registros
                                ->whereNotNull('fecha_entrada')
                                ->max('fecha_entrada');
                        }
                    @endphp
                    
                    <tr class="assignment-row">
                        <td class="territory-number">{{ $territorio->numero }}</td>
                        <td class="last-date-column">
                            @if($ultimaFechaCompleto)
                                {{ \Carbon\Carbon::parse($ultimaFechaCompleto)->format('d-m-y') }}
                            @endif
                        </td>
                        
                        @for($i = 0; $i < 4; $i++)
                            <td class="assignment-cell">
                                @if(isset($territorio->registros[$i]))
                                    @php $registro = $territorio->registros[$i]; @endphp
                                    <div>{{ $registro->publicador->nombre }} {{ $registro->publicador->apellidos }}</div>
                                    <div style="border-bottom: 1px solid black; margin: 2px 0;">{{ $registro->fecha_salida->format('d-m-y') }}</div>
                                    <div>@if($registro->fecha_entrada){{ $registro->fecha_entrada->format('d-m-y') }}@endif</div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Nota al final de cada página -->
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
