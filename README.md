# Sistema de Gestion de Territorios

> **Sistema web Laravel 11 multi-congregacion para digitalizacion completa de la gestion territorial con integracion WhatsApp y reportes S-13 oficiales**

[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![Status](https://img.shields.io/badge/Status-Funcional%20100%25-green.svg)](https://github.com/Plazasesamo23/Territorios)

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
- **Login simplificado** por nombre de congregacion (ej: "Centro Santa Coloma")
- **Configuracion independiente** de parametros por congregacion
- **Selector de congregacion** para superadmin (cambio entre congregaciones)

### Gestion de Territorios
- **Cards rediseñadas** con navegacion simplificada
- **Imagenes integradas** con mapeo automatico por numero
- **Filtros dinamicos** por estado con paginacion
- **Estados automaticos** basados en reglas configurables por congregacion
- **Vista detallada** unificada con modo edicion

### Gestion de Publicadores
- **Vista minimalista** tipo tabla con navegacion dual
- **Campos separados**: nombre/apellidos independientes
- **Gestion completa**: datos basicos + asignacion de territorios
- **Aislados por congregacion**

### Sistema de Registros
- **Vista separada**: registros activos vs archivados
- **Ordenamiento inteligente**: atrasados -> activos -> libres
- **Seguimiento completo** de cada asignacion
- **Filtrado automatico** por congregacion

### Integracion WhatsApp
- **Mensaje personalizado** configurado segun especificaciones
- **Modal automatico** tras crear asignacion
- **Compatible movil/desktop** con dos botones optimizados

### Reportes S-13 Oficiales
- **Generacion automatica** de PDFs oficiales con Dompdf
- **Vista previa** antes de generar
- **Formato oficial** con todas las asignaciones
- **Estadisticas por congregacion**

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
- **Responsive Design** - Adaptacion movil/desktop completa

### Infraestructura
- **XAMPP** - Servidor local de desarrollo
- **OVH Hosting** - Servidor de produccion
- **Composer** - Gestion de dependencias PHP
- **Git** - Control de versiones

---

## Arquitectura Multi-Congregacion

### Modelo de Datos

```
congregaciones
├── id
├── nombre
├── codigo (unique)
├── ciudad
├── password (hash)
├── dias_limite_activo (default: 60)
├── dias_archivo (default: 90)
├── activa (boolean)
└── timestamps

users
├── id
├── congregacion_id (FK nullable)
├── name (usado para login)
├── email
├── password
├── role (user|admin|superadmin)
└── timestamps

territorios
├── id
├── congregacion_id (FK)
├── numero (unique por congregacion)
├── nombre, descripcion, imagen_url
├── coordenadas_lat/lng
├── estado, activo, notas
└── timestamps

publicadores
├── id
├── congregacion_id (FK)
├── nombre, apellidos
├── telefono, activo, notas
└── timestamps

registros
├── id
├── territorio_id (FK)
├── publicador_id (FK)
├── fecha_salida, fecha_entrada
├── entrada_prevista, notas
└── timestamps
```

### Aislamiento de Datos

El sistema usa **Global Scopes** en Laravel para filtrar automaticamente todos los datos por congregacion:

```php
// Trait BelongsToCongregacion aplicado a Territorio y Publicador
// Filtra automaticamente por session('congregacion_activa_id')
```

### Usuarios del Sistema

| Usuario | Rol | Acceso |
|---------|-----|--------|
| Centro Santa Coloma | admin | Solo datos de Centro SC |
| Sabadell Este | admin | Solo datos de Sabadell Este |
| Administrador | superadmin | Todas las congregaciones |

---

## Sistema de Estados Automatico

### Estados Calculados Dinamicamente

Los estados se calculan automaticamente usando los parametros de cada congregacion:

| Estado | Condicion | Descripcion |
|--------|-----------|-------------|
| **LIBRE** | Sin registros o cumplio dias_archivo | Disponible para asignacion |
| **ACTIVO** | Asignado hace menos de dias_limite_activo | En trabajo normal |
| **ATRASADO** | Asignado hace mas de dias_limite_activo | Requiere seguimiento |
| **ARCHIVO** | Devuelto hace menos de dias_archivo | Periodo de descanso |

### Configuracion por Congregacion

Cada congregacion puede configurar:
- **dias_limite_activo**: Dias maximos antes de marcar como atrasado (default: 60)
- **dias_archivo**: Dias de descanso obligatorio tras devolucion (default: 90)

---

## Instalacion y Configuracion

### Requisitos Previos
- **PHP 8.2+** con extensiones: mbstring, openssl, PDO, tokenizer, XML
- **MySQL 8.0+** o MariaDB 10.3+
- **Composer 2.0+**
- **XAMPP** (recomendado para Windows)

### Instalacion Rapida

```bash
# 1. Clonar repositorio
git clone https://github.com/Plazasesamo23/Territorios.git
cd Territorios

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_DATABASE=territorios
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Crear base de datos y migrar
php artisan migrate --seed

# 6. Iniciar servidor
php artisan serve
```

### URLs de Acceso
- **Desarrollo**: `http://localhost:8000`
- **XAMPP**: `http://localhost/territorios/public/`

---

## Congregaciones Actuales

### Centro Santa Coloma
- **214 territorios** con imagenes
- **20+ publicadores** activos
- **Registros historicos** completos

### Sabadell Este
- **Nueva congregacion** (vacia)
- **Lista para agregar** territorios y publicadores
- **Configuracion independiente**

---

## Rutas Principales

| Ruta | Descripcion |
|------|-------------|
| `/` | Dashboard principal |
| `/territorios` | Gestion de territorios |
| `/publicadores` | Gestion de publicadores |
| `/registros` | Registros activos |
| `/registros-archivados` | Registros archivados |
| `/s13` | Reportes S-13 |
| `/configuracion` | Configuracion de congregacion |
| `/perfil` | Perfil de usuario |
| `/congregaciones` | Gestion de congregaciones (superadmin) |

---

## Comandos de Mantenimiento

### Durante Desarrollo
```bash
# Servidor de desarrollo
php artisan serve

# Limpiar cache
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Base de Datos
```bash
# Reset completo
php artisan migrate:fresh --seed

# Solo migraciones nuevas
php artisan migrate
```

---

## Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── TerritorioController.php
│   │   ├── PublicadorController.php
│   │   ├── RegistroController.php
│   │   ├── S13Controller.php
│   │   ├── CongregacionController.php
│   │   └── PerfilController.php
│   └── Middleware/
│       └── EnsureCongregacion.php
├── Models/
│   ├── Congregacion.php
│   ├── User.php
│   ├── Territorio.php
│   ├── Publicador.php
│   └── Registro.php
└── Traits/
    └── BelongsToCongregacion.php

resources/views/
├── layouts/app.blade.php
├── dashboard.blade.php
├── territorios/
├── publicadores/
├── registros/
├── s13/
├── configuracion.blade.php
├── perfil/
└── congregaciones/
```

---

## Documentacion Adicional

- **[docs/funcionalidad.md](docs/funcionalidad.md)** - Funcionalidades detalladas
- **[docs/debug.md](docs/debug.md)** - Historial de problemas y soluciones
- **[docs/ERRORES_SOLUCIONADOS.md](docs/ERRORES_SOLUCIONADOS.md)** - Errores resueltos

---

## Ultima Actualizacion

**Diciembre 2025** - Sistema multi-congregacion completamente funcional

### Cambios Recientes
- Sistema multi-congregacion con aislamiento de datos
- Login simplificado por nombre de congregacion
- Configuracion de parametros por congregacion
- Selector de congregacion para superadmin
- Perfil de usuario con cambio de contraseña
- Botones de perfil y logout rediseñados
- Correccion de estadisticas S-13 por congregacion
- Instalacion de Dompdf para PDFs

---

**Desarrollado con Laravel 11 y PHP 8.2+**
