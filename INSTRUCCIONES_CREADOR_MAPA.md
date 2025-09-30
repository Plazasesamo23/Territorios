# 🗺️ Creador de Territorios - Instrucciones de Uso

## 📍 Acceso

Entra al creador desde:
```
http://localhost/territorios/public/creador-territorios-mapa
```

O desde el menú principal: **🗺️ Crear Territorio**

---

## 🎯 Cómo Usar

### 1. **Ver el Mapa con Vista Satelital** 🛰️

- El mapa se carga automáticamente centrado en Santa Coloma de Gramenet
- **Vista satelital de alta resolución** (Esri World Imagery)
- Etiquetas de calles transparentes superpuestas para orientación
- Usa la **rueda del ratón** para hacer zoom
- **Arrastra** para moverte por el mapa
- Verás edificios reales de OpenStreetMap como **polígonos azul claro**

### 2. **Buscar una Dirección con Autocompletado** 🔍

**Nueva funcionalidad mejorada:**

- **Localidad:** Por defecto "Santa Coloma de Gramenet" (configurable)
- **Búsqueda inteligente:** Escribe solo 3 caracteres
- **Autocompletado en tiempo real:** Aparece un menú desplegable con sugerencias
- **No necesitas el nombre exacto:**
  - Escribe "sant" → verás "Carrer de Sant Carles", "Plaça de Sant Jaume", etc.
  - Escribe "verge" → verás "Carrer de la Verge", "Passatge de la Verge", etc.
- **Selección rápida:**
  - Click en una sugerencia → el mapa se mueve automáticamente
  - Presiona **Enter** → selecciona la primera sugerencia
  - Click en **🔍** → selecciona la primera sugerencia
- Los edificios del área se cargan automáticamente

### 3. **Seleccionar Edificios** 🖱️

**Edificios reales de OpenStreetMap:**

- Los edificios aparecen como **polígonos azul claro**
- **Haz clic** en un edificio para seleccionarlo
- El edificio cambiará al **color que hayas elegido** (amarillo por defecto)
- **Efecto hover:** Al pasar el mouse, el edificio se oscurece ligeramente
- **Tooltip:** Muestra el nombre o dirección del edificio
- Puedes seleccionar **múltiples edificios** haciendo clic en cada uno
- Para **deseleccionar**, haz clic de nuevo en el edificio seleccionado
- El **contador** muestra cuántos edificios tienes seleccionados

### 4. **Elegir Color** 🎨

Haz clic en uno de los 5 colores disponibles:

- 🟡 **Amarillo** (#FFEB3B) - Por defecto
- 🔴 **Rojo** (#FF5722)
- 🟢 **Verde** (#4CAF50)
- 🔵 **Azul** (#2196F3)
- 🟣 **Morado** (#9C27B0)

Todos los edificios seleccionados cambiarán automáticamente al nuevo color.

### 5. **Controles Adicionales** 🔄

- **🗑️ Limpiar:** Deselecciona todos los edificios
- **🔄 Recargar:** Recarga edificios del área actual del mapa
  - Útil después de hacer zoom o mover el mapa
  - Carga edificios reales de OpenStreetMap
  - Si falla la conexión, usa edificios de ejemplo

### 6. **Ver Vista Previa** 👁️

- En el **panel derecho** verás la tarjeta del territorio en tiempo real
- Se actualiza **automáticamente** al seleccionar edificios o cambiar color
- Muestra:
  - La forma coloreada de los edificios seleccionados
  - El número del territorio (editable)
  - Los textos fijos del formato estándar
- **Vista preliminar exacta** de cómo quedará la tarjeta final

### 7. **Guardar Territorio** 💾

1. Asegúrate de tener **al menos 1 edificio seleccionado**
2. Verifica el **número del territorio** (ejemplo: 215)
3. Haz clic en **💾 Guardar Territorio**
4. El sistema creará:
   - `formas-territorio/215.svg` (forma vectorial editable)
   - `formas-territorio/215.json` (coordenadas originales)
   - `imagenes/215.jpg` (tarjeta completa)

---

## 🆕 Nuevas Funcionalidades (Actualizado 2025-09-30)

### ✅ Vista Satelital
- Imágenes aéreas reales de alta resolución
- Etiquetas de calles con transparencia
- Mejor visualización de edificios reales

### ✅ Búsqueda con Autocompletado
- Búsqueda en tiempo real mientras escribes
- Menú desplegable con hasta 5 sugerencias
- No necesitas el nombre exacto de la calle
- Navegación automática al seleccionar sugerencia

### ✅ Edificios Reales de OpenStreetMap
- Carga edificios reales del área visible
- Tooltips con información del edificio
- Fallback a edificios de ejemplo si falla la API
- Logs detallados en consola para debugging

### ✅ Selección Mejorada
- Efecto hover visual
- Click para seleccionar/deseleccionar
- Contador en tiempo real
- Cambio de color instantáneo

---

## 🔧 Solución de Problemas

### El mapa no carga
1. Abre la consola del navegador (**F12**)
2. Busca errores relacionados con Leaflet
3. Verifica conexión a internet
4. Refresca la página (**Ctrl+F5**)
5. Verifica logs en consola:
   ```
   ✅ Leaflet cargado exitosamente
   ✅ Mapa creado exitosamente
   ✅ Tiles agregadas
   ```

### No veo edificios
1. Haz **zoom más cerca** (nivel 17-18 recomendado)
2. Haz clic en **🔄 Recargar**
3. Verifica en consola:
   ```
   ✅ X edificios encontrados en OSM
   ✅ X edificios renderizados correctamente
   ```
4. Si falla OpenStreetMap, se cargarán 8 edificios de ejemplo automáticamente

### La búsqueda no muestra sugerencias
1. Escribe **al menos 3 caracteres**
2. Espera **500ms** (debounce automático)
3. Verifica la localidad (debe estar correcta)
4. Prueba con nombres más simples (ej: "sant" en lugar de "sant carles 50")
5. Revisa la consola por errores de red

### No puedo seleccionar edificios
1. Verifica que aparezcan los polígonos azules
2. Haz clic **directamente sobre un polígono**
3. Abre consola y busca:
   ```
   💥 Click event fired en edificio X
   🖱️ Click en edificio detectado
   ```
4. Si no ves estos logs, recarga la página (Ctrl+F5)
5. Verifica que `window.L` esté disponible en consola

### La vista satelital no carga
1. Verifica conexión a internet
2. El servidor de Esri puede estar lento, espera unos segundos
3. Fallback: Los nombres de calles siguen visibles
4. Revisa consola por errores 404 o timeout

---

## 💡 Consejos y Mejores Prácticas

### Zoom y Navegación
- **Zoom recomendado:** Nivel 17-18 para ver edificios claramente
- Nivel 16: Vista general del barrio
- Nivel 19: Vista muy detallada de edificios individuales
- Usa **Ctrl + rueda** para zoom más preciso

### Selección de Edificios
- Selecciona edificios **contiguos** para territorios coherentes
- Puedes seleccionar **tantos edificios como necesites**
- El color amarillo es tradicional para territorios normales
- Usa otros colores para identificar tipos especiales

### Búsqueda Eficiente
- Escribe solo el nombre de la calle sin número
- Usa términos cortos: "sant", "verge", "Barcelona"
- Si no encuentras, busca una calle cercana conocida
- La localidad "Santa Coloma de Gramenet" garantiza resultados locales

### Guardar Territorios
- **Revisa siempre la vista previa** antes de guardar
- El número se sugiere automáticamente (siguiente disponible)
- Puedes cambiarlo manualmente si es necesario
- Guarda inmediatamente después de seleccionar, no esperes

---

## 📁 Archivos Generados

Después de guardar, encontrarás:

```
territorios/public/
├── formas-territorio/
│   ├── 215.svg     ← Forma vectorial editable (para edición futura)
│   └── 215.json    ← Coordenadas originales (backup)
└── imagenes/
    └── 215.jpg     ← Tarjeta completa lista para imprimir
```

### Formatos de Salida

- **SVG:** Vector escalable, editable en Illustrator/Inkscape
- **JSON:** Coordenadas geográficas originales
- **JPG:** Imagen rasterizada final para impresión

---

## ✅ Siguiente Paso

Después de crear el territorio:

1. El sistema te **redirigirá** a la vista del territorio
2. Podrás ver la tarjeta completa generada
3. Agregar **textos y datos adicionales** manualmente si es necesario
4. El territorio estará **listo para asignar** a un publicador

---

## 🐛 Debugging (Para Desarrolladores)

### Logs de Consola (F12)

**Carga exitosa del mapa:**
```javascript
🗺️ Inicializando mapa...
✅ Mapa creado exitosamente
✅ Tiles agregadas
🏗️ Cargando edificios...
✅ 156 edificios encontrados en OSM
🏗️ Renderizando 156 edificios...
✅ 156 edificios renderizados correctamente
```

**Click en edificio:**
```javascript
👆 Hover en edificio 1
💥 Click event fired en edificio 1
🖱️ Click en edificio detectado
   Estado actual: NO SELECCIONADO
   → Seleccionando edificio con color: #FFEB3B
   Total seleccionados: 1
```

**Búsqueda con autocompletado:**
```javascript
// Al escribir "sant"
Searching for: sant
// 500ms después
Results: 5 suggestions found
```

### Variables Globales Útiles

En la consola puedes inspeccionar:

```javascript
window.territoriosApp              // Estado global de la app
window.territoriosApp.map          // Instancia del mapa Leaflet
window.territoriosApp.selectedBuildings  // Array de edificios seleccionados
window.L                           // Librería Leaflet
typeof window.L.map                // Debe ser "function"
```

---

## 📞 Soporte Técnico

Si tienes problemas:

1. **Revisa la consola del navegador** (F12) por errores
2. Verifica que **Apache y MySQL estén corriendo** en XAMPP
3. Asegúrate de estar en: `http://localhost/territorios/public/`
4. Prueba en **modo incógnito** para descartar problemas de caché
5. Revisa los archivos de documentación:
   - `TROUBLESHOOTING_LEAFLET.md` - Problemas con el mapa
   - `SOLUCION_CORS.md` - Problemas con búsqueda
   - `SOLUCION_FINAL_LEAFLET.md` - Solución implementada

---

## 🎉 ¡Listo para Usar!

El creador de territorios está completamente funcional con:

- ✅ Vista satelital de alta resolución
- ✅ Búsqueda inteligente con autocompletado
- ✅ Edificios reales de OpenStreetMap
- ✅ Selección múltiple con efectos visuales
- ✅ 5 colores personalizables
- ✅ Vista previa en tiempo real
- ✅ Guardado en múltiples formatos

**¡Empieza a crear territorios ahora!** 🚀
