# Sistema de Gestion de Territorios

> **Sistema web Laravel 11 multi-congregacion para digitalizacion completa de la gestion territorial con integracion WhatsApp, reportes S-13 oficiales y modulo PPOC**

[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![Status](https://img.shields.io/badge/Status-Produccion-green.svg)](https://territorios.trastos.net)

## URL de Produccion

**https://territorios.trastos.net**

---

## Objetivo Principal

**Reemplazar completamente** los metodos manuales tradicionales (Excel, AppleScript) con una **solucion web moderna, automatica y completamente funcional** para la gestion de territorios organizacionales, con soporte para **multiples congregaciones**.

---

## Caracteristicas Principales

### Dashboard Inteligente
- **Estadisticas en tiempo real** filtradas por congregacion activa
- **Alertas automaticas** de territorios que requieren atencion
- **Accesos rapidos** a funciones mas utilizadas
- **Actividad reciente** de asignaciones

### Sistema Multi-Congregacion
- **Aislamiento completo de datos** entre congregaciones
- **Login simplificado** por nombre de congregacion
- **Configuracion independiente** de parametros por congregacion
- **Selector de congregacion** para superadmin

### Gestion de Territorios
- **Vista diferenciada por rol**:
  - Admins: gestion completa con creacion/edicion
  - Usuarios normales: solo visualizacion y asignacion
- **Cards rediseñadas** con navegacion simplificada
- **Imagenes integradas** con mapeo automatico por numero
- **Filtros dinamicos** por estado con paginacion
- **Estados automaticos** basados en reglas configurables
- **Vista detallada** unificada con modo edicion
- **Galeria de fotos** para usuarios normales

### Gestion de Publicadores
- **Vista minimalista** tipo tabla con navegacion dual
- **Campos separados**: nombre/apellidos independientes
- **Gestion completa**: datos basicos + asignacion de territorios
- **Aislados por congregacion**

### Sistema de Registros (Asignaciones)
- **Vista separada**: registros activos vs archivados
- **Ordenamiento inteligente**: atrasados -> activos -> libres
- **Seguimiento completo** de cada asignacion
- **Filtrado automatico** por congregacion
- **Boton de acceso rapido** desde territorios para usuarios normales
- **Filtro por zonas** con botones estilo chips

### Integracion WhatsApp
- **Mensaje personalizado** configurado segun especificaciones
- **Modal automatico** tras crear asignacion
- **Compatible movil/desktop** con dos botones optimizados

### Reportes S-13 Oficiales
- **Generacion automatica** de PDFs oficiales con Dompdf
- **Vista previa** antes de generar
- **Formato oficial** con todas las asignaciones
- **Estadisticas por congregacion**

### Modulo PPOC (Predicacion Publica Organizada)
- **Calendario mensual** con vista de turnos por dia
- **Turnos ordenados por hora** (los mas temprano primero)
- **Plantillas semanales** de turnos recurrentes
- **Generacion automatica** de turnos para cada mes
- **Regeneracion limpia** - limpia el mes antes de regenerar
- **Eliminacion de turnos individuales** (ej: dias festivos)
- **Sistema de asignaciones** con roles (capitan/voluntario)
- **Estados de turno** (pendiente/confirmado/completado/cancelado)
- **Multi-congregacion** - cada congregacion gestiona sus turnos
- **Usuario PPOC dedicado** - acceso limitado solo al calendario

### Grupos de Predicacion
- **Interfaz drag & drop** para organizar publicadores en 6 grupos
- **Roles asignables**: Superintendente (S), Auxiliar (A), Precursor (P)
- **Nombramientos visibles**: AN (Anciano), SM (Siervo Ministerial) en exportacion
- **Orden personalizado** - se guarda el orden en que colocas los publicadores
- **Colores distintivos**: Morado (SUP/AN/SM), Naranja (AUX), Verde (PR)
- **Panel "Sin Grupo"** para publicadores no asignados
- **100% responsive** para movil, tablet y desktop
- **Exportacion PDF** con roles y nombramientos visibles

### Navegacion Responsive
- **Menu hamburguesa** en dispositivos moviles
- **Diseño adaptativo** para tablets y desktop
- **Selector de modulos** (Territorios / PPOC)
- **Menu contextual** segun modulo activo
- **Tema claro/oscuro** con persistencia

### Sistema de Configuracion
- **Parametros editables por congregacion**:
  - Dias limite activo (cuando pasa a "atrasado")
  - Dias en archivo (tiempo de descanso obligatorio)
- **Cada congregacion puede tener diferentes valores**

---

## Stack Tecnologico

### Backend
- **Laravel 11** - Framework PHP moderno
- **MySQL 8.0+** - Base de datos relacional
- **Eloquent ORM** - Relaciones y consultas optimizadas
- **Carbon** - Manejo avanzado de fechas
- **Dompdf** - Generacion de PDFs

### Frontend
- **CSS Personalizado** - Sistema unificado con tema claro/oscuro
- **Blade Templates** - Vistas server-side rendering
- **JavaScript Vanilla** - Funcionalidades interactivas
- **Responsive Design** - Adaptacion movil/tablet/desktop completa
- **Bootstrap 5** - Componentes UI (modales, grids)

### Infraestructura
- **OVH Shared Hosting** - Servidor de produccion
- **SSH** - Despliegue y mantenimiento remoto
- **Composer** - Gestion de dependencias PHP
- **Git** - Control de versiones

---

## Arquitectura Multi-Congregacion

### Modelo de Datos Principal

```
congregaciones
├── id, nombre, codigo (unique)
├── ciudad, password (hash)
├── dias_limite_activo (default: 60)
├── dias_archivo (default: 90)
├── activa (boolean)
└── timestamps

users
├── id, congregacion_id (FK nullable)
├── name, email, password
├── role (user|admin|superadmin|territorios|ppoc)
└── timestamps

territorios
├── id, congregacion_id (FK)
├── numero (unique por congregacion)
├── nombre, descripcion, imagen_url
├── coordenadas_lat/lng
├── estado, activo, notas
└── timestamps

publicadores
├── id, congregacion_id (FK)
├── nombre, apellidos
├── telefono, activo, notas
├── es_anciano, es_siervo_ministerial
├── es_precursor
└── timestamps

registros
├── id, territorio_id (FK)
├── publicador_id (FK)
├── fecha_salida, fecha_entrada
├── entrada_prevista, notas
└── timestamps
```

### Modelo PPOC

```
turnos (plantillas semanales)
├── id, congregacion_id
├── nombre, dia_semana (0-6)
├── numero_turno, hora_inicio, hora_fin
├── ubicacion, tipo, capacidad
├── activo, notas
└── timestamps

turnos_generados (instancias por fecha)
├── id, turno_id (FK)
├── congregacion_id, fecha
├── hora_inicio, hora_fin
├── capacidad, ubicacion, estado
└── timestamps

asignaciones_ppoc
├── id, turno_id, turno_generado_id
├── publicador_id, fecha
├── rol (capitan|voluntario)
├── estado, notas
└── timestamps
```

### Permisos por Rol

| Rol | Dashboard | Territorios | PPOC Calendario | PPOC Admin | Usuarios |
|-----|-----------|-------------|-----------------|------------|----------|
| superadmin | Si | Si | Si | Si | Si |
| admin | Si | Si | Si | Si | Si |
| user | Si | Si | Configurable | No | No |
| territorios | Redirect | Si (directo) | No | No | No |
| ppoc | Redirect | No | Si (directo) | No | No |

### Metodos de Usuario

| Metodo | Descripcion |
|--------|-------------|
| `canEditTerritorios()` | Solo admin/superadmin |
| `canAccessPPOC()` | Admin o user con permiso |
| `canManagePPOC()` | Solo admin (plantillas, aprobados) |
| `canGeneratePPOC()` | Admin o usuario PPOC |
| `canAssignPPOC()` | Admin o usuario PPOC |
| `isPpocUser()` | Es rol ppoc |
| `isTerritoriosUser()` | Es rol territorios |
| `isAdmin()` | Es admin o superadmin |

---

## Sistema de Estados Automatico

### Estados Calculados Dinamicamente

| Estado | Condicion | Descripcion |
|--------|-----------|-------------|
| **LIBRE** | Sin registros o cumplio dias_archivo | Disponible para asignacion |
| **ACTIVO** | Asignado hace menos de dias_limite_activo | En trabajo normal |
| **ATRASADO** | Asignado hace mas de dias_limite_activo | Requiere seguimiento |
| **ARCHIVO** | Devuelto hace menos de dias_archivo | Periodo de descanso |

---

## Rutas Principales

### Modulo Territorios
| Ruta | Descripcion |
|------|-------------|
| `/` | Dashboard principal |
| `/territorios` | Gestion de territorios |
| `/publicadores` | Gestion de publicadores |
| `/registros` | Registros activos (asignaciones) |
| `/registros/create` | Crear asignacion (filtro por zonas) |
| `/registros-archivados` | Registros archivados |
| `/s13` | Reportes S-13 |
| `/grupos-predicacion` | Grupos de predicacion |
| `/creador-territorios` | Creador visual (admin) |
| `/usuarios` | Gestion de usuarios (admin) |

### Modulo PPOC
| Ruta | Descripcion | Acceso |
|------|-------------|--------|
| `/ppoc` | Calendario de turnos | Admin, PPOC |
| `/ppoc/turnos` | Gestion de plantillas | Solo Admin |
| `/ppoc/turnos/create` | Crear plantilla | Solo Admin |
| `/ppoc/turnos/{id}/edit` | Editar plantilla | Solo Admin |
| `/ppoc/generar-mes` | Generar turnos del mes | Admin, PPOC |
| `/ppoc/asignaciones` | Asignar publicador | Admin, PPOC |
| `/ppoc/turno-generado/{id}` | Eliminar turno | Solo Admin |
| `/ppoc/aprobados` | Publicadores aprobados | Solo Admin |
| `/ppoc/disponibilidad` | Disponibilidades | Solo Admin |

### Administracion
| Ruta | Descripcion |
|------|-------------|
| `/configuracion` | Configuracion de congregacion |
| `/perfil` | Perfil de usuario |
| `/congregaciones` | Gestion de congregaciones (superadmin) |

---

## Instalacion

### Requisitos
- **PHP 8.2+** con extensiones: mbstring, openssl, PDO, tokenizer, XML
- **MySQL 8.0+** o MariaDB 10.3+
- **Composer 2.0+**

### Instalacion Rapida

```bash
# Clonar e instalar
git clone https://github.com/Plazasesamo23/Territorios.git
cd Territorios
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate --seed

# Iniciar servidor
php artisan serve
```

---

## Ultima Actualizacion

**5 Enero 2026**

### Cambios Recientes

#### Usuario PPOC (NUEVO)
- **Nuevo rol de usuario**: ppoc - acceso exclusivo al calendario PPOC
- **Redireccion automatica**: login directo a /ppoc
- **Permisos limitados**:
  - Puede: ver calendario, generar mes, asignar/desasignar publicadores
  - No puede: gestionar plantillas, ver aprobados, ver disponibilidades, eliminar turnos
- **Vista adaptada**: oculta enlaces y botones no permitidos
- **Selector de rol**: disponible al crear/editar usuarios

#### Filtro por Zonas en Asignaciones
- **Reemplazo de filtro tipo** por filtro de zonas
- **Botones estilo chips** para seleccionar zona
- **Interfaz consistente** con el resto de la aplicacion

#### Nombramientos en Grupos de Predicacion
- **AN (Anciano)** y **SM (Siervo Ministerial)** visibles en exportacion
- **Solo para no-lideres**: no se muestra si es superintendente o auxiliar
- **Estilo morado** distintivo con clase .nombramiento
- **Visible en PDF** al exportar grupos

#### Grupos de Predicacion
- **Interfaz drag & drop** para organizar publicadores en 6 grupos
- **Roles asignables**: Superintendente (S), Auxiliar (A), Precursor (P)
- **Orden personalizado** - se guarda el orden en que colocas los publicadores
- **Colores distintivos**: Morado (SUP), Naranja (AUX), Verde (PR)
- **Panel "Sin Grupo"** para publicadores no asignados
- **100% responsive** para movil, tablet y desktop

#### Sistema de Precursores
- **Campo es_precursor** en publicadores
- **Toggle switch** en lista de publicadores para marcar precursores
- **Estilo verde** distintivo con badge "PR" en todas las vistas
- **Visible en**: lista publicadores, registros, PPOC aprobados

---

## Directrices de Diseno

### Responsive Design
**IMPORTANTE**: Todas las vistas deben ser 100% responsive. Siempre incluir:
- Breakpoints: 1200px (tablet landscape), 768px (tablet), 480px (movil)
- Flexbox/Grid con wrap para adaptacion automatica
- Tamanos de fuente escalables (rem/em)
- Touch-friendly: botones minimo 44x44px en movil
- Desactivar efectos hover en dispositivos tactiles

### Paleta de Colores del Sistema
| Uso | Color | Hex |
|-----|-------|-----|
| Primary (Indigo) | Botones principales, acentos | #6366f1, #4f46e5 |
| Success (Verde) | Precursores, disponible | #22c55e, #16a34a |
| Warning (Naranja) | Campana, auxiliar, atrasado | #f59e0b, #d97706 |
| Info (Azul) | Negocios, activo | #3b82f6, #1d4ed8 |
| Purple (Morado) | Superintendente, AN, SM | #8b5cf6, #7c3aed |
| Danger (Rojo) | Errores, eliminar | #ef4444, #dc2626 |
| Gray | Textos secundarios, bordes | #6b7280, #e5e7eb |

### Variables CSS (Dark/Light Theme)
- --bg-card: #fff (light) | #1f2937 (dark)
- --text-primary: #1f2937 (light) | #f3f4f6 (dark)
- --text-muted: #6b7280 (light) | #9ca3af (dark)
- --border-color: #e5e7eb (light) | #4b5563 (dark)

### Componentes Reutilizables
- Gradientes: linear-gradient(135deg, color1 0%, color2 100%)
- Border-radius: 6px (pequeno), 10px (medio), 12-16px (grande/cards)
- Box-shadow: 0 4px 15px rgba(0,0,0,0.08) (suave)
- Transiciones: all 0.2s ease

---

**Desarrollado con Laravel 11 y PHP 8.2+**
