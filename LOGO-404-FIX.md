# 🔧 SOLUCIÓN ESPECÍFICA: LOGO 404 EN SPACESHIP

## 🚨 PROBLEMA CONFIRMADO

-   Archivo existe en: `/public_html/shalom-erp/public/shalom_logo.svg`
-   URL da 404: `https://funerariasshalom.com/shalom_logo.svg`
-   Document Root: `/public_html/shalom-erp/public`

## ✅ SOLUCIÓN 1: VERIFICAR CONFIGURACIÓN LARAVEL

### PASO 1: Crear archivo de prueba

Crear archivo temporal en `/public_html/shalom-erp/public/test.txt` con contenido "FUNCIONA"

### PASO 2: Probar acceso

Ir a: `https://funerariasshalom.com/test.txt`

-   ✅ Si funciona: Problema específico con SVG
-   ❌ Si no funciona: Problema de configuración general

## ✅ SOLUCIÓN 2: PROBLEMA CON ARCHIVOS SVG

### PASO 1: Verificar tipo MIME

Crear archivo `/public_html/shalom-erp/public/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Configurar tipos MIME
<IfModule mod_mime.c>
    AddType image/svg+xml .svg
    AddType image/svg+xml .svgz
</IfModule>

# Permitir acceso a archivos SVG
<FilesMatch "\.(svg|svgz)$">
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresDefault "access plus 1 month"
    </IfModule>
</FilesMatch>
```

## ✅ SOLUCIÓN 3: RUTA ABSOLUTA EN CÓDIGO

### Cambiar en todas las vistas de Laravel:

**ANTES:**

```php
{{ asset('shalom_logo.svg') }}
```

**DESPUÉS:**

```php
{{ url('/shalom_logo.svg') }}
```

### ARCHIVOS A MODIFICAR:

-   `resources/views/layouts/app.blade.php`
-   `resources/views/layouts/error.blade.php`
-   `resources/views/comisione/show.blade.php`
-   `resources/views/pago/show.blade.php`

## ✅ SOLUCIÓN 4: VERIFICAR CONFIGURACIÓN ASSET_URL

### En archivo .env:

```bash
APP_URL=https://funerariasshalom.com
ASSET_URL=https://funerariasshalom.com
```

### Limpiar caché después:

```bash
php artisan config:clear
php artisan cache:clear
```

## 🛠️ PASOS INMEDIATOS

### PASO 1: Crear archivo de prueba

```bash
echo "FUNCIONA" > /public_html/shalom-erp/public/test.txt
```

### PASO 2: Probar acceso

Ir a: `https://funerariasshalom.com/test.txt`

### PASO 3A: Si test.txt funciona

El problema es específico con SVG - aplicar SOLUCIÓN 2

### PASO 3B: Si test.txt NO funciona

Hay problema de configuración general - revisar Document Root

## 🎯 SOLUCIÓN TEMPORAL INMEDIATA

Mientras solucionamos el problema de fondo, usar esta solución temporal:

### En las vistas, cambiar:

```php
<img src="{{ asset('shalom_logo.svg') }}" alt="Shalom Logo" height="40">
```

### Por:

```php
<img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('shalom_logo.svg'))) }}" alt="Shalom Logo" height="40">
```

Esto embebará el SVG directamente en el HTML.

---

**Diagnóstico:** Archivo existe pero URL da 404  
**Causa probable:** Configuración .htaccess o tipo MIME  
**Solución inmediata:** Probar archivo test.txt
