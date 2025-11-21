# 🚀 GUÍA RÁPIDA: Deploy con Git™ en cPanel

## ⚡ PASOS RÁPIDOS

### 1️⃣ PUSH TU CÓDIGO A GITHUB

```bash
git add .
git commit -m "Preparado para deploy en cPanel"
git push origin master
```

### 2️⃣ EN CPANEL: CONFIGURAR GIT

1. Abre **cPanel** → Busca **"Git™ Version Control"**
2. Click en **"Create"**
3. Completa:
   ```
   Repository URL: https://github.com/AllenC2/ShalomERP.git
   Repository Path: /home/mishoras/public_html
   Repository Name: ShalomERP
   Branch: master
   ```
4. Click **"Create"** - cPanel clonará automáticamente

### 3️⃣ CONFIGURAR BASE DE DATOS

**En cPanel → MySQL® Databases:**

1. Crear BD: `mishoras_shalom`
2. Crear usuario: `mishoras_shalom_user`
3. Agregar usuario a BD con **ALL PRIVILEGES**

### 4️⃣ CONFIGURAR .ENV

**En cPanel → File Manager:**

1. Ir a `/home/mishoras/public_html`
2. Editar `.env`
3. Configurar:

```env
APP_NAME="Shalom ERP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mishoras_shalom
DB_USERNAME=mishoras_shalom_user
DB_PASSWORD=[tu_password]
```

### 5️⃣ EJECUTAR POST-DEPLOY

**En cPanel → Terminal:**

```bash
cd ~/public_html
chmod +x deployment/post-deploy.sh
./deployment/post-deploy.sh
```

**O si prefieres empezar desde cero (recomendado para primera instalación):**

```bash
cd ~/public_html

# Verificar que estás en el directorio correcto
ls -la artisan  # Debe mostrar el archivo artisan

# Si NO ves el archivo artisan, verifica la ruta:
pwd  # Debe mostrar /home/mishoras/public_html

# Si el archivo artisan no existe, el proyecto no se clonó correctamente
# Vuelve al paso 2 para configurar Git

# Una vez que confirmes que estás en el directorio correcto:
php artisan migrate:fresh --seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
chmod -R 755 storage bootstrap/cache
```

> ⚠️ **Nota:** `migrate:fresh` elimina TODAS las tablas y las recrea. Úsalo solo si no hay datos importantes.

### 6️⃣ VERIFICAR

Visita tu sitio: `https://tudominio.com`

Login con:
- Email: `admin@test.com`
- Password: `password`

**⚠️ Cambia la contraseña inmediatamente**

---

## 🔄 ACTUALIZACIONES FUTURAS

### Desde tu Mac:
```bash
git add .
git commit -m "Actualización"
git push origin master
```

### En cPanel:
1. **Git™ Version Control** → Encuentra tu repo
2. Click **"Pull or Deploy"**
3. Click **"Update from Remote"**

✅ El archivo `.cpanel.yml` ejecutará automáticamente:
- Instalación de dependencias
- Migraciones
- Caché de configuración
- Permisos

---

## 🆘 PROBLEMAS COMUNES

### "Could not open input file: artisan"

Este error significa que NO estás en el directorio correcto del proyecto.

```bash
# Verificar dónde estás
pwd

# Debe mostrar: /home/mishoras/public_html
# Si estás en otro lugar, navega al directorio correcto:
cd ~/public_html

# Verificar que el archivo artisan existe
ls -la artisan

# Si NO existe, el proyecto no se clonó correctamente
# Vuelve al paso 2 (Git Version Control en cPanel)
```

**Causas comunes:**
- ✗ El proyecto se clonó en un subdirectorio (ej: `public_html/ShalomERP`)
- ✗ La ruta en Git Version Control estaba incorrecta
- ✗ No se completó el clonado del repositorio

**Solución:**
```bash
# Si el proyecto está en un subdirectorio:
cd ~/public_html/ShalomERP  # O el nombre que tenga

# Luego ejecuta los comandos desde ahí
php artisan migrate:fresh --seed --force
```

### Error 500
```bash
cd ~/public_html
php artisan config:clear
php artisan cache:clear
tail -f storage/logs/laravel.log
```

### BD no conecta
- Verifica credenciales en `.env`
- Confirma permisos del usuario en cPanel

### Assets no cargan
```bash
php artisan storage:link
php artisan config:cache
```

### Necesitas reinstalar la BD
```bash
cd ~/public_html
php artisan migrate:fresh --seed --force
```

> ✅ Esto elimina todas las tablas, las recrea y ejecuta los seeders

### Ver estado de migraciones
```bash
php artisan migrate:status
```

---

## 📚 DOCUMENTACIÓN COMPLETA

Para más detalles, consulta:
- `deployment/CPANEL-GIT-SETUP.md` - Guía completa paso a paso
- `deployment/DEPLOYMENT-GUIDE.md` - Guía general de deployment
- `deployment/post-deploy.sh` - Script de configuración

---

**¿Listo? ¡Empieza por el paso 1! 🎯**
