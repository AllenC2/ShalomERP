#!/bin/bash

# ==============================================================================
# SCRIPT DE BACKUP PARA PRODUCCIÓN
# ==============================================================================
# Ejecutar este script regularmente para crear backups de la aplicación
# ==============================================================================

echo "📦 Iniciando proceso de backup..."

# Configuración
BACKUP_DIR="backups"
DATE=$(date +%Y%m%d_%H%M%S)
APP_NAME="shalom-erp"
BACKUP_NAME="${APP_NAME}_backup_${DATE}"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# Crear directorio de backup si no existe
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    log_info "Directorio de backup creado: $BACKUP_DIR"
fi

# Crear directorio para este backup específico
FULL_BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"
mkdir -p "$FULL_BACKUP_PATH"

log_info "Creando backup en: $FULL_BACKUP_PATH"

# 1. Backup de la base de datos
log_info "Creando backup de base de datos..."
if [ -f ".env" ]; then
    # Extraer configuración de la base de datos del .env
    DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
    DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2)
    
    if [ ! -z "$DB_DATABASE" ] && [ ! -z "$DB_USERNAME" ]; then
        mysqldump -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$FULL_BACKUP_PATH/database.sql"
        if [ $? -eq 0 ]; then
            log_success "Backup de base de datos completado"
        else
            log_error "Error al crear backup de base de datos"
        fi
    else
        log_warning "No se pudieron obtener credenciales de BD del .env"
    fi
else
    log_warning "Archivo .env no encontrado, omitiendo backup de BD"
fi

# 2. Backup de archivos de la aplicación (excluyendo vendor y node_modules)
log_info "Creando backup de archivos de aplicación..."
tar -czf "$FULL_BACKUP_PATH/application.tar.gz" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.git' \
    --exclude="$BACKUP_DIR" \
    .

if [ $? -eq 0 ]; then
    log_success "Backup de archivos completado"
else
    log_error "Error al crear backup de archivos"
fi

# 3. Backup específico de storage/app (archivos subidos)
log_info "Creando backup de archivos subidos..."
if [ -d "storage/app" ]; then
    tar -czf "$FULL_BACKUP_PATH/storage.tar.gz" storage/app/
    log_success "Backup de storage completado"
else
    log_warning "Directorio storage/app no encontrado"
fi

# 4. Backup del archivo .env
log_info "Copiando archivo .env..."
if [ -f ".env" ]; then
    cp .env "$FULL_BACKUP_PATH/.env"
    log_success "Archivo .env copiado"
else
    log_warning "Archivo .env no encontrado"
fi

# 5. Crear archivo de información del backup
log_info "Creando archivo de información..."
cat > "$FULL_BACKUP_PATH/backup_info.txt" << EOF
# INFORMACIÓN DEL BACKUP
# =====================

Fecha: $(date)
Aplicación: $APP_NAME
Versión PHP: $(php -v | head -n 1)
Directorio: $(pwd)
Usuario: $(whoami)
Host: $(hostname)

# CONTENIDO DEL BACKUP
# ===================

- database.sql: Dump completo de la base de datos
- application.tar.gz: Código fuente de la aplicación (sin vendor/node_modules)
- storage.tar.gz: Archivos subidos por usuarios
- .env: Archivo de configuración
- backup_info.txt: Este archivo

# RESTAURACIÓN
# ============

Para restaurar este backup:
1. Extraer application.tar.gz en el directorio de la aplicación
2. Copiar .env al directorio raíz
3. Restaurar database.sql en MySQL
4. Extraer storage.tar.gz
5. Ejecutar composer install
6. Ejecutar php artisan key:generate (si es necesario)
7. Configurar permisos de storage/

EOF

# 6. Comprimir todo el backup
log_info "Comprimiendo backup completo..."
cd "$BACKUP_DIR"
tar -czf "${BACKUP_NAME}.tar.gz" "$BACKUP_NAME"
if [ $? -eq 0 ]; then
    rm -rf "$BACKUP_NAME"  # Eliminar directorio temporal
    log_success "Backup comprimido: $BACKUP_DIR/${BACKUP_NAME}.tar.gz"
else
    log_error "Error al comprimir backup"
fi
cd ..

# 7. Limpiar backups antiguos (mantener solo los últimos 5)
log_info "Limpiando backups antiguos..."
cd "$BACKUP_DIR"
ls -t ${APP_NAME}_backup_*.tar.gz | tail -n +6 | xargs -r rm
REMAINING=$(ls -1 ${APP_NAME}_backup_*.tar.gz 2>/dev/null | wc -l)
log_info "Backups mantenidos: $REMAINING"
cd ..

# 8. Mostrar resumen
echo ""
log_success "¡Backup completado exitosamente!"
echo ""
echo "📊 RESUMEN:"
echo "   📁 Ubicación: $BACKUP_DIR/${BACKUP_NAME}.tar.gz"
echo "   📅 Fecha: $(date)"
echo "   💾 Tamaño: $(du -h "$BACKUP_DIR/${BACKUP_NAME}.tar.gz" | cut -f1)"
echo ""
echo "🔧 Para restaurar:"
echo "   tar -xzf $BACKUP_DIR/${BACKUP_NAME}.tar.gz"
echo "   # Seguir instrucciones en backup_info.txt"
echo ""
