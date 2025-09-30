#!/bin/bash
################################################################################
# Script de Instalación Automática - Territorios en OVH
# Servidor: ssh.cluster100.hosting.ovh.net
# Usuario: trastos
# Dominio: trastosbvaa.org/Territorios
################################################################################

set -e  # Salir si hay algún error

echo "=========================================="
echo "🚀 INSTALACIÓN DE TERRITORIOS EN OVH"
echo "=========================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables
REPO_URL="https://github.com/Plazasesamo23/Territorios.git"
BRANCH="definitiva"
DOMAIN="trastosbvaa.org"
SUBDIRECTORY="Territorios"
DB_NAME="trastos_territorios"
DB_USER="trastos"
DB_PASS=""  # Se pedirá durante la instalación

echo -e "${YELLOW}📋 Verificando entorno...${NC}"

# 1. Detectar directorio actual
CURRENT_DIR=$(pwd)
echo "📍 Directorio actual: $CURRENT_DIR"

# 2. Verificar si tenemos git
if ! command -v git &> /dev/null; then
    echo -e "${RED}❌ Git no está instalado${NC}"
    echo "Por favor, contacta a tu proveedor de hosting para instalar Git"
    exit 1
fi
echo -e "${GREEN}✅ Git disponible${NC}"

# 3. Verificar si tenemos composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}⚠️  Composer no encontrado, descargando...${NC}"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    rm composer-setup.php
    COMPOSER_CMD="php composer.phar"
else
    COMPOSER_CMD="composer"
    echo -e "${GREEN}✅ Composer disponible${NC}"
fi

# 4. Verificar versión de PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "🐘 PHP Version: $PHP_VERSION"

if php -r "exit(version_compare(PHP_VERSION, '8.0.0', '<') ? 0 : 1);"; then
    echo -e "${RED}❌ Se requiere PHP 8.0 o superior${NC}"
    echo "Versión actual: $PHP_VERSION"
    exit 1
fi
echo -e "${GREEN}✅ Versión de PHP correcta${NC}"

echo ""
echo "=========================================="
echo "📥 CLONANDO REPOSITORIO"
echo "=========================================="

# 5. Verificar si ya existe el directorio
if [ -d "$SUBDIRECTORY" ]; then
    echo -e "${YELLOW}⚠️  El directorio $SUBDIRECTORY ya existe${NC}"
    read -p "¿Deseas eliminarlo y reinstalar? (s/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        rm -rf "$SUBDIRECTORY"
        echo -e "${GREEN}✅ Directorio eliminado${NC}"
    else
        echo -e "${RED}❌ Instalación cancelada${NC}"
        exit 1
    fi
fi

# 6. Clonar repositorio
echo "📦 Clonando desde GitHub..."
git clone -b "$BRANCH" "$REPO_URL" "$SUBDIRECTORY"
cd "$SUBDIRECTORY"

echo -e "${GREEN}✅ Repositorio clonado${NC}"

echo ""
echo "=========================================="
echo "📦 INSTALANDO DEPENDENCIAS"
echo "=========================================="

# 7. Instalar dependencias de Composer
echo "📚 Instalando dependencias PHP..."
$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction

echo -e "${GREEN}✅ Dependencias instaladas${NC}"

echo ""
echo "=========================================="
echo "⚙️  CONFIGURANDO APLICACIÓN"
echo "=========================================="

# 8. Crear archivo .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✅ Archivo .env creado${NC}"
fi

# 9. Generar APP_KEY
php artisan key:generate --force
echo -e "${GREEN}✅ APP_KEY generada${NC}"

# 10. Solicitar credenciales de base de datos
echo ""
echo -e "${YELLOW}🗄️  CONFIGURACIÓN DE BASE DE DATOS${NC}"
echo "Por favor, proporciona las credenciales de MySQL:"
echo ""

read -p "📌 Nombre de la base de datos [$DB_NAME]: " input_db_name
DB_NAME="${input_db_name:-$DB_NAME}"

read -p "👤 Usuario MySQL [$DB_USER]: " input_db_user
DB_USER="${input_db_user:-$DB_USER}"

read -sp "🔒 Contraseña MySQL: " DB_PASS
echo ""

read -p "🌐 Host MySQL [localhost]: " input_db_host
DB_HOST="${input_db_host:-localhost}"

# 11. Actualizar .env con configuración de BD
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

# 12. Actualizar APP_URL
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN/$SUBDIRECTORY|" .env
sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env

echo -e "${GREEN}✅ Archivo .env configurado${NC}"

echo ""
echo "=========================================="
echo "🗄️  IMPORTANDO BASE DE DATOS"
echo "=========================================="

# 13. Verificar si la base de datos existe
echo "🔍 Verificando base de datos..."
DB_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SHOW DATABASES LIKE '$DB_NAME';" | grep "$DB_NAME" > /dev/null; echo "$?")

if [ $DB_EXISTS -eq 0 ]; then
    echo -e "${YELLOW}⚠️  La base de datos $DB_NAME ya existe${NC}"
    read -p "¿Deseas sobrescribirla? (s/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE $DB_NAME;"
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        echo -e "${GREEN}✅ Base de datos recreada${NC}"
    fi
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo -e "${GREEN}✅ Base de datos creada${NC}"
fi

# 14. Importar datos
echo "📥 Importando datos desde database_export.sql..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database_export.sql
echo -e "${GREEN}✅ Datos importados correctamente${NC}"

echo ""
echo "=========================================="
echo "🔐 CONFIGURANDO PERMISOS"
echo "=========================================="

# 15. Configurar permisos
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs
chmod -R 777 public/formas-territorio 2>/dev/null || mkdir -p public/formas-territorio && chmod -R 777 public/formas-territorio
chmod -R 777 public/imagenes 2>/dev/null || mkdir -p public/imagenes && chmod -R 777 public/imagenes

echo -e "${GREEN}✅ Permisos configurados${NC}"

echo ""
echo "=========================================="
echo "🧹 OPTIMIZANDO APLICACIÓN"
echo "=========================================="

# 16. Limpiar y optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✅ Caché generada${NC}"

echo ""
echo "=========================================="
echo "🌐 CONFIGURACIÓN DE APACHE"
echo "=========================================="

# 17. Crear archivo .htaccess en el directorio raíz si no existe
cat > .htaccess <<'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
EOF

echo -e "${GREEN}✅ .htaccess creado${NC}"

# 18. Verificar .htaccess en public
if [ ! -f public/.htaccess ]; then
    cat > public/.htaccess <<'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF
    echo -e "${GREEN}✅ .htaccess de public creado${NC}"
fi

echo ""
echo "=========================================="
echo "✅ INSTALACIÓN COMPLETADA"
echo "=========================================="
echo ""
echo -e "${GREEN}🎉 ¡Territorios instalado exitosamente!${NC}"
echo ""
echo "📍 URL de acceso: https://$DOMAIN/$SUBDIRECTORY"
echo "📁 Directorio: $(pwd)"
echo ""
echo "📋 SIGUIENTE PASOS:"
echo ""
echo "1. Verifica que tu dominio apunte al directorio correcto"
echo "2. Asegúrate de que mod_rewrite esté habilitado en Apache"
echo "3. Accede a: https://$DOMAIN/$SUBDIRECTORY"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANTE:${NC}"
echo "- Cambia la contraseña SSH que compartiste"
echo "- Verifica los permisos de los directorios"
echo "- Revisa los logs en storage/logs/laravel.log si hay errores"
echo ""
echo "=========================================="
echo "📞 SOPORTE"
echo "=========================================="
echo ""
echo "Si encuentras algún error:"
echo "1. Revisa storage/logs/laravel.log"
echo "2. Verifica que PHP 8.0+ esté activo"
echo "3. Confirma que las extensiones PHP requeridas estén instaladas:"
echo "   - php-mbstring, php-xml, php-curl, php-zip, php-mysql"
echo ""
echo -e "${GREEN}✨ ¡Listo para usar!${NC}"
