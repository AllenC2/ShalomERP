#!/bin/bash

# ==============================================================================
# SCRIPT DE CORRECCIÓN PARA PRODUCCIÓN
# ==============================================================================
# Ejecutar cuando hay errores 500 solo en producción
# ==============================================================================

echo "🔧 Iniciando corrección de producción..."

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
log_success() { echo -e "${GREEN}✅ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
log_error() { echo -e "${RED}❌ $1${NC}"; }

# Verificar directorio
if [ ! -f "artisan" ]; then
    log_error "No se encontró artisan. ¿Estás en el directorio correcto?"
    exit 1
fi

log_info "Directorio: $(pwd)"

# 1. LIMPIAR TODOS LOS CACHÉS
log_info "Limpiando todos los cachés..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan optimize:clear
log_success "Cachés limpiados"

# 2. RECOMPILAR CACHÉS
log_info "Recompilando cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
log_success "Cachés recompilados"

# 3. VERIFICAR Y CORREGIR PERMISOS
log_info "Corrigiendo permisos..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chgrp -R www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache
log_success "Permisos corregidos"

# 4. VERIFICAR ENLACES SIMBÓLICOS
log_info "Recreando enlaces simbólicos..."
php artisan storage:link
log_success "Enlaces simbólicos recreados"

# 5. REINSTALAR DEPENDENCIAS SI ES NECESARIO
if [ "$1" == "--full" ]; then
    log_info "Reinstalando dependencias (modo completo)..."
    composer install --no-dev --optimize-autoloader --no-interaction
    log_success "Dependencias reinstaladas"
fi

# 6. VERIFICAR ESTADO DE LA APLICACIÓN
log_info "Verificando estado de la aplicación..."
php artisan about --only=environment

# 7. VERIFICAR LOGS DE ERROR
log_info "Últimas líneas del log de errores:"
if [ -f "storage/logs/laravel.log" ]; then
    tail -20 storage/logs/laravel.log
else
    log_warning "No se encontró el archivo de logs"
fi

echo ""
log_success "Corrección completada!"
echo ""
echo "📋 SIGUIENTES PASOS:"
echo "   1. Probar la aplicación en el navegador"
echo "   2. Si persiste el error, revisar logs: tail -f storage/logs/laravel.log"
echo "   3. Verificar .env tenga APP_DEBUG=false y APP_KEY configurado"
echo ""
