# Carpeta de Formas de Territorios

Esta carpeta contiene las formas geográficas de los territorios creados desde el mapa interactivo.

## Estructura de Archivos

Cada territorio tiene 3 archivos asociados:

### 1. `{numero}.svg`
- Archivo SVG vectorial con la forma del territorio
- Incluye el número del territorio centrado
- Se puede editar con cualquier editor SVG

### 2. `{numero}.json`
- Datos JSON con las coordenadas geográficas originales
- Incluye el color seleccionado
- Útil para editar el territorio después

### 3. `../imagenes/{numero}.jpg`
- Imagen completa de la tarjeta del territorio
- Incluye encabezado, forma del mapa y pie de página
- Se genera automáticamente desde el SVG

## Formato JSON

```json
{
  "numero": 215,
  "color": "#FFEB3B",
  "formas": [
    [
      {"lat": 41.4536, "lng": 2.2083},
      {"lat": 41.4537, "lng": 2.2085},
      {"lat": 41.4535, "lng": 2.2086}
    ]
  ],
  "created_at": "2025-01-15T10:30:00Z"
}
```

## Colores Disponibles

- `#FFEB3B` - Amarillo (predeterminado)
- `#FF5722` - Naranja/Rojo
- `#4CAF50` - Verde
- `#2196F3` - Azul
- `#9C27B0` - Morado

## Notas Técnicas

- Las coordenadas están en formato lat/lng (WGS84)
- Los archivos SVG tienen dimensiones 400x320px
- Las imágenes JPG finales son 500x650px (tarjeta completa)
- Los textos y datos adicionales se agregan manualmente después
