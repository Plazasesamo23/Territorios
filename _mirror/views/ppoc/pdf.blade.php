<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PPOC {{ strtoupper($nombreMes) }} {{ $year }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #333;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding: 18px 0;
            background-color: #4a6da7;
            border-radius: 6px;
        }

        .header h1 {
            font-size: 26px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 3px;
            margin: 0;
        }

        .header .subtitulo {
            font-size: 16px;
            color: #ffffff;
            margin-top: 5px;
            font-weight: normal;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        th, td {
            border: 1px solid #999;
            padding: 4px 5px;
            vertical-align: top;
        }

        .header-semana {
            background-color: #4a6da7;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            padding: 8px 5px;
        }

        .header-turnos {
            background-color: #d4e1f0;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            padding: 6px 4px;
        }

        .header-turnos .turno-label {
            font-weight: bold;
            color: #2c3e6e;
        }

        .header-turnos .turno-hora {
            font-size: 8px;
            font-weight: normal;
            color: #555;
            margin-top: 2px;
        }

        .dia-semana {
            background-color: #4a6da7;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            width: 75px;
            font-size: 9px;
            padding: 6px 4px;
        }

        .celda-turno {
            min-height: 45px;
            font-size: 8px;
            line-height: 1.4;
            padding: 4px 5px;
        }

        .celda-vacia {
            background-color: #e0e0e0 !important;
        }

        .capitan {
            font-weight: bold;
            color: #1a1a1a;
            font-size: 8px;
            margin-bottom: 1px;
        }

        .voluntario {
            color: #444;
            font-size: 7.5px;
        }

        .no-ppoc {
            color: #999;
            font-style: italic;
            text-align: center;
            font-size: 7px;
        }

        .horario-row {
            background-color: #4a6da7;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
            padding: 4px;
        }

        .horario-cell {
            background-color: #d4e1f0;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
            color: #2c3e6e;
            padding: 4px;
        }

        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            color: #666;
            padding: 10px;
            border-top: 2px solid #4a6da7;
        }

        .footer .leyenda {
            margin-top: 6px;
        }

        .footer .leyenda span {
            margin: 0 15px;
        }

        .bold-example {
            font-weight: bold;
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PROGRAMA PPOC</h1>
        <div class="subtitulo">{{ strtoupper($nombreMes) }} {{ $year }}</div>
    </div>

    <table>
        <!-- Fila de semanas -->
        <tr>
            <th class="header-semana">SEMANA</th>
            @foreach($semanas as $semana)
                <th class="header-semana" colspan="2">{{ $semana['rango'] }}</th>
            @endforeach
        </tr>

        <!-- Fila de turnos MAÑANA/TARDE -->
        <tr>
            <th class="header-turnos">TURNOS</th>
            @foreach($semanas as $semana)
                <th class="header-turnos">
                    <div class="turno-label">MAÑANA</div>
                    <div class="turno-hora">{{ $horarioManana }}</div>
                </th>
                <th class="header-turnos">
                    <div class="turno-label">TARDE</div>
                    <div class="turno-hora">{{ $horarioTarde }}</div>
                </th>
            @endforeach
        </tr>

        <!-- Filas de días (Lunes a Viernes) -->
        @php
            $colorPar = '#e8eef6';
            $colorImpar = '#f8f9fb';
        @endphp
        @foreach($diasSemana as $diaNum => $diaNombre)
            @if($diaNum < 5)
                @php
                    $bgColor = ($diaNum % 2 == 0) ? $colorPar : $colorImpar;
                @endphp
                <tr>
                    <td class="dia-semana">{{ strtoupper($diaNombre) }}</td>
                    @foreach($semanas as $semanaIdx => $semana)
                        @php
                            $existeDia = isset($semana['dias'][$diaNum]);
                            $turnosDelDia = $existeDia ? $semana['dias'][$diaNum] : null;
                            $turnoManana = $turnosDelDia['manana'] ?? null;
                            $turnoTarde = $turnosDelDia['tarde'] ?? null;
                        @endphp

                        @if($existeDia)
                            <!-- Celda MAÑANA -->
                            <td class="celda-turno" style="background-color: {{ $bgColor }};">
                                @if($turnoManana && isset($turnoManana['asignaciones']) && $turnoManana['asignaciones']->count() > 0)
                                    @php
                                        $capitan = $turnoManana['asignaciones']->firstWhere('rol', 'capitan');
                                        $voluntarios = $turnoManana['asignaciones']->where('rol', 'voluntario');
                                    @endphp
                                    @if($capitan)
                                        <div class="capitan">{{ $capitan->publicador->nombre }} {{ $capitan->publicador->apellidos }}</div>
                                    @endif
                                    @foreach($voluntarios as $vol)
                                        <div class="voluntario">{{ $vol->publicador->nombre }} {{ $vol->publicador->apellidos }}</div>
                                    @endforeach
                                @endif
                            </td>

                            <!-- Celda TARDE -->
                            <td class="celda-turno" style="background-color: {{ $bgColor }};">
                                @if($turnoTarde && isset($turnoTarde['asignaciones']) && $turnoTarde['asignaciones']->count() > 0)
                                    @php
                                        $capitan = $turnoTarde['asignaciones']->firstWhere('rol', 'capitan');
                                        $voluntarios = $turnoTarde['asignaciones']->where('rol', 'voluntario');
                                    @endphp
                                    @if($capitan)
                                        <div class="capitan">{{ $capitan->publicador->nombre }} {{ $capitan->publicador->apellidos }}</div>
                                    @endif
                                    @foreach($voluntarios as $vol)
                                        <div class="voluntario">{{ $vol->publicador->nombre }} {{ $vol->publicador->apellidos }}</div>
                                    @endforeach
                                @endif
                            </td>
                        @else
                            <!-- Celdas vacías para días que no existen en esta semana -->
                            <td class="celda-turno celda-vacia"></td>
                            <td class="celda-turno celda-vacia"></td>
                        @endif
                    @endforeach
                </tr>
            @endif
        @endforeach

        <!-- Fila HORARIO para Sábado -->
        <tr>
            <td class="horario-row">HORARIO</td>
            @foreach($semanas as $idx => $semana)
                <td class="horario-cell" colspan="2">{{ $horarioSabado }}</td>
            @endforeach
        </tr>

        <!-- Fila Sábado -->
        <tr>
            <td class="dia-semana">SABADO</td>
            @foreach($semanas as $semanaIdx => $semana)
                @php
                    $existeDia = isset($semana['dias'][5]);
                    $turnosDelDia = $existeDia ? $semana['dias'][5] : null;
                    $turnoManana = $turnosDelDia['manana'] ?? null;
                    $turnoTarde = $turnosDelDia['tarde'] ?? null;
                @endphp

                @if($existeDia)
                    <td class="celda-turno" style="background-color: {{ $colorImpar }};">
                        @if($turnoManana && isset($turnoManana['asignaciones']) && $turnoManana['asignaciones']->count() > 0)
                            @php
                                $capitan = $turnoManana['asignaciones']->firstWhere('rol', 'capitan');
                                $voluntarios = $turnoManana['asignaciones']->where('rol', 'voluntario');
                            @endphp
                            @if($capitan)
                                <div class="capitan">{{ $capitan->publicador->nombre }} {{ $capitan->publicador->apellidos }}</div>
                            @endif
                            @foreach($voluntarios as $vol)
                                <div class="voluntario">{{ $vol->publicador->nombre }} {{ $vol->publicador->apellidos }}</div>
                            @endforeach
                        @endif
                    </td>

                    <td class="celda-turno" style="background-color: {{ $colorImpar }};">
                        @if($turnoTarde && isset($turnoTarde['asignaciones']) && $turnoTarde['asignaciones']->count() > 0)
                            @php
                                $capitan = $turnoTarde['asignaciones']->firstWhere('rol', 'capitan');
                                $voluntarios = $turnoTarde['asignaciones']->where('rol', 'voluntario');
                            @endphp
                            @if($capitan)
                                <div class="capitan">{{ $capitan->publicador->nombre }} {{ $capitan->publicador->apellidos }}</div>
                            @endif
                            @foreach($voluntarios as $vol)
                                <div class="voluntario">{{ $vol->publicador->nombre }} {{ $vol->publicador->apellidos }}</div>
                            @endforeach
                        @endif
                    </td>
                @else
                    <td class="celda-turno celda-vacia"></td>
                    <td class="celda-turno celda-vacia"></td>
                @endif
            @endforeach
        </tr>

        <!-- Fila HORARIO para Domingo -->
        <tr>
            <td class="horario-row">HORARIO</td>
            @foreach($semanas as $idx => $semana)
                <td class="horario-cell" colspan="2">{{ $horarioDomingo }}</td>
            @endforeach
        </tr>

        <!-- Fila Domingo -->
        <tr>
            <td class="dia-semana">DOMINGO</td>
            @foreach($semanas as $semanaIdx => $semana)
                @php
                    $existeDia = isset($semana['dias'][6]);
                    $turnosDelDia = $existeDia ? $semana['dias'][6] : null;
                    $turnoManana = $turnosDelDia['manana'] ?? null;
                @endphp

                @if($existeDia)
                    <td class="celda-turno" style="background-color: {{ $colorPar }};" colspan="2">
                        @if($turnoManana && isset($turnoManana['asignaciones']) && $turnoManana['asignaciones']->count() > 0)
                            @php
                                $capitan = $turnoManana['asignaciones']->firstWhere('rol', 'capitan');
                                $voluntarios = $turnoManana['asignaciones']->where('rol', 'voluntario');
                            @endphp
                            @if($capitan)
                                <div class="capitan">{{ $capitan->publicador->nombre }} {{ $capitan->publicador->apellidos }}</div>
                            @endif
                            @foreach($voluntarios as $vol)
                                <div class="voluntario">{{ $vol->publicador->nombre }} {{ $vol->publicador->apellidos }}</div>
                            @endforeach
                        @endif
                    </td>
                @else
                    <td class="celda-turno celda-vacia"></td>
                    <td class="celda-turno celda-vacia"></td>
                @endif
            @endforeach
        </tr>
    </table>

    <div class="footer">
        <div>Predicacion Publica Organizada de la Congregacion</div>
        <div class="leyenda">
            <span><span class="bold-example">Nombre en negrita</span> = Capitan</span>
            <span>Nombre normal = Voluntario</span>
        </div>
    </div>
</body>
</html>
