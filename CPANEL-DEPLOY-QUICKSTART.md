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

---

## 📚 DOCUMENTACIÓN COMPLETA

Para más detalles, consulta:
- `deployment/CPANEL-GIT-SETUP.md` - Guía completa paso a paso
- `deployment/DEPLOYMENT-GUIDE.md` - Guía general de deployment
- `deployment/post-deploy.sh` - Script de configuración

---

**¿Listo? ¡Empieza por el paso 1! 🎯**
