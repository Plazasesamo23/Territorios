# 🚀 Guía de Instalación en Servidor OVH

## 📋 Información del Servidor

- **Host SSH:** ssh.cluster100.hosting.ovh.net
- **Usuario:** trastos
- **Dominio:** trastosbvaa.org
- **Subdirectorio:** /Territorios

---

## ⚠️ IMPORTANTE - SEGURIDAD

**CAMBIA TU CONTRASEÑA INMEDIATAMENTE** después de la instalación. La contraseña fue compartida públicamente en el chat y debe ser cambiada.

Para cambiar la contraseña SSH en OVH:
1. Accede al panel de control de OVH
2. Ve a "Alojamiento Web" → "FTP-SSH"
3. Haz clic en "Cambiar contraseña"

---

## 🎯 Método A: Instalación Automatizada (Recomendado)

### Paso 1: Conectar por SSH

```bash
ssh trastos@ssh.cluster100.hosting.ovh.net
```

Ingresa tu contraseña cuando se te solicite.

### Paso 2: Descargar el Script de Instalación

Una vez dentro del servidor:

```bash
# Descargar el script directamente desde GitHub
wget https://raw.githubusercontent.com/Plazasesamo23/Territorios/definitiva/install-ovh.sh

# O si no funciona wget, usar curl:
curl -O https://raw.githubusercontent.com/Plazasesamo23/Territorios/definitiva/install-ovh.sh
```

### Paso 3: Dar Permisos de Ejecución

```bash
chmod +x install-ovh.sh
```

### Paso 4: Ejecutar el Script

```bash
./install-ovh.sh
```

### Paso 5: Seguir las Instrucciones Interactivas

El script te preguntará:

1. **Nombre de la base de datos:** Por defecto `trastos_territorios`
2. **Usuario MySQL:** Por defecto `trastos`
3. **Contraseña MySQL:** La contraseña de tu base de datos (la que uses en cPanel)
4. **Host MySQL:** Por defecto `localhost` (presiona Enter)

El script automáticamente:
- ✅ Clonará el repositorio desde GitHub
- ✅ Instalará todas las dependencias
- ✅ Configurará el archivo `.env`
- ✅ Creará la base de datos
- ✅ Importará los 214 territorios
- ✅ Configurará permisos
- ✅ Optimizará la aplicación
- ✅ Creará los archivos `.htaccess` necesarios

---

## 🎯 Método B: Instalación Manual (Si el script falla)

### 1. Conectar por SSH

```bash
ssh trastos@ssh.cluster100.hosting.ovh.net
```

### 2. Clonar el Repositorio

```bash
git clone -b definitiva https://github.com/Plazasesamo23/Territorios.git Territorios
cd Territorios
```

### 3. Instalar Dependencias

```bash
# Si tienes composer instalado:
composer install --no-dev --optimize-autoloader

# Si NO tienes composer:
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar install --no-dev --optimize-autoloader
rm composer-setup.php
```

### 4. Configurar .env

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Editar .env con tus credenciales

```bash
nano .env
```

Cambia las siguientes líneas:

```env
APP_NAME="Territorios"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://trastosbvaa.org/Territorios

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=trastos_territorios
DB_USERNAME=trastos
DB_PASSWORD=TU_CONTRASEÑA_MYSQL_AQUI
```

Guarda con `Ctrl+O`, Enter, y sal con `Ctrl+X`.

### 6. Crear Base de Datos

```bash
# Conectar a MySQL
mysql -u trastos -p

# Dentro de MySQL:
CREATE DATABASE trastos_territorios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 7. Importar Datos

```bash
mysql -u trastos -p trastos_territorios < database_export.sql
```

### 8. Configurar Permisos

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs
mkdir -p public/formas-territorio
mkdir -p public/imagenes
chmod -R 777 public/formas-territorio
chmod -R 777 public/imagenes
```

### 9. Optimizar Aplicación

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10. Crear .htaccess

En el directorio raíz de Territorios:

```bash
nano .htaccess
```

Pega este contenido:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 🌐 Configuración del Dominio

### Opción 1: Subdirectorio (trastosbvaa.org/Territorios)

Si instalaste en `~/Territorios` o `~/www/Territorios`, el archivo `.htaccess` debería funcionar automáticamente.

Accede a: **https://trastosbvaa.org/Territorios**

### Opción 2: Subdominio (territorios.trastosbvaa.org)

Si prefieres usar un subdominio:

1. En el panel de OVH, crea un subdominio `territorios`
2. Apunta el subdominio al directorio `Territorios/public`
3. Accede a: **https://territorios.trastosbvaa.org**

---

## 🔍 Verificación Post-Instalación

### 1. Acceder a la aplicación

Abre tu navegador y ve a:
```
https://trastosbvaa.org/Territorios
```

Deberías ver el dashboard de territorios.

### 2. Verificar la base de datos

```bash
mysql -u trastos -p trastos_territorios -e "SELECT COUNT(*) FROM territorios;"
```

Debería mostrar: **214**

### 3. Revisar logs por errores

```bash
tail -f storage/logs/laravel.log
```

---

## 🐛 Solución de Problemas Comunes

### Error 500 - Internal Server Error

**Causa:** Permisos incorrectos o .env mal configurado

**Solución:**
```bash
cd ~/Territorios
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs
php artisan config:clear
php artisan cache:clear
```

### Página en blanco

**Causa:** PHP 8.0+ no está activo

**Solución:**
1. Accede al panel de OVH
2. Ve a "Hosting" → "PHP"
3. Selecciona PHP 8.1 o 8.2
4. Guarda los cambios

### Error de Base de Datos

**Causa:** Credenciales incorrectas en .env

**Solución:**
```bash
cd ~/Territorios
nano .env
# Verifica que DB_USERNAME, DB_PASSWORD y DB_DATABASE sean correctos
php artisan config:clear
```

### CSS/JS no cargan

**Causa:** Ruta base incorrecta

**Solución:**
```bash
nano .env
# Verifica que APP_URL sea: https://trastosbvaa.org/Territorios
php artisan config:clear
```

### "Class not found" errors

**Causa:** Composer no instaló todas las dependencias

**Solución:**
```bash
cd ~/Territorios
composer install --no-dev --optimize-autoloader
php artisan optimize
```

---

## 📊 Verificar Requisitos del Servidor

Ejecuta este comando para verificar que tienes todo lo necesario:

```bash
php -v  # Debe ser 8.0 o superior
php -m | grep -E "mbstring|xml|curl|zip|mysql"  # Todas deben aparecer
composer --version  # Debe estar instalado
git --version  # Debe estar instalado
mysql --version  # Debe estar instalado
```

---

## 📁 Estructura de Directorios en OVH

Típicamente en OVH:

```
~/
├── www/                    # Directorio público (document root)
│   └── Territorios/        # Aquí instalar la aplicación
└── logs/                   # Logs del servidor
```

O:

```
~/
├── Territorios/            # Aplicación Laravel
│   └── public/            # Este es el document root
└── www/                   # Enlace simbólico a Territorios/public
```

---

## 🔐 Configuración de Seguridad Post-Instalación

### 1. Cambiar Contraseña SSH
```bash
# En el panel de OVH o
passwd  # Si tienes acceso
```

### 2. Proteger archivos sensibles

Verifica que estos archivos NO sean accesibles públicamente:
- `.env`
- `database_export.sql`
- `storage/logs/`

### 3. Habilitar HTTPS

En el panel de OVH:
1. Ve a "Certificados SSL"
2. Activa "Let's Encrypt SSL" (gratis)
3. Espera 15 minutos
4. Verifica accediendo a `https://` en lugar de `http://`

---

## ✅ Checklist Final

- [ ] Aplicación accesible en https://trastosbvaa.org/Territorios
- [ ] Dashboard muestra estadísticas correctas
- [ ] Listado de territorios muestra 214 territorios
- [ ] Creador de territorios carga el mapa satelital
- [ ] Búsqueda con autocompletado funciona
- [ ] Se pueden seleccionar edificios en el mapa
- [ ] Contraseña SSH cambiada
- [ ] HTTPS habilitado
- [ ] Base de datos tiene 214 territorios

---

## 📞 Soporte

Si tienes problemas:

1. **Revisa los logs:**
   ```bash
   tail -100 ~/Territorios/storage/logs/laravel.log
   ```

2. **Verifica configuración PHP:**
   ```bash
   php -i | grep -E "memory_limit|max_execution_time|upload_max_filesize"
   ```

3. **Consulta documentación:**
   - `ESTADO_ACTUAL.md` - Resumen del proyecto
   - `INSTRUCCIONES_CREADOR_MAPA.md` - Manual de usuario
   - `TROUBLESHOOTING_LEAFLET.md` - Problemas con el mapa

---

## 🎉 ¡Instalación Completada!

Una vez que todo funcione:

1. Accede a: **https://trastosbvaa.org/Territorios**
2. Verifica que el dashboard cargue correctamente
3. Prueba el creador de territorios
4. Crea tu primer usuario administrador si es necesario

**El sistema está listo para usar con 214 territorios pre-cargados.** 🚀
