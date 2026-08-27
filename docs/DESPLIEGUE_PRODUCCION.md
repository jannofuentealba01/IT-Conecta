# Despliegue de IT Conecta a producción

Esta guía comienza cuando ya existe un servidor Linux con PHP 8.2 o superior, MySQL, Composer, Node.js/npm, un dominio y HTTPS.

## 1. Preparar el servidor

- Clonar el repositorio privado desde GitHub.
- Configurar el directorio público del dominio en `IT_Conecta/public`.
- Permitir escritura al usuario del servidor web en `storage` y `bootstrap/cache`.
- Crear una base MySQL vacía y un usuario exclusivo, sin utilizar `root`.

## 2. Crear la configuración

Copiar `.env.production.example` como `.env` solamente dentro del servidor. Completar dominio, base de datos y correo sin subir ese archivo a Git.

```bash
cp .env.production.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
npm ci
npm run build
```

No volver a generar `APP_KEY` después de que la plataforma entre en uso.

## 3. Crear la base y el catálogo

```bash
php artisan migrate --force
php artisan db:seed --class=EcoHuntActivitySeeder --force
```

No se debe ejecutar el administrador de prueba en producción. La protección también está incorporada en `UserSeeder`.

## 4. Crear el administrador real

```bash
php artisan app:create-admin
```

El comando solicita nombre, correo y una contraseña de al menos 12 caracteres. La contraseña permanece oculta mientras se escribe.

## 5. Optimizar y habilitar

```bash
php artisan optimize
php artisan app:production-check
php artisan up
```

Comprobar `https://TU-DOMINIO.CL/up` y verificar que responda correctamente.

## 6. Prueba mínima antes de abrir el acceso

1. Iniciar sesión como administrador.
2. Registrar y aprobar un profesor.
3. Crear curso y sala.
4. Entrar como estudiante desde un teléfono.
5. Calcular una huella.
6. Abrir la cámara y escanear una EcoBúsqueda.
7. Completar una partida del Impostor con varios participantes.
8. Descargar el reporte PDF.
9. Solicitar una recuperación de contraseña y comprobar que llegue al correo real.

## 7. Actualizaciones posteriores

Realizar un respaldo de MySQL y desplegar fuera del horario de clases:

```bash
php artisan down --retry=60
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=EcoHuntActivitySeeder --force
php artisan optimize
php artisan up
```

Si un comando falla durante una actualización, revisar el error antes de continuar y ejecutar `php artisan up` para retirar el modo de mantenimiento cuando sea seguro.
