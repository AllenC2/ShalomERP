# Funcionalidad: Cambio de Método de Pago

## Descripción

Esta funcionalidad permite a los usuarios cambiar el método de pago de un recibo existente directamente desde la vista de detalle del pago, sin necesidad de navegar a otra página.

## Características Implementadas

### 1. Botón de Cambio de Método

-   **Ubicación**: Junto al método de pago actual en la vista de detalle del recibo
-   **Diseño**: Botón pequeño con ícono de lápiz para una experiencia discreta
-   **Visibilidad**: Solo visible en pantalla (se oculta al imprimir)

### 2. Modal de Selección

-   **Interfaz**: Modal moderno con opciones de radio buttons
-   **Opciones Disponibles**:
    -   Efectivo
    -   Transferencia Bancaria
    -   Tarjeta Crédito/Débito
    -   Cheque
    -   Otro
-   **Iconos**: Cada método tiene un ícono representativo
-   **Selección Actual**: Se muestra preseleccionado el método actual

### 3. Actualización en Tiempo Real

-   **AJAX**: Actualización sin recargar la página
-   **Validación**: Validación tanto en frontend como backend
-   **Feedback**: Alertas de éxito/error con animaciones
-   **Estado del Botón**: Indicador visual de carga durante el proceso

### 4. Seguridad y Validación

-   **CSRF Protection**: Token CSRF incluido en todas las peticiones
-   **Validación Backend**: Verificación de métodos válidos
-   **Manejo de Errores**: Respuestas estructuradas para diferentes tipos de error
-   **Autorización**: Integrado con el sistema de autenticación existente

## Archivos Modificados

### Backend

-   `app/Http/Controllers/PagoController.php`

    -   Nuevo método: `updateMetodoPago()`
    -   Validación de entrada
    -   Manejo de errores con try-catch

-   `routes/web.php`
    -   Nueva ruta: `PATCH pagos/{id}/metodo-pago`

### Frontend

-   `resources/views/pago/show.blade.php`
    -   Botón de cambio de método
    -   Modal interactivo
    -   JavaScript para AJAX
    -   CSS adicional para estilos

## Uso

### Para el Usuario

1. **Acceder**: Navegar a la vista de detalle de cualquier pago
2. **Cambiar**: Hacer clic en el botón de lápiz junto al método de pago
3. **Seleccionar**: Elegir el nuevo método en el modal
4. **Confirmar**: Hacer clic en "Guardar Cambios"
5. **Verificar**: El cambio se refleja inmediatamente en la interfaz

### Para el Desarrollador

```php
// Ruta para actualizar método de pago
Route::patch('pagos/{id}/metodo-pago', [PagoController::class, 'updateMetodoPago'])
     ->name('pagos.updateMetodoPago');

// Método en el controlador
public function updateMetodoPago(Request $request, $id)
{
    // Validación y actualización
}
```

## Consideraciones Técnicas

### Responsividad

-   El modal es completamente responsivo
-   Los botones se adaptan a diferentes tamaños de pantalla
-   Las alertas se posicionan correctamente en dispositivos móviles

### Accesibilidad

-   Etiquetas aria para lectores de pantalla
-   Navegación por teclado soportada
-   Contraste adecuado en todos los elementos

### Performance

-   Peticiones AJAX optimizadas
-   Mínima recarga de elementos DOM
-   CSS y JavaScript eficientes

## Extensiones Futuras

### Posibles Mejoras

1. **Historial de Cambios**: Registrar quién y cuándo cambió el método
2. **Validación Condicional**: Restringir ciertos métodos según el monto
3. **Notificaciones**: Enviar notificaciones por email al cambiar métodos
4. **Auditoria**: Logging detallado de cambios para auditoria

### Integración con Otros Módulos

-   **Comisiones**: Recalcular comisiones si dependen del método de pago
-   **Reportes**: Incluir cambios de método en reportes financieros
-   **Contabilidad**: Generar asientos contables automáticos

## Notas de Mantenimiento

-   Los métodos de pago están definidos como constantes en `App\Models\Pago::METODOS_PAGO`
-   Para agregar nuevos métodos, modificar la constante y agregar el ícono correspondiente
-   La funcionalidad está completamente integrada con el sistema de autenticación existente
