# CONTEXTO PROYECTO TERRITORIOS - GESTOR DE CONGREGACION

## INFORMACION DEL SERVIDOR

- **Hosting**: OVH Shared Hosting
- **SSH Host**: `ssh.cluster100.hosting.ovh.net`
- **Usuario**: `trastos`
- **Password**: `Bopo191210`
- **Directorio del proyecto**: `Territorios`
- **URL Produccion**: https://territorios.trastosbvaa.org

### Comandos de conexion
```bash
# SSH
echo y | plink -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net "COMANDO"

# Subir archivo
echo y | pscp -pw Bopo191210 ARCHIVO_LOCAL trastos@ssh.cluster100.hosting.ovh.net:Territorios/RUTA

# Descargar archivo
echo y | pscp -pw Bopo191210 trastos@ssh.cluster100.hosting.ovh.net:Territorios/RUTA ARCHIVO_LOCAL
```

### Comandos Laravel frecuentes
```bash
php Territorios/artisan cache:clear
php Territorios/artisan view:clear
php Territorios/artisan route:clear
php Territorios/artisan config:clear
```

---

## STACK TECNOLOGICO

- **Framework**: Laravel 11
- **PHP**: 8.2
- **Base de datos**: MySQL (host remoto: trastos1.mysql.db)
- **Frontend**: Blade + CSS custom (NO Tailwind compilado, clases inline)
- **Tema**: Dark theme con naranja (#f97316) como color principal

---

## ESTRUCTURA DEL PROYECTO

### Modelos principales
- `Territorio` - Territorios de predicacion (tipos: normal, campana, negocios)
- `Publicador` - Miembros de la congregacion
- `Registro` - Asignaciones de territorios a publicadores
- `Congregacion` - Configuracion de cada congregacion
- `GrupoPredicacion` - Grupos de servicio

### Rutas importantes
- `/dashboard` - Panel principal
- `/panel-territorios` - Vista simplificada de territorios
- `/publicadores` - Lista de publicadores
- `/publicadores/{id}` - Perfil del publicador con estadisticas
- `/territorios` - Gestion de territorios
- `/registros` - Historial de asignaciones

---

## REGLAS CRITICAS - LEER ANTES DE HACER CAMBIOS

### 0. FLUJO DE TRABAJO OBLIGATORIO - NO SUBIR ARCHIVOS LOCALES
**MUY IMPORTANTE**: Este proyecto SOLO existe en el servidor de produccion.
- **NUNCA** subir archivos locales de C:\Users\bryan al servidor
- **NO** asumas que ningun archivo local tiene la version correcta
- Los archivos en C:\Users\bryan son TEMPORALES solo para edicion
- **SIEMPRE** descargar el archivo FRESCO del servidor antes de editar
- El **UNICO** lugar con el codigo real es: `trastos@ssh.cluster100.hosting.ovh.net:Territorios/`
- El servidor tiene **Git** instalado en rama `definitivo servidor`

**Flujo obligatorio para editar**:
1. DESCARGAR del servidor con pscp
2. Editar localmente (archivo temporal)
3. SUBIR al servidor con pscp
4. Limpiar cache
5. **BORRAR** el archivo local temporal despues de subir

**Git en servidor**:
```bash
cd Territorios && git status
cd Territorios && git add . && git commit -m "mensaje"
cd Territorios && git push origin "definitivo servidor"
```

### 1. NUNCA usar tinker en este servidor
El comando `php artisan tinker` NO FUNCIONA en OVH shared hosting. Siempre falla con errores de parsing.

### 2. file_exists() NO ES CONFIABLE
En OVH shared hosting, `file_exists(public_path(...))` frecuentemente devuelve `false` aunque el archivo exista.
- Las imagenes estan en: `public/imagenes/{congregacion_id}_{numero}.jpg`
- Ejemplo: `public/imagenes/1_100.jpg`
- Si necesitas verificar imagenes, devuelve siempre la URL sin usar file_exists.

### 3. Editar archivos - METODO CORRECTO
**NUNCA** usar sed con patrones complejos que incluyan `$` (se pierde en el escape).

**Metodo correcto**:
1. Descargar el archivo con pscp
2. Editarlo localmente con el tool Edit
3. Subir el archivo modificado con pscp
4. Limpiar cache: `php Territorios/artisan view:clear`

### 4. Variables en sed
Cuando uses sed en el servidor:
- `$variable` de PHP se convierte en `\$variable` o se pierde
- Evita patrones con `$` - mejor reemplazar lineas completas por numero

### 5. Territorios - Campo tipo
- El campo `tipo` puede ser: `'normal'`, `'campana'`, `'negocios'` o `NULL`
- Muchos territorios tienen `tipo = NULL` que debe tratarse como `'normal'`
- En blade usar: `{{ $territorio->tipo ?? 'normal' }}`

### 6. Imagenes de territorios
Formato de nombres:
- Normales: `{congregacion_id}_{numero}.jpg` → `1_100.jpg`
- Con tipo: `{congregacion_id}_{numero}_{tipo}.jpg` → `1_10_negocios.jpg`

### 7. Ano de servicio
El ano de servicio de los Testigos de Jehova va de Septiembre a Agosto.
- Septiembre 2024 a Agosto 2025 = Ano de servicio 2024-2025

---

## PROBLEMAS PENDIENTES

### 1. Filtros en panel-territorios NO FUNCIONAN
**Archivo**: `resources/views/panel-territorios.blade.php`
**Problema**: Los botones de filtro (Todos, Normales, Campana, Negocios) no filtran las tarjetas.
**Lo que se intento**:
- addEventListener con querySelectorAll
- onclick directo con funcion global
- Ambos metodos no funcionan

**Posibles causas a investigar**:
- Verificar en consola del navegador si hay errores JS
- Verificar que las tarjetas tengan el data-tipo correcto
- Podria ser un conflicto con otro JS del layout

### 2. Imagenes en panel-territorios
Se modifico `Territorio::getImagenUrl()` para devolver siempre la URL sin file_exists.
Verificar si ahora se muestran las imagenes.

---

## ARCHIVOS CLAVE MODIFICADOS RECIENTEMENTE

### Sesion Enero 2026 - Nombramientos y Filtro de Zonas

1. **resources/views/publicadores/create.blade.php**
   - Agregados checkboxes de nombramientos: Anciano, Siervo Ministerial, Precursor, Menor
   - JavaScript para exclusion mutua entre Anciano y Siervo Ministerial
   - Selector de Grupo de Predicacion
   - CSS para dark mode en nombramientos y selector de grupo

2. **app/Http/Controllers/PublicadorController.php**
   - `create()` - Ahora pasa $grupos a la vista
   - `store()` - Guarda: es_anciano, es_siervo_ministerial, es_precursor, es_menor, grupo_predicacion_id
   - `update()` - Procesa: es_precursor, aprobado_ppoc, grupo_predicacion_id

3. **resources/views/registros/create.blade.php**
   - Agregado filtro por zona (aparece si hay >1 zona unica)
   - Cambiado data-nombre a data-zona en las opciones
   - JavaScript para filtrado combinado tipo + zona
   - CSS para dark mode del filtro de zonas

4. **app/Http/Controllers/RegistroController.php**
   - Corregido SQL error: cambiado 'nombre' a 'zona' en la busqueda (linea 39)

5. **resources/views/territorios/show.blade.php**
   - Campo "Nombre" renombrado a "Zona"
   - Actualizado camposEditables en JavaScript

6. **resources/views/territorios/create.blade.php**
   - Campo "Nombre" renombrado a "Zona"

### Anteriores

7. **app/Models/Territorio.php**
   - `getImagenUrl()` - Devuelve URL de imagen sin file_exists
   - Busca primero formato legacy (sin tipo), luego con tipo

8. **resources/views/panel-territorios.blade.php**
   - Filtros con onclick
   - JavaScript simplificado con funcion global `filtrarTerritorios(tipo)`

9. **resources/views/publicadores/show.blade.php**
   - Muestra estadisticas del publicador
   - Formulario de edicion con checkboxes para precursor/PPOC

---

## COMO DEBUGGEAR

### Ver errores de Laravel
```bash
tail -50 Territorios/storage/logs/laravel.log
```

### Ver errores recientes
```bash
grep "ERROR" Territorios/storage/logs/laravel.log | tail -10
```

### Verificar sintaxis PHP
```bash
php -l Territorios/app/Models/Territorio.php
```

### Listar archivos de imagenes
```bash
ls Territorios/public/imagenes/*.jpg | head -20
```

---

## ESTILO CSS - TEMAS Y RESPONSIVE

### Dark Theme (Tema Oscuro)
El tema oscuro usa `[data-theme="dark"]` como selector.
- **Color principal**: `#f97316` (naranja)
- **Fondos**: `#0a0a0a`, `#1a1a1a`, `#262626`
- **Bordes**: `#404040`
- **Texto principal**: `#f5f5f5`
- **Texto secundario**: `#a3a3a3`, `#e5e5e5`

### Light Theme (Tema Claro)
- **Color principal**: `#16a34a` (verde)
- **Fondos**: `#ffffff`, `#f9fafb`
- **Bordes**: `#d1d5db`, `#e5e7eb`
- **Texto principal**: `#111827`
- **Texto secundario**: `#6b7280`

### REGLA OBLIGATORIA: Respetar ambos temas
Al crear elementos nuevos, **SIEMPRE** incluir estilos para ambos temas:
```css
/* Light mode (por defecto) */
.mi-clase {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    color: #111827;
}

/* Dark mode override */
[data-theme="dark"] .mi-clase {
    background: #1a1a1a;
    border-color: #404040;
    color: #f5f5f5;
}
```

### REGLA OBLIGATORIA: Diseno Responsive
Todos los elementos deben funcionar en dispositivos moviles:
- Usar `width: 100%` en inputs y selects
- Usar `flex-wrap: wrap` en contenedores de badges/tags
- Probar que los formularios sean usables en pantallas pequenas
- Grid de 2 columnas (`grid grid-2`) se colapsa automaticamente en movil

---

## USUARIOS Y PERMISOS

- Los usuarios tienen metodos como `canEditPublicadores()`, `canViewPublicadorStats()`
- Verificar permisos antes de mostrar opciones de edicion
- El trait `BelongsToCongregacion` filtra automaticamente por congregacion_id

---

## NOTAS FINALES

- Siempre limpiar cache despues de cambios en vistas o config
- Los cambios en modelos/controllers no requieren limpiar cache
- Probar cambios en produccion directamente (no hay entorno local)
- El usuario habla espanol, los comentarios en codigo estan en espanol
