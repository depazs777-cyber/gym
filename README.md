# PROMPT MAESTRO - Gym SaaS

## Setup Instructions

1.  **Environment Requirements**:
    *   PHP 8.x
    *   MySQL (InnoDB)
    *   Web Server (Apache/Nginx) pointing to public root (or serving `index.php` as entry point).

2.  **Configuration**:
    *   Edit `config/config.php` to set base URL if needed.
    *   Set Environment Variables for Database:
        *   `DB_HOST`
        *   `DB_NAME`
        *   `DB_USER`
        *   `DB_PASS`

3.  **Database Initialization**:
    *   Import `schema.sql` into your MySQL database.
    *   Default Super Admin credentials:
        *   Email: `admin@promptmaestro.com`
        *   Password: `admin` (Change this immediately!)

## Structure

*   `index.php`: Entry point (Front Controller).
*   `config/`: Configuration files.
*   `controllers/`: Application logic.
*   `models/`: Database interaction.
*   `views/`: HTML templates.
*   `helpers/`: Utility classes (Router, etc.).

## Security

*   Ensure `storage/` is writable but not directly accessible via web if possible.
*   Direct access to PHP files in subdirectories is prevented via `defined('APP_NAME')` check.

## Testing in Sandbox

A `setup_db.php` script is provided which was used to initialize the DB. It supports MySQL by default.
