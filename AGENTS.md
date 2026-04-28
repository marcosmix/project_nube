# AGENTS.md

## Contexto general del proyecto

Este proyecto es un sistema interno tipo ERP/CRM desarrollado con Laravel, Livewire, Blade y TailwindCSS.

El sistema gestiona módulos administrativos como proyectos, clientes, cobros, cuotas, pagos, comprobantes, mora, intereses, auditoría y otros procesos operativos de la empresa.

La prioridad del proyecto es:

1. Confiabilidad operativa.
2. Código claro y mantenible.
3. Arquitectura simple.
4. Separación correcta de responsabilidades.
5. Evitar sobreingeniería.
6. Mantener una interfaz moderna, profesional y consistente.

Este proyecto no debe tratarse como un CRUD simple. Algunos módulos, especialmente Cobros, tienen lógica de negocio sensible y deben ser implementados con cuidado.

---

# Stack técnico

- Laravel 12
- PHP 8.2+
- Livewire 3
- Blade
- TailwindCSS
- MariaDB
- Vite

---

# Principios generales de desarrollo

## Regla principal

El código debe ser claro, explícito y fácil de mantener.

No se deben crear abstracciones innecesarias, capas artificiales o patrones complejos si el problema no lo requiere.

Preferir soluciones simples, bien separadas y fáciles de leer.

## Criterio de interfaz y UX

Cuando se modifiquen pantallas Blade, Livewire o Tailwind, también se debe cuidar la claridad de uso.

- Mantener buen contraste entre fondo, texto y estados deshabilitados.
- Evitar botones, badges o alerts que se vean "lavados" o difíciles de leer.
- En flujos lineales de negocio, preferir acciones explícitas como avanzar o volver antes que controles ambiguos si eso mejora la comprensión.
- Los mensajes de bloqueo o advertencia deben mostrarse solo cuando realmente ayudan al usuario a entender qué falta o qué está ocurriendo.
- El estado actual y el siguiente paso posible deben quedar visualmente claros.

---

## Separación de responsabilidades

Cada parte del sistema debe tener una responsabilidad clara.

### Rutas

Las rutas deben ser declarativas y simples.

Pueden:

- Definir URLs.
- Asignar nombres de rutas.
- Aplicar middleware.
- Devolver vistas simples con `Route::view()`.
- Devolver una vista simple con un closure únicamente si no contiene lógica adicional.

No deben contener:

- Lógica de negocio.
- Acceso a Storage.
- Validaciones complejas.
- Verificaciones de pertenencia entre modelos.
- Carga de relaciones.
- Descarga de archivos.
- Preview de archivos.
- Lógica de permisos.
- Procesamiento de datos.
- Consultas complejas.
- Condiciones de dominio.

Regla práctica:

Si una ruta tiene más de 3 líneas o toca Storage, relaciones, permisos, validaciones o reglas de negocio, no debe estar en la ruta: debe ir a un Controller o Action.

---

### Controllers

Los Controllers deben ser finos.

Un Controller puede:

- Recibir la request.
- Recibir modelos por Route Model Binding.
- Delegar lógica de negocio a Actions.
- Validar acceso básico cuando corresponda.
- Devolver respuestas HTTP.
- Devolver vistas.
- Devolver descargas o previews de archivos.

Un Controller no debe transformarse en un archivo gigante de lógica de negocio.

Si una operación empieza a crecer o se reutiliza en más de un lugar, debe moverse a una Action.

---

### Actions

Las Actions deben contener lógica de negocio concreta.

Usar Actions para:

- Registrar pagos.
- Recalcular cuotas.
- Actualizar estados.
- Aplicar intereses.
- Validar pertenencia entre entidades si la regla se reutiliza.
- Generar cuotas.
- Mover saldos.
- Ejecutar procesos de dominio.

Las Actions deben vivir en:

```txt
app/Actions
