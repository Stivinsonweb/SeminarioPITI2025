<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['confirmaciones']) || !isset($data['id_usuario'])) {
        throw new Exception('Datos incompletos');
    }
    
    $confirmaciones = $data['confirmaciones'];
    $id_usuario = $data['id_usuario'];
    
    if (empty($confirmaciones)) {
        throw new Exception('No hay confirmaciones para guardar');
    }
    
    $stmt_verificar = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM confirmacion_de_participacion 
        WHERE Id_usuario = ?
    ");
    $stmt_verificar->execute([$id_usuario]);
    
    if ($stmt_verificar->fetch()['total'] > 0) {
        throw new Exception('Ya has confirmado asistencias anteriormente');
    }
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO confirmacion_de_participacion 
        (Id_Participante, Asistion, Id_Semestre, Fecha_de_confirmacion, Id_usuario) 
        VALUES (?, 'Si', ?, NOW(), ?)
    ");
    
    $confirmados = 0;
    foreach ($confirmaciones as $confirmacion) {
        $stmt->execute([
            $confirmacion['id_participante'],
            $confirmacion['id_semestre'],
            $id_usuario
        ]);
        $confirmados++;
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Se confirmaron {$confirmados} participantes exitosamente"
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error PDO en guardar_confirmaciones.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error en el sistema. Inténtalo de nuevo.'
    ]);
}
?>