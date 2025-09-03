#!/bin/bash

# ==============================================================================
# SCRIPT POST-DEPLOY PARA CPANEL
# ==============================================================================
# Este script debe ejecutarse DESPUÉS de que el código se haya desplegado
# desde GitHub al servidor cPanel
# ==============================================================================

echo "🚀 Iniciando proceso post-deploy para Shalom ERP..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    log_error "No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto Laravel."
    exit 1
fi

log_info "Directorio actual: $(pwd)"

# 1. Instalar/actualizar dependencias de Composer
log_info "Instalando dependencias de Composer..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
    log_success "Dependencias de Composer instaladas"
else
    log_warning "Composer no encontrado. Asegúrate de tenerlo instalado."
fi

# 2. Generar clave de aplicación si no existe
log_info "Verificando clave de aplicación..."
if grep -q "APP_KEY=$" .env 2>/dev/null || [ ! -f .env ]; then
    log_warning "Generando nueva clave de aplicación..."
    php artisan key:generate --force
    log_success "Clave de aplicación generada"
else
    log_success "Clave de aplicación ya existe"
fi

# 3. Ejecutar migraciones
log_info "Ejecutando migraciones de base de datos..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    log_success "Migraciones ejecutadas correctamente"
else
    log_error "Error al ejecutar migraciones"
    exit 1
fi

# 4. Ejecutar seeders (solo si es una instalación nueva)
log_info "Verificando si necesita seeders..."
if php artisan db:show 2>/dev/null | grep -q "Empty"; then
    log_info "Base de datos vacía, ejecutando seeders..."
    php artisan db:seed --force
    log_success "Seeders ejecutados"
else
    log_info "Base de datos ya tiene datos, omitiendo seeders"
fi

# 5. Limpiar y optimizar caché
log_info "Optimizando cachés..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
log_success "Cachés optimizados"

# 6. Optimizar Composer autoloader
log_info "Optimizando autoloader..."
if command -v composer &> /dev/null; then
    composer dump-autoload --optimize
    log_success "Autoloader optimizado"
fi

# 7. Configurar permisos de storage y bootstrap/cache
log_info "Configurando permisos de directorios..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
log_success "Permisos configurados"

# 8. Crear enlaces simbólicos de storage
log_info "Creando enlaces simbólicos de storage..."
php artisan storage:link
log_success "Enlaces simbólicos creados"

# 9. Instalar dependencias de Node.js y compilar assets (si están disponibles)
if [ -f "package.json" ]; then
    log_info "Instalando dependencias de Node.js..."
    if command -v npm &> /dev/null; then
        npm ci --production
        npm run build
        log_success "Assets compilados"
    else
        log_warning "Node.js/npm no encontrado. Los assets no se compilaron."
    fi
fi

# 10. Verificar estado final
log_info "Verificando estado final de la aplicación..."
php artisan about --only=environment
log_success "Deploy completado exitosamente!"

echo ""
echo "🎉 ¡Deployment completado!"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "   1. Verificar que el archivo .env esté configurado correctamente"
echo "   2. Probar la aplicación en el navegador"
echo "   3. Verificar que los usuarios por defecto funcionen"
echo "   4. Configurar tareas cron si es necesario"
echo ""
echo "🔗 URLs importantes:"
echo "   - Aplicación: \$APP_URL (configurar en .env)"
echo "   - Panel admin: \$APP_URL/login"
echo ""
