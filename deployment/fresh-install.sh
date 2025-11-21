#!/bin/bash

# ==============================================================================
# SCRIPT DE INSTALACIÓN LIMPIA PARA CPANEL
# ==============================================================================
# Usar SOLO en instalaciones nuevas o cuando NO hay datos importantes
# Este script hace migrate:fresh que ELIMINA todas las tablas
# ==============================================================================

echo "🚀 Iniciando instalación limpia de Shalom ERP..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Verificar directorio
if [ ! -f "artisan" ]; then
    log_error "No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto."
    log_error "Directorio actual: $(pwd)"
    echo ""
    log_info "Intenta navegar al directorio correcto:"
    log_info "  cd ~/public_html"
    log_info "  cd ~/public_html/ShalomERP"
    echo ""
    log_info "O busca el archivo artisan:"
    log_info "  find ~ -name artisan -type f 2>/dev/null | head -5"
    exit 1
fi

log_info "Directorio actual: $(pwd)"
log_success "Archivo artisan encontrado ✓"
echo ""

log_warning "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
log_warning "⚠️  ADVERTENCIA: Este script eliminará TODAS las tablas de la BD"
log_warning "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
log_warning "Este script ejecutará migrate:fresh que:"
echo "  • Eliminará todas las tablas existentes"
echo "  • Recreará todas las tablas desde cero"
echo "  • Ejecutará todos los seeders"
echo "  • Creará usuarios de prueba"
echo ""
log_warning "Solo úsalo si:"
echo "  ✓ Es una instalación nueva"
echo "  ✓ No hay datos importantes en la BD"
echo "  ✓ Quieres empezar desde cero"
echo ""

# Confirmar si es ejecución manual (no desde cPanel)
if [ -t 0 ]; then
    read -p "¿Estás seguro de continuar? (escribe 'SI' para confirmar): " -r
    echo
    if [[ ! $REPLY =~ ^SI$ ]]; then
        log_info "Instalación cancelada"
        exit 1
    fi
fi

log_info "Directorio actual: $(pwd)"

# 1. Verificar archivo .env
if [ ! -f ".env" ]; then
    log_warning "No existe archivo .env, copiando desde .env.example..."
    cp .env.example .env
    log_success "Archivo .env creado"
else
    log_success "Archivo .env existe"
fi

# 2. Instalar dependencias de Composer
log_info "Instalando dependencias de Composer..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
    log_success "Dependencias instaladas"
else
    log_warning "Composer no encontrado en PATH, intentando con /usr/local/bin/composer..."
    if [ -f "/usr/local/bin/composer" ]; then
        /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
        log_success "Dependencias instaladas"
    else
        log_error "Composer no encontrado. Instálalo o especifica la ruta correcta."
        exit 1
    fi
fi

# 3. Generar clave de aplicación
log_info "Generando clave de aplicación..."
if grep -q "APP_KEY=$" .env 2>/dev/null; then
    php artisan key:generate --force
    log_success "APP_KEY generada"
else
    log_info "APP_KEY ya existe, no se generará una nueva"
fi

# 4. Verificar conexión a BD
log_info "Verificando conexión a base de datos..."
php artisan db:show 2>/dev/null
if [ $? -ne 0 ]; then
    log_error "No se puede conectar a la base de datos"
    log_error "Verifica las credenciales en el archivo .env"
    exit 1
fi
log_success "Conexión a BD exitosa"

# 5. MIGRATE:FRESH - Eliminar y recrear todas las tablas
log_warning "Ejecutando migrate:fresh (eliminando todas las tablas)..."
php artisan migrate:fresh --force
if [ $? -eq 0 ]; then
    log_success "Tablas recreadas exitosamente"
else
    log_error "Error al ejecutar migraciones"
    exit 1
fi

# 6. Ejecutar TODOS los seeders
log_info "Ejecutando seeders..."
php artisan db:seed --force
if [ $? -eq 0 ]; then
    log_success "Seeders ejecutados"
else
    log_warning "Hubo un problema con los seeders, pero continuaremos..."
fi

# 7. Configurar permisos
log_info "Configurando permisos..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
log_success "Permisos configurados"

# 8. Crear enlaces simbólicos
log_info "Creando enlaces simbólicos de storage..."
php artisan storage:link --force
log_success "Enlaces simbólicos creados"

# 9. Optimizar cachés
log_info "Optimizando cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
log_success "Cachés optimizados"

# 10. Optimizar autoloader
log_info "Optimizando autoloader..."
if command -v composer &> /dev/null; then
    composer dump-autoload --optimize
elif [ -f "/usr/local/bin/composer" ]; then
    /usr/local/bin/composer dump-autoload --optimize
fi
log_success "Autoloader optimizado"

# 11. Mostrar información de usuarios creados
echo ""
log_success "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
log_success "✅ ¡Instalación limpia completada exitosamente!"
log_success "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 USUARIOS CREADOS POR LOS SEEDERS:"
echo ""
echo "👤 Administrador:"
echo "   Email:    admin@test.com"
echo "   Password: password"
echo ""
echo "👤 Empleado:"
echo "   Email:    empleado@test.com"
echo "   Password: password"
echo ""
log_warning "⚠️  IMPORTANTE: Cambia estas contraseñas inmediatamente en producción"
echo ""
echo "🌐 Accede a tu aplicación en: \$APP_URL (configurado en .env)"
echo ""
log_success "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
