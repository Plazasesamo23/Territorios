<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del Sistema de Territorios
    |--------------------------------------------------------------------------
    |
    | Configuración para el manejo automático de estados de territorios
    | basado en fechas y períodos de tiempo.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Días Límite para Territorios Activos
    |--------------------------------------------------------------------------
    |
    | Número de días que un territorio puede estar asignado antes de 
    | cambiar automáticamente a estado "ATRASADO".
    |
    */
    'dias_limite_activo' => env('TERRITORIOS_DIAS_LIMITE_ACTIVO', 90),

    /*
    |--------------------------------------------------------------------------
    | Días en Archivo
    |--------------------------------------------------------------------------
    |
    | Número de días que un territorio debe permanecer en estado "ARCHIVO"
    | después de ser devuelto antes de poder estar libre nuevamente.
    |
    */
    'dias_archivo' => env('TERRITORIOS_DIAS_ARCHIVO', 40),

    /*
    |--------------------------------------------------------------------------
    | Configuración de WhatsApp
    |--------------------------------------------------------------------------
    |
    | Plantillas y configuración para mensajes automáticos de WhatsApp
    |
    */
    'whatsapp' => [
        'plantilla_asignacion' => "🗺️ *Territorio #{numero}*\n\n📍 *Ubicación:* {nombre}\n\nQuerido/a hermano/a aquí te mando el territorio asignado. Solo recordar que cuando lo termines de trabajar lo borres del teléfono y me avises. También recuerda que este territorio dura 3 meses, por lo tanto, puedes disfrutar y hacer uso de el por todo este tiempo, te animamos a poder trabajarlo a plenitud y tener conversaciones de provecho con las personas, así, podrás disfrutar por completo de tu ministerio 😁😁. Muchas gracias por su gran trabajo.\n\n📸 *Imagen del territorio:*\n{imagen_url}",
        'url_base' => env('APP_URL', 'http://localhost/territorios/public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados del Sistema
    |--------------------------------------------------------------------------
    |
    | Definición de los estados posibles y sus características
    |
    */
    'estados' => [
        'libre' => [
            'emoji' => '🟢',
            'nombre' => 'Libre',
            'descripcion' => 'Disponible para asignar'
        ],
        'activo' => [
            'emoji' => '🔵',
            'nombre' => 'Activo',
            'descripcion' => 'Asignado y en tiempo normal'
        ],
        'atrasado' => [
            'emoji' => '🔴',
            'nombre' => 'Atrasado',
            'descripcion' => 'Excedió el tiempo límite'
        ],
        'archivo' => [
            'emoji' => '⚫',
            'nombre' => 'Archivo',
            'descripcion' => 'En período de descanso'
        ]
    ]
]; 