<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Congregacion extends Model
{
    protected $table = 'congregaciones';

    protected $fillable = [
        'nombre',
        'codigo',
        'usuario',
        'ciudad',
        'descripcion',
        'password',
        'password_plain',
        'dias_limite_activo',
        'dias_archivo',
        'dias_limite_activo_campana',
        'dias_archivo_campana',
        'dias_limite_activo_negocios',
        'dias_archivo_negocios',
        'mensaje_whatsapp',
        'activa',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function territorios(): HasMany
    {
        return $this->hasMany(Territorio::class);
    }

    public function publicadores(): HasMany
    {
        return $this->hasMany(Publicador::class);
    }

    public function registros(): HasManyThrough
    {
        return $this->hasManyThrough(Registro::class, Territorio::class);
    }

    public function getEstadisticas(): array
    {
        return [
            'territorios' => $this->territorios()->count(),
            'publicadores' => $this->publicadores()->where('activo', true)->count(),
            'usuarios' => $this->users()->count(),
        ];
    }

    /**
     * Obtiene el mensaje de WhatsApp formateado con los datos del territorio y publicador
     */
    public function getMensajeWhatsappFormateado($publicador, $territorio): string
    {
        $mensaje = $this->mensaje_whatsapp ?? "Hola {nombre}, te envío el territorio {numero}.\n\nImagen del territorio:\n{imagen_url}";

        // Reemplazar variables
        $mensaje = str_replace('{nombre}', $publicador->nombre, $mensaje);
        $mensaje = str_replace('{nombre_completo}', $publicador->nombre_completo, $mensaje);
        $mensaje = str_replace('{numero}', $territorio->numero, $mensaje);
        $mensaje = str_replace('{territorio_nombre}', $territorio->nombre ?? '', $mensaje);
        $mensaje = str_replace('{imagen_url}', $territorio->getImagenUrl(), $mensaje);

        return $mensaje;
    }

    /**
     * Obtiene el mensaje por defecto si no hay uno personalizado
     */
    public static function getMensajeWhatsappDefault(): string
    {
        return "Querido/a {nombre}, aquí te mando el territorio asignado. Solo recordar que cuando lo termines de trabajar lo borres del teléfono y me avises. También recuerda que este territorio dura 3 meses, por lo tanto, puedes disfrutar y hacer uso de el por todo este tiempo, te animamos a poder trabajarlo a plenitud y tener conversaciones de provecho con las personas, así, podrás disfrutar por completo de tu ministerio.\n\nTerritorio #{numero}\n\nImagen del territorio:\n{imagen_url}";
    }

    /**
     * Obtiene los días límite activo según el tipo de territorio
     */
    public function getDiasLimiteActivoPorTipo(string $tipo): int
    {
        return match($tipo) {
            'campana' => $this->dias_limite_activo_campana ?? 30,
            'negocios' => $this->dias_limite_activo_negocios ?? 60,
            default => $this->dias_limite_activo ?? 120,
        };
    }

    /**
     * Obtiene los días de archivo según el tipo de territorio
     */
    public function getDiasArchivoPorTipo(string $tipo): int
    {
        return match($tipo) {
            'campana' => $this->dias_archivo_campana ?? 30,
            'negocios' => $this->dias_archivo_negocios ?? 60,
            default => $this->dias_archivo ?? 120,
        };
    }
}
