# 🚨 SOLUCIÓN INMEDIATA - ERROR "DIRECTORY ALREADY CONTAINS FILES"

## ❌ PROBLEMA ENCONTRADO

```
Error: You cannot use the "/home/gulqqlkuts//public_html" directory because it already contains files.
```

## ✅ SOLUCIÓN RECOMENDADA

### USAR SUBDIRECTORIO PARA EVITAR CONFLICTOS

1. **En cPanel Git Version Control, usar estos datos:**

```
Repository URL: https://github.com/AllenC2/ShalomERP.git
Repository Path: /public_html/shalom-erp
Repository Name: shalom-erp
Branch: master
```

2. **Hacer clic en "Create"** - Ahora debería funcionar sin problemas.

## 🔧 CONFIGURACIÓN POST-INSTALACIÓN

### PASO 1: Acceso a la aplicación

Después de la instalación, la aplicación estará disponible en:

```
https://tudominio.com/shalom-erp
```

### PASO 2: Redirigir dominio principal (OPCIONAL)

Si quieres que la aplicación esté disponible directamente en `https://tudominio.com`, crear archivo `.htaccess` en `/public_html` con este contenido:

```apache
# Archivo: /public_html/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/shalom-erp/public/
RewriteRule ^(.*)$ /shalom-erp/public/$1 [L]
```

### PASO 3: Continuar con el deployment

Una vez que el repositorio se clone exitosamente, continuar con:

1. **Configurar base de datos MySQL**
2. **Crear y configurar archivo .env**
3. **Ejecutar script post-deploy**

## 🎯 VENTAJAS DEL SUBDIRECTORIO

-   ✅ **Sin conflictos** con archivos existentes
-   ✅ **Fácil mantenimiento** - aplicación aislada
-   ✅ **Actualizaciones seguras** via Git
-   ✅ **Backup independiente** de otros archivos

## 🚀 CONTINUAR DEPLOYMENT

Una vez solucionado el error, seguir la guía principal en:
`deployment/DEPLOYMENT-GUIDE.md`

---

**Estado:** 🟡 Solucionando error de directorio  
**Próximo paso:** Configurar subdirectorio en cPanel Git
