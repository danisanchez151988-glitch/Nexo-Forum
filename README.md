# Nexo Forum

Foro PHP/MySQL moderno para XAMPP, con estética AMOLED y una experiencia orientada a conexiones, no a listas interminables.

## Instalación local

1. Instala XAMPP y activa **Apache** y **MySQL**.
2. Copia esta carpeta en `C:/xampp/htdocs/Nexo-Forum`.
3. Abre `http://localhost/phpmyadmin` y crea/importa la base de datos desde `database/schema.sql`.
4. Revisa las credenciales en `config/database.php` y la URL en `config/config.php`.
5. Abre `http://localhost/Nexo-Forum/`.

## Cuenta inicial

Si importas el esquema incluido, se crea el usuario `admin`. Por seguridad, cambia la contraseña inmediatamente y no uses credenciales de ejemplo en producción.

## Estructura

- `index.php`: portada y feed de conversaciones.
- `login.php` / `register.php`: autenticación.
- `admin/`: reservado para el panel de administración.
- `config/`: conexión y utilidades.
- `database/`: esquema SQL.
- `assets/`: identidad visual y estilos.

Este repositorio es una base funcional y extensible. Antes de publicar, configura HTTPS, correo de verificación, límites de subida, logs y permisos del servidor.
