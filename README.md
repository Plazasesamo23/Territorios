# 🗺️ Sistema de Gestión de Territorios

> **Sistema web Laravel 11 para digitalización completa de la gestión territorial con integración WhatsApp y reportes S-13 oficiales**

[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![Status](https://img.shields.io/badge/Status-Funcional%20100%25-green.svg)](https://github.com/Plazasesamo23/Territorios)

## 🎯 Objetivo Principal

**Reemplazar completamente** los métodos manuales tradicionales (Excel, AppleScript) con una **solución web moderna, automática y completamente funcional** para la gestión de territorios organizacionales.

## ✨ Características Principales

### 🏠 **Dashboard Inteligente**
- **Estadísticas en tiempo real** con datos reales
- **Alertas automáticas** de territorios que requieren atención (>120 días)
- **Accesos rápidos** a funciones más utilizadas
- **Actividad reciente** de asignaciones

### 🗺️ **Gestión de Territorios (214 territorios)**
- **Cards rediseñadas** con navegación simplificada (2 acciones principales)
- **214 imágenes integradas** con mapeo automático
- **Filtros dinámicos** por estado con paginación
- **Estados automáticos** basados en reglas de negocio
- **Vista detallada** unificada con modo edición

### 👥 **Gestión de Publicadores**
- **Vista minimalista** tipo tabla con navegación dual
- **Campos separados**: nombre/apellidos independientes
- **Gestión completa**: datos básicos + asignación de territorios
- **20+ publicadores reales** importados desde Excel

### 📋 **Sistema de Registros**
- **Vista separada**: registros activos vs archivados
- **Ordenamiento inteligente**: atrasados → activos → libres
- **214+ registros históricos** importados
- **Seguimiento completo** de cada asignación

### 📱 **Integración WhatsApp**
- **Mensaje personalizado** configurado según especificaciones
- **Modal automático** tras crear asignación
- **Compatible móvil/desktop** con dos botones optimizados
- **Sin dependencia de datos móviles**

### 📊 **Reportes S-13 Oficiales**
- **Generación automática** de PDFs oficiales
- **Vista previa** antes de generar
- **Formato oficial** con todas las asignaciones
- **Lógica corregida**: solo muestra fecha completado si el último registro está cerrado

## 🛠️ Stack Tecnológico

### **Backend**
- **Laravel 11** - Framework PHP moderno
- **MySQL 8.0+** - Base de datos relacional
- **Eloquent ORM** - Relaciones y consultas optimizadas
- **Carbon** - Manejo avanzado de fechas

### **Frontend**
- **CSS Personalizado** - Sistema unificado (migrado desde TailwindCSS)
- **Blade Templates** - Vistas server-side rendering
- **JavaScript Vanilla** - Funcionalidades interactivas
- **Responsive Design** - Adaptación móvil/desktop completa

### **Infraestructura**
- **XAMPP** - Servidor local de desarrollo
- **Composer** - Gestión de dependencias PHP
- **NPM/Vite** - Build system y assets
- **Git** - Control de versiones

## 🔄 Sistema de Estados Automático

### **Estados Calculados Dinámicamente**
Los estados se calculan automáticamente basándose en fechas y reglas de negocio, **no se almacenan en base de datos**.

| Estado | Condición | Descripción |
|--------|-----------|-------------|
| **🟢 LIBRE** | Sin registros activos o cumplió 90 días descanso | Disponible para asignación |
| **🔵 ACTIVO** | Asignado hace menos de 120 días | En trabajo normal |
| **🔴 ATRASADO** | Asignado hace más de 120 días | Requiere seguimiento urgente |
| **⚫ ARCHIVO** | Devuelto hace menos de 90 días | En período de descanso obligatorio |

### **Reglas de Negocio**
- **120 días máximo** antes de marcar como "atrasado"
- **90 días de descanso** obligatorio después de devolución
- **Configuración editable** desde interfaz web

## 📊 Estructura de Base de Datos

### **Tablas Principales**

```sql
-- Territorios con campos extendidos
territorios:
├── numero (unique)
├── nombre
├── descripcion
├── coordenadas_lat/lng
├── imagen_url
├── estado
├── activo (boolean)
├── notas
└── timestamps

-- Publicadores con apellidos separados
publicadores:
├── nombre
├── apellidos
├── telefono (unique)
├── activo (boolean)
├── notas
└── timestamps

-- Registros de asignación
registros:
├── territorio_id (FK)
├── publicador_id (FK)
├── fecha_salida
├── fecha_entrada (nullable)
├── entrada_prevista
├── notas
└── timestamps
```

### **Relaciones**
- **Territorio** `1:N` **Registro**
- **Publicador** `1:N` **Registro**
- **Registro** `N:1` **Territorio**, `N:1` **Publicador**

## 🚀 Instalación y Configuración

### **📋 Requisitos Previos**
- **PHP 8.2+** con extensiones: mbstring, openssl, PDO, tokenizer, XML, ctype, JSON
- **MySQL 8.0+** o MariaDB 10.3+
- **Composer 2.0+** para dependencias PHP
- **Node.js 18+** y **NPM** para assets
- **XAMPP** (recomendado para Windows)

### **⚡ Instalación Rápida**

```bash
# 1. Clonar repositorio
git clone https://github.com/Plazasesamo23/Territorios.git
cd Territorios

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_DATABASE=territorios
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Crear base de datos
mysql -u root -p -e "CREATE DATABASE territorios;"

# 6. Ejecutar migraciones y poblar con datos
php artisan migrate --seed

# 7. Compilar assets (desarrollo)
npm run dev
# O para producción:
npm run build

# 8. Iniciar servidor
php artisan serve
```

### **🌐 URLs de Acceso**
- **Laravel Serve**: `http://localhost:8000`
- **XAMPP**: `http://localhost/territorios/public/`

### **📁 Configuración de Imágenes**
Las imágenes de territorios deben ubicarse en `public/imagenes/` con el formato `{numero}.jpg`:
```
public/imagenes/
├── 1.jpg
├── 2.jpg
├── ...
└── 214.jpg
```

## ✅ Estado Actual del Sistema (Enero 2025)

### **🚀 Sistema 100% Funcional y Optimizado**

#### **✅ Funcionalidades Implementadas**
- 🏠 **Dashboard**: Estadísticas en tiempo real con datos reales
- 🗺️ **Territorios**: 214 territorios con imágenes y estados automáticos
- 👥 **Publicadores**: 20+ publicadores reales con gestión completa
- 📋 **Registros**: 214+ registros históricos con separación activos/archivados
- 📱 **WhatsApp**: Mensaje personalizado y modal optimizado
- 📊 **S-13**: Reportes oficiales con lógica corregida
- ⚙️ **Configuración**: Sistema editable desde interfaz web

#### **✅ Datos Reales Poblados**
- **214 registros** importados desde Excel del usuario
- **170+ territorios únicos** con numeración real
- **20+ publicadores** con nombres/apellidos separados
- **Estados calculados** automáticamente según reglas de negocio
- **214 imágenes** mapeadas automáticamente

#### **✅ Mejoras Técnicas Recientes**
- **Corrección S-13**: Fecha completado solo si último registro cerrado
- **Reglas de negocio**: 120 días atrasado, 90 días descanso
- **Sistema CSS**: Migrado a CSS personalizado unificado
- **Navegación**: Simplificada a 2 acciones principales por territorio
- **UX minimalista**: Tablas limpias y navegación intuitiva

### **🌐 URLs de Acceso**
- **Desarrollo**: `http://localhost:8000` (Laravel Serve)
- **Local**: `http://localhost/territorios/public/` (XAMPP)

## 📱 Flujo de Trabajo Típico

### **👤 Para el Administrador**
```mermaid
graph LR
    A[Dashboard] --> B[Ver territorios libres]
    B --> C[Asignar a publicador]
    C --> D[WhatsApp automático]
    D --> E[Seguimiento S-13]
    E --> F[Marcar devolución]
    F --> G[Territorio en archivo]
    G --> H[90 días después: Libre]
```

### **📱 Para el Publicador**
1. **Recibe WhatsApp** con mensaje personalizado
2. **Ve imagen del territorio** por separado
3. **Trabaja el territorio** hasta 120 días máximo
4. **Contacta para devolver** cuando termine
5. **Territorio va a archivo** por 90 días obligatorios

## 🛠️ Comandos de Mantenimiento

### **Durante Desarrollo**
```bash
# Servidor de desarrollo
php artisan serve --host=127.0.0.1 --port=8000

# Limpiar cache después de cambios
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Recompilar assets
npm run dev
```

### **Base de Datos**
```bash
# Reimportar datos reales del usuario
php artisan db:seed --class=ExcelRegistrosSeeder

# Reset completo con datos
php artisan migrate:fresh --seed

# Solo migraciones nuevas
php artisan migrate
```

### **Para Producción**
```bash
# Deploy optimizado
git pull origin main
composer install --optimize-autoloader --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📚 Documentación Completa

- **[📘 Funcionalidad Completa](docs/funcionalidad.md)** - Documentación exhaustiva de todas las características
- **[🛠️ Debug y Desarrollo](docs/debug.md)** - Historial de problemas y soluciones técnicas
- **[📋 Plan de Mejoras](docs/Plan_de_Mejoras_estructuradas.md)** - Roadmap de mejoras futuras

## 🚀 Características Destacadas

### **💡 Innovaciones Técnicas**
- **Estados calculados dinámicamente** (no almacenados en BD)
- **Reglas de negocio configurable** desde interfaz web
- **Sistema de imágenes inteligente** con fallback SVG
- **WhatsApp optimizado móvil** con dos botones separados

### **🎯 Beneficios de Digitalización**
- ❌ **Eliminado**: Excel manual + AppleScript
- ✅ **Implementado**: Sistema web 100% automático
- ⚡ **2 clics máximo** para cualquier acción
- 📱 **Acceso universal** desde cualquier dispositivo
- 🔍 **Trazabilidad completa** de cada movimiento

## 🤝 Contribuir al Proyecto

```bash
# Fork del repositorio
git clone https://github.com/tu-usuario/Territorios.git

# Crear rama para feature
git checkout -b feature/nueva-funcionalidad

# Hacer cambios y commit
git commit -m "feat: nueva funcionalidad increíble"

# Push y crear Pull Request
git push origin feature/nueva-funcionalidad
```

## 📄 Licencia

**MIT License** - Proyecto desarrollado específicamente para gestión territorial organizacional.

---

## 🏆 Créditos

**Desarrollado con 💙 usando:**
- 🔧 **Laravel 11** - Framework PHP robusto
- 🎨 **CSS Personalizado** - Sistema de diseño unificado
- 📱 **Responsive Design** - Adaptación universal
- 🔗 **GitHub** - Control de versiones y colaboración

**Estado del Proyecto:** ✅ **Completamente funcional y optimizado**

**Última actualización:** Enero 2025 - Sistema S-13 corregido y subido a GitHub
