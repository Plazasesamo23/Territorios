# Documentacion Tecnica - Sistema de Territorios

> Documentacion completa de la arquitectura multi-congregacion y funcionamiento del sistema

**Ultima actualizacion:** Diciembre 2025

---

## Indice

1. [Arquitectura General](#arquitectura-general)
2. [Sistema Multi-Congregacion](#sistema-multi-congregacion)
3. [Modelos y Relaciones](#modelos-y-relaciones)
4. [Middleware y Seguridad](#middleware-y-seguridad)
5. [Controladores](#controladores)
6. [Vistas y Layouts](#vistas-y-layouts)
7. [Sistema de Estados](#sistema-de-estados)
8. [Configuracion por Congregacion](#configuracion-por-congregacion)

---

## Arquitectura General

### Estructura de Carpetas

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── LoginController.php      # Login por nombre
│   │   ├── CongregacionController.php   # CRUD congregaciones
│   │   ├── DashboardController.php      # Dashboard + Configuracion
│   │   ├── PerfilController.php         # Perfil usuario
│   │   ├── PublicadorController.php     # CRUD publicadores
│   │   ├── RegistroController.php       # CRUD registros
│   │   ├── S13Controller.php            # Reportes S-13
│   │   └── TerritorioController.php     # CRUD territorios
│   ├── Middleware/
│   │   └── EnsureCongregacion.php       # Middleware congregacion
│   └── Providers/
│       └── ViewServiceProvider.php      # Datos globales vistas
├── Models/
│   ├── Congregacion.php                 # Modelo congregacion
│   ├── Publicador.php                   # Modelo publicador
│   ├── Registro.php                     # Modelo registro
│   ├── Territorio.php                   # Modelo territorio
│   └── User.php                         # Modelo usuario
└── Traits/
    └── BelongsToCongregacion.php        # Trait filtrado automatico

database/
├── migrations/
│   ├── 2025_12_26_000001_create_congregaciones_table.php
│   ├── 2025_12_26_000002_add_congregacion_to_users_table.php
│   ├── 2025_12_26_000003_add_congregacion_to_territorios_table.php
│   ├── 2025_12_26_000004_add_congregacion_to_publicadores_table.php
│   └── 2025_12_26_200000_add_config_to_congregaciones_table.php
└── seeders/
    └── CongregacionSeeder.php

resources/views/
├── auth/
│   └── login.blade.php                  # Login simplificado
├── congregaciones/
│   ├── index.blade.php                  # Lista congregaciones
│   └── form.blade.php                   # Formulario crear/editar
├── layouts/
│   └── app.blade.php                    # Layout principal
├── perfil/
│   └── index.blade.php                  # Pagina de perfil
├── configuracion.blade.php              # Configuracion congregacion
├── dashboard.blade.php                  # Dashboard
└── ...
```

---

## Sistema Multi-Congregacion

### Concepto

El sistema soporta multiples congregaciones aisladas. Cada congregacion tiene:
- Territorios propios
- Publicadores propios
- Registros propios (derivados de territorios)
- Usuarios administradores propios
- Configuracion de parametros independiente

### Congregaciones Actuales

| Congregacion | Territorios | Publicadores | Estado |
|--------------|-------------|--------------|--------|
| Centro Santa Coloma | 214 | 20+ | Activa con datos |
| Sabadell Este | 0 | 0 | Nueva (vacia) |

### Roles de Usuario

| Rol | Descripcion | Permisos |
|-----|-------------|----------|
| `user` | Usuario basico | Ver datos de su congregacion |
| `admin` | Administrador | CRUD completo de su congregacion |
| `superadmin` | Super administrador | Acceso a todas las congregaciones |

### Usuarios del Sistema

| Nombre (Login) | Rol | Congregacion |
|----------------|-----|--------------|
| Centro Santa Coloma | admin | Centro Santa Coloma |
| Sabadell Este | admin | Sabadell Este |
| Administrador | superadmin | Todas |

### Flujo de Autenticacion

1. Usuario ingresa nombre (ej: "Centro Santa Coloma") y contraseña
2. LoginController busca por `name` en lugar de `email`
3. Si es valido, EnsureCongregacion establece `congregacion_activa_id` en sesion
4. BelongsToCongregacion filtra automaticamente todos los datos

---

## Modelos y Relaciones

### Congregacion

```php
class Congregacion extends Model
{
    protected $table = 'congregaciones';

    protected $fillable = [
        'nombre', 'codigo', 'ciudad', 'descripcion',
        'password', 'dias_limite_activo', 'dias_archivo', 'activa'
    ];

    protected $hidden = ['password'];

    // Relaciones
    public function users(): HasMany
    public function territorios(): HasMany
    public function publicadores(): HasMany
    public function registros(): HasManyThrough
    public function getEstadisticas(): array
}
```

### User

```php
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'congregacion_id', 'role'
    ];

    // Relaciones
    public function congregacion(): BelongsTo

    // Helpers
    public function isSuperAdmin(): bool  // role === 'superadmin'
    public function isAdmin(): bool       // role === 'admin'
    public function getRolNombreAttribute(): string
}
```

### Territorio

```php
class Territorio extends Model
{
    use BelongsToCongregacion; // Filtrado automatico

    protected $fillable = [
        'congregacion_id', 'numero', 'nombre', 'descripcion',
        'coordenadas_lat', 'coordenadas_lng', 'imagen_url',
        'estado', 'activo', 'notas'
    ];

    // Relaciones
    public function congregacion(): BelongsTo
    public function registros(): HasMany

    // Metodos de estado (usan config de congregacion)
    public function calcularEstado(): string
    public function estaDisponibleParaAsignar(): bool
    public function diasRestantesParaEstarDisponible(): int
    public function fechaDisponible(): Carbon
}
```

### Publicador

```php
class Publicador extends Model
{
    use BelongsToCongregacion; // Filtrado automatico

    protected $fillable = [
        'congregacion_id', 'nombre', 'apellidos',
        'telefono', 'activo', 'notas'
    ];

    // Relaciones
    public function congregacion(): BelongsTo
    public function registros(): HasMany
}
```

### Registro

```php
class Registro extends Model
{
    // NO usa BelongsToCongregacion (se filtra por territorio)

    protected $fillable = [
        'territorio_id', 'publicador_id',
        'fecha_salida', 'fecha_entrada',
        'entrada_prevista', 'notas'
    ];

    // Relaciones
    public function territorio(): BelongsTo
    public function publicador(): BelongsTo
}
```

---

## Middleware y Seguridad

### EnsureCongregacion Middleware

Archivo: `app/Http/Middleware/EnsureCongregacion.php`

```php
public function handle($request, Closure $next)
{
    if (!auth()->check()) {
        return $next($request);
    }

    $user = auth()->user();

    // Superadmin: usa sesion o primera congregacion
    if ($user->isSuperAdmin()) {
        if (!session('congregacion_activa_id')) {
            $primera = Congregacion::first();
            session(['congregacion_activa_id' => $primera?->id]);
        }
    } else {
        // Usuario normal: siempre usa su congregacion
        session(['congregacion_activa_id' => $user->congregacion_id]);
    }

    return $next($request);
}
```

### BelongsToCongregacion Trait

Archivo: `app/Traits/BelongsToCongregacion.php`

```php
trait BelongsToCongregacion
{
    protected static function bootBelongsToCongregacion()
    {
        // Global Scope: filtra automaticamente por congregacion
        static::addGlobalScope('congregacion', function ($query) {
            if ($id = session('congregacion_activa_id')) {
                $query->where('congregacion_id', $id);
            }
        });

        // Auto-asignar congregacion al crear nuevos registros
        static::creating(function ($model) {
            if (!$model->congregacion_id) {
                $model->congregacion_id = session('congregacion_activa_id');
            }
        });
    }
}
```

### Registro en bootstrap/app.php

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'congregacion' => \App\Http\Middleware\EnsureCongregacion::class,
    ]);
})
```

### Rutas Protegidas

```php
// routes/web.php
Route::middleware(['auth', 'congregacion'])->group(function () {
    // Todas las rutas de la aplicacion
});
```

---

## Controladores

### DashboardController

```php
public function index()
// Dashboard con estadisticas filtradas por congregacion

public function configuracion()
// Pagina de configuracion de la congregacion activa

public function guardarConfiguracion(Request $request)
// Guarda dias_limite_activo y dias_archivo en congregacion
```

### CongregacionController (Solo Superadmin)

```php
public function index()      // Lista todas las congregaciones
public function create()     // Formulario crear
public function store()      // Guardar nueva congregacion
public function edit()       // Formulario editar
public function update()     // Actualizar congregacion
public function destroy()    // Eliminar congregacion
public function cambiar()    // Cambiar congregacion activa en sesion
```

### PerfilController

```php
public function index()              // Ver perfil del usuario
public function cambiarPassword()    // Cambiar contraseña (sin requerir actual)
public function actualizarNombre()   // Actualizar nombre
```

### S13Controller

```php
public function index()        // Vista S-13 con estadisticas por congregacion
public function generarPdf()   // Generar PDF con Dompdf
public function vistaPrevia()  // Vista previa del reporte
```

### RegistroController

```php
// Filtra registros por territorios de la congregacion activa
$territorioIds = Territorio::pluck('id');
$registros = Registro::whereIn('territorio_id', $territorioIds)->get();
```

---

## Vistas y Layouts

### Layout Principal (app.blade.php)

Componentes:
- **Header** con logo y navegacion
- **Badge de congregacion** activa
- **Selector de congregacion** dropdown (solo superadmin, click-based)
- **Boton de perfil** circular azul
- **Boton de logout** circular rojo
- **Toggle tema** claro/oscuro
- **Flash messages** con auto-hide
- **Footer** con nombre congregacion

### Variables Globales en Vistas

```php
// ViewServiceProvider.php
View::composer('*', function ($view) {
    if (auth()->check()) {
        $congregacionId = session('congregacion_activa_id');
        $view->with([
            'congregacionActiva' => Congregacion::find($congregacionId),
            'todasCongregaciones' => Congregacion::all(),
            'esSuperAdmin' => auth()->user()->isSuperAdmin(),
        ]);
    }
});
```

---

## Sistema de Estados

### Calculo Dinamico

Los estados NO se almacenan en base de datos. Se calculan en tiempo real usando la configuracion de cada congregacion:

```php
// Territorio::calcularEstado()

public function calcularEstado()
{
    $congregacion = $this->congregacion;
    $diasMaximos = $congregacion->dias_limite_activo ?? 60;
    $diasArchivo = $congregacion->dias_archivo ?? 90;

    $ultimoRegistro = $this->registros()->latest('fecha_salida')->first();

    // Sin registros = LIBRE
    if (!$ultimoRegistro) {
        return 'libre';
    }

    // Fue devuelto
    if ($ultimoRegistro->fecha_entrada) {
        $diasDesdeDevolucion = Carbon::parse($ultimoRegistro->fecha_entrada)
            ->diffInDays(now());

        if ($diasDesdeDevolucion < $diasArchivo) {
            return 'archivo';
        }
        return 'libre';
    }

    // Esta asignado
    $diasAsignado = Carbon::parse($ultimoRegistro->fecha_salida)
        ->diffInDays(now());

    if ($diasAsignado > $diasMaximos) {
        return 'atrasado';
    }

    return 'activo';
}
```

### Estados Disponibles

| Estado | Color | Significado |
|--------|-------|-------------|
| LIBRE | Verde | Disponible para asignar |
| ACTIVO | Azul | Asignado dentro del tiempo limite |
| ATRASADO | Rojo | Excedio tiempo limite |
| ARCHIVO | Gris | En periodo de descanso |

---

## Configuracion por Congregacion

### Campos Configurables

| Campo | Descripcion | Default |
|-------|-------------|---------|
| `dias_limite_activo` | Dias antes de marcar atrasado | 60 |
| `dias_archivo` | Dias de descanso tras devolucion | 90 |

### Ruta de Configuracion

```
GET  /configuracion -> DashboardController@configuracion
POST /configuracion -> DashboardController@guardarConfiguracion
```

### Flujo de Guardado

1. Usuario accede a `/configuracion`
2. Vista muestra valores actuales de la congregacion
3. Usuario modifica y envia formulario
4. Controlador valida (1-365 dias)
5. Actualiza `dias_limite_activo` y `dias_archivo` en tabla `congregaciones`
6. Redirige con mensaje de exito

---

## Migraciones Importantes

### Crear tabla congregaciones
```php
Schema::create('congregaciones', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('codigo')->unique();
    $table->string('ciudad')->nullable();
    $table->text('descripcion')->nullable();
    $table->string('password')->nullable();
    $table->boolean('activa')->default(true);
    $table->timestamps();
});
```

### Agregar campos config a congregaciones
```php
Schema::table('congregaciones', function (Blueprint $table) {
    $table->integer('dias_limite_activo')->default(60);
    $table->integer('dias_archivo')->default(90);
});
```

### Agregar congregacion a users
```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('congregacion_id')->nullable();
    $table->string('role')->default('user');
});
```

### Agregar congregacion a territorios/publicadores
```php
Schema::table('territorios', function (Blueprint $table) {
    $table->foreignId('congregacion_id')->nullable();
});
```

---

## Documentacion Adicional

- [funcionalidad.md](funcionalidad.md) - Funcionalidades detalladas del sistema
- [debug.md](debug.md) - Problemas encontrados y soluciones
- [ERRORES_SOLUCIONADOS.md](ERRORES_SOLUCIONADOS.md) - Errores especificos corregidos

---

## Comandos Utiles

```bash
# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Ejecutar migraciones
php artisan migrate

# Servidor de desarrollo
php artisan serve

# Ver rutas
php artisan route:list
```

---

*Documentacion actualizada: Diciembre 2025*
