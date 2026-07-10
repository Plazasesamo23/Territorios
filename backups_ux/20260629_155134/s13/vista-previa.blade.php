<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vista Previa - S13 Registro de Asignación de Territorio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .preview-header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .page-container {
            background: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 210mm;
            margin-left: auto;
            margin-right: auto;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        
        .year-info {
            text-align: left;
            margin-bottom: 20px;
            font-size: 11pt;
            font-weight: bold;
        }
        
        .page-info {
            text-align: right;
            font-size: 9pt;
            margin-bottom: 15px;
            color: #666;
        }
        
        .table-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table-container th,
        .table-container td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            vertical-align: top;
        }
        
        .table-container th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .territory-number {
            width: 50px;
            font-weight: bold;
            background-color: #f8f8f8;
        }
        
        .assignment-cell {
            width: 150px;
            font-size: 9pt;
            text-align: left;
            padding: 5px;
        }
        
        .assignment-row {
            height: 40px;
        }
        
        .assignment-data {
            line-height: 1.3;
        }
        
        .publisher-name {
            font-weight: bold;
            font-size: 9pt;
        }
        
        .dates {
            font-size: 8pt;
            color: #333;
        }
        
        .no-data {
            color: #ccc;
            font-style: italic;
            font-size: 8pt;
        }
        
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="preview-header">
        <h1>📋 Vista Previa - Reporte S13</h1>
        <p><strong>Año de servicio:</strong> {{ $añoServicio }}-{{ $añoSiguiente }}</p>
        <p><strong>Total de páginas:</strong> {{ $paginas->count() }} | <strong>Territorios:</strong> 1-214</p>
        
        <div style="margin-top: 20px;">
            <a href="{{ route('s13.generar-pdf', ['año' => $añoServicio]) }}" target="_blank" class="btn btn-success">
                📄 Generar PDF
            </a>
            <a href="{{ route('s13.index') }}" class="btn">
                ← Volver a S13
            </a>
        </div>
    </div>

    @foreach($paginas as $indicePagina => $territoriosPagina)
        <div class="page-container">
            <div class="header">
                REGISTRO DE ASIGNACIÓN DE TERRITORIO
            </div>
            
            <div class="year-info">
                Año de servicio: {{ $añoServicio }}-{{ $añoSiguiente }}
            </div>
            
            <div class="page-info">
                Página {{ $indicePagina + 1 }} de {{ $paginas->count() }} | 
                Territorios {{ $territoriosPagina->first()->numero }} - {{ $territoriosPagina->last()->numero }}
            </div>
            
            <table class="table-container">
                <thead>
                    <tr>
                        <th rowspan="2" class="territory-number">Territorio<br>N°</th>
                        <th colspan="5">Asignaciones</th>
                    </tr>
                    <tr>
                        <th class="assignment-cell">Asignado a<br><small>Fecha en que salió</small></th>
                        <th class="assignment-cell">Asignado a<br><small>Fecha en que salió</small></th>
                        <th class="assignment-cell">Asignado a<br><small>Fecha en que salió</small></th>
                        <th class="assignment-cell">Asignado a<br><small>Fecha en que salió</small></th>
                        <th class="assignment-cell">Asignado a<br><small>Fecha en que salió</small></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($territoriosPagina as $territorio)
                        <tr class="assignment-row">
                            <td class="territory-number">{{ $territorio->numero }}</td>
                            
                            @for($i = 0; $i < 5; $i++)
                                <td class="assignment-cell">
                                    @if(isset($territorio->registros[$i]))
                                        @php
                                            $registro = $territorio->registros[$i];
                                        @endphp
                                        <div class="assignment-data">
                                            <div class="publisher-name">{{ $registro->publicador->nombre }} {{ $registro->publicador->apellidos }}</div>
                                            <div class="dates">
                                                Salida: {{ $registro->fecha_salida->format('d/m/Y') }}
                                                @if($registro->fecha_entrada)
                                                    <br>Entrada: {{ $registro->fecha_entrada->format('d/m/Y') }}
                                                @else
                                                    <br><em>En curso</em>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="no-data">Sin asignación</div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if($indicePagina == $paginas->count() - 1)
                <div style="margin-top: 20px; font-size: 9pt; color: #666; text-align: center;">
                    <strong>S-13-S 1/22</strong> - Generado el {{ $fechaGeneracion->format('d/m/Y H:i') }}
                </div>
            @endif
        </div>
    @endforeach
    
    <div class="preview-header">
        <a href="{{ route('s13.generar-pdf', ['año' => $añoServicio]) }}" target="_blank" class="btn btn-success">
            📄 Generar PDF Final
        </a>
        <a href="{{ route('s13.index') }}" class="btn">
            ← Volver a S13
        </a>
    </div>
</body>
</html>
