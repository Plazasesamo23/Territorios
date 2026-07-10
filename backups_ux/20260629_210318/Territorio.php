<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Traits\BelongsToCongregacion;

// ULTIMA ACTUALIZACION: 28/12/2025 - TIPOS DE TERRITORIO + TIEMPOS POR TIPO

class Territorio extends Model
{
    use BelongsToCongregacion;

    protected $table = 'territorios';

    // Constantes para los tipos de territorio
    const TIPO_NORMAL = 'normal';
    const TIPO_CAMPANA = 'campana';
    const TIPO_NEGOCIOS = 'negocios';

    // Prefijos para mostrar los numeros
    const PREFIJOS = [
        'normal' => '',
        'campana' => 'C-',
        'negocios' => 'N-'
    ];

    // Nombres legibles de los tipos
    const TIPOS_NOMBRES = [
        'normal' => 'Normal',
        'campana' => 'Campana',
        'negocios' => 'Negocios'
    ];

    protected $fillable = [
        'congregacion_id',
        'numero',
        'tipo',
        'zona',
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
     * Obtener el numero con prefijo segun el tipo
     * Ej: 1, C-1, N-1
     */
    public function getNumeroCompletoAttribute()
    {
        $prefijo = self::PREFIJOS[$this->tipo] ?? '';
        return $prefijo . $this->numero;
    }

    /**
     * Obtener el nombre del tipo legible
     */
    public function getTipoNombreAttribute()
    {
        return self::TIPOS_NOMBRES[$this->tipo] ?? 'Normal';
    }

    /**
     * Verificar si el territorio va al S-13
     * Solo los normales van al S-13
     */
    public function vaAlS13()
    {
        return $this->tipo === self::TIPO_NORMAL;
    }

    /**
     * Verificar si es territorio de campana
     */
    public function esCampana()
    {
        return $this->tipo === self::TIPO_CAMPANA;
    }

    /**
     * Verificar si es territorio de negocios
     */
    public function esNegocios()
    {
        return $this->tipo === self::TIPO_NEGOCIOS;
    }

    /**
     * Verificar si es territorio normal
     */
    public function esNormal()
    {
        return $this->tipo === self::TIPO_NORMAL;
    }

    /**
     * Obtener el color del badge segun el tipo
     */
    public function getColorTipo()
    {
        return match($this->tipo) {
            self::TIPO_CAMPANA => 'badge-yellow',
            self::TIPO_NEGOCIOS => 'badge-blue',
            default => 'badge-gray'
        };
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeTipo($query, $tipo)
    {
        if ($tipo && $tipo !== 'todos') {
            return $query->where('tipo', $tipo);
        }
        return $query;
    }

    /**
     * Scope para obtener solo territorios normales (para S-13)
     */
    public function scopeNormales($query)
    {
        return $query->where('tipo', self::TIPO_NORMAL);
    }

    /**
     * Scope para obtener territorios de campana
     */
    public function scopeCampana($query)
    {
        return $query->where('tipo', self::TIPO_CAMPANA);
    }

    /**
     * Scope para obtener territorios de negocios
     */
    public function scopeNegocios($query)
    {
        return $query->where('tipo', self::TIPO_NEGOCIOS);
    }

    /**
     * Relacion: Un territorio pertenece a una congregacion
     */
    public function congregacion(): BelongsTo
    {
        return $this->belongsTo(Congregacion::class);
    }

    /**
     * Relacion: Un territorio tiene muchos registros
     */
    public function registros()
    {
        return $this->hasMany(Registro::class);
    }

    /**
     * Obtener el ultimo registro del territorio
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
        // 1. Usar URL de la base de datos si existe
        if (!empty($this->imagen_url)) {
            return $this->convertirUrlDirecta($this->imagen_url);
        }

        // 2. Devolver siempre la URL local (file_exists no funciona bien en hosting compartido)
        // Para territorios normales o sin tipo: 1_100.jpg
        $imagenPath = "imagenes/" . $this->congregacion_id . "_" . $this->numero . ".jpg";

        // Para territorios con tipo especial: 1_10_negocios.jpg
        if ($this->tipo && $this->tipo !== 'normal') {
            $imagenPathTipo = "imagenes/" . $this->congregacion_id . "_" . $this->numero . "_" . $this->tipo . ".jpg";
            // Intentar primero con tipo, si no existe se usa el legacy
            if (file_exists(public_path($imagenPathTipo))) {
                return asset($imagenPathTipo);
            }
        }

        // Devolver la URL de imagen legacy (sin verificar - mejor rendimiento)
        return asset($imagenPath);
    }

    /**
     * Convertir URLs de Dropbox/Drive a URLs directas
     */
    private function convertirUrlDirecta($url)
    {
        if (strpos($url, 'dropbox.com') !== false) {
            $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $url);
            $url = preg_replace('/\?dl=.*/', '', $url);
            return $url;
        }

        if (strpos($url, 'drive.google.com') !== false) {
            if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                return 'https://lh3.googleusercontent.com/d/' . $matches[1];
            }
            if (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
                return 'https://lh3.googleusercontent.com/d/' . $matches[1];
            }
        }

        return $url;
    }

    /**
     * Verificar si el territorio tiene imagen
     */
    public function tieneImagen()
    {
        // Primero buscar sin sufijo
        $imagenPathLegacy = public_path("imagenes/" . $this->congregacion_id . "_" . $this->numero . ".jpg");
        if (file_exists($imagenPathLegacy)) {
            return true;
        }

        // Buscar con sufijo de tipo
        if ($this->tipo && $this->tipo !== 'normal') {
            $imagenPath = public_path("imagenes/" . $this->congregacion_id . "_" . $this->numero . "_" . $this->tipo . ".jpg");
            if (file_exists($imagenPath)) {
                return true;
            }
        }

        return !empty($this->imagen_url);
    }

    /**
     * Calcular el estado actual del territorio basado en fechas y configuracion de la congregacion
     * Ahora usa tiempos especificos por tipo de territorio
     */
    public function calcularEstado()
    {
        $diasMaximos = $this->getDiasLimiteActivo();
        $diasArchivo = $this->getDiasArchivo();

        $ultimoRegistro = $this->registros()
            ->latest('fecha_salida')
            ->first();

        if (!$ultimoRegistro) {
            return 'libre';
        }

        if ($ultimoRegistro->fecha_entrada) {
            $fechaDevolucion = Carbon::parse($ultimoRegistro->fecha_entrada);
            $diasDesdeDevolucion = $fechaDevolucion->diffInDays(now());

            if ($diasDesdeDevolucion < $diasArchivo) {
                return 'archivo';
            }

            return 'libre';
        }

        $fechaSalida = Carbon::parse($ultimoRegistro->fecha_salida);
        $diasAsignado = $fechaSalida->diffInDays(now());

        if ($diasAsignado > $diasMaximos) {
            return 'atrasado';
        }

        return 'activo';
    }

    /**
     * Dias que lleva asignado el territorio ahora mismo (registro activo).
     * Devuelve null si no esta asignado.
     */
    public function diasEnUso(): ?int
    {
        $reg = $this->registroActivo();
        if (!$reg) {
            return null;
        }
        return (int) round(Carbon::parse($reg->fecha_salida)->diffInDays(now()));
    }

    /**
     * Limite de dias antes de considerarse pasado de tiempo (segun tipo y congregacion).
     */
    public function diasLimiteUso(): int
    {
        return $this->getDiasLimiteActivo();
    }

    /**
     * Nivel de uso del territorio asignado, para destacarlo visualmente:
     *  - 'en_uso'     : dentro de plazo
     *  - 'por_vencer' : se acerca el limite, conviene pedir la devolucion
     *  - 'pasado'     : se ha pasado del tiempo (atrasado)
     * Devuelve null si no esta asignado.
     */
    public function nivelUso(): ?string
    {
        $dias = $this->diasEnUso();
        if ($dias === null) {
            return null;
        }
        $limite = $this->getDiasLimiteActivo();
        if ($dias > $limite) {
            return 'pasado';
        }
        // Aviso cuando faltan 15 dias o menos (o el 20% final en limites cortos)
        $margenAviso = min(15, (int) ceil($limite * 0.2));
        if ($dias >= $limite - $margenAviso) {
            return 'por_vencer';
        }
        return 'en_uso';
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
     * Verificar si tiene coordenadas validas
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
     * Verificar si el territorio esta activo
     */
    public function estaActivo()
    {
        return $this->activo;
    }

    /**
     * Obtener los dias de archivo configurados para este tipo de territorio
     */
    protected function getDiasArchivo()
    {
        $congregacion = $this->congregacion;
        if (!$congregacion) {
            return 90;
        }

        return match($this->tipo) {
            self::TIPO_CAMPANA => $congregacion->dias_archivo_campana ?? 30,
            self::TIPO_NEGOCIOS => $congregacion->dias_archivo_negocios ?? 60,
            default => $congregacion->dias_archivo ?? 90,
        };
    }

    /**
     * Obtener los dias limite activo configurados para este tipo de territorio
     */
    protected function getDiasLimiteActivo()
    {
        $congregacion = $this->congregacion;
        if (!$congregacion) {
            return 60;
        }

        return match($this->tipo) {
            self::TIPO_CAMPANA => $congregacion->dias_limite_activo_campana ?? 30,
            self::TIPO_NEGOCIOS => $congregacion->dias_limite_activo_negocios ?? 60,
            default => $congregacion->dias_limite_activo ?? 120,
        };
    }

    /**
     * Verificar si el territorio esta disponible para asignar
     */
    public function estaDisponibleParaAsignar()
    {
        $estado = $this->calcularEstado();

        if ($estado !== 'libre') {
            return false;
        }

        $diasArchivo = $this->getDiasArchivo();

        $ultimoRegistroDevuelto = $this->registros()
            ->whereNotNull('fecha_entrada')
            ->latest('fecha_entrada')
            ->first();

        if ($ultimoRegistroDevuelto) {
            $fechaDevolucion = Carbon::parse($ultimoRegistroDevuelto->fecha_entrada);
            $diasDesdeDevolucion = $fechaDevolucion->diffInDays(now());

            if ($diasDesdeDevolucion < $diasArchivo) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener los dias restantes para que este disponible
     */
    public function diasRestantesParaEstarDisponible()
    {
        if ($this->estaDisponibleParaAsignar()) {
            return 0;
        }

        $diasArchivo = $this->getDiasArchivo();

        $ultimoRegistroDevuelto = $this->registros()
            ->whereNotNull('fecha_entrada')
            ->latest('fecha_entrada')
            ->first();

        if ($ultimoRegistroDevuelto) {
            $fechaDevolucion = Carbon::parse($ultimoRegistroDevuelto->fecha_entrada);
            $diasDesdeDevolucion = $fechaDevolucion->diffInDays(now());

            return max(0, $diasArchivo - $diasDesdeDevolucion);
        }

        return 0;
    }

    /**
     * Obtener la fecha cuando estara disponible
     */
    public function fechaDisponible()
    {
        $diasArchivo = $this->getDiasArchivo();

        $ultimoRegistroDevuelto = $this->registros()
            ->whereNotNull('fecha_entrada')
            ->latest('fecha_entrada')
            ->first();

        if ($ultimoRegistroDevuelto) {
            return Carbon::parse($ultimoRegistroDevuelto->fecha_entrada)->addDays($diasArchivo);
        }

        return now();
    }

    /**
     * Obtener el motivo por el cual no esta disponible
     */
    public function motivoNoDisponible()
    {
        $estado = $this->calcularEstado();

        if ($estado === 'activo') {
            $publicador = $this->publicadorActual();
            return $publicador ? "Asignado a {$publicador->nombre_completo}" : "Territorio actualmente asignado";
        }

        if ($estado === 'atrasado') {
            $publicador = $this->publicadorActual();
            return $publicador ? "Atrasado con {$publicador->nombre_completo}" : "Territorio atrasado";
        }

        if ($estado === 'archivo') {
            $diasRestantes = $this->diasRestantesParaEstarDisponible();
            if ($diasRestantes > 0) {
                return "En descanso ({$diasRestantes} dias restantes)";
            }
        }

        return "No disponible";
    }

    /**
     * Obtener el siguiente numero disponible para un tipo de territorio
     */
    public static function getSiguienteNumero($congregacionId, $tipo = 'normal')
    {
        $ultimoNumero = self::where('congregacion_id', $congregacionId)
            ->where('tipo', $tipo)
            ->max('numero');

        return ($ultimoNumero ?? 0) + 1;
    }
}
