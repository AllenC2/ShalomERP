# ShalomERP

Sistema de gestión interna para **Shalom**: clientes, contratos, pagos, comisiones y rutas de cobro en campo.

## Stack

- PHP 8.2+ · Laravel 12
- MySQL
- Vite · Bootstrap 5

## Qué incluye

- **Clientes y contratos** — alta, seguimiento, PDFs y estados (activo, suspendido, cancelado, finalizado)
- **Pagos** — registro, tickets, métodos de pago y documentos de respaldo
- **Comisiones** — cálculo, parcialidades y recibos consolidados
- **Empleados** — roles admin / cobrador
- **Rutas** — plantillas recurrentes y cobro en campo
- **Ajustes y auditoría** — datos de empresa, tolerancia de pagos, recordatorios WhatsApp y bitácora

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL

## Instalación local

```bash
git clone https://github.com/AllenC2/ShalomERP.git
cd ShalomERP

composer install
cp .env.example .env
php artisan key:generate

# Configura DB_* en .env, luego:
php artisan migrate --seed

npm install
npm run build
php artisan serve
```

Abre `http://localhost:8000`.

Para desarrollo (servidor + Vite + colas + logs):

```bash
composer run dev
```

## Roles

| Rol | Acceso |
|-----|--------|
| **Admin** | Gestión completa, ajustes, auditoría, empleados y comisiones |
| **Empleado / cobrador** | Clientes, contratos, pagos y rutas asignadas |

## Configuración

Copia `.env.example` y ajusta al menos `DB_*` y `APP_URL`.

Opcional:

| Variable | Uso |
|----------|-----|
| `WHATSAPP_ENABLED` | Activa recordatorios por WhatsApp |
| `WHATSAPP_API_URL` / `WHATSAPP_API_TOKEN` | Credenciales del proveedor |
| `APP_TIMEZONE` | Zona horaria |

## Deploy

Notas para cPanel/hosting: `CPANEL-DEPLOY-QUICKSTART.md` y `deployment/`.

## Licencia

Uso privado · AllenC2
