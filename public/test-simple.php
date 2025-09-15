<?php
echo '<h1>TEST SIMPLE</h1>';
echo '<p>Fecha actual: ' . date('Y-m-d H:i:s') . '</p>';

// Conexión directa a la base de datos
$host = 'localhost';
$dbname = 'territorios';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<p>✅ Conexión a base de datos exitosa</p>';
    
    // Buscar territorio #1
    $stmt = $pdo->prepare("SELECT * FROM territorios WHERE numero = 1");
    $stmt->execute();
    $territorio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($territorio) {
        echo '<h2>🏠 Territorio #1 encontrado</h2>';
        echo '<p>ID: ' . $territorio['id'] . '</p>';
        echo '<p>Número: ' . $territorio['numero'] . '</p>';
        echo '<p>Nombre: ' . ($territorio['nombre'] ?? 'N/A') . '</p>';
        
        // Buscar último registro devuelto
        $stmt2 = $pdo->prepare("SELECT * FROM registros WHERE territorio_id = ? AND fecha_entrada IS NOT NULL ORDER BY fecha_entrada DESC LIMIT 1");
        $stmt2->execute([$territorio['id']]);
        $registro = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($registro) {
            echo '<h3>📋 Último registro devuelto:</h3>';
            echo '<p>Fecha salida: ' . $registro['fecha_salida'] . '</p>';
            echo '<p>Fecha entrada: ' . $registro['fecha_entrada'] . '</p>';
            
            $fechaDevolucion = new DateTime($registro['fecha_entrada']);
            $fechaActual = new DateTime();
            $diferencia = $fechaActual->diff($fechaDevolucion);
            $diasTranscurridos = $diferencia->days;
            
            echo '<p><strong>Días transcurridos desde devolución: ' . $diasTranscurridos . '</strong></p>';
            echo '<p><strong>¿Cumple 90 días?: ' . ($diasTranscurridos >= 90 ? 'SÍ' : 'NO') . '</strong></p>';
            
            if ($diasTranscurridos < 90) {
                echo '<p style="color: red;"><strong>❌ DEBE ESTAR EN ARCHIVO</strong></p>';
                echo '<p>Días restantes: ' . (90 - $diasTranscurridos) . '</p>';
            } else {
                echo '<p style="color: green;"><strong>✅ PUEDE ESTAR LIBRE</strong></p>';
            }
        } else {
            echo '<p>No hay registros devueltos para este territorio</p>';
        }
    } else {
        echo '<p>❌ Territorio #1 no encontrado</p>';
    }
    
} catch(PDOException $e) {
    echo '<p style="color: red;">❌ Error de conexión: ' . $e->getMessage() . '</p>';
}

echo '<hr>';
echo '<p><a href="/territorios/public/territorios">Ver territorios</a></p>';
?>

