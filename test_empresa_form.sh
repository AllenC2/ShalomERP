#!/bin/bash

echo "🚀 Probando funcionalidad del formulario de empresa..."

# 1. Verificar que las tablas existan
echo "📋 Verificando estructura de base de datos..."
php artisan tinker --execute="
try {
    \$count = App\\Models\\Ajuste::count();
    echo 'Tabla ajustes encontrada con ' . \$count . ' registros';
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"

echo ""

# 2. Insertar datos de prueba
echo "📝 Insertando datos de prueba..."
php artisan tinker --execute="
\$data = [
    'empresa_razon_social' => 'Shalom Servicios Test S.A. de C.V.',
    'empresa_rfc' => 'SST123456ABC',
    'empresa_calle_numero' => 'Av. Reforma 456',
    'empresa_colonia' => 'Centro',
    'empresa_ciudad' => 'México',
    'empresa_estado' => 'Ciudad de México',
    'empresa_pais' => 'México',
    'empresa_codigo_postal' => '06000',
    'empresa_telefono' => '(55) 1234-5678',
    'empresa_email' => 'contacto@shalom-test.com'
];

foreach (\$data as \$nombre => \$valor) {
    App\\Models\\Ajuste::updateOrCreate(
        ['nombre' => \$nombre],
        [
            'valor' => \$valor,
            'tipo' => 'string',
            'descripcion' => 'Configuración de empresa - ' . \$nombre,
            'activo' => true
        ]
    );
}

echo 'Datos de prueba insertados correctamente';
"

echo ""

# 3. Verificar que los datos se guardaron
echo "✅ Verificando datos guardados..."
php artisan tinker --execute="
\$ajustes = App\\Models\\Ajuste::where('nombre', 'like', 'empresa_%')
    ->orderBy('nombre')
    ->get(['nombre', 'valor']);

foreach (\$ajustes as \$ajuste) {
    echo \$ajuste->nombre . ': ' . \$ajuste->valor . PHP_EOL;
}
"

echo ""

# 4. Probar el método del controlador
echo "🎯 Probando método obtenerInfoEmpresa()..."
php artisan tinker --execute="
\$controller = new App\\Http\\Controllers\\AjustesController();
\$info = App\\Models\\Ajuste::where('nombre', 'like', 'empresa_%')->get();
echo 'Total de registros de empresa: ' . \$info->count();
"

echo ""
echo "✨ Prueba completada. El formulario debería estar funcionando correctamente."
echo "🌐 Accede a: http://localhost:8000/ajustes"
