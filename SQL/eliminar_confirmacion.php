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
   
    if (!isset($data['confirmacion_id'])) {
        throw new Exception('ID de confirmación no proporcionado');
    }
   
    $confirmacion_id = $data['confirmacion_id'];
    $usuario_id = $_SESSION['user_id'];
   
    // Verificar que la confirmación pertenece al usuario actual
    $stmt_verificar = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM confirmacion_de_participacion
        WHERE ID = ? AND Id_usuario = ?
    ");
    $stmt_verificar->execute([$confirmacion_id, $usuario_id]);
   
    if ($stmt_verificar->fetch()['total'] == 0) {
        throw new Exception('No tienes permiso para eliminar esta confirmación');
    }
   
    // Eliminar la confirmación
    $stmt_eliminar = $pdo->prepare("
        DELETE FROM confirmacion_de_participacion
        WHERE ID = ? AND Id_usuario = ?
    ");
    
    $stmt_eliminar->execute([$confirmacion_id, $usuario_id]);
   
    echo json_encode([
        'success' => true,
        'message' => 'Confirmación eliminada correctamente'
    ]);
   
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
   
} catch (PDOException $e) {
    error_log("Error PDO en eliminar_confirmacion.php: " . $e->getMessage());
   
    echo json_encode([
        'success' => false,
        'message' => 'Error en el sistema. Inténtalo de nuevo.'
    ]);
}
?>
