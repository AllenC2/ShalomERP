# Sistema de Distribución Automática de Pagos

## Descripción General

El sistema de distribución automática de pagos gestiona inteligentemente los pagos de contratos, distribuyendo automáticamente los montos según las cuotas programadas y manejando tanto pagos parciales como excedentes.

**Importante:** Todos los nuevos pagos desde contratos se registran inicialmente como "parcialidad", pero el sistema los procesa automáticamente según su monto para determinar el tipo final y la distribución correspondiente.

## Funcionalidades Principales

### 1. Restricción en Nuevos Pagos desde Contratos

**Interfaz de Usuario:**
- Al crear un nuevo pago desde un contrato, el campo `tipo_pago` solo permite seleccionar "Parcialidad"
- El monto máximo permitido está limitado a la cuota sugerida del contrato
- Esto simplifica la experiencia del usuario y evita confusiones
- El sistema automáticamente determina el procesamiento correcto según el monto

### 2. Pagos Parciales (Menores a la Cuota Sugerida)

**Cuando un pago es menor a la cuota sugerida:**

-   Se mantiene como `tipo_pago = 'parcialidad'`
-   Se marca como `estado = 'hecho'`
-   Se busca el próximo pago pendiente en orden cronológico
-   Se reduce el monto del próximo pago pendiente por el monto de la parcialidad
-   El pago pendiente mantiene su estado como `'pendiente'`
-   Se actualiza la observación del pago pendiente para registrar la reducción

### 3. Pagos Completos (Iguales o Mayores a la Cuota Sugerida)

**Cuando un pago es igual o mayor a la cuota sugerida:**

-   Se cambia automáticamente a `tipo_pago = 'cuota'`
-   Se procesan los pagos pendientes en orden cronológico
-   Se cubren tantas cuotas completas como sea posible
-   Los pagos pendientes cubiertos se marcan como `estado = 'hecho'`
-   Si queda un excedente, se procesa como parcialidad para el siguiente pago

**Ejemplo 1 - Pago exacto:**

-   Cuota sugerida: $1,000.00
-   Pago realizado: $1,000.00
-   **Resultado:**
    -   Se crea un pago de cuota regular por $1,000.00 (estado: hecho)
    -   El próximo pago pendiente se marca como hecho

**Ejemplo 2 - Pago con excedente:**

-   Cuota sugerida: $1,000.00
-   Pagos pendientes: $1,000.00, $1,000.00, $1,000.00
-   Pago realizado: $2,300.00
-   **Resultado:**
    -   Se crea un pago de cuota regular por $2,300.00 (estado: hecho)
    -   Los primeros 2 pagos pendientes se marcan como hecho
    -   Se crea una parcialidad automática por $300.00 (estado: hecho)
    -   El tercer pago pendiente se reduce de $1,000.00 a $700.00 (estado: pendiente)

## Casos de Uso

### Caso 1: Cliente paga menos de lo esperado

```
Cuota esperada: $1,000
Pago realizado: $400
→ Parcialidad $400 (hecho) + Próximo pago reducido a $600 (pendiente)
```

### Caso 2: Cliente paga exactamente la cuota

```
Cuota esperada: $1,000
Pago realizado: $1,000
→ Cuota regular $1,000 (hecho) + Próximo pago marcado como hecho
```

### Caso 3: Cliente paga más de una cuota

```
Cuotas pendientes: $1,000, $1,000, $1,000
Pago realizado: $2,500
→ Cuota regular $2,500 (hecho) + 2 pagos marcados como hecho + Parcialidad $500 (hecho) + Tercer pago reducido a $500 (pendiente)
```

    -   Se crea un pago de cuota regular por $2,300.00 (estado: hecho)
    -   Los primeros 2 pagos pendientes se marcan como hecho
    -   Se crea una parcialidad automática por $300.00 (estado: hecho)
    -   El tercer pago pendiente se reduce de $1,000.00 a $700.00 (estado: pendiente)

## Implementación Técnica

### Métodos Principales

#### `procesarDistribucionAutomaticaPagos()`

Método principal que determina qué tipo de procesamiento aplicar según el monto del pago.

#### `procesarPagoParcial()`

Maneja pagos menores a la cuota sugerida:

-   Crea el pago como parcialidad
-   Reduce el monto del próximo pago pendiente
-   Actualiza observaciones

#### `procesarPagoCompleto()`

Maneja pagos iguales o mayores a la cuota sugerida:

-   Cubre pagos pendientes completos
-   Maneja excedentes como parcialidades
-   Actualiza estados y observaciones

### Campos Automáticos

#### Observaciones Automáticas

-   **Pago parcial:** "Pago parcial aplicado automáticamente."
-   **Pago que cubre otros:** "Cubrió automáticamente X pago(s) pendiente(s)."
-   **Excedente:** "Excedente de pago principal aplicado como parcialidad."
-   **Reducción:** "Reducido por pago parcial/excedente de $X.XX"

#### Tipos de Pago Automáticos

-   `'parcialidad'`: Para pagos menores a la cuota o excedentes
-   `'cuota'`: Para pagos principales que cubren cuotas completas

## Casos de Uso

### Caso 1: Cliente paga menos de lo esperado

```
Cuota esperada: $1,000
Pago realizado: $400
→ Parcialidad $400 (hecho) + Próximo pago reducido a $600 (pendiente)
```

### Caso 2: Cliente paga exactamente la cuota

```
Cuota esperada: $1,000
Pago realizado: $1,000
→ Cuota regular $1,000 (hecho) + Próximo pago marcado como hecho
```

### Caso 3: Cliente paga más de una cuota

```
Cuotas pendientes: $1,000, $1,000, $1,000
Pago realizado: $2,500
→ Cuota regular $2,500 (hecho) + 2 pagos marcados como hecho + Parcialidad $500 (hecho) + Tercer pago reducido a $500 (pendiente)
```

## Consideraciones Importantes

### Estados de Pagos

-   **Parcialidades:** Siempre se marcan como `'hecho'` al crearse
-   **Pagos pendientes reducidos:** Mantienen estado `'pendiente'` hasta ser completamente cubiertos
-   **Pagos cubiertos completamente:** Se marcan como `'hecho'`

### Orden de Procesamiento

-   Los pagos pendientes se procesan en orden cronológico (`fecha_pago ASC`)
-   Se prioriza cubrir pagos completos antes de crear parcialidades

### Recálculo de Saldos

-   El saldo restante se calcula correctamente considerando todos los pagos procesados
-   Se mantiene la integridad de los saldos del contrato

## Integración con Formularios

El sistema se activa automáticamente cuando:

-   Se registra un nuevo pago desde un contrato
-   El pago tiene `estado = 'hecho'`
-   El contrato tiene cuotas configuradas válidas

El formulario mantiene su funcionalidad original para pagos pendientes o sin contrato asociado.
