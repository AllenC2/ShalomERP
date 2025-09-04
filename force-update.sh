#!/bin/bash

echo "🔄 FORZANDO ACTUALIZACIÓN DEL REPOSITORIO..."

# Navegar al directorio correcto
cd public_html/shalom-erp || cd /home/username/public_html/shalom-erp

echo "📍 Directorio actual: $(pwd)"

# Mostrar commit actual
echo "🔍 Commit actual:"
git log --oneline -1

# Hacer backup del .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Forzar actualización
echo "⬇️ Haciendo fetch..."
git fetch origin

echo "🔄 Reseteando a origin/master..."
git reset --hard origin/master

# Restaurar .env
if [ -f .env.backup.* ]; then
    echo "📋 Restaurando .env..."
    cp .env.backup.* .env 2>/dev/null || echo "⚠️ No se pudo restaurar .env automáticamente"
fi

# Verificar archivos críticos
echo "✅ Verificando archivos actualizados:"
echo "📁 vite.config.js existe: $([ -f vite.config.js ] && echo 'SÍ' || echo 'NO')"
echo "📁 manifest.json existe: $([ -f public/build/.vite/manifest.json ] && echo 'SÍ' || echo 'NO')"
echo "📁 CSS compilado existe: $([ -f public/build/assets/app-DGcEmovK.css ] && echo 'SÍ' || echo 'NO')"

# Limpiar cache
echo "🧹 Limpiando cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "✅ ACTUALIZACIÓN COMPLETADA"
echo "🌐 Prueba tu sitio web ahora"
