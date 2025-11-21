# 🚀 GUÍA DE DESPLIEGUE CON GIT™ VERSION CONTROL EN CPANEL

## 📋 TABLA DE CONTENIDOS
1. [Requisitos Previos](#requisitos-previos)
2. [Paso 1: Preparar Repositorio](#paso-1-preparar-repositorio)
3. [Paso 2: Configurar cPanel](#paso-2-configurar-cpanel)
4. [Paso 3: Configuración Inicial](#paso-3-configuración-inicial)
5. [Paso 4: Actualizaciones Futuras](#paso-4-actualizaciones-futuras)
6. [Solución de Problemas](#solución-de-problemas)

---

## 📦 REQUISITOS PREVIOS

### En tu Hosting cPanel:
- ✅ PHP 8.2 o superior
- ✅ MySQL/MariaDB
- ✅ Composer instalado
- ✅ Git Version Control habilitado
- ✅ Acceso a Terminal SSH (recomendado)

### En tu Repositorio GitHub:
- ✅ Repositorio público o token de acceso configurado
- ✅ Código actualizado y testeado
- ✅ Archivo `.cpanel.yml` incluido (ya está en tu proyecto)

---

## 🔧 PASO 1: PREPARAR REPOSITORIO

### 1.1 Verificar estado del código

```bash
cd /Users/allencontreras/Documents/PARA\ FOLDER/02.\ Areas/SHALOM/ShalomERP
git status
```

### 1.2 Asegurar que todo esté actualizado

```bash
# Agregar cambios
git add .

# Commit
git commit -m "Preparado para deploy en cPanel"

# Push a GitHub
git push origin master
```

### 1.3 Verificar que el archivo .cpanel.yml esté en la raíz

El archivo `.cpanel.yml` ya está creado y configurará el despliegue automático.

---

## 🌐 PASO 2: CONFIGURAR CPANEL

### 2.1 Acceder a Git™ Version Control

1. **Inicia sesión en tu cPanel**
2. Busca **"Git™ Version Control"** en el buscador o en la sección "Files"
3. Haz clic para abrir la herramienta

### 2.2 Crear Repositorio

Haz clic en **"Create"** y completa:

```
┌─────────────────────────────────────────────────────────┐
│ Clone a Repository                                      │
├─────────────────────────────────────────────────────────┤
│ Repository URL:                                         │
│ https://github.com/AllenC2/ShalomERP.git              │
│                                                         │
│ Repository Path:                                        │
│ /home/tuusuario/public_html                            │
│                                                         │
│ Repository Name:                                        │
│ ShalomERP                                              │
└─────────────────────────────────────────────────────────┘
```

**⚠️ IMPORTANTE:**
- Si `public_html` contiene archivos, usa `/home/tuusuario/public_html/erp`
- Si es repositorio privado, necesitarás configurar un Deploy Key o Token

### 2.3 Configurar Deploy Key (si es privado)

Si tu repositorio es privado:

1. En cPanel Git, copia la **SSH Public Key**
2. Ve a GitHub → Settings → Deploy keys
3. Agrega la clave pública
4. Marca "Allow write access" si necesitas hacer push desde el servidor

### 2.4 Clonar el Repositorio

- Haz clic en **"Create"**
- cPanel clonará el repositorio automáticamente
- Verás el estado en la lista de repositorios

---

## ⚙️ PASO 3: CONFIGURACIÓN INICIAL

### 3.1 Acceder al Terminal SSH

Opción A - Desde cPanel:
1. Busca **"Terminal"** en cPanel
2. Abre el terminal

Opción B - Desde tu Mac:
```bash
ssh usuario@tudominio.com
```

### 3.2 Navegar al directorio del proyecto

```bash
cd ~/public_html  # o ~/public_html/erp si usaste subdirectorio
```

### 3.3 Configurar Base de Datos

#### 3.3.1 Crear Base de Datos en cPanel

1. Ve a **"MySQL® Databases"** en cPanel
2. Crea una nueva base de datos:
   - **Nombre:** `tuusuario_shalom`
3. Crea un nuevo usuario:
   - **Usuario:** `tuusuario_shalomuser`
   - **Contraseña:** Genera una segura y guárdala
4. Agrega el usuario a la base de datos con **ALL PRIVILEGES**

#### 3.3.2 Anotar las credenciales

```
DB_HOST=localhost
DB_DATABASE=tuusuario_shalom
DB_USERNAME=tuusuario_shalomuser
DB_PASSWORD=[tu contraseña generada]
```

### 3.4 Configurar archivo .env

```bash
# Copiar el ejemplo
cp .env.example .env

# Editar el archivo (usar nano o vi)
nano .env
```

Configurar las siguientes variables:

```env
APP_NAME="Shalom ERP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tuusuario_shalom
DB_USERNAME=tuusuario_shalomuser
DB_PASSWORD=tu_password_segura

MAIL_MAILER=smtp
MAIL_HOST=smtp.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@tudominio.com
MAIL_PASSWORD=tu_password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

Guardar: `Ctrl+O`, Enter, `Ctrl+X`

### 3.5 Ejecutar Script de Post-Deploy

```bash
# Dar permisos de ejecución
chmod +x deployment/post-deploy.sh

# Ejecutar el script
./deployment/post-deploy.sh
```

El script automáticamente:
- ✅ Instala dependencias de Composer
- ✅ Genera la clave de aplicación (APP_KEY)
- ✅ Ejecuta migraciones
- ✅ Ejecuta seeders
- ✅ Configura cachés
- ✅ Configura permisos
- ✅ Crea enlaces simbólicos

### 3.6 Configurar el Document Root (si es necesario)

Si instalaste en `/public_html`:

1. Ve a **"Domains"** en cPanel
2. Edita tu dominio principal
3. Cambia el **Document Root** a: `/public_html/public`
4. Guarda los cambios

Si instalaste en subdirectorio, puedes:
- Acceder vía `https://tudominio.com/erp/public`
- O crear un subdominio que apunte a `/public_html/erp/public`

---

## 🔄 PASO 4: ACTUALIZACIONES FUTURAS

### 4.1 Hacer cambios en tu código local

```bash
# Hacer cambios
git add .
git commit -m "Descripción de los cambios"
git push origin master
```

### 4.2 Actualizar en cPanel

#### Opción A: Desde cPanel Git™ Version Control

1. Ve a **Git™ Version Control** en cPanel
2. Localiza tu repositorio **"ShalomERP"**
3. Haz clic en **"Pull or Deploy"**
4. Confirma haciendo clic en **"Update from Remote"**

#### Opción B: Desde Terminal SSH

```bash
cd ~/public_html
git pull origin master
./deployment/post-deploy.sh
```

### 4.3 Deploy Automático con Webhooks (Avanzado)

Puedes configurar un webhook de GitHub para que actualice automáticamente:

1. Crea un script en tu servidor:

```bash
nano ~/deploy-webhook.php
```

```php
<?php
// Script simple de webhook
$secret = 'tu_secreto_seguro';
$repo_path = '/home/tuusuario/public_html';

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $signature)) {
    shell_exec("cd $repo_path && git pull origin master 2>&1");
    shell_exec("cd $repo_path && ./deployment/post-deploy.sh 2>&1");
    echo "Deploy exitoso";
} else {
    http_response_code(403);
    echo "Firma inválida";
}
```

2. En GitHub → Settings → Webhooks → Add webhook:
   - **Payload URL:** `https://tudominio.com/deploy-webhook.php`
   - **Content type:** `application/json`
   - **Secret:** `tu_secreto_seguro`
   - **Events:** Just the push event

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### ❌ Error: "Permission denied"

```bash
# Configurar permisos correctos
chmod -R 755 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache
```

### ❌ Error: "Class not found"

```bash
# Regenerar autoloader
composer dump-autoload --optimize
php artisan config:clear
php artisan cache:clear
```

### ❌ Error 500 en el navegador

```bash
# Ver logs de error
tail -f storage/logs/laravel.log

# Verificar configuración
php artisan about
```

### ❌ Base de datos no conecta

1. Verifica credenciales en `.env`
2. Prueba la conexión desde phpMyAdmin
3. Confirma que el usuario tiene permisos

```bash
# Limpiar cache de configuración
php artisan config:clear
```

### ❌ Assets no cargan (CSS/JS)

```bash
# Verificar URL en .env
php artisan config:cache

# Regenerar enlaces simbólicos
php artisan storage:link

# Si usas Vite/npm
npm run build
```

### ❌ Git no puede actualizar

```bash
# Verificar estado
git status

# Si hay conflictos, resolver
git stash
git pull origin master
git stash pop
```

---

## 📊 VERIFICACIÓN POST-DEPLOY

### Checklist de Verificación

- [ ] El sitio carga sin errores
- [ ] Puedes iniciar sesión con: `admin@test.com` / `password`
- [ ] Los estilos CSS se ven correctamente
- [ ] Las imágenes cargan
- [ ] Los formularios funcionan
- [ ] La base de datos tiene datos
- [ ] Los logs no muestran errores críticos
- [ ] SSL/HTTPS está activo

### Usuarios por Defecto

Después del seeder, tendrás:

```
Admin:
  Email: admin@test.com
  Password: password

Empleado:
  Email: empleado@test.com
  Password: password
```

**⚠️ IMPORTANTE: Cambia estas contraseñas inmediatamente en producción**

---

## 🔒 SEGURIDAD POST-DEPLOY

### Acciones Inmediatas:

1. **Cambiar contraseñas por defecto**
2. **Configurar SSL** (Let's Encrypt en cPanel)
3. **Configurar cortafuegos** si está disponible
4. **Habilitar backups automáticos** en cPanel
5. **Desactivar modo debug:** `APP_DEBUG=false`
6. **Revisar permisos de archivos**

### Archivo .htaccess de seguridad

Si necesitas protección adicional en `/public`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Proteger archivos sensibles
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## 📞 SOPORTE Y RECURSOS

- 📖 Documentación Laravel: https://laravel.com/docs
- 📚 cPanel Docs: https://docs.cpanel.net/
- 🐛 Logs del sistema: `storage/logs/laravel.log`
- 💬 Logs de cPanel: Terminal → `/home/usuario/logs/`

---

## ✅ RESUMEN EJECUTIVO

### Para el primer deploy:

```bash
# 1. En tu Mac
git push origin master

# 2. En cPanel
# - Git Version Control → Create
# - Clonar repositorio

# 3. En Terminal SSH
cd ~/public_html
cp .env.example .env
nano .env  # Configurar
./deployment/post-deploy.sh

# 4. Verificar en navegador
```

### Para actualizaciones:

```bash
# 1. En tu Mac
git push origin master

# 2. En cPanel
# - Git Version Control → Pull or Deploy

# 3. Si hay cambios en BD
cd ~/public_html
./deployment/post-deploy.sh
```

---

**¡Tu aplicación Shalom ERP está lista para producción! 🎉**
