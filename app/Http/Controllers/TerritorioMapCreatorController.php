<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Territorio;

class TerritorioMapCreatorController extends Controller
{
    /**
     * Mostrar el creador de territorios con mapa interactivo
     */
    public function index()
    {
        // Obtener el siguiente número de territorio disponible
        $ultimoTerritorio = Territorio::max('numero');
        $siguienteNumero = $ultimoTerritorio ? $ultimoTerritorio + 1 : 215;

        return view('creador-territorios.map-creator', compact('siguienteNumero'));
    }

    /**
     * Buscar dirección usando Nominatim (proxy para evitar CORS)
     */
    public function buscarDireccion(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'locality' => 'nullable|string'
        ]);

        $address = $request->address;
        $locality = $request->locality ?? 'Santa Coloma de Gramenet';
        $query = "{$address}, {$locality}, España";

        try {
            // Usar cURL para evitar CORS
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'format' => 'json',
                'q' => $query,
                'limit' => 5
            ]));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'TerritoriosApp/1.0 (Laravel)');
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para XAMPP

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new \Exception('Error cURL: ' . curl_error($ch));
            }

            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception("HTTP Error: {$httpCode}");
            }

            $data = json_decode($response, true);

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la dirección'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'results' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar la forma del territorio desde el mapa
     */
    public function guardarForma(Request $request)
    {
        $request->validate([
            'numero' => 'required|integer|unique:territorios,numero',
            'color' => 'required|string',
            'formas' => 'required|array',
            'formas.*' => 'array'
        ]);

        try {
            // Crear directorio si no existe
            $formasPath = public_path('formas-territorio');
            if (!file_exists($formasPath)) {
                mkdir($formasPath, 0755, true);
            }

            $numero = $request->numero;
            $color = $request->color;
            $formas = $request->formas;

            // Generar SVG de la forma
            $svgContent = $this->generarSVG($formas, $color, $numero);

            // Guardar SVG en la carpeta formas-territorio
            $svgFilename = "{$numero}.svg";
            file_put_contents($formasPath . '/' . $svgFilename, $svgContent);

            // Generar imagen JPG del territorio usando el SVG
            $this->generarImagenTerritorio($numero, $svgContent);

            // Guardar datos JSON de las formas (para edición futura)
            $jsonData = [
                'numero' => $numero,
                'color' => $color,
                'formas' => $formas,
                'created_at' => now()->toIso8601String()
            ];

            file_put_contents(
                $formasPath . '/' . "{$numero}.json",
                json_encode($jsonData, JSON_PRETTY_PRINT)
            );

            // Crear registro del territorio en la base de datos
            $territorio = Territorio::create([
                'numero' => $numero,
                'nombre' => "Territorio #{$numero}",
                'descripcion' => 'Creado desde mapa interactivo',
                'estado' => 'libre',
                'activo' => true,
                'imagen_url' => "imagenes/{$numero}.jpg"
            ]);

            return response()->json([
                'success' => true,
                'message' => "Territorio #{$numero} guardado exitosamente",
                'territorio_id' => $territorio->id,
                'svg_path' => "formas-territorio/{$svgFilename}",
                'image_path' => "imagenes/{$numero}.jpg"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el territorio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar SVG a partir de las formas seleccionadas
     */
    private function generarSVG($formas, $color, $numero)
    {
        // Calcular bounding box de todas las formas
        $minLat = PHP_FLOAT_MAX;
        $maxLat = -PHP_FLOAT_MAX;
        $minLng = PHP_FLOAT_MAX;
        $maxLng = -PHP_FLOAT_MAX;

        foreach ($formas as $forma) {
            foreach ($forma as $punto) {
                $minLat = min($minLat, $punto['lat']);
                $maxLat = max($maxLat, $punto['lat']);
                $minLng = min($minLng, $punto['lng']);
                $maxLng = max($maxLng, $punto['lng']);
            }
        }

        $latRange = $maxLat - $minLat;
        $lngRange = $maxLng - $minLng;

        // Dimensiones del canvas (igual que la vista previa)
        $width = 400;
        $height = 320;
        $padding = 40;

        $usableWidth = $width - ($padding * 2);
        $usableHeight = $height - ($padding * 2);

        $scale = min($usableWidth / $lngRange, $usableHeight / $latRange);

        // Generar paths SVG
        $paths = '';
        foreach ($formas as $forma) {
            $points = [];
            foreach ($forma as $punto) {
                $x = $padding + ($punto['lng'] - $minLng) * $scale;
                $y = $height - $padding - ($punto['lat'] - $minLat) * $scale;
                $points[] = "{$x},{$y}";
            }
            $pointsStr = implode(' ', $points);
            $paths .= "<polygon points=\"{$pointsStr}\" fill=\"{$color}\" stroke=\"#000000\" stroke-width=\"2\"/>\n";
        }

        // Calcular centro para el número
        $centerX = $padding + ($lngRange / 2) * $scale;
        $centerY = $height - $padding - ($latRange / 2) * $scale;

        // Generar SVG completo
        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="{$width}" height="{$height}" xmlns="http://www.w3.org/2000/svg">
    <rect width="{$width}" height="{$height}" fill="#ffffff"/>
    {$paths}
    <text x="{$centerX}" y="{$centerY}" font-family="Arial" font-size="48" font-weight="bold" fill="#000000" text-anchor="middle" dominant-baseline="middle">{$numero}</text>
</svg>
SVG;

        return $svg;
    }

    /**
     * Generar imagen JPG del territorio completo (con encabezado y pie)
     */
    private function generarImagenTerritorio($numero, $svgContent)
    {
        // Dimensiones de la tarjeta completa (igual que territorios 1-214)
        $cardWidth = 500;
        $cardHeight = 650;

        // Crear imagen
        $image = imagecreatetruecolor($cardWidth, $cardHeight);

        // Colores
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 220, 38, 38);
        $gray = imagecolorallocate($image, 102, 102, 102);

        // Fondo blanco
        imagefill($image, 0, 0, $white);

        // === ENCABEZADO ===
        $y = 15;

        // Título
        $font = 5; // Fuente grande incorporada
        imagestring($image, $font, 20, $y, 'Tarjeta de mapa del territorio', $black);

        // Círculo rojo con número
        imagefilledellipse($image, $cardWidth - 50, $y + 25, 50, 50, $red);
        $numText = (string)$numero;
        $textWidth = imagefontwidth($font) * strlen($numText);
        imagestring($image, $font, $cardWidth - 50 - ($textWidth / 2), $y + 20, $numText, $white);

        $y += 60;

        // Localidad
        imagestring($image, 3, 20, $y, 'Localidad: Santa Coloma de Gramenet', $black);
        imagestring($image, 3, $cardWidth - 150, $y, 'Terr. num.: ........................', $black);

        // Línea separadora
        $y += 20;
        imageline($image, 0, $y, $cardWidth, $y, $black);
        imageline($image, 0, $y + 1, $cardWidth, $y + 1, $black);

        // === ÁREA DEL MAPA (renderizar SVG) ===
        $y += 10;

        // Nota: Para renderizar SVG en PHP necesitamos Imagick
        // Si no está disponible, guardaremos el SVG por separado
        // y aquí pondremos un placeholder

        // Intentar renderizar con Imagick si está disponible
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick();
                $imagick->readImageBlob($svgContent);
                $imagick->setImageFormat('png');

                // Convertir a recurso GD
                $tempPng = tempnam(sys_get_temp_dir(), 'svg_');
                $imagick->writeImage($tempPng);

                $svgImage = imagecreatefrompng($tempPng);
                imagecopyresampled(
                    $image, $svgImage,
                    50, $y,  // destino
                    0, 0,     // fuente
                    400, 320, // tamaño destino
                    imagesx($svgImage), imagesy($svgImage) // tamaño fuente
                );

                imagedestroy($svgImage);
                unlink($tempPng);
                $imagick->destroy();
            } catch (\Exception $e) {
                // Si falla, mostrar mensaje
                imagestring($image, 3, 150, $y + 150, 'Mapa del territorio', $gray);
            }
        } else {
            // Sin Imagick, mostrar mensaje
            imagestring($image, 3, 100, $y + 150, 'Ver formas-territorio/' . $numero . '.svg', $gray);
        }

        $y += 330;

        // === PIE DE PÁGINA ===

        // Línea separadora
        imageline($image, 0, $y, $cardWidth, $y, $black);
        imageline($image, 0, $y + 1, $cardWidth, $y + 1, $black);

        $y += 15;

        // Badge CENTRO
        imagerectangle($image, $cardWidth - 120, $y - 12, $cardWidth - 20, $y + 8, $black);
        imagestring($image, 3, $cardWidth - 110, $y - 8, 'CENTRO', $black);

        $y += 5;

        // Instrucción
        imagestring($image, 2, 100, $y, '(Pega el mapa arriba o dibuja el territorio)', $black);

        $y += 20;

        // Texto de instrucciones (más pequeño)
        $instructions = 'Por favor, manten esta tarjeta en el sobre. No manches, marques ni dobles.';
        imagestring($image, 2, 20, $y, $instructions, $black);
        $y += 12;
        $instructions2 = 'Cada vez que hayas trabajado completamente el territorio, informalo al hermano';
        imagestring($image, 2, 20, $y, $instructions2, $black);
        $y += 12;
        imagestring($image, 2, 20, $y, 'encargado de los territorios.', $black);

        $y += 20;

        // Códigos al pie
        imagestring($image, 2, 20, $y, 'S-12-S 6/72', $gray);
        imagestring($image, 2, $cardWidth - 120, $y, 'Printed in Britain', $gray);

        // Guardar imagen
        $imagenesPath = public_path('imagenes');
        if (!file_exists($imagenesPath)) {
            mkdir($imagenesPath, 0755, true);
        }

        imagejpeg($image, $imagenesPath . '/' . $numero . '.jpg', 90);
        imagedestroy($image);
    }
}
