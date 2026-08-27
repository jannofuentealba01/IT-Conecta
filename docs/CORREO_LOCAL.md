# Recuperación de contraseña en local

IT Conecta utiliza Mailpit para capturar los correos de desarrollo sin enviar mensajes a Internet.

## Uso diario

1. Haz doble clic en `start-local-mail.cmd` desde la carpeta del proyecto.
2. Se abrirá la bandeja `http://127.0.0.1:8025`.
3. Mantén Apache y MySQL de XAMPP encendidos y abre IT Conecta.
4. En el inicio de sesión, selecciona “¿Olvidaste tu contraseña?”.
5. Escribe el correo de un profesor registrado.
6. Abre el mensaje recibido en Mailpit y pulsa el enlace de recuperación.
7. Define la contraseña nueva e inicia sesión.

Mailpit escucha únicamente en `127.0.0.1`: sus correos no salen del computador y otros dispositivos de la red no pueden abrir esta bandeja.

## Producción

Mailpit es solo para desarrollo. En producción se deben reemplazar `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` y `MAIL_FROM_ADDRESS` por las credenciales del proveedor de correo real. Nunca se debe subir el archivo `.env` a Git.
