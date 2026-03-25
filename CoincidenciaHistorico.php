<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoincidenciaHistorico extends Model
{
    protected $table = 'coincidencias_historico';

    protected $fillable = [
        'congregacion_id',
        'publicador_1_id',
        'publicador_2_id',
        'cantidad',
        'ultimo_ano'
    ];

    // Incrementar coincidencia entre dos publicadores
    public static function registrarCoincidencia($congregacionId, $pub1Id, $pub2Id, $ano)
    {
        // Ordenar IDs para consistencia
        $ids = [$pub1Id, $pub2Id];
        sort($ids);

        return self::updateOrCreate(
            [
                'congregacion_id' => $congregacionId,
                'publicador_1_id' => $ids[0],
                'publicador_2_id' => $ids[1],
            ],
            [
                'cantidad' => \DB::raw('cantidad + 1'),
                'ultimo_ano' => $ano
            ]
        );
    }
}
