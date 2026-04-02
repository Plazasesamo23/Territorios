# Territorios - Gestor de Congregacion

## Credenciales

### Servidor SSH (OVH Hosting Compartido)
- **Host:** `ssh.cluster100.hosting.ovh.net`
- **Usuario:** `trastos`
- **Contraseña:** `Bopo191210`
- **Ruta proyecto:** `/home/trastos/Territorios/`
- **URL Produccion:** https://territorios.trastosbvaa.org

### Base de datos MySQL
- **Host:** `trastos1.mysql.db`
- **BD:** `trastos1`
- **Usuario:** `trastos1`
- **Contraseña:** `Bopo191210`

### GitHub
- **Repo:** `https://github.com/Plazasesemo23/Territorios.git`
- **Rama activa:** `definitivo-servicio`
- **Rama backup pre-modularizacion:** `pre-modularizacion-backup` (en rama servidor)
- **Token GitHub (marzo 2026):** `TOKEN-ELIMINADO-POR-SEGURIDAD`
- **Remote con token:** `https://TOKEN-ELIMINADO-POR-SEGURIDAD@github.com/Plazasesemo23/Territorios.git`

---

## Comandos de conexion

```bash
# Ejecutar comando en servidor
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && [comando]"

# Subir archivo
echo y | pscp -pw Bopo191210 "C:\Users\bryan\archivo.php" trastos@ssh.cluster100.hosting.ovh.net:/home/trastos/Territorios/ruta/archivo.php

# Descargar archivo
echo y | pscp -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net:/home/trastos/Territorios/ruta/archivo.php "C:\Users\bryan\archivo.php"

# Limpiar cache Laravel
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && php artisan cache:clear && php artisan view:clear && php artisan route:clear && php artisan config:clear"
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
| `routes/reuniones.php` | Reunion VyM: CRUD programas, asignaciones, autorizaciones, historial, generos | auth + congregacion |
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
├── Reuniones/
│   └── ReunionController.php
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
| `submenu-reuniones` | Teal | `reuniones.*` |
| `submenu-admin` | Naranja | `administracion`, `publicadores.*`, `usuarios.*`, `grupos-predicacion.*`, `configuracion`, `congregaciones.*` |

Partials en `resources/views/layouts/partials/`.

---

## Estructura de la aplicacion

### Modelos (17)
User, Territorio, Publicador, Registro, Congregacion, Turno, TurnoGenerado, TurnoAsignacion, DisponibilidadPpoc, GrupoPredicacion, GrupoHistorico, CoincidenciaHistorico, RelacionFamiliar, ReunionPrograma, ReunionParte, ReunionHistorial, ReunionAutorizacion

### Middlewares custom (5)
- `CheckRole` - Verificar rol del usuario
- `EnsureCongregacion` - Filtrar por congregacion activa
- `RestrictPpocUser` - Limitar usuarios PPOC
- `VerifyCongregacionPassword` - Verificar password de congregacion
- `SecurityHeaders` - Headers de seguridad HTTP

### Services (5)
- `AsignacionPpocService` - Logica de asignacion automatica PPOC
- `GeneradorGruposService` - Generacion automatica de grupos
- `S13ImportService` - Importacion de datos S-13
- `AsignacionReunionService` - Auto-asignacion y scoring de partes VyM
- `ImportadorVymService` - Importacion de titulos desde wol.jw.org via proxy

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
- Campos especificos de reuniones: `genero`, `excluido_reuniones`, `puede_dirigir_estudio`, `puede_leer_estudio`

### 6. Modulo Reuniones VyM (Vida y Ministerio)

Gestion completa del programa de la reunion Vida y Ministerio: importacion desde jw.org, asignacion automatica inteligente, sistema de autorizaciones por drag & drop, y vista de impresion.

#### 6.1 Arquitectura

| Capa | Archivo | Responsabilidad |
|------|---------|-----------------|
| **Controller** | `App\Http\Controllers\Reuniones\ReunionController` | CRUD programas, auto-asignar, importar, recomendar, autorizaciones, generos |
| **Service** | `App\Services\AsignacionReunionService` | Algoritmo de scoring, auto-asignacion, historial, partes estandar |
| **Service** | `App\Services\ImportadorVymService` | Importar HTML de wol.jw.org via proxy, parsear con DOMDocument/XPath |
| **Model** | `App\Models\ReunionPrograma` | Programa semanal (fecha_semana, roles globales, estado) |
| **Model** | `App\Models\ReunionParte` | Parte individual (seccion, tipo, titulo, publicador, ayudante) |
| **Model** | `App\Models\ReunionHistorial` | Auditoria de todas las asignaciones realizadas |
| **Model** | `App\Models\ReunionAutorizacion` | Quien puede hacer cada tipo de parte |
| **Routes** | `routes/reuniones.php` | Todas las rutas `reuniones.*` |

#### 6.2 Tablas de base de datos

**`reuniones_programas`** - Programa semanal

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `congregacion_id` | int | FK congregacion |
| `fecha_semana` | date | Lunes de la semana |
| `presidente_id` | int/null | FK publicador |
| `oracion_inicio_id` | int/null | FK publicador |
| `oracion_final_id` | int/null | FK publicador |
| `conductor_estudio_id` | int/null | FK publicador |
| `lector_estudio_id` | int/null | FK publicador |
| `estado` | string | `borrador` o `publicado` |
| `notas` | text/null | Notas internas |

**`reuniones_partes`** - Partes individuales del programa

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `programa_id` | int | FK programa |
| `seccion` | string | `tesoros`, `maestros`, `vida_cristiana` |
| `tipo` | string | Tipo de parte (ver tabla de tipos) |
| `titulo` | string/null | Titulo importado de jw.org o manual |
| `duracion_minutos` | int | Duracion en minutos |
| `orden` | int | Orden de la parte en el programa |
| `publicador_id` | int/null | FK publicador asignado |
| `ayudante_id` | int/null | FK ayudante asignado |
| `necesita_ayudante` | bool | Si la parte requiere ayudante |

**`reuniones_historial`** - Auditoria de asignaciones

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `congregacion_id` | int | FK congregacion |
| `publicador_id` | int | FK publicador |
| `programa_id` | int | FK programa |
| `fecha_semana` | date | Fecha de la semana |
| `tipo_parte` | string | Tipo de parte asignada |
| `rol` | string | `principal` o `ayudante` |

**`reuniones_autorizaciones`** - Permisos por tipo de parte

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `publicador_id` | int | FK publicador |
| `congregacion_id` | int | FK congregacion |
| `tipo_parte` | string | Tipo de autorizacion (10 tipos) |

#### 6.3 Tipos de parte

**Tipos usados en `reuniones_partes.tipo`** (partes del programa):

| Tipo | Seccion | Descripcion | Ayudante |
|------|---------|-------------|----------|
| `discurso_tesoros` | tesoros | Discurso de 10 min de Tesoros | No |
| `perlas` | tesoros | Busquemos perlas escondidas (10 min) | No |
| `lectura` | tesoros | Lectura de la Biblia (4 min) | No |
| `empiece_conversaciones` | maestros | Empiece conversaciones (3 min) | Si |
| `haga_revisitas` | maestros | Haga revisitas (4 min) | Si |
| `haga_discipulos` | maestros | Haga discipulos (5 min) | Si |
| `explique_creencias` | maestros | Explique sus creencias | Si |
| `discurso_maestros` | maestros | Discurso sin ayudante (solo varones) | No |
| `discurso_vida` | vida_cristiana | Discurso Vida Cristiana (15 min) | No |

**Roles globales del programa** (en `reuniones_programas`):
- `presidente` - Presidente de la reunion
- `oracion_inicio` / `oracion_final` - Oraciones
- `conductor_estudio` - Conductor del estudio biblico
- `lector_estudio` - Lector del estudio biblico

**Tipos de autorizacion** (en `reuniones_autorizaciones.tipo_parte`):

| tipo_parte (auth) | Mapea a tipos de parte |
|-------------------|----------------------|
| `presidente` | presidente |
| `oracion` | oracion_inicio, oracion_final |
| `tesoros` | discurso_tesoros |
| `perlas` | perlas |
| `lectura` | lectura |
| `maestros` | empiece_conversaciones, haga_revisitas, haga_discipulos, explique_creencias, **ayudante** |
| `discurso_maestros` | discurso_maestros |
| `discurso_vida` | discurso_vida |
| `conductor_estudio` | conductor_estudio |
| `lector_estudio` | lector_estudio |

El mapeo se define en `Publicador::puedeHacerParte()` con un `match()`. Nota: el tipo `ayudante` mapea a la autorizacion `maestros` (quien puede ser estudiante tambien puede ser ayudante).

#### 6.4 Sistema de autorizaciones

**seedAutorizaciones** - Relleno automatico inicial basado en nombramientos:

| Nombramiento | Autorizaciones que recibe |
|-------------|--------------------------|
| Anciano | presidente, oracion, tesoros, perlas, lectura, discurso_maestros, maestros, discurso_vida |
| Siervo Ministerial | oracion, tesoros, perlas, lectura, discurso_maestros, maestros, discurso_vida |
| Hermano (varon) | oracion (si no es menor), lectura, discurso_maestros, maestros |
| Hermana | maestros |
| Todos | maestros |
| `puede_dirigir_estudio` | conductor_estudio |
| `puede_leer_estudio` / anciano / SM | lector_estudio |

**UI de autorizaciones** (`reuniones-autorizaciones.blade.php`):
- Layout de 2 columnas: paneles de autorizacion a la izquierda, pool de publicadores a la derecha (sticky)
- **Drag & drop**: arrastrar chips entre paneles para agregar/quitar autorizaciones
- **Modal (+)**: alternativa al drag & drop, boton + en cada panel abre modal con busqueda
- **Pool**: lista completa de publicadores activos, con buscador, badges de genero (M/F) y nombramiento (A/SM)
- **Excluidos**: panel rojo para publicadores que no reciben ninguna asignacion
- Guardar via AJAX (`POST reuniones.autorizaciones.guardar`) sin recargar pagina
- 3 casos de guardado:
  1. Arrastrar al pool = quitar autorizacion del panel origen
  2. Arrastrar a excluidos = `excluido_reuniones = true` + borrar todas las autorizaciones
  3. Arrastrar a un panel = agregar autorizacion (sin quitar del origen)
- Al des-excluir (arrastrar de excluidos a un panel): se ejecuta `seedAutorizacionesPublicador()` para restaurar autorizaciones basicas

#### 6.5 Algoritmo de auto-asignacion

El algoritmo esta en `AsignacionReunionService::autoAsignar()` y usa un sistema de **puntuacion (scoring)** para elegir al mejor candidato para cada parte.

**Orden de asignacion** (mas restrictivo primero):
1. Presidente
2. Oracion de inicio
3. Discurso Tesoros
4. Perlas escondidas
5. Discurso Vida Cristiana
6. Conductor estudio
7. Lectura biblica
8. Lector estudio
9. Oracion final
10. Partes de maestros (del programa)
11. Ayudantes (despues de asignar cada estudiante)

**Sistema de puntuacion** (`elegirMejorCandidato`):

| Factor | Puntos | Descripcion |
|--------|--------|-------------|
| Rotacion por tipo | 0 a +30 | Mas dias desde ultima asignacion de ese tipo = mas puntos. Escala: `dias / (N_candidatos * 7) * 25` |
| Nunca asignado en tipo | +35 | Prioridad alta para publicadores sin historial en ese tipo |
| Equidad por tipo | x12 | `(media_tipo - conteo_tipo) * 12`. Publicadores con menos asignaciones del tipo reciben mas puntos |
| Equidad global | x5 | `(media_global - conteo_global) * 5`. Evitar sobrecarga total |
| Ya tiene parte esta semana | -20 por cada | Penalizacion por asignacion multiple en la misma semana |
| Mismo tipo semana pasada | -50 | Penalizacion fuerte si hizo la misma parte hace <=7 dias |
| Mismo tipo hace 2 semanas | -15 | Penalizacion leve si hizo la misma parte hace <=14 dias |
| Anciano en parte de estudiante | -60 | Ancianos no deberian hacer partes de estudiante |
| SM en parte de estudiante | -20 | Siervos ministeriales menos frecuentes en partes de estudiante |
| Anciano en parte compartida | -25 | Desincentivar ancianos en partes que SM tambien pueden hacer (tesoros, perlas, vida, lector, oraciones) |
| SM en parte compartida | +10 | Bonificar SM en partes compartidas para que asuman mas carga que ancianos |
| Jitter de desempate | +-0.5 | Random minimo para romper empates |

**Partes compartidas** (donde SM tienen prioridad sobre ancianos) = `discurso_tesoros`, `perlas`, `discurso_vida`, `lector_estudio`, `oracion_inicio`, `oracion_final`

**Partes de estudiante** = `empiece_conversaciones`, `haga_revisitas`, `haga_discipulos`, `explique_creencias`, `ayudante`

**Reglas de ayudante** (`elegirMejorAyudante`):
- Mismo genero que el estudiante, siempre permitido
- Genero opuesto solo si son conyuges (matrimonio registrado en `relaciones_familiares`)
- No puede ser el mismo publicador que el estudiante
- Conyuge del estudiante recibe +5 puntos bonus
- Usa el mismo scoring que `elegirMejorCandidato` con tipo `ayudante`

**Limpieza de asignaciones invalidas** (`limpiarAsignacionesInvalidas`):
Se ejecuta antes de auto-asignar. Limpia asignaciones donde:
- El publicador ya no existe o esta inactivo
- El publicador fue excluido de reuniones
- El publicador perdio la autorizacion para ese tipo de parte
- El ayudante es de genero opuesto al estudiante y no es su conyuge

**Historial** (`guardarHistorial`):
- Metodo **publico** - se puede llamar desde fuera
- Se ejecuta en 3 momentos:
  1. Al hacer auto-asignacion (`autoAsignar`)
  2. Al guardar manualmente el programa (`update`)
  3. Al publicar el programa (`publicar`)
- Limpia historial previo del programa y lo recrea completo
- Registra roles globales (presidente, oraciones, conductor, lector) y partes individuales (publicador + ayudante)

#### 6.6 Importacion desde jw.org

**Proxy**: `https://n8n.trastosbvaa.org/wol-proxy?y=YYYY&m=MM&d=DD`
- Necesario porque wol.jw.org bloquea CORS y OVH shared hosting bloquea salida HTTPS
- El proxy corre como Node.js en VPS
- CORS configurado solo en Nginx (no en Node.js) para evitar header duplicado

**Flujo de importacion**:
1. Usuario hace clic en "Importar de jw.org" en la vista de edicion
2. **Client-side**: fetch al proxy → recibe HTML → `parsearProgramaVym()` parsea con DOMParser
3. **Client-side**: envia array de partes parseadas al servidor via POST JSON
4. **Server-side**: `importarTitulos()` borra partes existentes y crea nuevas con los titulos
5. Pagina se recarga mostrando las partes importadas

**Tambien se importa al crear semanas** (`store`):
- `ImportadorVymService::importar()` hace lo mismo server-side con DOMDocument/XPath
- Si falla la importacion, cae al fallback `generarPartesEstandar()` (partes sin titulo)

**Logica de parseo** (identica en JS y PHP):
1. Buscar bloque `.todayItem.pub-mwb` en el HTML
2. Recorrer `h2` (detectar seccion: TESOROS / MAESTROS / VIDA CRISTIANA) y `h3` (partes)
3. Extraer titulo limpiando numeracion (`N. Titulo` → `Titulo`)
4. Extraer duracion: primero del `h3`, luego del `<p>` hermano siguiente
5. Ignorar canticos, oraciones, conclusiones, introducciones
6. Clasificar tipo con `clasificarTipo()`:
   - Tesoros: `perlas` si contiene "perlas escondidas", `lectura` si contiene "lectura de la biblia", sino `discurso_tesoros`
   - Maestros: patron de titulo → `empiece_conversaciones` / `haga_revisitas` / `haga_discipulos` / `explique_creencias`. **Fallback**: si no coincide con ningun patron de estudiante → `discurso_maestros` (solo varones, sin ayudante)
   - Vida cristiana: ignora "estudio biblico de la congregacion", el resto es `discurso_vida`

**Partes estandar** (fallback sin importacion):

```
Tesoros: discurso_tesoros (10 min), perlas (10 min), lectura (4 min)
Maestros: empiece_conversaciones (3 min), haga_revisitas (4 min), haga_discipulos (5 min)
Vida cristiana: discurso_vida (15 min)
```

#### 6.7 Vista de edicion (reuniones-edit.blade.php)

**Dropdowns con autorizados + otros**:
- Cada dropdown muestra primero los publicadores autorizados para ese tipo
- Bajo un `<optgroup label="Otros">` aparecen los demas publicadores varones (flexibilidad manual)
- Esto permite que la auto-asignacion respete autorizaciones, pero el usuario pueda asignar a cualquiera manualmente

**Secciones visuales**:
| Seccion | Color | Icono |
|---------|-------|-------|
| Roles generales | Gris | - |
| Tesoros de la Biblia | Teal (#0f766e / #14b8a6) | 💎 |
| Seamos mejores maestros | Amber (#b45309 / #d97706) | 🌾 |
| Nuestra vida cristiana | Rojo (#991b1b / #dc2626) | 🐑 |

**Funcion `filtrarAyudante` (JS)**:
- Se ejecuta cuando se cambia el estudiante en una parte de maestros
- Filtra opciones del dropdown de ayudante segun genero del estudiante
- Excepcion: conyuge siempre visible aunque sea genero opuesto
- Usa mapa `conyuges` (JSON inyectado desde PHP via `relaciones_familiares`)

**Boton de recomendacion (?)**:
- En cada dropdown, boton "?" que abre modal con los 5 mejores candidatos
- Muestra: nombre, puntuacion, total asignaciones, dias desde ultima, asignaciones esta semana
- Al hacer clic en un candidato, se selecciona en el dropdown
- Usa el endpoint `GET reuniones/{id}/recomendar/{tipoParte}` que aplica el mismo scoring

**Botones de accion**:
- "Importar de jw.org" - importa titulos (reemplaza partes actuales)
- "Auto-asignar" - asigna publicadores a todas las partes vacias
- "Vista imprimible" - muestra el programa en formato print
- "Guardar cambios" - guarda asignaciones manuales + regenera historial
- "Publicar" - cambia estado a `publicado` + guarda historial
- "Eliminar" - borra el programa

#### 6.8 Reglas de negocio

**Partes de estudiante** (maestros con ayudante):
- Normalmente asignadas a hermanas o hermanos no nombrados
- Ancianos penalizados -60 puntos, SM penalizados -20 puntos
- Necesitan ayudante del mismo genero (o conyuge)

**Lectura biblica**:
- Solo hermanos (varones)
- Preferiblemente no nombrados (misma penalizacion de ancianos/SM)

**Discurso maestros** (`discurso_maestros`):
- Solo hermanos (varones), requiere autorizacion `discurso_maestros`
- No necesita ayudante
- Se detecta como fallback en la importacion cuando una parte de "maestros" no coincide con ningun patron de estudiante

**Al desactivar un publicador**:
- Se excluye de reuniones
- Se limpian asignaciones futuras invalidas en la proxima auto-asignacion
- El historial se conserva para estadisticas

**Historial se guarda en 3 momentos**: guardado manual, auto-asignacion, publicacion

#### 6.9 Rutas

| Metodo | Ruta | Nombre | Accion |
|--------|------|--------|--------|
| GET | `/reuniones` | `reuniones.index` | Listado de programas (cards semanales) |
| GET | `/reuniones/crear` | `reuniones.create` | Formulario crear semanas |
| POST | `/reuniones` | `reuniones.store` | Crear N semanas con import automatico |
| GET | `/reuniones/fin-de-semana` | `reuniones.finsemana` | Reunion fin de semana (placeholder) |
| GET | `/reuniones/asignaciones` | `reuniones.asignaciones` | Estadisticas de asignaciones por publicador |
| GET | `/reuniones/historial/{publicador}` | `reuniones.historial` | Historial de un publicador |
| GET | `/reuniones/autorizaciones` | `reuniones.autorizaciones` | UI drag & drop de autorizaciones |
| POST | `/reuniones/autorizaciones` | `reuniones.autorizaciones.guardar` | Guardar autorizacion (AJAX) |
| GET | `/reuniones/generos` | `reuniones.generos` | Asignacion masiva de generos |
| POST | `/reuniones/generos` | `reuniones.generos.guardar` | Guardar generos |
| GET | `/reuniones/{id}` | `reuniones.show` | Vista imprimible del programa |
| GET | `/reuniones/{id}/editar` | `reuniones.edit` | Editor del programa |
| PUT | `/reuniones/{id}` | `reuniones.update` | Guardar cambios manuales |
| DELETE | `/reuniones/{id}` | `reuniones.destroy` | Eliminar programa |
| POST | `/reuniones/{id}/auto-asignar` | `reuniones.auto-asignar` | Ejecutar auto-asignacion |
| POST | `/reuniones/{id}/importar-titulos` | `reuniones.importar-titulos` | Importar partes desde jw.org (AJAX) |
| POST | `/reuniones/{id}/publicar` | `reuniones.publicar` | Publicar programa |
| GET | `/reuniones/{id}/recomendar/{tipo}` | `reuniones.recomendar` | Recomendaciones para una parte (AJAX JSON) |

#### 6.10 UI/UX

- **Dark mode only** - diseño exclusivo para modo oscuro
- **Colores teocraticos**: Tesoros=teal, Maestros=amber, Vida Cristiana=rojo
- **Diseño limpio** inspirado en Linear.app
- **Index**: cards semanales con rango de fechas, semana actual destacada en teal con badge "Esta semana", barra de progreso de asignaciones, orden: actual → futuras → pasadas
- **Auto-generacion**: al entrar al index se generan automaticamente las proximas 4 semanas si no existen
- **Responsive**: tablas con scroll horizontal en movil, sticky pool en autorizaciones, formularios con flex-wrap

### 7. Multi-congregacion
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
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "tail -50 Territorios/storage/logs/laravel.log"

# Verificar sintaxis PHP
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "php -l Territorios/app/Models/Territorio.php"

# Git status
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && git status"

# Git commit + push
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && git add . && git commit -m 'mensaje' && git push origin definitivo-servicio"

# Listar rutas registradas
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "cd Territorios && php artisan route:list | head -50"
```

---

## Historial de sesiones

### 2 Abril 2026 - Grupo emergencia VyM + bonus ancianos discurso_tesoros + UX autorizaciones

**Grupo de emergencia VyM:**
- Nuevo panel "Voluntarios de emergencia" en autorizaciones (naranja, drag & drop)
- Tipo de autorizacion `voluntario_emergencia` en `reuniones_autorizaciones`
- Ciclo de rotacion INDEPENDIENTE del normal: asignaciones de emergencia no afectan scoring normal
- Boton ⚡ en cada parte de maestros (edit) que abre modal con voluntarios de emergencia
- Modal muestra top 5 voluntarios con scoring basado solo en historial de emergencia
- Al seleccionar voluntario, se marca `emergencia[parte_id] = 1` en hidden input
- Al guardar, se crea registro en `reuniones_historial` con `es_emergencia = true`
- Nuevo endpoint AJAX `POST /{id}/reemplazo-emergencia` para guardado directo
- Nuevo endpoint AJAX `GET /{id}/recomendar-emergencia/{tipoParte}` para scoring

**Migracion BD:**
- `reuniones_historial.es_emergencia` (boolean, default false) — separa ciclo normal de emergencia

**Cambios en scoring (`AsignacionReunionService`):**
- `cargarHistorial()`: filtra `es_emergencia = false` (solo historial normal)
- `guardarHistorial()`: solo limpia registros normales, preserva los de emergencia
- `discurso_tesoros` sacado de "partes compartidas" (ya no penaliza ancianos -25)
- Nuevo bloque: ancianos +15 y SM -5 en `discurso_tesoros` (ratio ~1.5x a favor de ancianos)
- Partes compartidas restantes: perlas, discurso_vida, lector_estudio, oracion_inicio, oracion_final

**UX Autorizaciones mejorada:**
- Click en chip de publicador → popover con estadisticas:
  - Asignaciones del tipo (ultimos 12 meses)
  - Promedio del grupo
  - Porcentaje vs promedio (ej: "+30%" o "-15%")
  - Ultima vez que hizo esa parte
  - Boton "Quitar de [tipo]"
- Modal "+" mejorado: ahora muestra stats de cada publicador al agregar (asignaciones y % vs promedio)
- Guia de uso simplificada: "Usa + para agregar, clic en nombre para ver stats y quitar"
- Controller pasa `$statsJson` y `$promediosPorTipo` a la vista (historial 12 meses agrupado)

**Fix proxy wol.jw.org (VPS):**
- Faltaba bloque `location /wol-proxy` en Nginx del VPS (`/etc/nginx/sites-enabled/n8n`)
- Las peticiones caian al bloque `location /` (n8n) sin headers CORS
- Agregado bloque con `proxy_pass http://127.0.0.1:3847` y `Access-Control-Allow-Origin: https://territorios.trastosbvaa.org`
- Proxy Node.js en puerto 3847 (`wol-proxy.mjs`) no fue modificado (sin CORS, como debe ser)

**Rutas nuevas (`routes/reuniones.php`):**
```
GET  /{reunione}/recomendar-emergencia/{tipoParte}  → recomendarEmergencia
POST /{reunione}/reemplazo-emergencia               → guardarReemplazoEmergencia
```

**Archivos modificados:**
| Archivo | Cambio |
|---------|--------|
| `database/migrations/2026_04_02_000001_*` | NUEVO - Migracion es_emergencia |
| `app/Models/ReunionHistorial.php` | es_emergencia en fillable y casts |
| `app/Services/AsignacionReunionService.php` | Scoring ancianos tesoros + filtro emergencia |
| `app/Http/Controllers/Reuniones/ReunionController.php` | voluntario_emergencia + 2 metodos + stats |
| `routes/reuniones.php` | 2 rutas nuevas |
| `resources/views/reuniones/autorizaciones.blade.php` | Panel emergencia + popover stats + modal mejorado |
| `resources/views/reuniones/edit.blade.php` | Boton ⚡ + modal emergencia + JS |
| `public/css/flat-global.css` | Estilos emergencia + popover |
| `/etc/nginx/sites-enabled/n8n` (VPS) | Bloque location /wol-proxy con CORS |

### 25 Marzo 2026 - Fix busqueda client-side en Registros

**Registros (devolver territorios) — `registros/index.blade.php`:**
- Busqueda convertida de server-side (`<form>` con GET que recargaba pagina) a client-side (JS filtering instantaneo)
- Tabs de tipo (Todos/Normal/Campana/Negocios) convertidos de `<a href>` a `<button>` con JS
- Buscador añadido en Vista Resumen (graficos pastel) — antes solo existia en Vista Lista
- Filas `<tr>` con `data-tipo` y `data-search` para filtrado JS por tipo y texto
- Funciones JS: `filtrarTipo()`, `filtrarLista()`, `filtrarResumen()`, `filtrarNombramiento()` actualizado para combinar con busqueda
- Ya no recarga pagina al buscar/filtrar: graficos pastel se mantienen intactos
- Buscador en vista resumen filtra las tarjetas de publicadores (`.pub-card`) por nombre

### 24 Marzo 2026 - UX Reuniones, scoring SM, vista imprimible, fix movil

**Fix scroll movil (dashboard):**
- `body` con `display:flex; flex-direction:column; min-height:100dvh` (inline en app.blade.php)
- `.main` con `flex:1; min-height:0` para que footer quede pegado sin crear espacio vacio
- Eliminado `min-height: calc(100vh - 140px)` de app.css sobreescribiendolo
- NO se toca html height (causaba bloqueo de scroll en desktop)

**Fix importador jw.org en movil:**
- AbortController con timeout 45s (proxy) y 30s (servidor) en vez de sin timeout
- Verificacion `saveResp.ok` antes de parsear JSON
- `credentials: 'same-origin'` para cookies en movil
- Validacion de respuesta vacia del proxy
- Mensajes de error descriptivos

**Registros (devolver territorios):**
- ~~Busqueda convertida de server-side a client-side~~ NO se hizo en esta sesion, se hizo el 25 marzo
- ~~Tabs convertidos a JS~~ NO se hizo en esta sesion, se hizo el 25 marzo

**Asignar territorio:**
- Filtros por tipo añadidos (Normal/Campana/Negocios) con conteo
- Se combinan con filtro de zonas existente

**Navegacion Reuniones sincronizada:**
- Header y submenu ahora muestran los mismos items: VyM | Asignaciones | Autorizaciones | Generos
- "Fin de semana" deshabilitado (gris, no clicable) con mensaje "En desarrollo"
- Eliminada inconsistencia donde Autorizaciones solo estaba en submenu y Generos solo en header

**UX Reuniones (auditoria completa):**
- Autorizaciones: guia de uso añadida arriba explicando drag&drop y boton +
- Generos: subtitulo explica POR QUE se necesita el genero + buscador + "Sin asignar" en dropdown
- Create: textos claros, boton "Crear programas" en vez de "Crear e importar de jw.org"
- Index: estado vacio con boton cuando no hay programas
- Edit: "X de Y partes asignadas", confirm mejorado en auto-asignar, eliminar discreto
- Show: "Sin asignar" en vez de "---"

**Vista imprimible rediseñada (show.blade.php):**
- Nuevo layout tipo programa oficial VyM (como PDF de referencia)
- Cabecera: titulo + nombre congregacion
- Barra azul con fecha
- Columna de hora calculada automaticamente (19:30 + duraciones acumuladas)
- Cancion+oracion inicio, palabras de introduccion con Presidente
- Secciones con barras de color: Tesoros (teal #0f766e), Maestros (dorado #b45309), Vida (rojo #991b1b)
- Partes numeradas con duracion, nombre a la derecha
- Estudiante & Ayudante con "&"
- Estudio biblico con Conductor/Lector en misma linea
- Conclusion + oracion final
- "Impreso DD-MM-YYYY" al pie
- Print: titulo vacio para evitar URL en encabezado de impresion

**Scoring auto-asignacion (AsignacionReunionService):**
- NUEVO: Ancianos penalizados -25 en partes compartidas (tesoros, perlas, vida, lector, oraciones)
- NUEVO: SM bonificados +10 en partes compartidas
- Resultado: Ancianos bajan de prom 12.6 a 11.0, SM suben de 6.9 a 8.6
- Ancianos solo hacen presidente + conductor (inevitable) + 1 discurso_vida
- SM asumen tesoros, perlas, discurso_vida, lector_estudio

**Autorizaciones actualizadas (24 marzo):**
- Antonio Milan: quitado `discurso_maestros` (queda: oracion, perlas)
- Guillermo Rivera: quitado `discurso_maestros` y `maestros` (queda: oracion, perlas)

**Autorizaciones SM vigentes:**

| SM | Autorizaciones |
|---|---|
| Adrian Rivera | conductor_estudio, discurso_maestros, discurso_vida, lector_estudio, maestros, oracion, perlas, tesoros |
| Antonio Milan | oracion, perlas |
| Benjamin Abarca | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Bryan Andrade | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Esteban Mayordomo | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Francisco Garcia | discurso_maestros, oracion |
| Guillermo Rivera | oracion, perlas |
| Harold Alvarado | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Jose Cortez | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Jose Martinez | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Manolo Mateos | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Mario Fuentes | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Nacho Pauner | discurso_maestros, discurso_vida, lector_estudio, oracion, perlas, tesoros |
| Oleg Poznishev | discurso_maestros, discurso_vida, oracion, perlas, tesoros |

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

### 23 Marzo 2026 - Optimizacion movil + Rediseno asignaciones reuniones

**Fix scroll innecesario en movil (global):**
- `flat-global.css`: Eliminado `min-height: 100vh` y `display: flex` del body global
- `flat-global.css`: Añadido `overflow-x: hidden` en html y body (elimina scroll horizontal)
- `app.blade.php`: `.main` cambiado de `min-height: calc(100vh - 52px - 48px)` a sin min-height
- `app.blade.php`: Padding `.main` reducido en movil de 1.5rem a 1rem
- `app.blade.php`: Header movil mas compacto (padding y margins reducidos)
- `app.blade.php`: Cache-busting añadido a CSS links (`?v={{ time() }}`)
- `login.blade.php`: `min-height: 100vh` → `100dvh` + `position: fixed` + `overflow: hidden` + `overscroll-behavior: none` para eliminar todo scroll
- `login.blade.php`: Viewport meta con `maximum-scale=1, user-scalable=no`
- `login.blade.php`: Meta `Cache-Control: no-cache` para evitar cache del navegador

**Dashboard movil compacto (@media max-width 640px):**
- `.dash-header`: margin-bottom 2rem → 1rem, h1 font-size reducido
- `.module-grid`: gap 1rem → 0.625rem, margin-top 1.5rem → 0.75rem
- `.module-card`: padding reducido, iconos 44px → 36px
- Textos de cards reducidos para ocupar menos espacio vertical

**Rediseno pagina Asignaciones Reuniones VyM:**
- Archivos: `ReunionController.php` metodo `asignaciones()`, `reuniones/asignaciones.blade.php`
- Nuevo toggle de vistas: "Resumen" (graficos) y "Por Nombre" (tabla original)
- Vista Resumen con 3 graficos donut SVG por nombramiento:
  - Ancianos (morado #6366f1): % asignaciones, personas, promedio/persona
  - Siervos Ministeriales (teal #14b8a6): idem
  - Publicadores (ambar #f59e0b): idem
- Tarjetas de publicadores con avatar, badge nombramiento, count y ultima fecha
- Filtros por nombramiento (Todos/Ancianos/Siervos/Publicadores) con JavaScript
- Controller: datos agrupados por nombramiento (`$porNombramiento`, `$totalAsignaciones`)
- Vista "Por Nombre": tabla original preservada como opcion secundaria

**CSS nuevo en flat-global.css:**
- Estilos `.vista-toggle`, `.vista-btn` (toggle de vistas)
- Estilos `.chart-grid`, `.chart-card`, `.chart-donut` (graficos donut)
- Estilos `.filtro-nombramiento`, `.filtro-btn` (filtros por tipo)
- Estilos `.pub-card`, `.pub-avatar`, `.pub-header`, `.pub-detalles` (tarjetas publicadores territorios)
- Media query 640px para layout responsive de graficos y cards

**Nota sobre OPcache:** Los cambios en vistas requieren `php -r "opcache_reset();"` ademas de `php artisan view:clear` para que se apliquen inmediatamente en OVH compartido.

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
- Revisar scroll vertical en todas las paginas internas en movil (aplicar mismos fixes dvh/overflow)
- Auditar paginas con vistas propias (como login) que no usan layouts.app y pueden tener CSS desactualizado
