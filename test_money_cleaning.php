<?php

require_once 'vendor/autoload.php';

// Simular la función de limpieza
function cleanMoneyField($value)
{
    if (empty($value)) {
        return null;
    }
    
    // Remover símbolo de dólar, comas y espacios
    $cleaned = str_replace(['$', ',', ' '], '', $value);
    
    // Si queda vacío después de limpiar, retornar null
    if (empty($cleaned)) {
        return null;
    }
    
    // Verificar que sea un número válido
    if (is_numeric($cleaned)) {
        return $cleaned;
    }
    
    return $value; // Retornar original si no se puede limpiar
}

// Casos de prueba
$testCases = [
    '$1,000.00',
    '$1000',
    '1000.50',
    '$2,500.75',
    '',
    null,
    '$',
    'abc',
    '$abc123',
    '$ 1,500.25'
];

echo "Pruebas de limpieza de campos de moneda:\n";
echo "=====================================\n";

foreach ($testCases as $test) {
    $result = cleanMoneyField($test);
    echo "Input: '" . ($test ?? 'null') . "' -> Output: '" . ($result ?? 'null') . "'\n";
}

echo "\nVerificación con is_numeric:\n";
echo "============================\n";

foreach ($testCases as $test) {
    $result = cleanMoneyField($test);
    $isValid = $result !== null && is_numeric($result);
    echo "Input: '" . ($test ?? 'null') . "' -> Valid: " . ($isValid ? 'YES' : 'NO') . "\n";
}
