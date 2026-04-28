# Rol

Actuá como arquitecto senior Laravel/Livewire y planificador técnico del proyecto ERP Nube.

Tu tarea es analizar el código existente y generar un plan de implementación claro, incremental y seguro.  
NO modifiques archivos.  
NO ejecutes cambios.  
NO escribas código final salvo ejemplos mínimos cuando ayuden a explicar el plan.

# Contexto del proyecto

Stack:
- Laravel 12
- PHP 8.2+
- Livewire 3
- Blade
- TailwindCSS
- MariaDB
- Breeze
- Vite

El sistema actual tiene módulos de:
- Clientes
- Developers
- Proyectos
- Cobros

Actualmente el módulo Proyectos contiene estados comerciales y operativos mezclados:

- prospection
- interested
- sale_closed
- execution
- paused
- finished

Se quiere separar el flujo comercial del flujo operativo.

Nueva decisión de arquitectura:
- El módulo Proyectos debe mostrar solamente proyectos posteriores a una venta cerrada.
- Los estados comerciales deben salir de Proyectos.
- Se debe crear un nuevo módulo de Ventas / Oportunidades.
- Ventas administra intención comercial.
- Proyectos administra ejecución operativa.

# Objetivo funcional

Diseñar un plan para crear un módulo de Ventas que permita:

1. Crear oportunidades comerciales.
2. Permitir oportunidades sin cliente formal asociado.
3. Registrar nombre de consulta.
4. Registrar fecha de primer contacto.
5. Registrar fuente de origen:
   - manual
   - whatsapp
   - instagram
   - website
   - chatbot
   - referral
   - email
   - other
6. Registrar datos iniciales del contacto:
   - nombre opcional
   - teléfono opcional
   - email opcional
   - usuario/red social opcional
   - mensaje inicial opcional
7. Registrar historial de notas comerciales.
8. Registrar historial de cambios de estado.
9. Asociar oportunidad a un cliente existente o permitir crear cliente después.
10. Convertir una oportunidad ganada en proyecto.
11. Al convertir a proyecto, el proyecto debe iniciar en un estado operativo, no comercial.

# Estados comerciales sugeridos

Crear un enum para oportunidades:

- new
- contacted
- qualified
- proposal_sent
- negotiation
- won
- lost
- discarded

Labels en español:
- Nueva consulta
- Contactado
- Calificado
- Propuesta enviada
- Negociación
- Ganada
- Perdida
- Descartada

# Estados operativos sugeridos para proyectos

Revisar el enum actual ProjectStatus y proponer migración hacia:

- pending_start
- execution
- paused
- finished
- cancelled

No aplicar cambios todavía. Solo planificar.

# Reglas importantes

- No romper datos existentes.
- No borrar estados viejos sin estrategia de migración.
- Mantener compatibilidad mientras se implementa el nuevo módulo.
- Usar SoftDeletes en entidades comerciales.
- Usar Enums para estados y fuentes.
- Usar Actions para lógica de negocio.
- No meter toda la lógica dentro de componentes Livewire.
- Mantener el patrón del proyecto:
  - Componentes Livewire en app/Livewire/**
  - Modelos en app/Models/**
  - Enums en app/Enums/**
  - Vistas raíz en resources/views/NombreModulo/index.blade.php
  - Vistas Livewire en resources/views/livewire/**
- Para módulos nuevos, crear una vista raíz:
  resources/views/Ventas/index.blade.php

Debe tener una estructura similar a:

<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                <livewire:sales.index />
            </div>
        </div>
    </div>
</x-app-layout>

# Arquitectura esperada

Proponer, si corresponde, estos modelos:

- Opportunity
- OpportunityNote
- OpportunityStatusLog

Relaciones esperadas:

Opportunity:
- belongsTo Client nullable
- hasMany OpportunityNote
- hasMany OpportunityStatusLog
- hasOne Project

Project:
- belongsTo Opportunity nullable

Client:
- hasMany Opportunity
- hasMany Project

# Actions sugeridas

Analizar si conviene crear:

- CreateOpportunityAction
- UpdateOpportunityStatusAction
- AddOpportunityNoteAction
- ConvertOpportunityToProjectAction

La conversión a proyecto debe ser transaccional.

# Módulo Livewire sugerido

Analizar y proponer estructura para:

- app/Livewire/Sales/Index.php
- app/Livewire/Sales/Show.php
- resources/views/livewire/sales/index.blade.php
- resources/views/livewire/sales/show.blade.php

El listado debería incluir:

- búsqueda
- filtro por estado
- filtro por fuente
- filtro por responsable si existe
- fecha de primer contacto
- último avance / última nota
- acción rápida para agregar nota
- acción para ver detalle
- acción para convertir en proyecto cuando corresponda

# Migraciones esperadas

Proponer migraciones para:

- opportunities
- opportunity_notes
- opportunity_status_logs
- agregar opportunity_id nullable a projects

No ejecutar migraciones. Solo planificar archivos y campos.

# Estrategia de migración de datos existentes

El sistema ya puede tener proyectos con estados:

- prospection
- interested
- sale_closed

Proponer estrategia incremental:

1. Crear módulo Ventas sin tocar lo viejo.
2. Ocultar estados comerciales del listado de Proyectos.
3. Crear conversión de oportunidad ganada a proyecto.
4. Migrar datos viejos comerciales hacia opportunities.
5. Recién después limpiar ProjectStatus y validaciones antiguas.

# Salida esperada

Respondé con un plan técnico estructurado.

El resultado debe incluir:

1. Diagnóstico del estado actual.
2. Decisión de arquitectura recomendada.
3. Archivos a crear.
4. Archivos a modificar.
5. Migraciones necesarias.
6. Modelos y relaciones.
7. Enums necesarios.
8. Actions necesarias.
9. Componentes Livewire necesarios.
10. Cambios en rutas.
11. Estrategia de migración segura.
12. Riesgos de regresión.
13. Orden de implementación por fases.
14. Checklist para que otro agente ejecutor pueda implementar.

# Formato de respuesta

Usá este formato:

## Diagnóstico

## Decisión arquitectónica

## Plan por fases

### Fase 1 — Base de datos y dominio
- Archivos:
- Objetivo:
- Riesgos:

### Fase 2 — Módulo Ventas
- Archivos:
- Objetivo:
- Riesgos:

### Fase 3 — Conversión a proyecto
- Archivos:
- Objetivo:
- Riesgos:

### Fase 4 — Ajustes en Proyectos
- Archivos:
- Objetivo:
- Riesgos:

### Fase 5 — Migración de datos viejos
- Archivos:
- Objetivo:
- Riesgos:

## Checklist para ejecución

## Pruebas mínimas recomendadas

# Restricciones

No modificar archivos.
No ejecutar comandos destructivos.
No proponer borrar tablas existentes.
No proponer cambios masivos innecesarios.
No introducir React, Vue ni JS complejo.
No romper Livewire.
No duplicar layouts.
No crear múltiples raíces HTML en componentes Livewire.