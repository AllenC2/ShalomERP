#!/bin/bash

# ==============================================================================
# SCRIPT DE VERIFICACIÓN PRE-DEPLOY
# ==============================================================================
# Ejecuta este script ANTES de hacer deploy para verificar que todo esté listo
# ==============================================================================

echo "🔍 Verificando requisitos para deploy..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Contadores
ERRORS=0
WARNINGS=0

# Función para mostrar mensajes
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
    ((WARNINGS++))
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
    ((ERRORS++))
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    log_error "No se encontró el archivo artisan. Ejecuta este script desde el directorio raíz del proyecto Laravel."
    exit 1
fi

echo "📁 Directorio: $(pwd)"
echo ""

# 1. Verificar archivos críticos
log_info "Verificando archivos críticos..."

# Verificar .env.example
if [ -f ".env.example" ]; then
    log_success ".env.example existe"
else
    log_error ".env.example no encontrado"
fi

# Verificar composer.json
if [ -f "composer.json" ]; then
    log_success "composer.json existe"
else
    log_error "composer.json no encontrado"
fi

# Verificar package.json
if [ -f "package.json" ]; then
    log_success "package.json existe"
else
    log_warning "package.json no encontrado (opcional)"
fi

# 2. Verificar rutas de testing
log_info "Verificando rutas de testing..."
if grep -q "test-" routes/web.php; then
    log_error "Encontradas rutas de testing en routes/web.php - deben ser eliminadas"
else
    log_success "No se encontraron rutas de testing"
fi

# 3. Verificar configuración de producción en .env.example
log_info "Verificando configuración de .env.example..."

if grep -q "APP_ENV=production" .env.example; then
    log_success "APP_ENV configurado para producción"
else
    log_error "APP_ENV debe ser 'production' en .env.example"
fi

if grep -q "APP_DEBUG=false" .env.example; then
    log_success "APP_DEBUG configurado correctamente (false)"
else
    log_error "APP_DEBUG debe ser 'false' en .env.example"
fi

if grep -q "DB_CONNECTION=mysql" .env.example; then
    log_success "Base de datos configurada para MySQL"
else
    log_error "DB_CONNECTION debe ser 'mysql' para cPanel"
fi

if grep -q "LOG_LEVEL=error" .env.example; then
    log_success "LOG_LEVEL configurado para producción"
else
    log_warning "Considera cambiar LOG_LEVEL a 'error' para producción"
fi

# 4. Verificar dependencias de Composer
log_info "Verificando dependencias de Composer..."
if command -v composer &> /dev/null; then
    log_success "Composer está disponible"
    composer validate --no-check-publish --quiet
    if [ $? -eq 0 ]; then
        log_success "composer.json es válido"
    else
        log_error "composer.json tiene errores"
    fi
else
    log_warning "Composer no encontrado (se puede instalar en el servidor)"
fi

# 5. Verificar migraciones
log_info "Verificando migraciones..."
MIGRATION_FILES=$(find database/migrations -name "*.php" | wc -l)
if [ $MIGRATION_FILES -gt 0 ]; then
    log_success "Encontradas $MIGRATION_FILES migraciones"
else
    log_error "No se encontraron archivos de migración"
fi

# 6. Verificar seeders
log_info "Verificando seeders..."
if [ -f "database/seeders/DatabaseSeeder.php" ]; then
    log_success "DatabaseSeeder.php existe"
else
    log_warning "DatabaseSeeder.php no encontrado"
fi

# 7. Verificar modelos principales
log_info "Verificando modelos principales..."
MODELS=("User" "Cliente" "Contrato" "Pago" "Empleado" "Paquete")
for model in "${MODELS[@]}"; do
    if [ -f "app/Models/$model.php" ]; then
        log_success "Modelo $model existe"
    else
        log_error "Modelo $model no encontrado"
    fi
done

# 8. Verificar controladores principales
log_info "Verificando controladores principales..."
CONTROLLERS=("HomeController" "ClienteController" "ContratoController" "PagoController" "EmpleadoController")
for controller in "${CONTROLLERS[@]}"; do
    if [ -f "app/Http/Controllers/$controller.php" ]; then
        log_success "Controlador $controller existe"
    else
        log_error "Controlador $controller no encontrado"
    fi
done

# 9. Verificar middleware personalizado
log_info "Verificando middleware personalizado..."
if [ -f "app/Http/Middleware/EmpleadoContratoAccess.php" ]; then
    log_success "Middleware EmpleadoContratoAccess existe"
else
    log_error "Middleware EmpleadoContratoAccess no encontrado"
fi

# 10. Verificar assets compilados
log_info "Verificando assets..."
if [ -d "public/build" ]; then
    log_success "Directorio public/build existe"
else
    log_warning "public/build no encontrado - ejecutar 'npm run build'"
fi

# 11. Verificar permisos requeridos
log_info "Verificando estructura de directorios..."
REQUIRED_DIRS=("storage/app" "storage/framework" "storage/logs" "bootstrap/cache")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        log_success "Directorio $dir existe"
    else
        log_error "Directorio $dir no encontrado"
    fi
done

# 12. Verificar configuración de gitignore
log_info "Verificando .gitignore..."
if grep -q ".env" .gitignore; then
    log_success ".env está en .gitignore"
else
    log_error ".env debe estar en .gitignore"
fi

if grep -q "vendor" .gitignore; then
    log_success "vendor está en .gitignore"
else
    log_error "vendor debe estar en .gitignore"
fi

echo ""
echo "📊 RESUMEN DE VERIFICACIÓN:"
echo "   ✅ Elementos correctos: $(($(echo "${GREEN}✅" | grep -o "✅" | wc -l)))"
echo "   ⚠️  Advertencias: $WARNINGS"
echo "   ❌ Errores: $ERRORS"
echo ""

if [ $ERRORS -eq 0 ]; then
    log_success "¡Proyecto listo para deploy!"
    echo ""
    echo "🚀 PRÓXIMOS PASOS:"
    echo "   1. Hacer push del código a tu repositorio GitHub"
    echo "   2. Configurar Git Version Control en cPanel"
    echo "   3. Crear archivo .env en el servidor"
    echo "   4. Ejecutar script post-deploy.sh"
    echo ""
    exit 0
else
    log_error "Se encontraron $ERRORS errores que deben ser corregidos antes del deploy."
    echo ""
    echo "🔧 ACCIONES REQUERIDAS:"
    echo "   1. Corregir los errores listados arriba"
    echo "   2. Ejecutar este script nuevamente"
    echo "   3. Proceder con el deploy una vez que no haya errores"
    echo ""
    exit 1
fi
