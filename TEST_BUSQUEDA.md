# 🧪 Test de Búsqueda - Verificación

## ✅ Cómo Probar la Búsqueda

### 1. **Acceder al Creador**
```
http://localhost/territorios/public/creador-territorios-mapa
```

### 2. **Abrir Consola del Navegador**
- Presiona **F12**
- Ve a la pestaña **Console**
- Ve a la pestaña **Network**

### 3. **Realizar Búsqueda**

#### Test 1: Dirección Simple
```
Localidad: Santa Coloma de Gramenet
Dirección: Carrer Sant Carles
```
**Resultado esperado:**
- ✅ Mensaje: "🔍 Buscando dirección..."
- ✅ Mensaje: "✅ Dirección encontrada"
- ✅ El mapa se mueve a la ubicación
- ✅ Aparece un marcador

#### Test 2: Dirección con Número
```
Localidad: Santa Coloma de Gramenet
Dirección: Carrer Sant Carles, 50
```
**Resultado esperado:**
- ✅ Búsqueda más precisa
- ✅ Marcador en ubicación exacta

#### Test 3: Plaza Principal
```
Localidad: Santa Coloma de Gramenet
Dirección: Plaça de la Vila
```

#### Test 4: Avenida
```
Localidad: Santa Coloma de Gramenet
Dirección: Avinguda Pallaresa
```

### 4. **Verificar en Network**

En la pestaña **Network** del navegador:

1. Busca el request: `buscar-direccion`
2. Haz clic en él
3. Ve a **Headers:**
   ```
   Request URL: http://localhost/territorios/public/api/territorios/buscar-direccion
   Request Method: POST
   Status Code: 200 OK
   ```

4. Ve a **Request Payload:**
   ```json
   {
       "address": "Carrer Sant Carles",
       "locality": "Santa Coloma de Gramenet"
   }
   ```

5. Ve a **Response:**
   ```json
   {
       "success": true,
       "results": [
           {
               "lat": "41.4534",
               "lon": "2.2081",
               "display_name": "Carrer Sant Carles, ..."
           }
       ]
   }
   ```

### 5. **Verificar en Console**

Deberías ver logs como:
```
Inicializando creador de territorios...
Leaflet cargado correctamente
Mapa creado
8 edificios de ejemplo cargados
```

Y al buscar:
```
✅ Dirección encontrada (en notificación visual)
```

---

## 🐛 Si Hay Errores

### Error 404
```
POST http://localhost/territorios/public/api/territorios/buscar-direccion 404
```
**Solución:**
- Verifica que agregaste la ruta en `routes/web.php`
- Limpia cache: `php artisan route:clear`

### Error 419 (Token Mismatch)
```
419 CSRF token mismatch
```
**Solución:**
- Verifica que el meta tag CSRF esté en el layout
- Refresca la página (F5)

### Error 500
```
Internal Server Error
```
**Solución:**
- Revisa los logs: `storage/logs/laravel.log`
- Verifica que cURL esté habilitado en PHP
- Comprueba el controlador

### Error de cURL
```
Error cURL: ...
```
**Solución:**
- Verifica conexión a internet
- Comprueba que `extension=curl` esté activo en `php.ini`
- Reinicia Apache

---

## 🎯 Checklist de Verificación

- [ ] La página carga sin errores
- [ ] El mapa aparece y es interactivo
- [ ] Los edificios de ejemplo están visibles
- [ ] El campo de búsqueda está habilitado
- [ ] Al escribir y buscar, no hay error "Failed to fetch"
- [ ] Aparece notificación "Buscando dirección..."
- [ ] Aparece notificación "Dirección encontrada"
- [ ] El mapa se mueve a la nueva ubicación
- [ ] Aparece un marcador en el punto buscado
- [ ] En Network aparece el request con status 200
- [ ] La respuesta contiene coordenadas válidas
- [ ] No hay errores en la consola

---

## 📊 Resultados Esperados

### ✅ Éxito Total
```
1. Campo de búsqueda: "Carrer Sant Carles"
2. Clic en 🔍
3. Notificación: "🔍 Buscando dirección..."
4. Request POST → 200 OK (< 2 segundos)
5. Notificación: "✅ Dirección encontrada"
6. Mapa se mueve suavemente a la ubicación
7. Marcador aparece con popup
8. Edificios del área se recargan
9. Console limpia, sin errores
```

### ⚠️ Si Falla
- Captura de pantalla de la consola (F12)
- Captura del error en Network
- Copia el mensaje de error completo
- Revisa `storage/logs/laravel.log`

---

## 🔧 Test Técnico (Opcional)

### Probar el Endpoint Directamente

Usa Postman, Insomnia o cURL:

```bash
curl -X POST http://localhost/territorios/public/api/territorios/buscar-direccion \
  -H "Content-Type: application/json" \
  -d '{
    "address": "Carrer Sant Carles",
    "locality": "Santa Coloma de Gramenet"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "results": [
    {
      "place_id": 123456,
      "lat": "41.4534",
      "lon": "2.2081",
      "display_name": "Carrer Sant Carles, Santa Coloma de Gramenet, ...",
      "boundingbox": ["41.453", "41.454", "2.207", "2.209"]
    }
  ]
}
```

---

## 💡 Tips

1. **Primera búsqueda lenta:** Normal, Nominatim puede tardar 2-3 segundos
2. **Búsquedas siguientes:** Más rápidas (< 1 segundo)
3. **Dirección no encontrada:** Prueba sin número de portal
4. **Resultados múltiples:** El sistema usa el primero automáticamente
5. **Fuera de Santa Coloma:** Recibirás una advertencia pero funcionará

---

## 🎉 Si Todo Funciona

¡Perfecto! Ahora puedes:
1. Buscar cualquier dirección en Santa Coloma
2. Seleccionar edificios del área
3. Crear territorios nuevos
4. Guardar las formas

**Próximo paso:** Probar la selección de edificios y guardado de territorios.
