# Impacto en cifras reales de la huella de carbono

## 1. Objetivo

Después de calcular su huella de carbono, el estudiante podrá seleccionar el botón **“Ver impacto en cifras reales”**, ubicado junto a **“Ir a mis actividades”**.

La nueva vista traducirá el resultado anual del estudiante a comparaciones cotidianas, comprensibles y respaldadas por fuentes. Las cifras se calcularán usando el resultado exacto guardado, no un valor fijo asignado a toda la categoría.

> **Aviso educativo:** estas son equivalencias aproximadas para facilitar la comprensión. No demuestran que el estudiante haya consumido literalmente esa cantidad de combustible, electricidad o recursos, ni constituyen una medición certificada.

## 2. Clasificaciones actuales y nuevos subrangos

Las tres clasificaciones visuales actuales se mantienen sin cambios:

- **Baja, color verde:** menos de 600 kg CO₂e/año.
- **Media, color amarillo:** desde 600 hasta menos de 1.200 kg CO₂e/año.
- **Alta, color rojo:** desde 1.200 kg CO₂e/año.

Para seleccionar mensajes más adecuados, cada clasificación se divide internamente en dos. Se considera el mínimo y máximo que actualmente puede producir la calculadora: **169,10 a 2.621,40 kg CO₂e/año**.

| Código | Clasificación visible | Subrango interno | Intervalo anual |
|---|---|---|---:|
| B1 | Baja | Baja inferior | 169,10 a 384,54 kg CO₂e |
| B2 | Baja | Baja superior | 384,55 a 599,99 kg CO₂e |
| M1 | Media | Media inferior | 600,00 a 899,99 kg CO₂e |
| M2 | Media | Media superior | 900,00 a 1.199,99 kg CO₂e |
| A1 | Alta | Alta inferior | 1.200,00 a 1.910,69 kg CO₂e |
| A2 | Alta | Alta superior | 1.910,70 a 2.621,40 kg CO₂e |

Los cortes B1/B2 y A1/A2 corresponden al punto medio del tramo que realmente puede generar la calculadora. M1/M2 se divide en 900 kg CO₂e/año.

## 3. Factores y fórmulas

En las siguientes fórmulas, `H` es la huella exacta del estudiante expresada en kg CO₂e/año.

| Comparación | Factor empleado | Fórmula |
|---|---:|---:|
| Recorrido de automóvil liviano a gasolina | 0,1822 kg CO₂e/km | `H / 0,1822` km |
| Electricidad del Sistema Eléctrico Nacional | 0,2384 kg CO₂e/kWh | `H / 0,2384` kWh |
| Meses de electricidad de un hogar chileno | 180 kWh/hogar/mes | `(H / 0,2384) / 180` hogares-mes |
| Años de electricidad de un hogar chileno | 2.160 kWh/hogar/año | `(H / 0,2384) / 2.160` años-hogar |
| Captura de un árbol urbano en crecimiento | 60 kg CO₂/árbol/año | `H / 60` árboles-año |
| Gasolina quemada | 2,3477 kg CO₂/litro | `H / 2,3477` litros |
| Diésel quemado | 2,6893 kg CO₂/litro | `H / 2,6893` litros |
| Cilindro doméstico con 16 lb de propano | 22 kg CO₂/cilindro | `H / 22` cilindros |
| Carbón quemado | aproximadamente 1,984 kg CO₂/kg | `H / 1,984` kg de carbón |

## 4. Reglas de presentación

- Mostrar primero: **“Tu huella estimada es de X kg CO₂e al año”**.
- Presentar entre tres y cuatro comparaciones para no sobrecargar la pantalla de un teléfono.
- Calcular siempre desde `H`, el resultado real del estudiante.
- Redondear kilómetros, kWh, litros y kilogramos de carbón al entero más cercano.
- Redondear hogares-mes y árboles al entero más cercano, excepto cuando el resultado sea menor que uno.
- Mostrar años de electricidad con un decimal.
- Si árboles-año es menor que uno, expresarlo como meses de captura: `(H / 60) × 12`.
- Usar “equivale aproximadamente” o “es comparable con”; nunca presentar la equivalencia como consumo real comprobado.
- Conservar el color principal vigente: verde para B1/B2, amarillo para M1/M2 y rojo para A1/A2.
- Mostrar una sección desplegable **“¿De dónde salen estas cifras?”** con las referencias del apartado 8.

## 5. Plantillas dinámicas generales

Estas plantillas pueden utilizarse en todos los subrangos reemplazando las variables por los cálculos del apartado 3.

### Electricidad

> “Tu huella anual es comparable con las emisiones asociadas a aproximadamente **{KWH} kWh de electricidad** del Sistema Eléctrico Nacional.”

> “Esa cantidad de electricidad equivale al consumo mensual promedio de aproximadamente **{HOGARES_MES} hogares chilenos**.”

> “También equivale a mantener abastecido con electricidad un hogar promedio durante aproximadamente **{ANIOS_HOGAR} años**.”

La frase de hogares no significa que se abastezca una población completa. Es una equivalencia entre energía y emisiones usando un promedio residencial de 180 kWh mensuales por hogar.

### Árboles

> “Tu huella anual equivale aproximadamente al carbono que **{ARBOLES} árboles urbanos en crecimiento** podrían capturar durante un año.”

> “Se necesitarían aproximadamente **{ARBOLES} árboles creciendo y capturando carbono durante un año** para compensar una cantidad equivalente a tu huella estimada.”

No utilizar “equivale a deforestar X árboles”. La tala de un árbol no tiene un valor universal: depende de especie, edad, diámetro, biomasa, suelo, descomposición y destino de la madera. La equivalencia válida aquí es **árboles-año de captura**.

### Transporte

> “Tu resultado es comparable con las emisiones de recorrer aproximadamente **{KM_AUTO} km en un automóvil liviano a gasolina**.”

### Combustibles y materiales

> “Tu huella anual es comparable con el CO₂ producido al quemar aproximadamente **{LITROS_GASOLINA} litros de gasolina**.”

> “También es comparable con el CO₂ producido al quemar aproximadamente **{LITROS_DIESEL} litros de diésel**.”

> “Representa una cantidad de CO₂ similar a la generada por aproximadamente **{CILINDROS_PROPANO} cilindros domésticos de propano**.”

> “Es comparable con las emisiones producidas al quemar aproximadamente **{KG_CARBON} kg de carbón**.”

## 6. Contenido sugerido para cada subrango

Las frases siguientes indican el tono y las comparaciones que conviene mostrar. Los números entre llaves siempre se reemplazan por el cálculo exacto del estudiante.

### B1 — Baja inferior: 169,10 a 384,54 kg CO₂e/año

**Encabezado:** “Tu resultado está en el tramo más bajo de la calculadora.”

**Explicación:** “Tus respuestas reflejan varios hábitos de bajo impacto. Aun así, toda huella representa emisiones y siempre existen oportunidades para seguir mejorando.”

**Comparaciones:**

1. “Tu huella anual es comparable con las emisiones de recorrer **{KM_AUTO} km en automóvil a gasolina**.”
2. “Equivale a las emisiones asociadas a aproximadamente **{KWH} kWh de electricidad** de la red chilena.”
3. “Se necesitarían cerca de **{ARBOLES} árboles urbanos en crecimiento durante un año** para capturar una cantidad comparable.”

### B2 — Baja superior: 384,55 a 599,99 kg CO₂e/año

**Encabezado:** “Tu huella sigue siendo baja, pero ya representa un impacto que vale la pena reducir.”

**Explicación:** “Estás bajo el límite de impacto medio. Mantener tus buenos hábitos y mejorar transporte, alimentación, reciclaje o uso de energía puede disminuir aún más el resultado.”

**Comparaciones:**

1. “Tu resultado equivale aproximadamente al consumo eléctrico mensual de **{HOGARES_MES} hogares chilenos**.”
2. “Es comparable con la quema de aproximadamente **{LITROS_GASOLINA} litros de gasolina**.”
3. “Se necesitarían cerca de **{ARBOLES} árboles creciendo durante un año** para capturar una cantidad equivalente.”

### M1 — Media inferior: 600,00 a 899,99 kg CO₂e/año

**Encabezado:** “Tu huella se encuentra en el primer tramo de impacto medio.”

**Explicación:** “Algunos hábitos están elevando tu resultado. Pequeños cambios mantenidos durante el año pueden generar una diferencia importante.”

**Comparaciones:**

1. “Tu huella es comparable con recorrer aproximadamente **{KM_AUTO} km en automóvil a gasolina**.”
2. “Equivale al consumo mensual de electricidad de aproximadamente **{HOGARES_MES} hogares chilenos**.”
3. “Se necesitarían cerca de **{ARBOLES} árboles urbanos capturando carbono durante un año** para compensar una cantidad comparable.”

### M2 — Media superior: 900,00 a 1.199,99 kg CO₂e/año

**Encabezado:** “Tu huella está cerca del tramo de impacto alto.”

**Explicación:** “Tu resultado muestra varias oportunidades concretas de mejora. Las actividades de la aplicación te ayudarán a reconocer cuáles hábitos pueden producir una mayor reducción.”

**Comparaciones:**

1. “Tu huella es comparable con las emisiones asociadas a **{KWH} kWh de electricidad** de la red chilena.”
2. “Esa electricidad podría abastecer un hogar promedio durante aproximadamente **{ANIOS_HOGAR} años**.”
3. “También es comparable con quemar aproximadamente **{KG_CARBON} kg de carbón**.”
4. “Se necesitarían alrededor de **{ARBOLES} árboles creciendo durante un año** para capturar una cantidad equivalente.”

### A1 — Alta inferior: 1.200,00 a 1.910,69 kg CO₂e/año

**Encabezado:** “Tu resultado se encuentra en el primer tramo de impacto alto.”

**Explicación:** “Esto no es una calificación ni una sanción. Es una señal para identificar los hábitos con mayor impacto y comenzar a cambiarlos de forma realista.”

**Comparaciones:**

1. “Tu resultado es comparable con recorrer aproximadamente **{KM_AUTO} km en automóvil a gasolina**.”
2. “Equivale al consumo eléctrico mensual promedio de aproximadamente **{HOGARES_MES} hogares chilenos**.”
3. “Es comparable con quemar aproximadamente **{LITROS_GASOLINA} litros de gasolina**.”
4. “Se necesitarían cerca de **{ARBOLES} árboles capturando carbono durante un año** para compensar una cantidad equivalente.”

### A2 — Alta superior: 1.910,70 a 2.621,40 kg CO₂e/año

**Encabezado:** “Tu resultado se encuentra en el tramo más alto de la calculadora.”

**Explicación:** “Hay un potencial importante de reducción. No necesitas cambiar todo de una vez: comienza por las acciones de mayor impacto y conviértelas en hábitos durante el año.”

**Comparaciones:**

1. “Tu huella es comparable con recorrer aproximadamente **{KM_AUTO} km en automóvil a gasolina**.”
2. “Equivale a las emisiones asociadas a aproximadamente **{KWH} kWh de electricidad** de la red chilena.”
3. “Esa electricidad equivale al consumo mensual promedio de aproximadamente **{HOGARES_MES} hogares chilenos**, o al consumo de un hogar durante **{ANIOS_HOGAR} años**.”
4. “Se necesitarían aproximadamente **{ARBOLES} árboles urbanos creciendo y capturando carbono durante un año** para compensar una cantidad equivalente.”

## 7. Ejemplo con la huella máxima actual

Para `H = 2.621,40 kg CO₂e/año`, el estudiante pertenece al subrango **A2**:

- Automóvil: **14.387 km**.
- Electricidad: **10.996 kWh**.
- Consumo residencial: **61 hogares durante un mes**.
- Duración equivalente para un hogar: **5,1 años**.
- Captura anual: **44 árboles urbanos en crecimiento**.
- Gasolina: **1.117 litros**.
- Diésel: **975 litros**.
- Propano: **119 cilindros**.
- Carbón: **1.321 kg**.

Texto propuesto para pantalla:

> “Tu huella estimada es de **2.621,40 kg CO₂e al año**. Es comparable con las emisiones asociadas a **10.996 kWh de electricidad** de la red chilena: aproximadamente el consumo mensual de **61 hogares**, o el consumo de un hogar durante **5,1 años**. Se necesitarían alrededor de **44 árboles urbanos en crecimiento durante un año** para capturar una cantidad equivalente.”

## 8. Referencias

1. **Coordinador Eléctrico Nacional — factor de emisión del SEN.** Para 2023 informa un factor operacional de **0,2384 tCO₂e/MWh**, numéricamente equivalente a **0,2384 kg CO₂e/kWh**.
   <https://www.coordinador.cl/novedades/sistema-electrico-redujo-21-sus-emisiones-en-2023-y-se-espera-que-siga-creciendo-participacion-de-energia-renovable-variable/>

2. **Superintendencia de Electricidad y Combustibles — consumo residencial.** Informa que el consumo promedio mensual de las familias chilenas es cercano a **180 kWh por mes**.
   <https://www.sec.cl/limite-de-invierno/?view_full_site=true>

3. **Programa HuellaChile — transporte terrestre.** Presenta como ejemplo un factor de **0,1822 kg CO₂e/km** para vehículo liviano a gasolina.
   <https://huellachile.mma.gob.cl/wp-content/uploads/2026/04/PPT-HuellaChile-Webinar-Eventos-14042026.pdf>

4. **EPA — gasolina y diésel.** La metodología informa **8,887 kg CO₂ por galón de gasolina** y **10,180 kg CO₂ por galón de diésel**. Usando 3,78541 litros por galón se obtienen aproximadamente 2,3477 y 2,6893 kg CO₂/litro, respectivamente.
   <https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos>

5. **EPA — árboles urbanos.** Estima una captura promedio de **0,060 toneladas métricas de CO₂ por árbol urbano**, promediada bajo supuestos de crecimiento y supervivencia durante diez años. La captura real depende de especie, edad, ubicación y condiciones de crecimiento.
   <https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos>

6. **EPA — cilindros de propano.** Estima **0,022 toneladas métricas de CO₂** para un cilindro doméstico típico con 16 libras de propano.
   <https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos>

7. **EPA — carbón quemado.** Informa aproximadamente **9,00 × 10⁻⁴ toneladas métricas de CO₂ por libra de carbón**, equivalente a unos 1,984 kg de CO₂ por kilogramo de carbón.
   <https://espanol.epa.gov/la-energia-y-el-medioambiente/calculadora-de-equivalencias-de-gases-de-efecto-invernadero-calculos>

8. **EPA — alcance de las equivalencias.** La calculadora advierte que estas comparaciones son aproximadas y no deben utilizarse como inventario formal de emisiones.
   <https://www.epa.gov/energy/greenhouse-gas-equivalencies-calculator>

## 9. Nota para la futura implementación

El botón **“Ver impacto en cifras reales”** debe aparecer únicamente después de existir una huella guardada. La aplicación debe enviar el valor exacto al servicio de equivalencias, identificar uno de los seis subrangos y devolver las comparaciones calculadas. Los factores deben mantenerse centralizados y versionados para poder actualizar el factor eléctrico cuando exista un nuevo dato oficial sin modificar textos o vistas individualmente.
