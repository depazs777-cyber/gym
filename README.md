# Gym SaaS - Sistema de Gestión para Gimnasios

Sistema Multi-tenant para administración de gimnasios, control de acceso y contabilidad (Norma Colombiana).

## Requisitos del Sistema
- PHP 8.0 o superior.
- MySQL 5.7 / MariaDB 10.x.
- Servidor Web Apache (con `mod_rewrite` habilitado).
- Extensión `pdo_mysql`.

## Instalación

1.  **Base de Datos:**
    - Crear una base de datos vacía (ej. `gym_saas`).
    - Ejecutar el script `install.php` desde el navegador o CLI para instalar el esquema y datos base.
    - O importar manualmente: `database/schema.sql` y `database/seeds/01_initial_seed.sql`.

2.  **Configuración:**
    - Editar `config/config.php`:
      ```php
      define('DB_HOST', 'localhost');
      define('DB_NAME', 'gym_saas');
      define('DB_USER', 'tu_usuario');
      define('DB_PASS', 'tu_password');
      ```

3.  **Despliegue:**
    - Apuntar el DocumentRoot del servidor web a la carpeta `/public` (Recomendado).
    - El sistema soporta subdirectorios (ej. `localhost/gym/`) gracias a la detección dinámica en `Router.php` y `config.php`.

## Credenciales por Defecto
- **Usuario:** `admin@demo.com`
- **Contraseña:** `password123`

## Estructura del Proyecto
- `/app`: Lógica del negocio (Modelos, Vistas, Controladores, Servicios, Middleware).
- `/public`: Archivos accesibles vía web (index.php, CSS, JS).
- `/config`: Configuración global.
- `/database`: Migraciones y Seeds.
