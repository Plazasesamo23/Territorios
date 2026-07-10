# Territorios — Gestor de Congregación

App Laravel 11 (Blade + CSS custom) para gestionar una congregación de los Testigos de Jehová: **Territorios** (asignar/devolver + informe S-13), **Reuniones VyM** (programa semanal con auto-asignación), **PPOC** (turnos de carritos con disponibilidad pública), **Grupos de predicación**, **Publicadores** y **multi-congregación** (2 congregaciones).

**Usuarios reales: hermanos mayores, NO técnicos, casi siempre en móvil.** Toda pantalla debe entenderse "a golpe de vista" en el primer uso, sin explicaciones. Ante la duda: menos botones, etiquetas en lenguaje llano, estado visible con color + texto.

## Regla nº 1: el código vivo SOLO está en el servidor

Este repo local NO es la app. Los archivos sueltos de la raíz son restos antiguos (marzo/abril 2026). El código real está en OVH (`/home/trastos/Territorios/`, rama `definitivo-servicio`). Copia de trabajo local: `_mirror/` (bajada del servidor; refréscala antes de editar).

**Flujo obligatorio para CUALQUIER cambio:**
1. Descargar el archivo actual del servidor (pscp) — nunca editar sobre una copia vieja.
2. Guardar backup local `.backup` antes de tocar nada.
3. Editar en local → subir (pscp) → verificar sintaxis (`php -l` remoto).
4. Limpiar caché Laravel **y resetear OPcache vía HTTP** (obligatorio tras subir PHP, si no el servidor sirve el código viejo).
5. Verificar en https://territorios.trastosbvaa.org que funciona. Si falla → restaurar backup.

Credenciales SSH/BD/GitHub y comandos plink/pscp exactos: ver `RESUMEN.md` (secciones "Credenciales" y "Comandos de conexion"). No commitear tokens.

**Antes de tocar la BD: mysqldump obligatorio.** Al final de cada sesión: `git add/commit/push`.

## Reglas de diseño (innegociables)

- **Dark-mode only** desde 2026-03-18. Cualquier `background: #fff`/`color: #333` inline es un bug.
- CSS canónico: `public/css/flat-global.css` con variables (`--primary`, `--text`, `--text-muted`, `--bg`, `--bg-white`, `--bg-hover`, `--border`). **Nunca colores hardcodeados inline.**
- Navegación modular: cada módulo (Territorios, Reuniones, PPOC, Admin) tiene su nav propio; "Inicio" vuelve al dashboard. No mezclar items entre módulos ni duplicar botones que ya están en el submenu.
- Touch targets ≥ 44px, texto funcional ≥ 0.85rem, ortografía con tildes ("Campaña", "año").
- Terminología de cara al usuario: "Asignación" (no "Registro"), "Rellenar huecos" (no "Auto-asignar"), "Traer títulos de jw.org" (no "Importar").

## Reglas de negocio críticas

- **NUNCA borrar historial** al desactivar publicadores (se conserva para estadísticas y S-13).
- Auto-asignación VyM: prioriza siervos ministeriales sobre ancianos en partes compartidas; ayudante del mismo género salvo cónyuges; ciclo de emergencia separado (`es_emergencia`).
- Todo filtra por `congregacion_id` (trait `BelongsToCongregacion`). Un cambio que ignore la congregación mezcla datos de dos congregaciones.
- No usar `tinker` (no funciona en OVH). Consultas BD: script PHP temporal → subir → ejecutar → **borrar del servidor**.

## Dónde está cada cosa

- Documentación completa (arquitectura, rutas, scoring VyM, historial de sesiones): `RESUMEN.md`.
- Agentes expertos por módulo: `.claude/agents/` (úsalos para tareas de su dominio).
- Mapa de usuarios/roles del servidor y estado de auditorías UX: memoria del proyecto (`project_auditoria_ux`).
