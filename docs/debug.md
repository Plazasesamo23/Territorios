# Documentacion Tecnica - Debug y Desarrollo

> Guia de desarrollo, debugging y historial de cambios tecnicos

**Ultima actualizacion:** Diciembre 2025

---

## Informacion del Proyecto

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Frontend**: CSS Personalizado + Blade Templates
- **Base de Datos**: MySQL (XAMPP local, OVH produccion)
- **PDFs**: Dompdf
- **Servidor Local**: `C:\xampp\htdocs\territorios`

### URLs de Desarrollo
- `http://localhost:8000` (Artisan serve)
- `http://localhost/territorios/public/` (XAMPP)

---

## Sesion Diciembre 2025 - Sistema Multi-Congregacion

### Cambios Principales Implementados

1. **Sistema Multi-Congregacion**
   - Tabla `congregaciones` con campos de configuracion
   - Trait `BelongsToCongregacion` para filtrado automatico
   - Middleware `EnsureCongregacion` para sesion
   - Global Scopes en Territorio y Publicador

2. **Autenticacion Simplificada**
   - Login por nombre de congregacion
   - Sin requerir email
   - Usuarios: Centro Santa Coloma, Sabadell Este, Administrador

3. **Configuracion por Congregacion**
   - `dias_limite_activo` y `dias_archivo` por congregacion
   - Guardado en base de datos (no archivo config)
   - Vista de configuracion actualizada

4. **Interfaz Mejorada**
   - Selector de congregacion click-based
   - Botones de perfil y logout circulares
   - Menu de configuracion agregado

---

## Archivos Clave Modificados

### Modelos

| Archivo | Cambios |
|---------|---------|
| `app/Models/Congregacion.php` | Nuevo modelo con relaciones |
| `app/Models/User.php` | Agregado role, congregacion_id, helpers |
| `app/Models/Territorio.php` | Usa trait, calcula estado por congregacion |
| `app/Models/Publicador.php` | Usa trait |

### Controladores

| Archivo | Cambios |
|---------|---------|
| `app/Http/Controllers/DashboardController.php` | Metodos configuracion() y guardarConfiguracion() |
| `app/Http/Controllers/CongregacionController.php` | Nuevo controlador CRUD |
| `app/Http/Controllers/PerfilController.php` | Nuevo controlador perfil |
| `app/Http/Controllers/S13Controller.php` | Filtrado por congregacion |
| `app/Http/Controllers/RegistroController.php` | Filtrado por territorioIds |
| `app/Http/Controllers/Auth/LoginController.php` | Login por name |

### Middleware y Traits

| Archivo | Cambios |
|---------|---------|
| `app/Http/Middleware/EnsureCongregacion.php` | Nuevo middleware |
| `app/Traits/BelongsToCongregacion.php` | Nuevo trait |
| `bootstrap/app.php` | Registro de middleware |

### Vistas

| Archivo | Cambios |
|---------|---------|
| `resources/views/layouts/app.blade.php` | Selector congregacion, botones |
| `resources/views/auth/login.blade.php` | Campo name |
| `resources/views/configuracion.blade.php` | Usa datos congregacion |
| `resources/views/perfil/index.blade.php` | Nueva vista perfil |

### Migraciones

| Archivo | Cambios |
|---------|---------|
| `2025_12_26_000001_create_congregaciones_table.php` | Tabla congregaciones |
| `2025_12_26_000002_add_congregacion_to_users_table.php` | FK y role |
| `2025_12_26_000003_add_congregacion_to_territorios_table.php` | FK |
| `2025_12_26_000004_add_congregacion_to_publicadores_table.php` | FK |
| `2025_12_26_200000_add_config_to_congregaciones_table.php` | dias config |

---

## Comandos de Debug Utiles

### Limpiar Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Ver Logs
```bash
tail -f storage/logs/laravel.log
```

### Tinker (Debug Interactivo)
```bash
php artisan tinker

# Ver congregaciones
App\Models\Congregacion::all()

# Ver usuario
App\Models\User::find(1)

# Ver session
session('congregacion_activa_id')
```

### Ver Rutas
```bash
php artisan route:list
php artisan route:list --name=configuracion
```

### Ejecutar Migraciones
```bash
# Ver estado
php artisan migrate:status

# Ejecutar pendientes
php artisan migrate

# Reset completo
php artisan migrate:fresh --seed
```

---

## Problemas Comunes y Soluciones

### Error: "congregacion_activa_id not set"

**Causa**: Usuario no ha pasado por el middleware de congregacion.

**Solucion**: Verificar que la ruta esta dentro del grupo protegido:
```php
Route::middleware(['auth', 'congregacion'])->group(function () {
    // rutas aqui
});
```

### Error: "Call to a member function on null"

**Causa**: Modelo no tiene congregacion asociada o esta filtrado.

**Solucion**: Verificar que el modelo usa el trait:
```php
use BelongsToCongregacion;
```

Y que tiene `congregacion_id` en fillable.

### Error: "Class Dompdf not found"

**Causa**: Paquete dompdf no instalado.

**Solucion**:
```bash
composer require dompdf/dompdf
```

### Dropdown no funciona

**Causa**: CSS hover-based no funciona bien.

**Solucion**: Usar JavaScript click-based:
```javascript
toggle.addEventListener('click', function(e) {
    e.stopPropagation();
    dropdown.classList.toggle('show');
});
```

---

## Estructura de la Base de Datos

### Tabla congregaciones
```sql
CREATE TABLE congregaciones (
    id BIGINT PRIMARY KEY,
    nombre VARCHAR(255),
    codigo VARCHAR(255) UNIQUE,
    ciudad VARCHAR(255),
    descripcion TEXT,
    password VARCHAR(255),
    dias_limite_activo INT DEFAULT 60,
    dias_archivo INT DEFAULT 90,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabla users
```sql
ALTER TABLE users ADD COLUMN congregacion_id BIGINT;
ALTER TABLE users ADD COLUMN role VARCHAR(255) DEFAULT 'user';
```

### Tabla territorios
```sql
ALTER TABLE territorios ADD COLUMN congregacion_id BIGINT;
```

### Tabla publicadores
```sql
ALTER TABLE publicadores ADD COLUMN congregacion_id BIGINT;
```

---

## Flujo de Autenticacion

```
1. Usuario accede a /login
2. Ingresa nombre (ej: "Centro Santa Coloma") y contraseña
3. LoginController valida por campo 'name'
4. Auth::login() establece sesion
5. Middleware 'congregacion' se ejecuta
6. EnsureCongregacion establece session('congregacion_activa_id')
7. BelongsToCongregacion filtra queries automaticamente
8. Usuario ve solo datos de su congregacion
```

---

## Flujo de Cambio de Congregacion (Superadmin)

```
1. Superadmin hace click en badge de congregacion
2. Dropdown muestra todas las congregaciones
3. Click en congregacion deseada
4. POST a /congregaciones/{id}/cambiar
5. CongregacionController::cambiar() actualiza sesion
6. Redirect con datos de nueva congregacion
```

---

## Testing Manual

### Verificar Multi-Congregacion

1. Login como "Centro Santa Coloma"
2. Verificar que ve 214 territorios
3. Logout
4. Login como "Sabadell Este"
5. Verificar que ve 0 territorios
6. Login como "Administrador"
7. Cambiar entre congregaciones usando selector
8. Verificar datos cambian correctamente

### Verificar Configuracion

1. Acceder a /configuracion
2. Cambiar dias_limite_activo a 30
3. Guardar
4. Verificar mensaje de exito
5. Verificar que territorios calculan estado con nuevo valor

### Verificar S-13

1. Acceder a /s13
2. Verificar estadisticas coinciden con congregacion
3. Generar PDF
4. Verificar descarga correcta

---

## Notas de Desarrollo

- El sistema usa Laravel UI para autenticacion base
- Los estados de territorio se calculan dinamicamente, NO se almacenan
- Registros no necesitan congregacion_id (se infiere del territorio)
- El trait BelongsToCongregacion usa Global Scopes
- ViewServiceProvider comparte datos de congregacion a todas las vistas

---

## Proximos Pasos Sugeridos

1. [ ] Tests automatizados para multi-congregacion
2. [ ] API REST para app movil
3. [ ] Notificaciones push para territorios atrasados
4. [ ] Backup automatico de base de datos
5. [ ] Logs de auditoria por congregacion

---

*Documentacion actualizada: Diciembre 2025*
