# Prueba física del lector QR

Esta pauta permite certificar el comportamiento real de la cámara de IT Conecta en teléfonos y tablets. Las comprobaciones automáticas cubren la lógica del lector, pero esta tabla debe completarse con los dispositivos que se utilizarán en clases.

## Condiciones previas

- Abrir la aplicación mediante `https://` en el teléfono o tablet. Los navegadores no permiten la cámara en vivo desde una dirección IP local servida solo por HTTP.
- Mantener disponible Apache, MySQL y la misma red para todos los dispositivos.
- Tener un QR válido de una misión tradicional y otro de la EcoBúsqueda.
- Probar con iluminación normal y con iluminación baja.

## Matriz de dispositivos

| Dispositivo | Sistema | Navegador y versión | Cámara trasera | Fotografía | Linterna | Resultado | Observaciones |
|---|---|---|---|---|---|---|---|
| Teléfono Android | | Chrome | ☐ | ☐ | ☐ | ☐ Aprobado / ☐ Falló | |
| iPhone | | Safari | ☐ | ☐ | ☐ | ☐ Aprobado / ☐ Falló | |
| Tablet disponible | | | ☐ | ☐ | ☐ | ☐ Aprobado / ☐ Falló | |

## Casos obligatorios

1. **Permitir cámara:** pulsar “Abrir cámara”, aceptar el permiso y comprobar que se prefiera la cámara trasera.
2. **QR válido:** enfocar un QR de IT Conecta y verificar que abra exclusivamente la misión correspondiente.
3. **QR ajeno:** escanear un QR externo y comprobar que muestre un mensaje sin abandonar IT Conecta.
4. **Permiso rechazado:** rechazar el permiso y comprobar que aparezcan instrucciones claras y la alternativa “Tomar foto”.
5. **Recuperación del permiso:** habilitar la cámara en los ajustes del navegador, volver a la aplicación y pulsar “Reintentar cámara”.
6. **Cámara ocupada:** abrir otra aplicación que use la cámara; IT Conecta debe explicar el conflicto y permitir reintentar.
7. **Cancelar fotografía:** pulsar “Tomar foto” y cancelar. La pantalla debe permanecer operativa, sin registrar una actividad.
8. **Fotografía válida:** fotografiar un QR y comprobar que se procese incluso cuando la cámara en vivo no esté disponible.
9. **Poca luz:** comprobar la lectura; si aparece “Encender linterna”, activarla. Si el dispositivo no la admite, la aplicación debe mantener disponible la fotografía.
10. **Cerrar y volver:** cerrar el lector y verificar que el indicador de cámara del dispositivo se apague; abrirlo nuevamente y escanear.
11. **Interrupción:** con el lector abierto, cambiar de aplicación o bloquear la pantalla. Al volver, la cámara debe estar detenida y debe poder abrirse otra vez.
12. **Ambos flujos:** repetir los casos esenciales tanto en Misiones ambientales como en EcoBúsqueda.

## Criterio de aprobación

Cada tipo de dispositivo debe completar los casos 1 al 8, 10, 11 y 12 sin errores de servidor, navegación externa ni registro accidental. El caso de linterna puede marcarse “no compatible” si el navegador o la cámara no expone esa función.

La tarea queda certificada físicamente solo después de completar esta matriz. Hasta entonces, el código y sus recuperaciones están verificados de forma automática, pero no se afirma compatibilidad con un modelo concreto de teléfono o tablet.
