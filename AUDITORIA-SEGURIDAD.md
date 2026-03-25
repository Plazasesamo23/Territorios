# Auditoria de Seguridad - Territorios

**Fecha:** 16 Marzo 2026
**Servidor:** OVH Hosting Compartido (cluster100)
**URL:** https://territorios.trastosbvaa.org

---

## Estado del servidor

- **PHP:** 8.2.29 con OPcache + ionCube
- **Laravel:** 11.45.1
- **OS:** Debian 10 (Buster) - EOL
- **RAM:** 30 GB (compartida), 26 GB disponibles
- **CPU:** AMD EPYC-Milan, 16 cores (compartidos)
- **Disco:** 266 MB usados de 63 TB compartidos
- **Uptime:** estable, load average 0.42

---

## Problemas encontrados y corregidos

### CRITICOS (todos corregidos el 16/03/2026)

#### 1. Archivos debug/test accesibles publicamente
- **Problema:** 12 archivos PHP en `/public/` accesibles sin autenticacion (debug-config.php, debug-s13.php, test-*.php, etc.)
- **Riesgo:** Filtracion de datos de BD, configuracion interna, version PHP
- **Correccion:** Eliminados todos los archivos

#### 2. Permisos .env demasiado abiertos
- **Problema:** `.env` con permisos 644 (legible por todos los usuarios del sistema)
- **Correccion:** Cambiado a 600 (solo el propietario)

#### 3. Copias .env con credenciales
- **Problema:** `.env.backup`, `.env.broken`, `.env.save` con credenciales en claro y permisos 644
- **Correccion:** Eliminados los 3 archivos

#### 4. Directorios con permisos 777
- **Problema:** `public/imagenes/` y `public/formas-territorio/` con permisos world-writable
- **Correccion:** Cambiados a 755

#### 5. SQL dumps en el servidor
- **Problema:** `database_export.sql` y `database_export_latest.sql` en la raiz del proyecto
- **Riesgo:** Volcado completo de la BD accesible si alguien obtiene acceso al servidor
- **Correccion:** Eliminados

#### 6. Headers de seguridad ausentes
- **Problema:** Sin X-Frame-Options, HSTS, X-Content-Type-Options, etc.
- **Correccion:** Middleware `SecurityHeaders.php` creado y registrado globalmente en `bootstrap/app.php`
- **Headers añadidos:**
  - X-Frame-Options: SAMEORIGIN
  - X-Content-Type-Options: nosniff
  - X-XSS-Protection: 1; mode=block
  - Strict-Transport-Security: max-age=31536000; includeSubDomains
  - Referrer-Policy: strict-origin-when-cross-origin
  - Permissions-Policy: camera=(), microphone=(), geolocation=()

---

## Problemas pendientes (nivel medio)

### 1. SESSION_ENCRYPT=false
- Las sesiones no estan cifradas
- **Recomendacion:** Poner `SESSION_ENCRYPT=true` en `.env`

### 2. Exclusiones CSRF
- `disponibilidad/*` y `s13/importar/*` excluidas de CSRF
- `disponibilidad/*` es aceptable (rutas publicas con token)
- `s13/importar/*` deberia revisarse

### 3. X-Powered-By: PHP/8.2
- OVH lo inyecta a nivel proxy, no se puede eliminar desde la app
- Riesgo bajo: solo revela la version de PHP

### 4. Misma contraseña SSH y BD
- `Bopo191210` se usa para ambos
- **Recomendacion:** Cambiar la contraseña de BD para que sea diferente

### 5. Vulnerabilidades en dependencias
- 5 CVEs detectados por `composer audit`
- **Recomendacion:** Ejecutar `composer update` periodicamente

### 6. .gitignore incompleto
- No protege `.env.broken`, `.env.save`, archivos debug sueltos
- **Recomendacion:** Actualizar `.gitignore`

---

## Lo que esta bien

- APP_DEBUG=false en produccion
- APP_KEY configurada correctamente
- HTTPS forzado via .htaccess
- Cookies con flags secure, httponly, samesite=lax
- .env no accesible via web (OVH lo bloquea)
- .git/ no accesible via web (OVH lo bloquea)
- Registro publico desactivado
- Sin phpinfo() expuesto
- Sin rutas de debug (telescope, debugbar)
- Log limpio, sin errores

---

## Archivos modificados en esta auditoria

| Archivo | Accion |
|---------|--------|
| `public/.htaccess` | Añadidos security headers (tambien via htaccess como fallback) |
| `app/Http/Middleware/SecurityHeaders.php` | NUEVO - Middleware de headers de seguridad |
| `bootstrap/app.php` | Registrado SecurityHeaders como middleware global |
| `public/debug-*.php` (5 archivos) | ELIMINADOS |
| `public/test-*.php` (5 archivos) | ELIMINADOS |
| `public/check-ultimos-registros.php` | ELIMINADO |
| `public/test.html`, `public/test.css` | ELIMINADOS |
| `.env` | Permisos cambiados a 600 |
| `.env.backup`, `.env.broken`, `.env.save` | ELIMINADOS |
| `database_export.sql`, `database_export_latest.sql` | ELIMINADOS |
| `public/imagenes/` | Permisos 777 → 755 |
| `public/formas-territorio/` | Permisos 777 → 755 |
