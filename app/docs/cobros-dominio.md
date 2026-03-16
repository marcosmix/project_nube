# Dominio de Cobros – ERP Nube

Este documento define las reglas funcionales del sistema de cobros utilizado dentro de ERP Nube.
Su objetivo es asegurar consistencia en la lógica financiera del sistema.

---

# 1. Flujo de cobros

Un **flujo de cobros** representa un acuerdo financiero entre la empresa y un cliente.

Ejemplo:

Venta de un sistema en cuotas.

Un flujo contiene:

- cliente
- proyecto asociado
- monto total
- cantidad de cuotas
- tasa de interés
- días de gracia
- fecha de inicio

Al crearse un flujo se generan automáticamente todas las cuotas.

---

# 2. Cuotas

Una **cuota** representa una obligación de pago dentro de un flujo.

Cada cuota tiene:

- número de cuota
- fecha de vencimiento
- capital
- interés
- saldo pendiente
- estado

Estados posibles:

- pendiente
- parcialmente pagada
- pagada
- vencida

---

# 3. Período de gracia

Cada cuota posee un período de gracia configurado en días.

Durante este período:

- la cuota puede pagarse
- no se generan intereses por mora

Una vez superado el período de gracia:

- comienza a correr el interés diario.

---

# 4. Interés por mora

El sistema calcula interés diario sobre el saldo pendiente de la cuota.

Características:

- se calcula por día de atraso
- se acumula hasta que se registra un pago
- puede pagarse total o parcialmente

Si queda interés pendiente puede diferirse a la siguiente cuota.

---

# 5. Imputación de pagos

Cuando se registra un pago el sistema aplica el dinero en el siguiente orden:

1️⃣ interés acumulado  
2️⃣ capital de la cuota

Esto asegura que la deuda financiera no quede impaga.

---

# 6. Excedentes de pago

Si un pago supera el saldo de la cuota:

El excedente se aplica automáticamente a la siguiente cuota pendiente.

El sistema nunca deja dinero sin imputar.

---

# 7. Diferimiento de interés

Si el usuario decide no pagar todo el interés:

El interés restante se traslada a la siguiente cuota.

Esto evita que el cliente deba pagar montos excesivos en una sola cuota.

---

# 8. Recalculo de cuotas

Cuando ocurre alguno de los siguientes eventos:

- pagos parciales
- pagos adelantados
- diferimiento de interés

El sistema recalcula las cuotas futuras para mantener consistencia financiera.

---

# 9. Roles y permisos

Acciones permitidas por rol:

Administrador

- crear flujos
- modificar cuotas
- registrar pagos
- recalcular cuotas

Operador

- registrar pagos
- ver estado de cuotas

Cliente

- visualizar estado de su deuda
- descargar comprobantes