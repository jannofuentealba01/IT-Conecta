# IT Conecta

Aplicación web escolar de gamificación ambiental. Permite calcular una huella de carbono educativa, realizar acciones mediante misiones QR, acumular puntos diferenciados y conservar resultados por sala.

## Primera versión funcional

- Acceso de profesores mediante correo y contraseña.
- Cursos y salas temporales administradas por su profesor.
- Ingreso del estudiante mediante código y nombre, sin cuenta personal.
- Recuperación del participante dentro de la misma sala.
- Calculadora de huella inicial con historial.
- Catálogo reutilizable de actividades y códigos QR.
- Una realización de cada actividad por estudiante y día.
- Puntos ambientales separados de los puntos de aprendizaje.
- Ranking general persistente.
- Juego del Impostor controlado por el profesor.
- Reporte docente por sala, disponible después del cierre.

## Ejecución con XAMPP

Requisitos: PHP 8.2, Composer, Apache/MySQL de XAMPP y una base de datos llamada `ecoimpact`.

```text
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

La configuración local esperada de MariaDB/MySQL es:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecoimpact
DB_USERNAME=root
DB_PASSWORD=
```

## Pruebas con teléfonos o tablets

El teléfono y el computador deben estar conectados a la misma red. Inicia Laravel aceptando conexiones de la red local:

```text
php artisan serve --host=0.0.0.0 --port=8000
```

Obtén la dirección IPv4 del computador con `ipconfig` y abre en todos los dispositivos una dirección como:

```text
http://192.168.1.50:8000
```

El profesor también debe abrir la aplicación usando esa dirección antes de imprimir los QR. Así los códigos contendrán una URL que los teléfonos puedan alcanzar. Puede ser necesario autorizar PHP o el puerto 8000 en el firewall de Windows.

## Verificación

```text
php artisan migrate:status
php artisan view:cache
php artisan test --testsuite=Unit
```

Las pruebas Feature incluidas por Laravel usan SQLite en memoria. Para ejecutarlas, PHP debe tener habilitada la extensión `pdo_sqlite`. La aplicación en XAMPP utiliza `pdo_mysql` y MariaDB.

## Reglas cerradas de esta versión

- Una actividad es global/reutilizable y se vincula a una sala como misión.
- La huella calculada no entrega puntos.
- Las actividades entregan puntos ambientales según su nivel de impacto.
- El Juego del Impostor entrega solamente puntos de aprendizaje.
- Una actividad puede repetirse en días diferentes, nunca dos veces el mismo día.
- Una sala cerrada no puede reabrirse y su información histórica permanece guardada.
- El ranking inicial es general.

## Pendientes que requieren definición externa

- Valores reales de reducción anual de CO₂ para cada actividad.
- Porcentaje de reducción proyectada respecto de la huella inicial.
- Validación mediante IA, fotografía u otra evidencia.
- Rankings específicos por curso y colegio.
- Identidad persistente del mismo estudiante entre salas diferentes sin utilizar una cuenta.

Hasta incorporar factores ambientales documentados, la reducción proyectada se muestra como **Pendiente** y nunca como una cifra inventada.
