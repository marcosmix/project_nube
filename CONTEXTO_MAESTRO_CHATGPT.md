# Contexto maestro — Project Nube

## 1) Resumen ejecutivo
Project Nube es una aplicación web de gestión comercial y operativa construida con **Laravel 12 + Livewire 3 (Volt/Breeze)**. El dominio principal gira sobre:
- **Clientes**
- **Developers**
- **Proyectos** (con estados, subestados, historial y notas)

La app permite llevar proyectos desde prospección hasta finalización, asignar developers, registrar cambios de estado y mantener información de seguimiento comercial/técnico.

---

## 2) Stack técnico
- **Backend:** PHP 8.2, Laravel 12
- **UI reactiva server-driven:** Livewire 3
- **Auth/base UI:** Laravel Breeze
- **Frontend build:** Vite + TailwindCSS + Alpine
- **DB:** Eloquent ORM + migraciones Laravel
- **Contenedores:** docker-compose con Nginx/PHP

Dependencias clave:
- `livewire/livewire`
- `livewire/volt`
- `laravel/framework`

---

## 3) Módulos funcionales

### 3.1 Proyectos
Rutas principales:
- `/proyectos` → listado (`App\Livewire\Projects\Index`)
- `/proyectos/{project}` → detalle/edición (`App\Livewire\Projects\Show`)

Características relevantes:
- Alta de proyecto (inicia siempre en `prospection`)
- Búsqueda y filtro por estado
- Asignación de developers al proyecto (tabla pivote)
- Edición según reglas por estado
- Historial de cambios de estado (`project_status_logs`)
- Notas de proyecto (`project_notes`)

Estados (`App\Enums\ProjectStatus`):
- `prospection`
- `interested`
- `sale_closed`
- `execution`
- `paused`
- `finished`

Subestado de ejecución (`App\Enums\ExecutionSubStatus`):
- `on_track`
- `with_debt`
- `delayed`

### 3.2 Clientes
Ruta principal:
- `/clientes` (vista + Livewire `App\Livewire\Clients\Index`)

Características:
- CRUD de cliente + contacto asociado
- Búsqueda por organización o nombre/apellido de contacto
- Soft delete
- Campo de score y métricas de resumen

### 3.3 Developers
Ruta principal:
- `/developers` (`App\Livewire\Developers\DevelopersIndex`)

Características:
- CRUD de developer + contacto
- Skills/skins como arrays
- Estado, disponibilidad, seniority
- Avatar opcional y links (GitHub/LinkedIn)
- Sugerencias/normalización de skills
- Soft delete

---

## 4) Modelo de datos (visión rápida)
Entidades dominio:
- `contacts`
- `clients` (FK a contact)
- `developers` (FK a contact)
- `projects` (FK a client)
- `developer_project` (N:M entre developers y projects)
- `project_status_logs` (auditoría de estados)
- `project_notes` (bitácora de notas)

Relaciones Eloquent importantes:
- `Project belongsTo Client`
- `Project belongsToMany Developer`
- `Project hasMany ProjectStatusLog`
- `Project hasMany ProjectNote`
- `Client belongsTo Contact`
- `Developer belongsTo Contact`

---

## 5) Arquitectura y organización de código
- **Lógica de UI/acciones de negocio:** `app/Livewire/**`
- **Modelo de dominio:** `app/Models/**`
- **Enums de negocio:** `app/Enums/**`
- **Vistas Blade/Livewire:** `resources/views/**`
- **Rutas:** `routes/web.php`, `routes/auth.php`
- **Migraciones y seeders:** `database/migrations`, `database/seeders`

Patrones visibles:
- Validaciones contextuales por estado en componentes Livewire.
- Uso de `SoftDeletes` en entidades de negocio.
- Propiedades computadas y filtros server-side para listados.

---

## 6) Convenciones funcionales útiles para asistentes (ChatGPT)
1. **Idioma de negocio/UI:** español (labels, textos y estados mostrados al usuario).
2. **No romper flujo de estados de proyectos:** cualquier cambio debe respetar transición y validaciones por estado.
3. **Mantener coherencia en relaciones Contacto ↔ Cliente/Developer.**
4. **Preferir cambios en Livewire + Blade** sobre introducir JS complejo innecesario.
5. **Respetar SoftDelete** antes de borrar físicamente datos.
6. **Para filtros/búsquedas**, seguir patrón Eloquent con `when`, scopes o propiedades computadas.

---

## 7) Prompt sugerido para reutilizar este contexto en otros proyectos de ChatGPT

```txt
Actuá como asistente técnico del proyecto “Project Nube”.

Contexto técnico:
- Laravel 12, PHP 8.2, Livewire 3, Breeze, Vite, Tailwind.
- Dominio: clientes, developers y proyectos.
- Proyecto tiene estados: prospection, interested, sale_closed, execution, paused, finished.
- Subestado de ejecución: on_track, with_debt, delayed.
- Relaciones clave:
  - Project belongsTo Client
  - Project belongsToMany Developer
  - Project hasMany ProjectStatusLog y ProjectNote
  - Client/Developer pertenecen a Contact

Reglas de trabajo:
- Priorizá consistencia de negocio y validaciones por estado.
- Proponé cambios incrementales y específicos por archivo.
- En Laravel, usá convenciones nativas (Eloquent, Form Request o validación en Livewire, policies si aplica).
- Respondé en español, con pasos accionables y código listo para copiar.

Cuando te pida implementar algo:
1) Indicá archivos a tocar.
2) Mostrá diff o bloques finales por archivo.
3) Incluí verificación mínima (comandos de prueba/lint).
4) Señalá riesgos de regresión.
```

---

## 8) Mapa rápido de archivos clave
- `routes/web.php`
- `app/Livewire/Projects/Index.php`
- `app/Livewire/Projects/Show.php`
- `app/Livewire/Clients/Index.php`
- `app/Livewire/Developers/DevelopersIndex.php`
- `app/Models/Project.php`
- `app/Models/Client.php`
- `app/Models/Developer.php`
- `app/Enums/ProjectStatus.php`
- `app/Enums/ExecutionSubStatus.php`
- `database/migrations/*projects*`

---

## 9) Estado del repo
Este contexto fue generado en base a inspección estática del repositorio actual y apunta a servir como “brief maestro” para sesiones de ChatGPT orientadas a mantenimiento y evolución funcional.
