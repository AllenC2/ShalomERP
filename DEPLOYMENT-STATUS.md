# 🚀 DEPLOYMENT EN PROGRESO - SHALOM ERP

## ✅ COMPLETADO: CÓDIGO SUBIDO A GITHUB

El código ha sido preparado y subido exitosamente al repositorio GitHub:

-   **Repositorio:** ShalomERP (AllenC2)
-   **Rama:** master
-   **Commit:** 9a7637c - "PREPARADO PARA PRODUCCIÓN - Deploy v1.0"
-   **Estado:** ✅ Listo para deployment en cPanel

## 🎯 PRÓXIMOS PASOS PARA CPANEL

### PASO 2: CONFIGURAR GIT VERSION CONTROL EN CPANEL

1. **Acceder al panel de control cPanel** de tu hosting
2. **Buscar "Git Version Control"** en las herramientas disponibles
3. **Hacer clic en "Create Repository"**

### PASO 3: CONFIGURAR EL REPOSITORIO

Usar estos datos exactos:

```
Repository URL: https://github.com/AllenC2/ShalomERP.git
Repository Path: /public_html (o tu directorio deseado)
Repository Name: shalom-erp
Branch: master
```

### PASO 4: CLONAR EL REPOSITORIO

1. **Hacer clic en "Create"**
2. **Esperar a que cPanel descargue el código**
3. **Verificar que los archivos estén en el directorio correcto**

### PASO 5: CONFIGURAR BASE DE DATOS MYSQL

1. **Ir a "MySQL Databases" en cPanel**
2. **Crear nueva base de datos:**
    - Nombre: `tuusuario_shalom_erp`
3. **Crear usuario:**
    - Usuario: `tuusuario_shalom`
    - Contraseña: (una contraseña segura)
4. **Agregar usuario a la base de datos** con TODOS los privilegios

### PASO 6: CONFIGURAR ARCHIVO .env

1. **Ir a File Manager en cPanel**
2. **Navegar al directorio donde instalaste la aplicación**
3. **Copiar .env.example a .env**
4. **Editar .env con estos valores:**

```bash
# APLICACIÓN
APP_NAME="Shalom ERP"
APP_ENV=production
APP_KEY=base64:XXXX  # Se generará automáticamente
APP_DEBUG=false
APP_URL=https://tudominio.com  # TU DOMINIO REAL

# LOCALIZACIÓN
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

# BASE DE DATOS (usar tus datos reales)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tuusuario_shalom_erp  # Tu BD real
DB_USERNAME=tuusuario_shalom      # Tu usuario real
DB_PASSWORD=tu_password_seguro    # Tu contraseña real

# CORREO (configurar con datos de tu hosting)
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com      # Tu servidor SMTP
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_password_correo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="Shalom ERP"

# RESTO mantener como está en .env.example
```

### PASO 7: EJECUTAR POST-DEPLOYMENT

1. **Acceder al Terminal en cPanel** (si está disponible)
2. **Navegar al directorio:**
    ```bash
    cd public_html  # o tu directorio
    ```
3. **Ejecutar el script:**
    ```bash
    ./deployment/post-deploy.sh
    ```

### SI NO HAY TERMINAL DISPONIBLE:

Ejecutar manualmente estos comandos via SSH o solicitar al hosting:

```bash
# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders
php artisan db:seed --force

# Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimizar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear enlaces simbólicos
php artisan storage:link

# Configurar permisos
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## 🔒 VERIFICACIÓN FINAL

1. **Visitar tu dominio** en el navegador
2. **Probar acceso:**
    - Admin: `admin@test.com` / `password`
    - Empleado: `empleado@test.com` / `password`
3. **CAMBIAR CONTRASEÑAS** inmediatamente

## ⚠️ IMPORTANTE

-   **NUNCA** dejar las contraseñas por defecto
-   **VERIFICAR** que APP_DEBUG=false
-   **CONFIGURAR** SSL/HTTPS si no está activado
-   **HACER BACKUP** antes de cualquier cambio

## 🆘 PROBLEMAS COMUNES

**Error 500:** Verificar logs de error en cPanel, permisos de storage/, configuración .env

**Base de datos no conecta:** Verificar credenciales en .env, existencia de la BD

**Assets no cargan:** Verificar APP_URL en .env, ejecutar storage:link

## 📞 SIGUIENTE PASO

Una vez completado el deployment, contactar para verificación final y configuración de seguridad adicional.

---

**Estado actual:** 🟡 EN PROGRESO - Código listo, procediendo con cPanel
