# Territorios - Gestor de Congregacion

## Credenciales

### Servidor SSH (OVH Hosting Compartido)
- **Host:** `ssh.cluster100.hosting.ovh.net`
- **Usuario:** `trastos`
- **Contraseña:** `[PASSWORD]`
- **Ruta proyecto:** `/home/trastos/Territorios/`
- **URL Produccion:** https://territorios.trastosbvaa.org

### Base de datos MySQL
- **Host:** `trastos1.mysql.db`
- **BD:** `trastos1`
- **Usuario:** `trastos1`
- **Contraseña:** `[PASSWORD]`

### GitHub
- **Repo:** `https://github.com/Plazasesemo23/Territorios.git`
- **Rama activa:** `definitivo-servicio`
- **Rama backup pre-modularizacion:** `pre-modularizacion-backup` (en rama servidor)
- **Token GitHub (marzo 2026):** `[TOKEN_GITHUB]`
- **Remote con token:** `https://[TOKEN_GITHUB]@github.com/Plazasesemo23/Territorios.git`

---

## Comandos de conexion

```bash
# Ejecutar comando en servidor
echo y | plink -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && [comando]"

# Subir archivo
echo y | pscp -pw [PASSWORD] "C:\Users\bryan\archivo.php" trastos@ssh.cluster100.hosting.ovh.net:/home/trastos/Territorios/ruta/archivo.php

# Descargar archivo
echo y | pscp -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net:/home/trastos/Territorios/ruta/archivo.php "C:\Users\bryan\archivo.php"

# Limpiar cache Laravel
echo y | plink -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && php artisan cache:clear && php artisan view:clear && php artisan route:clear && php artisan config:clear"
```

---

## Stack Tecnologico

- **Framework:** Laravel 11.45.1 + PHP 8.2.29
- **Frontend:** Blade + Tailwind CSS + CSS custom (`flat-global.css`)
- **Base de datos:** MySQL
- **Servidor:** OVH Hosting Compartido (cluster100, Debian 10)
- **Congregaciones:** 2 (ID 1 = principal, ID 2 = Sabadell)

---

## Reglas criticas de trabajo

1. **El codigo real SOLO esta en el servidor** - Los archivos locales son temporales para edicion
2. **Flujo obligatorio:** Descargar → Guardar backup local (.backup) → Editar → Subir → Limpiar cache → Si funciona, borrar temporales / Si falla, restaurar backup
3. **No usar `tinker`** - No funciona en OVH shared hosting
4. **Para consultar BD:** Crear script PHP temporal → Subir → Ejecutar → Borrar del servidor
5. **`file_exists()` no es confiable** - Devuelve false aunque el archivo exista
6. **CSS unificado** en `/public/css/flat-global.css` - Usar variables CSS (`--primary`, `--text`, `--bg-white`, etc.). NO estilos inline con colores hardcodeados
7. **Borrar archivos temporales** despues de subirlos al servidor
8. **Guardar siempre backup local** antes de editar
9. **No duplicar botones** - Los submenus son el punto central de navegacion. No añadir shortcuts redundantes en las páginas

---

## Arquitectura Modular (desde 16/03/2026)

### Rutas separadas por modulo

| Archivo | Contenido | Middleware |
|---------|-----------|------------|
| `routes/web.php` | Auth, Dashboard, Perfil, CambiarUsuario, referencia-ui | auth + congregacion |
| `routes/territorios.php` | Territorios CRUD, Registros, S13, S13Import, PanelTerritorios, Creador | auth + congregacion + role:admin (escritura) |
| `routes/ppoc.php` | Disponibilidad publica + Calendario, Turnos, Asignaciones | publicas + auth + congregacion + role:admin |
| `routes/admin.php` | Publicadores, Usuarios, Grupos, Config, Congregaciones | auth + congregacion + role:admin + can:superadmin |

Registradas en `bootstrap/app.php` con callback `then:` que aplica middleware `web` a cada archivo.

### Controllers organizados en subdirectorios

```
app/Http/Controllers/
├── Territorios/
│   ├── TerritorioController.php
│   ├── RegistroController.php
│   ├── S13Controller.php
│   ├── S13ImportController.php
│   └── PanelTerritoriosController.php
├── PPOC/
│   ├── TurnoController.php
│   └── DisponibilidadPpocController.php
├── Admin/
│   ├── PublicadorController.php
│   ├── UsuarioController.php
│   ├── GrupoPredicacionController.php
│   └── CongregacionController.php
├── Auth/                          (Laravel auth)
├── DashboardController.php        (no se mueve)
├── PerfilController.php           (no se mueve)
├── CambiarUsuarioController.php   (no se mueve)
└── Controller.php                 (base)
```

Todos los controllers movidos usan `namespace App\Http\Controllers\{Modulo}` y `use App\Http\Controllers\Controller`.

### Dashboard con Cards por modulo

El DashboardController redirige automaticamente segun rol:
- `territorios` → redirect a `panel-territorios`
- `ppoc` → redirect a `ppoc.calendario`
- `admin`/`superadmin`/`user` → vista de cards

Cards disponibles segun permisos:
| Card | Color | Condicion |
|------|-------|-----------|
| Territorios | Verde | Todos los usuarios |
| PPOC | Azul | `canAccessPPOC()` |
| S-13 | Morado | `canGenerateS13()` |
| Administracion | Naranja | `isAdmin()` |

### Submenus automaticos

Detectados en `layouts/app.blade.php` por nombre de ruta. Se muestran como barra horizontal bajo el header.

| Submenu | Color | Se activa con rutas |
|---------|-------|---------------------|
| `submenu-territorios` | Verde | `panel-territorios`, `territorios.*`, `registros.*`, `s13.*`, `creador-territorios.*` |
| `submenu-ppoc` | Azul | `ppoc.*` |
| `submenu-admin` | Naranja | `administracion`, `publicadores.*`, `usuarios.*`, `grupos-predicacion.*`, `configuracion`, `congregaciones.*` |

Partials en `resources/views/layouts/partials/`.

---

## Estructura de la aplicacion

### Modelos (13)
User, Territorio, Publicador, Registro, Congregacion, Turno, TurnoGenerado, TurnoAsignacion, DisponibilidadPpoc, GrupoPredicacion, GrupoHistorico, CoincidenciaHistorico, RelacionFamiliar

### Middlewares custom (5)
- `CheckRole` - Verificar rol del usuario
- `EnsureCongregacion` - Filtrar por congregacion activa
- `RestrictPpocUser` - Limitar usuarios PPOC
- `VerifyCongregacionPassword` - Verificar password de congregacion
- `SecurityHeaders` - Headers de seguridad HTTP

### Services (3)
- `AsignacionPpocService` - Logica de asignacion automatica PPOC
- `GeneradorGruposService` - Generacion automatica de grupos
- `S13ImportService` - Importacion de datos S-13

---

## Sistema de usuarios y permisos

### Roles
| Rol | Descripcion |
|-----|-------------|
| `superadmin` | Acceso total a todo el sistema |
| `admin` | Administrador de su congregacion |
| `user` | Usuario basico |
| `territorios` | Solo panel de territorios (asignar/devolver) |
| `ppoc` | Solo calendario PPOC |

### Permisos configurables
| Campo DB | Descripcion |
|----------|-------------|
| `puede_generar_s13` | Permite generar reporte S-13 |
| `puede_acceder_ppoc` | Permite acceder al modulo PPOC |

---

## Modulos principales

### 1. Territorios
- CRUD completo + 3 tipos (normal, campaña, negocios)
- Estados automaticos: libre/activo/atrasado/archivo
- Asignar/devolver con envio de WhatsApp
- Imagenes almacenadas en `/public/imagenes/`

### 2. S-13 (Reporte oficial)
- Generacion PDF con vista previa
- Importacion rapida de datos
- Separacion por año de servicio

### 3. PPOC (Calendario de turnos)
- Turnos configurables por congregacion
- Disponibilidad publica via token (sin login)
- Asignacion automatica inteligente
- Exportacion PDF

### 4. Grupos de Predicacion
- Organizacion por grupos con drag & drop
- Historial por año
- Generacion automatica

### 5. Publicadores
- Datos personales, nombramientos, relaciones familiares
- Toggle precursor, aprobado PPOC, capitan PPOC

### 6. Multi-congregacion
- 2 congregaciones activas
- Cambio estilo Netflix entre congregaciones
- Filtrado automatico por congregacion en todas las queries

---

## Sistema CSS

### Archivo principal: `/public/css/flat-global.css`

Variables CSS definidas en `:root` y `[data-theme="dark"]`:

| Variable | Light | Dark | Uso |
|----------|-------|------|-----|
| `--primary` | `#4a6da7` | `#6b8fc7` | Color principal (azul) |
| `--primary-hover` | `#3d5a8a` | `#8aa8d6` | Hover del primario |
| `--text` | `#212529` | `#f1f3f5` | Texto principal |
| `--text-muted` | `#6c757d` | `#8b939c` | Texto secundario |
| `--bg` | `#f1f3f5` | `#0d0f11` | Fondo pagina |
| `--bg-white` | `#ffffff` | `#151719` | Fondo cards/sections |
| `--bg-hover` | `#e9ecef` | `#1a1d21` | Fondo hover |
| `--border` | `#e9ecef` | `#2d3339` | Bordes |
| `--radius` | `6px` | `6px` | Border radius global |

### Clases de botones
- `.btn-primary` - Azul principal
- `.btn-secondary` - Gris neutro
- `.btn-success` - Azul (alias primario)
- `.btn-warning` - Naranja (#d97706) - Acciones de advertencia
- `.btn-danger` - Rojo (#dc2626) - Acciones destructivas
- `.btn-ghost` - Transparente
- `.btn-outline` - Borde blanco (para header)

### Componentes CSS clave
- `.submenu` + `.submenu-green/blue/orange` - Barra de submenu modular
- `.module-card` + `.module-card-green/blue/purple/orange` - Cards del dashboard
- `.table-flat` - Tablas planas
- `.clickable-row` - Filas de tabla clicables (con soporte ctrl+click)
- `.stats-row` + `.stat-item` - Barra de estadisticas
- `.empty-state` - Estado vacio
- `.config-row`, `.config-info-box`, `.config-preview` - Estilos de configuracion
- `.tipo-config-section` - Secciones de config por tipo de territorio

### Regla: NO usar inline styles
Siempre usar variables CSS del tema. Nunca hardcodear colores como `#374151` o `color: #666`. Usar `var(--text)`, `var(--text-muted)`, etc. Esto garantiza que el dark theme funcione.

---

## Debugging

```bash
# Ver errores Laravel
echo y | plink -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net "tail -50 Territorios/storage/logs/laravel.log"

# Verificar sintaxis PHP
echo y | plink -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net "php -l Territorios/app/Models/Territorio.php"

# Git status
echo y | plink -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && git status"

# Git commit + push
echo y | plink -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && git add . && git commit -m 'mensaje' && git push origin definitivo-servicio"

# Listar rutas registradas
echo y | plink -pw [PASSWORD] trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && php artisan route:list | head -50"
```

---

## Historial de sesiones

### 19 Marzo 2026 - Responsive, Importador jw.org, Navegacion modular, VyM teocratico
**Commit:** b21751a

**Responsive global (flat-global.css):**
- Clase `.table-responsive` para scroll horizontal en tablas movil
- Clase `.hide-mobile` / `.show-mobile` para ocultar columnas secundarias en <640px
- Breakpoints: 768px (tablet), 640px (movil), 480px (movil pequeno)
- Tablas responsive en: publicadores, registros, reuniones (index, historial, asignaciones, generos)
- Config rows, info rows, form actions, search, modals: stack vertical en movil
- Tabs scrolleables, breadcrumbs wrap, botones flex-wrap

**Fix importador jw.org (CORS + Parser):**
- Bug: header `Access-Control-Allow-Origin` duplicado (Nginx + Node.js) → navegadores lo rechazan
- Fix: CORS solo en Nginx, restringido a `https://territorios.trastosbvaa.org`
- Node.js (`wol-proxy.mjs`): eliminados headers CORS (solo Nginx los pone)
- Parser JS reescrito: usa DOM real (`.todayItem.pub-mwb` → `h2`/`h3`), busca duracion en `<p>` hermano
- `ImportadorVymService.php` reescrito con DOMDocument + XPath (misma logica)

**Colores teocraticos VyM:**
- Tesoros de la Biblia: teal (#0f766e / #14b8a6) - antes era indigo
- Seamos mejores maestros: dorado (#b45309 / #d97706)
- Nuestra vida cristiana: granate (#991b1b / #dc2626)
- Iconos de seccion: 💎 Tesoros, 🌾 Maestros, 🐑 Vida
- Print styles actualizados con colores teocraticos

**Navegacion modular independiente:**
- Cada modulo tiene su propio nav: Territorios, Reuniones, Admin, PPOC
- Cuando estas dentro de Reuniones solo ves: Inicio | VyM | Fin de semana | Asignaciones | Generos
- Cuando estas dentro de Territorios solo ves: Inicio | Panel | Todos | Asignaciones | S-13
- "Inicio" siempre lleva al dashboard para cambiar de modulo
- Eliminados submenus duplicados (la nav principal cumple esa funcion)

**Reuniones VyM - Vista calendario:**
- Index rediseñado: cards semanales con rango de fechas (16-22 Mar)
- Semana actual destacada en teal con badge "Esta semana"
- Semanas pasadas atenuadas, futuras normales
- Barra de progreso visual de asignaciones
- Orden: semana actual → futuras → pasadas
- Boton "Crear semanas" para generar programas

**Crear semanas con import automatico de jw.org:**
- Al crear semanas, importa automaticamente los titulos del cuadernillo VyM de jw.org
- Opciones: 1, 2, 3, 4 (default), 6 meses
- jw.org tiene disponible todo 2026 completo
- Fallback a partes estandar si jw.org no tiene disponible alguna semana

**Backup BD:** backup_20260319_230119.sql

### 16 Marzo 2026 - Reestructuracion Modular + Auditoria
**Commits:** cd46041, 43fb748, b7d8b67, d3176c5

**Reestructuracion modular completa:**
- Rutas separadas en 4 archivos: `web.php`, `territorios.php`, `ppoc.php`, `admin.php`
- 11 controllers movidos a subdirectorios: `Territorios/`, `PPOC/`, `Admin/`
- `bootstrap/app.php` actualizado con `then:` callback para cargar rutas modulares
- Dashboard rediseñado con cards grandes por modulo segun permisos del usuario
- Redireccion automatica por rol (territorios→panel, ppoc→calendario)
- 3 submenus automaticos (verde, azul, naranja) detectados por ruta activa
- Header simplificado: logo + Inicio + badge modulo activo + botones usuario + dark toggle

**Auditoria de diseño (nota inicial 6.3/10):**
- Fix variables CSS fantasma en `territorios/index` (7 variables corregidas)
- Fix dark theme roto en `configuracion.blade.php` (inline styles → clases CSS)
- Fix dark theme en `s13/index` (colores hardcodeados → variables)
- `btn-danger` cambiado de gris a ROJO, `btn-warning` de azul a NARANJA
- Eliminada duplicacion de `.btn` entre layout y flat-global.css
- Tablas: `onclick` reemplazado por `data-href` con soporte ctrl+click
- Botones `<button onclick>` → `<a href>` (accesibilidad)
- Alertas duplicadas eliminadas (registros, configuracion)
- Queries Eloquent movidas de vista configuracion al controller (MVC)
- 173 lineas de CSS nuevas en flat-global.css (config, btn-danger, clickable-row, etc.)

**Limpieza de redundancia:**
- Eliminados 43 lineas de botones/links redundantes
- Quitados shortcuts duplicados en registros y publicadores (ya en submenus)
- Quitados 3 links crear territorio en administracion (ya hay dropdown)
- Quitados botones empty state redundantes
- Quitados breadcrumbs "Dashboard" innecesarios

### 16 Marzo 2026 - Auditoria de seguridad
- Eliminados 12 archivos debug/test de /public/
- Permisos .env corregidos (644 → 600)
- Eliminados .env.backup, .env.broken, .env.save
- Directorios 777 corregidos a 755
- Eliminados SQL dumps de la raiz
- Middleware SecurityHeaders añadido (X-Frame-Options, HSTS, etc.)

### 13 Marzo 2026
- Login convertido a dark mode
- Archivo: resources/views/auth/login.blade.php

### 12 Marzo 2026
- Fix S-13: reportes de diferentes años mostraban datos identicos
- Token GitHub actualizado
- Rama backup: definitivo-servidor-backup-2026-03-12

### 24 Enero 2026
- Permisos S-13 y PPOC configurables para todos los roles
- Enlace S-13 en menu de navegacion
- Commit: c6777d2

### 19 Enero 2026
- Formulario entrada rapida S-13
- Fix CSRF 419 para disponibilidad y s13/importar
- Limpieza territorios duplicados Sabadell

### 18 Enero 2026
- Rediseño botones panel territorios
- Formulario turnos PPOC modernizado
- Fix error 419 cookies

---

## Pendiente / Ideas futuras

- Filtrar territorios y publicadores por congregacion activa en desplegables
- Posible importacion CSV como alternativa
- Revisar si hay mas duplicados en otras congregaciones
- Reemplazar emojis Unicode por SVGs consistentes en todas las vistas
- Mover CSS inline restante de `territorios/index.blade.php` (~300 lineas) a flat-global.css
- Estandarizar max-width entre paginas (actualmente varia: 800px, 900px, 1000px, 1200px)
