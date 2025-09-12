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
    'dias_limite_activo' => env('TERRITORIOS_DIAS_LIMITE_ACTIVO', 80),

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
        'plantilla_asignacion' => "🗺️ *Territorio #{numero}*\n\n📍 *Ubicación:* {nombre}\n\n📸 *Imagen del territorio:*\n{imagen_url}\n\n¿Te interesa trabajar este territorio?\n\nSaludos cordiales! 😊",
        'url_base' => env('APP_URL', 'http://localhost:8000'),
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