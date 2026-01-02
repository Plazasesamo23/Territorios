# Documentacion de Funcionalidad - Sistema de Territorios

> Guia completa de todas las funcionalidades del sistema multi-congregacion

**Ultima actualizacion:** 28 Diciembre 2025

---

## Descripcion General

El Sistema de Territorios es una aplicacion web multi-congregacion para gestionar territorios de predicacion. Cada congregacion tiene su propio espacio aislado con territorios, publicadores y registros independientes.

### Objetivos del Sistema
- **Multi-congregacion**: Soporte para multiples congregaciones aisladas
- **Digitalizacion completa**: Eliminacion de procesos manuales (Excel, AppleScript)
- **Accesibilidad universal**: Funcionamiento en moviles y ordenadores
- **Comunicacion eficiente**: Integracion con WhatsApp
- **Configuracion flexible**: Parametros editables por congregacion

---

## Arquitectura

- **Framework**: Laravel 11 con PHP 8.2+
- **Base de datos**: MySQL
- **Frontend**: CSS personalizado con tema claro/oscuro
- **PDFs**: Dompdf para reportes S-13
- **Autenticacion**: Login por nombre de congregacion

---

## Modulos y Paginas

### 1. Login

**Ruta**: `/login`

**Funcionalidad**:
- Login simplificado por nombre de congregacion
- Campo "Congregacion" en lugar de "Correo Electronico"
- Usuarios disponibles:
  - `Centro Santa Coloma`
  - `Sabadell Este`
  - `Administrador` (superadmin)

---

### 2. Dashboard

**Ruta**: `/`

**Funcionalidad**:
- Estadisticas en tiempo real filtradas por congregacion
- Total de territorios, publicadores activos
- Territorios por estado (libre, activo, atrasado, archivo)
- Alertas de territorios que requieren atencion
- Ultimos 5 registros activos
- Accesos rapidos a funciones principales

**Estadisticas mostradas**:
- Total Territorios
- Publicadores Activos
- Total Registros
- Territorios Libres
- Territorios Activos
- Territorios Atrasados
- Territorios en Archivo

---

### 3. Territorios

**Ruta**: `/territorios`

**Funcionalidad**:
- Lista de territorios con filtros por estado
- Cards con imagen, numero y estado
- Busqueda por numero o nombre
- Paginacion
- Estados visuales con colores

**Crear/Editar Territorio**:
- Numero (unico por congregacion)
- Nombre y descripcion
- Coordenadas GPS
- Imagen automatica por numero
- Notas

**Acciones disponibles**:
- Ver detalle
- Editar
- Asignar a publicador
- Enviar por WhatsApp
- Eliminar

---

### 4. Publicadores

**Ruta**: `/publicadores`

**Funcionalidad**:
- Lista tipo tabla minimalista
- Campos: Nombre, Apellidos, Telefono, Estado
- Filtro por estado activo/inactivo
- Crear/editar publicadores

**Vista individual**:
- Informacion del publicador
- Historial de territorios asignados
- Territorios actualmente asignados

---

### 5. Registros

**Ruta**: `/registros`

**Funcionalidad**:
- Registros activos (sin fecha de entrada)
- Ordenamiento: atrasados primero, luego activos
- Columnas: Territorio, Publicador, Fecha Salida, Dias, Estado

**Ruta archivados**: `/registros-archivados`
- Registros completados (con fecha de entrada)
- Historial completo de asignaciones

**Crear registro**:
- Seleccionar territorio (solo disponibles)
- Seleccionar publicador (solo activos)
- Fecha de salida
- Fecha prevista de devolucion
- Notas

**Marcar devolucion**:
- Fecha de entrada
- Territorio pasa a estado "archivo"

---

### 6. Reportes S-13

**Ruta**: `/s13`

**Funcionalidad**:
- Estadisticas de la congregacion:
  - Total territorios
  - Territorios libres
  - Territorios asignados
  - Total registros historicos
- Vista previa del reporte
- Generacion de PDF oficial

**Generar PDF**:
- Formato oficial S-13
- Tabla de territorios con fechas
- Estadisticas resumidas
- Logo y encabezados oficiales

---

### 7. Configuracion

**Ruta**: `/configuracion`

**Funcionalidad**:
- Configuracion especifica por congregacion
- Parametros editables:
  - **Dias limite activo**: Dias antes de marcar como atrasado (default: 60)
  - **Dias archivo**: Dias de descanso tras devolucion (default: 90)
- Guardado en base de datos (no archivo config)
- Afecta calculo de estados de territorios

---

### 8. Perfil

**Ruta**: `/perfil`

**Funcionalidad**:
- Ver informacion del usuario actual
- Cambiar nombre
- Cambiar contraseña (sin requerir actual)
- Ver rol y congregacion asignada

---


### 10. PPOC - Programa de Predicacion Organizada

**Ruta**: `/ppoc`

**Descripcion**: Modulo para gestionar turnos de predicacion publica organizada por congregacion.

**Funcionalidades principales**:

#### Calendario Mensual
- Vista mensual con todos los turnos
- Navegacion entre meses
- Indicador visual del dia actual
- Vista de turnos por dia

#### Plantillas de Turnos
**Ruta**: `/ppoc/turnos`

- Crear plantillas de turnos semanales recurrentes
- Definir dia de semana, hora inicio/fin
- Ubicacion y notas
- Activar/desactivar turnos

#### Generacion de Turnos
- Generar turnos automaticamente para un mes
- Basado en las plantillas semanales activas
- Estados: pendiente, confirmado, completado, cancelado

#### Sistema de Asignaciones
- Asignar publicadores a turnos generados
- Roles: capitan y voluntario
- Confirmacion de asistencia

**Tablas de base de datos**:
- `turnos` - Plantillas semanales
- `turnos_generados` - Turnos por fecha
- `asignaciones_ppoc` - Asignaciones de publicadores

**Rutas PPOC**:
| Ruta | Descripcion |
|------|-------------|
| `/ppoc` | Calendario mensual |
| `/ppoc/turnos` | Lista de plantillas |
| `/ppoc/turnos/create` | Crear plantilla |
| `/ppoc/turnos/{id}/edit` | Editar plantilla |
| `/ppoc/generar-mes` | Generar turnos del mes |

---

### 11. Congregaciones (Solo Superadmin)

**Ruta**: `/congregaciones`

**Funcionalidad**:
- Lista de todas las congregaciones
- Crear nueva congregacion
- Editar congregacion existente
- Ver estadisticas por congregacion
- Cambiar congregacion activa (selector en header)

---

## Sistema de Estados

### Estados de Territorios

| Estado | Condicion | Color | Significado |
|--------|-----------|-------|-------------|
| LIBRE | Sin asignar o cumplio dias_archivo | Verde | Disponible |
| ACTIVO | Asignado < dias_limite_activo | Azul | En trabajo |
| ATRASADO | Asignado > dias_limite_activo | Rojo | Requiere seguimiento |
| ARCHIVO | Devuelto < dias_archivo | Gris | En descanso |

### Transiciones

```
LIBRE --> (asignar) --> ACTIVO
ACTIVO --> (tiempo) --> ATRASADO
ACTIVO/ATRASADO --> (devolver) --> ARCHIVO
ARCHIVO --> (tiempo) --> LIBRE
```

### Configuracion por Congregacion

Cada congregacion puede definir:
- `dias_limite_activo`: Dias maximos antes de "atrasado"
- `dias_archivo`: Dias de descanso obligatorio

---

## Integracion WhatsApp

### Envio de Territorio

1. Ver detalle de territorio
2. Click en "Enviar WhatsApp"
3. Seleccionar publicador
4. Abre WhatsApp con mensaje predefinido
5. Incluye imagen del territorio

### Mensaje Automatico

```
Territorio #{numero}
Imagen: {url_imagen}
Fecha: {fecha_asignacion}
```

---

## Interfaz de Usuario

### Header

- Logo del sistema
- Navegacion principal
- Badge de congregacion activa
- Selector de congregacion (superadmin)
- Boton de perfil (circular azul)
- Boton de logout (circular rojo)
- Toggle tema claro/oscuro

### Navegacion

1. Dashboard
2. Territorios
3. Publicadores
4. Registros
5. S13
6. Configuracion
7. PPOC
8. Congregaciones (solo superadmin)

### Tema Oscuro

- Toggle en header
- Guardado en localStorage
- Colores adaptados para modo oscuro
- Transiciones suaves

### Responsive

- Adaptacion automatica a moviles
- Menu colapsable en pantallas pequenas
- Cards apiladas en vertical
- Tablas con scroll horizontal

---

## Roles y Permisos

### user
- Ver datos de su congregacion
- Sin acceso a edicion

### admin
- CRUD completo de su congregacion
- Acceso a configuracion
- Sin acceso a otras congregaciones

### superadmin
- Acceso a todas las congregaciones
- Gestion de congregaciones
- Cambio entre congregaciones
- Todas las funcionalidades

---

## Congregaciones Actuales

### Centro Santa Coloma
- **Territorios**: 214 con imagenes
- **Publicadores**: 20+ activos
- **Registros**: Historico completo
- **Estado**: Operativa

### Sabadell Este
- **Territorios**: 0 (nueva)
- **Publicadores**: 0
- **Registros**: 0
- **Estado**: Lista para usar

---

## Flujo de Trabajo Tipico

### Asignar Territorio

1. Dashboard -> Ver territorios libres
2. Seleccionar territorio disponible
3. Click en "Asignar"
4. Seleccionar publicador
5. Establecer fecha de salida
6. Opcional: Enviar por WhatsApp

### Devolver Territorio

1. Registros -> Ver activos
2. Buscar territorio asignado
3. Click en "Marcar Devolucion"
4. Seleccionar fecha de entrada
5. Territorio pasa a "archivo"

### Generar Reporte S-13

1. Menu -> S13
2. Ver estadisticas actuales
3. Click en "Generar PDF"
4. Descargar documento oficial

---

## URLs Principales

| Ruta | Descripcion |
|------|-------------|
| `/` | Dashboard |
| `/login` | Inicio de sesion |
| `/territorios` | Lista de territorios |
| `/territorios/create` | Crear territorio |
| `/territorios/{id}` | Ver territorio |
| `/publicadores` | Lista de publicadores |
| `/registros` | Registros activos |
| `/registros-archivados` | Registros completados |
| `/s13` | Reportes S-13 |
| `/configuracion` | Configuracion |
| `/perfil` | Perfil de usuario |
| `/ppoc` | Calendario PPOC |
| `/ppoc/turnos` | Plantillas de turnos |
| `/congregaciones` | Gestion congregaciones |

---

## Comandos Utiles

```bash
# Iniciar servidor
php artisan serve

# Limpiar cache
php artisan config:clear
php artisan cache:clear

# Ver rutas
php artisan route:list

# Ejecutar migraciones
php artisan migrate
```

---

*Documentacion actualizada: 28 Diciembre 2025*
