---
description: Implementar flujo Agregar pago en módulo Cobros
agent: build
---

Primero inspeccioná el módulo Cobros y localizá:
- componente Livewire principal de `/cobros/{paymentFlow}`
- vista del listado de cuotas
- panel o drawer de detalle de cuota
- modelos y acciones relacionados con pagos
- validaciones actuales de cuotas/pagos
- manejo actual de archivos adjuntos/comprobantes

Usá @explore para ubicar archivos y relaciones si te sirve.
Antes de editar, resumí brevemente el plan y luego implementalo.

## Objetivo
Implementar la acción **Agregar pago** dentro del detalle de un flujo de cobro.

## Reglas funcionales

### Botón “Agregar pago”
Mostrar botón “Agregar pago”:
- en cada fila del listado de cuotas
- dentro del detalle de la cuota seleccionada

Mostrarlo solo si:
- la cuota NO está cancelada
- la cuota tiene saldo mayor a 0

Si la cuota está cancelada:
- ocultar botón “Agregar pago”
- mostrar estado visual en verde con texto “Cancelada”

Si la cuota tiene saldo 0:
- no permitir cargar pago
- no mostrar botón “Agregar pago”

## Modal de carga de pago
La carga del pago debe abrirse en un **modal centrado** en pantalla.

Debe:
- abrirse desde el botón “Agregar pago”
- tener overlay suave
- tener diseño limpio y consistente con la UI actual
- cerrarse con botón cerrar
- cerrarse al guardar correctamente
- refrescar tabla y detalle de cuota luego de guardar

## Datos del pago
Cada pago debe permitir cargar:

1. **Monto**
- obligatorio
- numérico
- no puede ser mayor al saldo pendiente de la cuota
- si supera el saldo, bloquear guardado
- no permitir sobrantes
- no aplicar excedente a cuotas siguientes

2. **Fecha**
- obligatoria
- por defecto fecha actual
- editable manualmente

3. **Comprobante de pago**
- opcional
- un solo archivo por pago
- tipos permitidos:
  - PDF
  - PNG
  - JPG / JPEG

4. **Tipo de pago**
- opcional
- opciones:
  - echeq
  - efectivo
  - transferencia
  - mercado pago

## Reglas de negocio
- el pago siempre aplica solamente a la cuota seleccionada
- una cuota puede tener múltiples pagos parciales
- una cuota puede tener más de un pago
- al registrar un pago, recalcular:
  - pagado acumulado
  - saldo pendiente
  - estado de la cuota si corresponde
- no manejar estados de pago por ahora
- permitir crear pagos
- permitir anular pagos
- no implementar edición de pagos por ahora

## Historial de pagos
En el detalle de la cuota, si existen pagos registrados, mostrar historial simple con:
- fecha
- monto

Si no hay pagos:
- mostrar “Sin movimientos todavía.”

## Restricciones
- No tocar estilos globales innecesariamente
- No rediseñar toda la pantalla
- No agregar librerías nuevas
- No meter lógica que contradiga estas reglas
- No permitir pagos sobrantes
- No aplicar pagos a otras cuotas

## Entregable
Respondé con:
1. estrategia breve
2. archivos a modificar
3. nuevos archivos a crear si hace falta
4. código final por archivo
5. cómo resolviste:
   - apertura del modal
   - visibilidad del botón “Agregar pago”
   - validación de monto contra saldo
   - carga opcional de comprobante
   - recálculo de saldo/pagado
   - anulación de pagos
6. cómo se refrescan tabla y detalle luego de guardar