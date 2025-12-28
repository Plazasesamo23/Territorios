<?php
// Script para arreglar las rutas de imágenes por congregación

// 1. Modificar Territorio.php
 = 'app/Models/Territorio.php';
 = file_get_contents();

// Reemplazar getImagenUrl
 = str_replace(
    '$imagenPath = "imagenes/{$this->numero}.jpg";',
    '$imagenPath = "imagenes/" . $this->congregacion_id . "_" . $this->numero . ".jpg";',
    
);

// Reemplazar tieneImagen  
 = str_replace(
    '$imagenPath = public_path("imagenes/{$this->numero}.jpg");',
    '$imagenPath = public_path("imagenes/" . $this->congregacion_id . "_" . $this->numero . ".jpg");',
    
);

file_put_contents(, );
echo "Territorio.php actualizado\n";

// 2. Modificar TerritorioController.php
 = 'app/Http/Controllers/TerritorioController.php';
 = file_get_contents();

// Reemplazar en store - nombre del archivo
 = str_replace(
    '$nombreArchivo = $request->numero . \'.jpg\';',
    '$nombreArchivo = session("congregacion_activa_id") . "_" . $request->numero . ".jpg";',
    
);

// Reemplazar en update - imagen anterior
 = str_replace(
    '$imagenAnterior = public_path(\'imagenes/\' . $territorio->numero . \'.jpg\');',
    '$imagenAnterior = public_path("imagenes/" . $territorio->congregacion_id . "_" . $territorio->numero . ".jpg");',
    
);

file_put_contents(, );
echo "TerritorioController.php actualizado\n";

echo "Modificaciones completadas!\n";
