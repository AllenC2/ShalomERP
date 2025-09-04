# 🌱 GUÍA DE SEEDERS EN PRODUCCIÓN - SHALOM ERP

## ✅ EJECUTAR SEEDERS EN CPANEL

### MÉTODO 1: Terminal SSH/cPanel

```bash
cd /public_html/shalom-erp
php artisan db:seed
```

### MÉTODO 2: Script automatizado

```bash
cd /public_html/shalom-erp
./deployment/post-deploy.sh
```

## 📋 SEEDERS DISPONIBLES

### DATOS FUNDAMENTALES (OBLIGATORIOS)

-   **UserSeeder** - Usuarios admin/empleado por defecto
-   **AjustesSeeder** - Configuración empresa
-   **PaqueteSeeder** - Paquetes de servicios
-   **PorcentajeSeeder** - Porcentajes de comisiones

### DATOS DE EJEMPLO (OPCIONALES)

-   **ClienteSeeder** - Clientes de ejemplo
-   **EmpleadoSeeder** - Empleados de ejemplo
-   **WhatsAppRecordatorioSeeder** - Configuración WhatsApp

## 🚀 EJECUCIÓN PASO A PASO

### PASO 1: Seeders básicos (OBLIGATORIO)

```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=AjustesSeeder
php artisan db:seed --class=PaqueteSeeder
php artisan db:seed --class=PorcentajeSeeder
```

### PASO 2: Verificar usuarios creados

-   **Admin:** admin@test.com / password
-   **Empleado:** empleado@test.com / password

### PASO 3: Datos de ejemplo (OPCIONAL)

```bash
php artisan db:seed --class=ClienteSeeder
php artisan db:seed --class=EmpleadoSeeder
```

## ⚠️ SEGURIDAD EN PRODUCCIÓN

### CAMBIAR CONTRASEÑAS INMEDIATAMENTE

```bash
# Después de ejecutar seeders, cambiar contraseñas:
# 1. Iniciar sesión como admin
# 2. Ir a configuración de usuarios
# 3. Cambiar contraseñas por defecto
```

### ELIMINAR DATOS DE EJEMPLO

Si ejecutaste ClienteSeeder/EmpleadoSeeder:

```bash
# Eliminar datos de ejemplo después de testing
```

## 🔄 RE-EJECUTAR SEEDERS

### SOLO DATOS ESPECÍFICOS

```bash
# Re-ejecutar seeder específico (cuidado con duplicados)
php artisan db:seed --class=UserSeeder --force
```

### RESET COMPLETO (CUIDADO)

```bash
# SOLO EN DESARROLLO - BORRA TODOS LOS DATOS
php artisan migrate:fresh --seed
```

## 🛠️ TROUBLESHOOTING

### Error: "Class not found"

```bash
composer dump-autoload
php artisan db:seed
```

### Error: "Duplicate entry"

-   Los seeders ya se ejecutaron antes
-   Verificar datos existentes antes de re-ejecutar

### Error de permisos

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## 📊 VERIFICACIÓN POST-SEEDERS

### COMPROBAR DATOS INSERTADOS

1. **Acceder a phpMyAdmin** en cPanel
2. **Verificar tablas:**
    - `users` - Deben existir admin y empleado
    - `ajustes` - Configuración empresa
    - `paquetes` - Servicios disponibles
    - `porcentajes` - Comisiones configuradas

### PROBAR LOGIN

1. **Ir a:** https://funerariasshalom.com/login
2. **Probar con:** admin@test.com / password
3. **Verificar acceso** al panel completo

## 🎯 RECOMENDACIONES

### PARA PRODUCCIÓN:

1. ✅ **Ejecutar** UserSeeder, AjustesSeeder, PaqueteSeeder
2. ⚠️ **Evaluar** si necesitas ClienteSeeder (datos de ejemplo)
3. 🔒 **Cambiar** contraseñas inmediatamente
4. 🗑️ **Eliminar** datos de ejemplo después de testing

### PARA DESARROLLO LOCAL:

1. ✅ **Ejecutar** todos los seeders para testing completo
2. ✅ **Usar** datos de ejemplo para desarrollo

---

**Estado:** 🟢 Seeders listos para ejecutar  
**Próximo paso:** Ejecutar seeders fundamentales
