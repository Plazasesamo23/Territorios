# Errores Solucionados - Sistema de Territorios

> Registro de todos los errores encontrados y sus soluciones durante el desarrollo

**Ultima actualizacion:** Diciembre 2025

---

## Indice

1. [Errores de Multi-Congregacion](#errores-de-multi-congregacion)
2. [Errores de Autenticacion](#errores-de-autenticacion)
3. [Errores de Interfaz](#errores-de-interfaz)
4. [Errores de S-13 y PDFs](#errores-de-s-13-y-pdfs)
5. [Errores de Base de Datos](#errores-de-base-de-datos)

---

## Errores de Multi-Congregacion

### Error: "Call to a member function calcularEstado() on null"

**Ubicacion:** RegistroController.php, vista de registros

**Causa:** Los registros no estaban siendo filtrados por congregacion. El sistema buscaba registros de Centro-SC pero intentaba calcular estados de territorios que no existian en Sabadell-Este.

**Solucion:**
```php
// Antes (error)
$registros = Registro::whereNull('fecha_entrada')->get();

// Despues (correcto)
$territorioIds = Territorio::pluck('id');
$registros = Registro::whereIn('territorio_id', $territorioIds)
    ->whereNull('fecha_entrada')
    ->get();
```

**Archivo modificado:** `app/Http/Controllers/RegistroController.php`

---

### Error: S-13 muestra estadisticas incorrectas (100 asignados, 332 total para Sabadell Este vacia)

**Causa:** S13Controller tenia referencias hardcodeadas a 214 territorios y no filtraba estadisticas por congregacion.

**Solucion:**
```php
// Filtrar estadisticas por congregacion
$territorioIds = Territorio::pluck('id');

$estadisticas = [
    'total_territorios' => Territorio::count(),
    'territorios_libres' => Territorio::get()->filter(fn($t) =>
        $t->calcularEstado() === 'libre'
    )->count(),
    'territorios_asignados' => Registro::whereIn('territorio_id', $territorioIds)
        ->whereNull('fecha_entrada')
        ->count(),
    'total_registros' => Registro::whereIn('territorio_id', $territorioIds)->count(),
];
```

**Archivo modificado:** `app/Http/Controllers/S13Controller.php`

---

## Errores de Autenticacion

### Error: Login con email no funciona para nombres de congregacion

**Causa:** El sistema usaba `email` como campo de autenticacion pero se queria login por nombre de congregacion.

**Solucion:**
```php
// LoginController.php
public function username()
{
    return 'name'; // Cambiado de 'email' a 'name'
}

// login.blade.php
<label for="name">Congregacion</label>
<input type="text" id="name" name="name" placeholder="Ej: Centro Santa Coloma">
```

**Archivos modificados:**
- `app/Http/Controllers/Auth/LoginController.php`
- `resources/views/auth/login.blade.php`

---

## Errores de Interfaz

### Error: Selector de congregacion desaparece al mover el raton

**Descripcion del usuario:** "el menu selector solo se mantiene activo si mantengo el raton alli"

**Causa:** El dropdown usaba CSS hover-based que desaparecia al mover el raton hacia las opciones.

**Solucion:** Cambiar a JavaScript click-based toggle:

```javascript
// Antes (CSS hover)
.congregacion-selector:hover .dropdown-menu {
    display: block;
}

// Despues (JS click)
congregacionToggle.addEventListener('click', function(e) {
    e.stopPropagation();
    congregacionDropdown.classList.toggle('show');
});

// Cerrar al hacer click fuera
document.addEventListener('click', function(e) {
    if (!congregacionDropdown.contains(e.target)) {
        congregacionDropdown.classList.remove('show');
    }
});
```

**Archivo modificado:** `resources/views/layouts/app.blade.php`

---

### Error: Boton de perfil no es visible/intuitivo

**Descripcion del usuario:** "deberia notarse mas que es un boton y solo deberia ser un icono intuitivo"

**Solucion:** Crear botones circulares estilizados:

```css
.profile-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
}

.logout-btn {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}
```

**Archivo modificado:** `resources/views/layouts/app.blade.php`

---

## Errores de S-13 y PDFs

### Error: "Class Dompdf\Options not found"

**Causa:** El paquete dompdf no estaba instalado en el proyecto.

**Solucion:**
```bash
# Descargar composer.phar
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php

# Instalar dompdf
php composer.phar require dompdf/dompdf
```

**Archivos creados:** `composer.phar`, `vendor/dompdf/`

---

## Errores de Base de Datos

### Error: Campos de configuracion no existen en congregaciones

**Causa:** La tabla `congregaciones` no tenia los campos `dias_limite_activo` y `dias_archivo`.

**Solucion:** Crear migracion:
```php
// database/migrations/2025_12_26_200000_add_config_to_congregaciones_table.php

public function up(): void
{
    Schema::table('congregaciones', function (Blueprint $table) {
        $table->integer('dias_limite_activo')->default(60)->after('password');
        $table->integer('dias_archivo')->default(90)->after('dias_limite_activo');
    });
}
```

**Comando:** `php artisan migrate`

---

## Errores Menores Corregidos

### Contraseña anterior requerida para cambiar contraseña

**Descripcion:** El usuario queria poder cambiar contraseña sin requerir la actual (en caso de olvido).

**Solucion:** Eliminar validacion de `current_password`:
```php
// Antes
$request->validate([
    'current_password' => ['required', 'current_password'],
    'password' => ['required', 'confirmed', Password::min(6)],
]);

// Despues
$request->validate([
    'password' => ['required', 'confirmed', Password::min(6)],
]);
```

**Archivos modificados:**
- `app/Http/Controllers/PerfilController.php`
- `resources/views/perfil/index.blade.php`

---

### Menu de configuracion faltante

**Descripcion:** Los usuarios no veian el enlace de configuracion en el menu.

**Solucion:** Agregar enlace en navegacion:
```html
<a href="{{ route('configuracion') }}" class="nav-link">
    Configuracion
</a>
```

**Archivo modificado:** `resources/views/layouts/app.blade.php`

---

### Configuracion guardaba en archivo global en lugar de por congregacion

**Causa:** `guardarConfiguracion()` modificaba `config/territorios.php` en lugar de la tabla `congregaciones`.

**Solucion:**
```php
public function guardarConfiguracion(Request $request)
{
    $congregacion = Congregacion::find(session('congregacion_activa_id'));

    $congregacion->update([
        'dias_limite_activo' => $request->dias_limite_activo,
        'dias_archivo' => $request->dias_archivo,
    ]);

    return redirect()->route('configuracion')
        ->with('success', 'Configuracion actualizada');
}
```

**Archivo modificado:** `app/Http/Controllers/DashboardController.php`

---

## Comandos Utiles para Debug

```bash
# Limpiar todas las caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Verificar rutas
php artisan route:list

# Verificar migraciones pendientes
php artisan migrate:status
```

---

## Prevencion de Errores Futuros

1. **Siempre filtrar por congregacion** al consultar territorios, publicadores o registros
2. **Usar el trait BelongsToCongregacion** en modelos que requieren aislamiento
3. **Probar con Sabadell Este** (vacia) para verificar que no hay datos cruzados
4. **Limpiar cache** despues de cambios en configuracion o rutas
5. **Verificar session** tiene `congregacion_activa_id` antes de consultas

---

*Documento actualizado: Diciembre 2025*
