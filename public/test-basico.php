<?php
echo '<h1>🔧 DIAGNÓSTICO XAMPP</h1>';
echo '<p>✅ PHP funcionando: ' . phpversion() . '</p>';
echo '<p>📅 Fecha actual: ' . date('Y-m-d H:i:s') . '</p>';

// Test de conexión a MySQL
$host = 'localhost';
$dbname = 'territorios';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo '<p>✅ Base de datos conectada</p>';
    
    // Contar territorios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM territorios");
    $result = $stmt->fetch();
    echo '<p>📊 Total territorios en DB: ' . $result['total'] . '</p>';
    
    // Verificar territorio #1
    $stmt = $pdo->prepare("SELECT numero, nombre FROM territorios WHERE numero = 1");
    $stmt->execute();
    $territorio1 = $stmt->fetch();
    
    if ($territorio1) {
        echo '<p>🏠 Territorio #1 encontrado: ' . $territorio1['numero'] . ' - ' . $territorio1['nombre'] . '</p>';
        
        // Buscar registros del territorio #1
        $stmt = $pdo->prepare("SELECT fecha_salida, fecha_entrada FROM registros WHERE territorio_id = (SELECT id FROM territorios WHERE numero = 1) ORDER BY fecha_salida DESC LIMIT 3");
        $stmt->execute();
        $registros = $stmt->fetchAll();
        
        echo '<h3>📋 Últimos registros territorio #1:</h3>';
        foreach ($registros as $reg) {
            echo '<p>Salida: ' . $reg['fecha_salida'] . ' | Entrada: ' . ($reg['fecha_entrada'] ?? 'AÚN ASIGNADO') . '</p>';
        }
    } else {
        echo '<p>❌ Territorio #1 NO encontrado</p>';
    }
    
} catch(PDOException $e) {
    echo '<p>❌ Error DB: ' . $e->getMessage() . '</p>';
}

echo '<hr>';
echo '<p><strong>CONCLUSIÓN:</strong> Si ves este mensaje, XAMPP funciona correctamente.</p>';
?>

