# Gestión de Territorios

Sistema web desarrollado en Laravel 11 para la gestión y seguimiento de territorios asignados a publicadores.

## 🚀 Características

- **Dashboard principal**: Vista general con estadísticas del sistema
- **Gestión de territorios**: CRUD completo con filtros por estado
- **Gestión de publicadores**: Registro y control de personas asignadas
- **Historial de registros**: Seguimiento de asignaciones con fechas
- **Vista S13**: Seguimiento temporal de territorios
- **Integración WhatsApp**: Envío automático de territorios a publicadores
- **Interfaz responsive**: Diseñada con TailwindCSS

## 🛠️ Tecnologías

- **Backend**: Laravel 11
- **Frontend**: TailwindCSS, Blade Templates
- **Base de datos**: MySQL
- **Servidor local**: XAMPP

## 📂 Estructura de la base de datos

### Tablas principales:

- **territorios**: número, imagen_url, estado, notas, ultima_salida
- **publicadores**: nombre, teléfono, notas, activo
- **registros**: territorio_id, publicador_id, fecha_salida, fecha_entrada, entrada_prevista, notas

## 🚀 Instalación local

### Requisitos previos:
- XAMPP con MySQL activo
- Composer
- Node.js y npm
- Base de datos `territorios` creada

### Pasos de instalación:

1. **Clonar el repositorio**:
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd territorios
   ```

2. **Instalar dependencias**:
   ```bash
   composer install
   npm install
   ```

3. **Configurar entorno**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar base de datos** en `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=territorios
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Ejecutar migraciones y seeders**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Compilar assets**:
   ```bash
   npm run dev
   ```

7. **Iniciar servidor**:
   ```bash
   php artisan serve
   ```

## ✅ Estado Actual de Instalación (Diciembre 2024)

### **Sistema Completamente Instalado y Funcional**:
- ✅ **Entorno configurado**: XAMPP + PHP + MySQL + Composer
- ✅ **Base de datos poblada**: 214 territorios, 20+ publicadores, 200+ registros
- ✅ **Datos reales importados**: Información completa desde Excel del usuario
- ✅ **Imágenes integradas**: 214 territorios con imágenes mapeadas
- ✅ **Sistema funcional**: Todas las funcionalidades operativas
- ✅ **Estilos aplicados**: CSS compilado y funcionando en XAMPP
- ✅ **URLs operativas**: 
  - `http://localhost/territorios/public/` (XAMPP)
  - `http://localhost:8000` (Artisan serve)

### **Últimas Tareas Completadas**:
- ✅ **Importación de datos Excel**: Creado `ExcelRegistrosSeeder` para datos reales
- ✅ **214 registros importados**: Datos completos de territorios activos
- ✅ **Publicadores creados**: Basados en datos reales del usuario
- ✅ **Estados calculados**: Sistema automático funcionando correctamente
- ✅ **Configuración optimizada**: Assets compilados para XAMPP
- ✅ **Problema de estilos resuelto**: CSS funcionando correctamente

## 🌐 URLs

- **Local**: http://localhost:8000
- **XAMPP**: https://localhost/territorios
- **Producción**: https://buyveo.com/scripts/territorios

## 📱 Funcionalidades principales

### Dashboard
- Estadísticas generales del sistema
- Alertas de territorios que requieren atención
- Accesos rápidos a funciones principales
- Lista de últimas asignaciones

### Territorios
- Lista con vista de tarjetas
- Filtros por estado (libre, activo, archivo, etc.)
- Estados calculados automáticamente basados en tiempo
- Botón directo para envío por WhatsApp
- Información del publicador actual

### Publicadores
- Gestión completa de datos personales
- Control de estado activo/inactivo
- Historial de territorios asignados

### Registros
- Seguimiento de todas las asignaciones
- Fechas de salida y entrada
- Posibilidad de marcar entrada
- Notas personalizadas

### S13 (Seguimiento)
- Vista de seguimiento temporal
- Cálculo automático de días transcurridos
- Alertas de territorios vencidos

## 🔧 Estados de territorios

- **Libre**: Sin asignación actual
- **Activo**: Asignado y en trabajo (menos de 60 días)
- **Pendiente**: Asignado hace 60-90 días
- **Atrasado**: Asignado hace más de 90 días
- **Archivo**: Devuelto y archivado

## 📋 Flujo de trabajo típico

1. **Crear publicadores** en el sistema
2. **Registrar territorios** con sus números e imágenes
3. **Asignar territorios** a publicadores mediante registros
4. **Enviar WhatsApp** automáticamente con la imagen del territorio
5. **Marcar entrada** cuando el territorio es devuelto
6. **Revisar S13** para seguimiento temporal

## 🚢 Despliegue en producción

### Ruta en servidor: `/var/www/html/buyveocom/scripts/territorios`

```bash
git pull origin master
composer install --optimize-autoloader --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🤝 Contribuciones

Para solicitar nuevas funciones o reportar problemas:
- Sitio web: [buyveo.com](https://buyveo.com)
- Email: contacto a través del sitio web

## 📄 Licencia

Proyecto desarrollado específicamente para la gestión de territorios de la organización.

---

**Desarrollado con ❤️ usando Laravel 11 y TailwindCSS**
