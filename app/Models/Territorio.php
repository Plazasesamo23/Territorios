<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Territorio extends Model
{
    protected $table = 'territorios';

    protected $fillable = [
        'numero',
        'nombre',
        'descripcion',
        'coordenadas_lat',
        'coordenadas_lng',
        'imagen_url',
        'estado',
        'activo',
        'notas',
        'ultima_salida'
    ];

    protected $casts = [
        'ultima_salida' => 'date',
        'activo' => 'boolean',
        'coordenadas_lat' => 'decimal:8',
        'coordenadas_lng' => 'decimal:8',
    ];

    /**
     * Relación: Un territorio tiene muchos registros
     */
    public function registros()
    {
        return $this->hasMany(Registro::class);
    }

    /**
     * Obtener el último registro del territorio
     */
    public function ultimoRegistro()
    {
        return $this->registros()->latest('fecha_salida')->first();
    }

    /**
     * Obtener el registro activo (sin fecha de entrada)
     */
    public function registroActivo()
    {
        return $this->registros()->whereNull('fecha_entrada')->first();
    }

    /**
     * Obtener el publicador actual del territorio
     */
    public function publicadorActual()
    {
        $registroActivo = $this->registroActivo();
        return $registroActivo ? $registroActivo->publicador : null;
    }

    /**
     * Obtener la URL de la imagen del territorio
     */
    public function getImagenUrl()
    {
        $imagenPath = "imagenes/{$this->numero}.jpg";
        $fullPath = public_path($imagenPath);
        
        if (file_exists($fullPath)) {
            return asset($imagenPath);
        }
        
        // URL por defecto con gradiente SVG
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Cdefs%3E%3ClinearGradient id='grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%234f46e5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%237c3aed;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='100%25' height='100%25' fill='url(%23grad)'/%3E%3Ctext x='50%25' y='40%25' font-family='Arial,sans-serif' font-size='48' fill='white' text-anchor='middle' dominant-baseline='middle'%3E🗺️%3C/text%3E%3Ctext x='50%25' y='60%25' font-family='Arial,sans-serif' font-size='18' fill='white' text-anchor='middle' dominant-baseline='middle'%3ETerritorio " . $this->numero . "%3C/text%3E%3Ctext x='50%25' y='75%25' font-family='Arial,sans-serif' font-size='12' fill='rgba(255,255,255,0.8)' text-anchor='middle' dominant-baseline='middle'%3ESin imagen disponible%3C/text%3E%3C/svg%3E";
    }

    /**
     * Verificar si el territorio tiene imagen
     */
    public function tieneImagen()
    {
        $imagenPath = public_path("imagenes/{$this->numero}.jpg");
        return file_exists($imagenPath);
    }

    /**
     * Calcular el estado actual del territorio basado en fechas y configuración
     * Nueva lógica automática basada en registros
     */
    public function calcularEstado()
    {
        // Obtener configuración del sistema
        $diasMaximos = config('territorios.dias_limite_activo', 60);
        $diasArchivo = config('territorios.dias_archivo', 30);
        
        // Buscar el registro más reciente (independientemente de si está cerrado o no)
        $ultimoRegistro = $this->registros()
            ->latest('fecha_salida')
            ->first();
        
        // Sin registros = LIBRE
        if (!$ultimoRegistro) {
            return 'libre';
        }
        
        // Si el último registro tiene fecha_entrada = fue devuelto
        if ($ultimoRegistro->fecha_entrada) {
            $fechaDevolucion = Carbon::parse($ultimoRegistro->fecha_entrada);
            $diasDesdeDevolucion = $fechaDevolucion->diffInDays(now());
            
            // Si no ha pasado el tiempo de archivo = ARCHIVO
            if ($diasDesdeDevolucion < $diasArchivo) {
                return 'archivo';
            }
            
            // Ya cumplió el descanso = LIBRE
            return 'libre';
        }
        
        // No tiene fecha_entrada = está asignado actualmente
        $fechaSalida = Carbon::parse($ultimoRegistro->fecha_salida);
        $diasAsignado = $fechaSalida->diffInDays(now());
        
        // Verificar si excedió el tiempo límite
        if ($diasAsignado > $diasMaximos) {
            return 'atrasado';
        }
        
        // Dentro del tiempo normal = ACTIVO
        return 'activo';
    }

    /**
     * Obtener la clase CSS para el estado
     */
    public function getClaseEstado()
    {
        $estado = $this->calcularEstado();
        
        return match($estado) {
            'activo' => 'estado-activo',
            'libre' => 'estado-libre',
            'archivo' => 'estado-archivo',
            'atrasado' => 'estado-atrasado',
            default => 'estado-libre'
        };
    }

    /**
     * Verificar si tiene coordenadas válidas
     */
    public function tieneCoordenadasValidas()
    {
        return !is_null($this->coordenadas_lat) && !is_null($this->coordenadas_lng);
    }

    /**
     * Obtener URL de Google Maps
     */
    public function getGoogleMapsUrl()
    {
        if (!$this->tieneCoordenadasValidas()) {
            return null;
        }
        
        return "https://www.google.com/maps?q={$this->coordenadas_lat},{$this->coordenadas_lng}";
    }

    /**
     * Verificar si el territorio está activo
     */
    public function estaActivo()
    {
        return $this->activo;
    }
}
