# 🚀 SOLUCIÓN PARA SPACESHIP HOSTING - SHALOM ERP

## 🎯 PROBLEMA EN SPACESHIP

Error 404 al acceder a `https://funerariasshalom.com/shalom-erp/public`

## ✅ SOLUCIÓN 1: CONFIGURAR DOMINIO PRINCIPAL

### PASO 1: Verificar configuración en Spaceship

1. **Panel de Spaceship**
2. **Verificar que `funerariasshalom.com` sea el dominio principal**
3. **Document Root debe apuntar a `/public_html`**

### PASO 2: Nuevo archivo .htaccess optimizado para Spaceship

**Ubicación:** `/public_html/.htaccess`

```apache
# CONFIGURACIÓN PARA SPACESHIP - SHALOM ERP
RewriteEngine On

# Forzar HTTPS (recomendado)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Redirigir al directorio público de Laravel
RewriteCond %{REQUEST_URI} !^/shalom-erp/public/
RewriteCond %{REQUEST_URI} !^/cgi-bin/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /shalom-erp/public/index.php [L,QSA]

# Acceso directo a shalom-erp redirige al public
RewriteRule ^shalom-erp/?$ /shalom-erp/public/ [L,R=301]
```

## ✅ SOLUCIÓN 2: CONFIGURAR URL REDIRECT EN SPACESHIP

### OPCIÓN A: Sin redirect (recomendado)

-   **NO usar** la función "URL redirect" de Spaceship
-   **Dejar que el dominio principal** maneje todo

### OPCIÓN B: Si necesitas redirect

1. **Redirect to:** `funerariasshalom.com` (sin subdirectorios)
2. **Redirect type:** 301 Permanent
3. **Aplicar para los otros dominios:**
    - `shalomfuneraria.com` → `funerariasshalom.com`
    - `shalomfunerarias.com` → `funerariasshalom.com`

## ✅ SOLUCIÓN 3: VERIFICAR ESTRUCTURA DE ARCHIVOS

### PASO 1: Confirmar ubicación de archivos

```
/public_html/
├── .htaccess (archivo que acabamos de crear)
├── index.html (página por defecto, se puede eliminar)
└── shalom-erp/
    ├── app/
    ├── public/
    │   └── index.php (archivo principal de Laravel)
    ├── .env
    └── ...
```

### PASO 2: Verificar permisos

-   `.htaccess` → 644
-   `shalom-erp/public/` → 755
-   `shalom-erp/storage/` → 755

## ✅ SOLUCIÓN 4: CONFIGURAR .ENV CORRECTAMENTE

**Archivo:** `/public_html/shalom-erp/.env`

```bash
APP_URL=https://funerariasshalom.com
APP_ENV=production
APP_DEBUG=false

# Resto de configuración...
```

## 🔍 VERIFICACIÓN PASO A PASO

### PASO 1: Probar acceso directo

```
https://funerariasshalom.com/shalom-erp/public/
```

✅ **Debe mostrar:** Laravel welcome o login

### PASO 2: Probar acceso con .htaccess

```
https://funerariasshalom.com/
```

✅ **Debe mostrar:** Laravel welcome o login

### PASO 3: Probar otros dominios

```
https://shalomfuneraria.com/
https://shalomfunerarias.com/
```

## 🆘 TROUBLESHOOTING SPACESHIP

### Si sigue dando 404:

1. **Revisar logs de error** en Spaceship
2. **Verificar que mod_rewrite esté habilitado**
3. **Contactar soporte de Spaceship** si es necesario

### Si aparece "Index of":

1. **Eliminar o renombrar** `index.html` en `/public_html`
2. **Verificar que .htaccess** esté en la ubicación correcta

### Si aparece error 500:

1. **Revisar sintaxis** del archivo .htaccess
2. **Verificar permisos** de archivos y directorios
3. **Revisar logs** de PHP en Spaceship

## 📞 CONFIGURACIÓN ESPECÍFICA SPACESHIP

### Dominios múltiples en Spaceship:

1. **Usar parked domains** en lugar de addon domains
2. **Configurar todos los dominios** para apuntar al mismo directorio
3. **Usar .htaccess** para manejar las redirecciones

---

**Estado:** 🟡 Configurando para Spaceship  
**Próximo paso:** Actualizar .htaccess y verificar configuración
