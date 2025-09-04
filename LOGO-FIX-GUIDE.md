# 🖼️ SOLUCIÓN: LOGO NO SE MUESTRA - SHALOM ERP

## 🚨 PROBLEMA

El archivo `shalom_logo.svg` no se muestra en el header de la aplicación.

## ✅ SOLUCIÓN 1: VERIFICAR ARCHIVO EN SERVIDOR

### PASO 1: Confirmar ubicación del archivo

Verificar que el archivo exista en el servidor:

```
/public_html/shalom-erp/public/shalom_logo.svg
```

### PASO 2: Verificar permisos

```bash
chmod 644 /public_html/shalom-erp/public/shalom_logo.svg
```

### PASO 3: Probar acceso directo

Acceder en navegador a:

```
https://funerariasshalom.com/shalom_logo.svg
```

## ✅ SOLUCIÓN 2: VERIFICAR CONFIGURACIÓN .ENV

### Archivo: /public_html/shalom-erp/.env

```bash
APP_URL=https://funerariasshalom.com
ASSET_URL=https://funerariasshalom.com
```

## ✅ SOLUCIÓN 3: LIMPIAR CACHÉ

```bash
cd /public_html/shalom-erp
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## ✅ SOLUCIÓN 4: EJECUTAR STORAGE:LINK

```bash
cd /public_html/shalom-erp
php artisan storage:link
```

## ✅ SOLUCIÓN 5: VERIFICAR .HTACCESS

Si el archivo existe pero no se muestra, verificar que no haya reglas que bloqueen SVG:

### Archivo: /public_html/shalom-erp/public/.htaccess

Agregar estas líneas si no existen:

```apache
# Permitir archivos SVG
<FilesMatch "\.(svg)$">
    Allow from all
</FilesMatch>

# Configurar tipo MIME para SVG
AddType image/svg+xml .svg
```

## ✅ SOLUCIÓN 6: RUTA ABSOLUTA TEMPORAL

Si persiste el problema, usar ruta absoluta temporalmente:

### En las vistas (layouts/app.blade.php):

```php
<!-- Cambiar de: -->
<img src="{{ asset('shalom_logo.svg') }}" alt="Shalom Logo" height="40">

<!-- A: -->
<img src="{{ url('/') }}/shalom_logo.svg" alt="Shalom Logo" height="40">
```

## 🔍 DIAGNÓSTICO COMPLETO

### VERIFICAR PASO A PASO:

1. **¿Existe el archivo?**

    ```
    ls -la /public_html/shalom-erp/public/shalom_*
    ```

2. **¿Se puede acceder directamente?**

    ```
    curl -I https://funerariasshalom.com/shalom_logo.svg
    ```

3. **¿Qué dice la función asset()?**
   En tu aplicación, verificar que:
    ```php
    {{ asset('shalom_logo.svg') }}
    ```
    Genere: `https://funerariasshalom.com/shalom_logo.svg`

## 🛠️ SCRIPT DE DIAGNÓSTICO

Crear archivo temporal para diagnóstico:

### Archivo: /public_html/shalom-erp/public/test-logo.php

```php
<?php
echo "Verificación de logo:\n";
echo "1. Archivo existe: " . (file_exists('shalom_logo.svg') ? 'SÍ' : 'NO') . "\n";
echo "2. Ruta completa: " . realpath('shalom_logo.svg') . "\n";
echo "3. Permisos: " . substr(sprintf('%o', fileperms('shalom_logo.svg')), -4) . "\n";
echo "4. Tamaño: " . filesize('shalom_logo.svg') . " bytes\n";
?>
```

Acceder a: `https://funerariasshalom.com/test-logo.php`

## 🚀 SOLUCIÓN RÁPIDA

### MÉTODO 1: Re-subir archivo

1. **Descargar** shalom_logo.svg desde local
2. **Subir vía File Manager** a `/public_html/shalom-erp/public/`
3. **Configurar permisos** 644

### MÉTODO 2: Usar base64 inline (temporal)

Si necesitas una solución inmediata:

```php
<img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('shalom_logo.svg'))) }}" alt="Shalom Logo" height="40">
```

## 🎯 PASOS RECOMENDADOS

1. ✅ **Verificar acceso directo** al archivo
2. ✅ **Limpiar caché** de Laravel
3. ✅ **Verificar permisos** del archivo
4. ✅ **Probar ruta absoluta** si es necesario

---

**Problema:** Logo SVG no se muestra  
**Causa más común:** Permisos o caché  
**Solución inmediata:** Acceso directo + limpiar caché
