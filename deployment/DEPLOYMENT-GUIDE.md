# ==============================================================================

# GUÍA DE DEPLOYMENT EN CPANEL CON GIT VERSION CONTROL

# ==============================================================================

## 📋 REQUISITOS PREVIOS

1. **Hosting con cPanel que soporte:**

    - PHP 8.2 o superior
    - MySQL 5.7 o superior
    - Composer
    - Git Version Control
    - Node.js (opcional, para compilar assets)

2. **Repositorio GitHub:**
    - Código limpio sin rutas de testing
    - .env.example configurado para producción
    - Dependencias actualizadas

## 🚀 PROCESO DE DEPLOYMENT

### PASO 1: Preparar el código localmente

```bash
# Verificar que todo esté listo
chmod +x deployment/pre-deploy-check.sh
./deployment/pre-deploy-check.sh

# Si hay errores, corregirlos y volver a verificar
# Una vez que no haya errores, continuar
```

### PASO 2: Subir código a GitHub

```bash
git add .
git commit -m "Preparado para producción - deploy v1.0"
git push origin main
```

### PASO 3: Configurar cPanel

1. **Acceder a cPanel** de tu hosting
2. **Buscar "Git Version Control"** en las herramientas
3. **Hacer clic en "Create"** para nuevo repositorio

### PASO 4: Configurar Git Repository en cPanel

**Repository URL:** `https://github.com/tu-usuario/tu-repositorio.git`
**Repository Path:** `/public_html` (o el directorio donde quieras la app)
**Repository Name:** `shalom-erp`
**Branch:** `main`

### PASO 5: Clonar repositorio

1. Hacer clic en **"Create"**
2. cPanel descargará el código automáticamente
3. Verificar que los archivos estén en el directorio correcto

### PASO 6: Configurar Base de Datos

1. **Ir a "MySQL Databases"** en cPanel
2. **Crear nueva base de datos:** `tuusuario_shalom_erp`
3. **Crear usuario:** `tuusuario_shalom`
4. **Asignar contraseña segura**
5. **Agregar usuario a la base de datos** con todos los privilegios

### PASO 7: Configurar archivo .env

1. **Navegar al directorio del proyecto** en File Manager
2. **Copiar .env.example a .env**
3. **Editar .env con los valores correctos:**

```bash
APP_NAME="Shalom ERP"
APP_ENV=production
APP_KEY=  # Se generará automáticamente
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tuusuario_shalom_erp
DB_USERNAME=tuusuario_shalom
DB_PASSWORD=tu_password_de_bd

# Configurar otros valores según tu hosting
```

### PASO 8: Ejecutar post-deployment

1. **Acceder al Terminal** en cPanel (si está disponible)
2. **Navegar al directorio del proyecto:**
    ```bash
    cd public_html  # o el directorio donde instalaste
    ```
3. **Hacer el script ejecutable y ejecutarlo:**
    ```bash
    chmod +x deployment/post-deploy.sh
    ./deployment/post-deploy.sh
    ```

### PASO 9: Configurar permisos (si es necesario)

Si no tienes acceso al terminal, configurar manualmente:

-   `storage/` → 755
-   `bootstrap/cache/` → 755

### PASO 10: Verificar instalación

1. **Visitar tu dominio** en el navegador
2. **Probar login** con usuarios por defecto:
    - Admin: `admin@test.com` / `password`
    - Empleado: `empleado@test.com` / `password`
3. **Cambiar contraseñas** inmediatamente

## 🔄 ACTUALIZACIONES FUTURAS

Para actualizar la aplicación:

1. **Hacer cambios localmente**
2. **Commit y push a GitHub**
3. **En cPanel Git Version Control:**
    - Hacer clic en "Pull or Deploy"
    - Seleccionar el repositorio
    - Hacer clic en "Update"
4. **Ejecutar post-deploy.sh** nuevamente si hay cambios en BD

## ⚠️ CONSIDERACIONES IMPORTANTES

### Seguridad

-   **SIEMPRE** cambiar contraseñas por defecto
-   **NUNCA** dejar `APP_DEBUG=true` en producción
-   Configurar **SSL/HTTPS** en el dominio
-   Revisar permisos de archivos regularmente

### Performance

-   Configurar **caché** de opcodes de PHP si está disponible
-   Usar **CDN** para assets estáticos
-   Configurar **compresión GZIP**

### Backup

-   Configurar **backup automático** de la base de datos
-   Hacer **backup de archivos** antes de actualizaciones
-   Documentar **procedimientos de rollback**

### Monitoreo

-   Configurar **logs de errores**
-   Monitorear **uso de recursos**
-   Establecer **alertas** para errores críticos

## 🆘 TROUBLESHOOTING COMÚN

### Error 500

1. Verificar logs de errores en cPanel
2. Revisar permisos de storage/
3. Verificar configuración .env
4. Ejecutar `php artisan config:clear`

### Base de datos no conecta

1. Verificar credenciales en .env
2. Confirmar que la BD existe
3. Verificar que el usuario tenga permisos
4. Probar conexión desde phpMyAdmin

### Assets no cargan

1. Ejecutar `npm run build`
2. Verificar ruta en .env (APP_URL)
3. Ejecutar `php artisan storage:link`

### Git no actualiza

1. Verificar permisos del repositorio
2. Comprobar que el branch sea correcto
3. Revisar logs de Git en cPanel

## 📞 SOPORTE

Si encuentras problemas:

1. Revisar logs de error en cPanel
2. Consultar documentación de Laravel
3. Verificar requisitos del hosting
4. Contactar soporte del hosting si es necesario
