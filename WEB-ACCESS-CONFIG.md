# 🌐 CONFIGURACIÓN DE ACCESO WEB - SHALOM ERP

## 🎯 PROBLEMA RESUELTO

La aplicación está desplegada exitosamente en `/public_html/shalom-erp` pero los dominios no la muestran.

## ✅ SOLUCIÓN 1: REDIRECCIÓN AUTOMÁTICA

### PASO 1: Crear archivo .htaccess en /public_html

**Ubicación:** `/public_html/.htaccess`

**Contenido:**

```apache
# REDIRECCIÓN AUTOMÁTICA A SHALOM ERP
RewriteEngine On

# Redirigir todo el tráfico a la aplicación Laravel
RewriteCond %{REQUEST_URI} !^/shalom-erp/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /shalom-erp/public/$1 [L,QSA]

# Redirigir acceso directo al directorio shalom-erp
RewriteRule ^shalom-erp/?$ /shalom-erp/public/ [L,R=301]
```

### RESULTADO

Después de esta configuración:

-   ✅ `funerariasshalom.com` → Aplicación Laravel
-   ✅ `shalomfuneraria.com` → Aplicación Laravel
-   ✅ `shalomfunerarias.com` → Aplicación Laravel
-   ✅ `cualquierdominio.com/shalom-erp` → Aplicación Laravel

## ✅ SOLUCIÓN 2: SUBDOMAIN DEDICADO

### PASO 1: Crear subdomain en cPanel

1. **Ir a "Subdomains" en cPanel**
2. **Crear subdomain:** `erp`
3. **Document Root:** `/public_html/shalom-erp/public`

### RESULTADO

-   ✅ `erp.funerariasshalom.com` → Aplicación Laravel
-   ✅ `erp.shalomfuneraria.com` → Aplicación Laravel
-   ✅ `erp.shalomfunerarias.com` → Aplicación Laravel

## ✅ SOLUCIÓN 3: ADDON DOMAIN (Si tienes un dominio dedicado)

Si quieres usar un dominio completo para la aplicación:

1. **Ir a "Addon Domains" en cPanel**
2. **Nuevo dominio:** `erp-tuempresa.com`
3. **Document Root:** `/public_html/shalom-erp/public`

## 🎯 RECOMENDACIÓN

**USAR SOLUCIÓN 1** porque:

-   ✅ Funciona con todos tus dominios existentes
-   ✅ No requiere configuración adicional de DNS
-   ✅ Fácil de mantener y actualizar

## 📝 INSTRUCCIONES PASO A PASO

### PASO 1: Acceder a File Manager

1. **Ir a cPanel**
2. **Abrir File Manager**
3. **Navegar a `/public_html`**

### PASO 2: Crear/Editar .htaccess

1. **Si existe .htaccess:** editarlo
2. **Si no existe:** crear nuevo archivo `.htaccess`
3. **Agregar el contenido** de arriba

### PASO 3: Guardar y probar

1. **Guardar archivo**
2. **Probar en navegador:** `funerariasshalom.com`
3. **Debería mostrar:** Laravel Welcome o Login

## ⚠️ IMPORTANTE

-   **Hacer backup** del .htaccess actual si existe
-   **Verificar que el .env** tenga la URL correcta:
    ```
    APP_URL=https://funerariasshalom.com
    ```

## 🆘 SI NO FUNCIONA

1. **Verificar permisos** del archivo .htaccess (644)
2. **Revisar logs de error** en cPanel
3. **Probar acceso directo:** `tudominio.com/shalom-erp/public`

---

**Estado:** 🟡 Configurando acceso web  
**Próximo paso:** Crear .htaccess en /public_html
