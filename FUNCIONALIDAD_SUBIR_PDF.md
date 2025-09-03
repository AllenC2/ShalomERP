# Funcionalidad de Subir PDF para Contratos

## Descripción

Se ha implementado una funcionalidad completa para subir y reemplazar documentos PDF en los contratos del sistema.

## Características

### Frontend (show.blade.php)

-   ✅ Botón "Subir Documento PDF" que activa el selector de archivos
-   ✅ Formulario oculto con validación de archivos PDF
-   ✅ Validación de tamaño máximo (10MB)
-   ✅ Confirmación antes de subir/reemplazar
-   ✅ Indicador de carga durante la subida
-   ✅ Mensajes de éxito y error
-   ✅ Recarga automática de la página tras éxito
-   ✅ Funciona tanto cuando hay documento como cuando no lo hay

### Backend (ContratoController.php)

-   ✅ Método `updateDocumento` que maneja la subida
-   ✅ Validación de archivos (solo PDF, máximo 10MB)
-   ✅ Eliminación del documento anterior si existe
-   ✅ Almacenamiento seguro en `storage/app/public/contratos/`
-   ✅ Nombres de archivo únicos: `contrato_{id}_{timestamp}.pdf`
-   ✅ Respuestas JSON apropiadas
-   ✅ Manejo de errores y validaciones

### Rutas

-   ✅ Ruta PATCH: `contratos/{contrato}/documento`
-   ✅ Nombre de ruta: `contratos.updateDocumento`

## Flujo de Uso

1. **Usuario hace clic en "Subir Documento PDF"**
2. **Se abre el selector de archivos (solo PDF)**
3. **Usuario selecciona un archivo PDF**
4. **Sistema valida:**
    - Que sea un archivo PDF
    - Que no exceda 10MB
5. **Se muestra confirmación al usuario**
6. **Si confirma, se muestra indicador de carga**
7. **El archivo se envía al servidor via AJAX**
8. **El servidor:**
    - Valida el archivo nuevamente
    - Elimina el documento anterior (si existe)
    - Guarda el nuevo documento
    - Actualiza la base de datos
9. **Se muestra mensaje de éxito**
10. **La página se recarga automáticamente para mostrar el nuevo documento**

## Validaciones

### Frontend

-   Tipo de archivo: Solo PDF
-   Tamaño máximo: 10MB
-   Confirmación del usuario

### Backend

-   Validación Laravel: `required|file|mimes:pdf|max:10240`
-   Manejo de errores de validación
-   Manejo de errores de almacenamiento

## Almacenamiento

-   Directorio: `storage/app/public/contratos/`
-   Nombres de archivo: `contrato_{id}_{timestamp}.pdf`
-   Enlace simbólico: `public/storage` → `storage/app/public`

## Estados

La funcionalidad maneja dos estados:

1. **Sin documento**: Botón dice "Subir Documento PDF"
2. **Con documento**: Botón dice "Reemplazar Documento"

## Seguridad

-   ✅ Token CSRF incluido
-   ✅ Validación de tipos de archivo
-   ✅ Validación de tamaño
-   ✅ Nombres de archivo únicos para evitar conflictos
-   ✅ Eliminación segura de archivos anteriores
