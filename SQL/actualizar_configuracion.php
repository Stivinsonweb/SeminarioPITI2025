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

if ($_SESSION['rol_id'] != 1 && $_SESSION['rol_id'] != 2) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['tipo']) || !isset($data['estado'])) {
        throw new Exception('Datos incompletos');
    }
    
    $tipo = $data['tipo'];
    $estado = $data['estado'];
    
    if ($tipo === 'preinscripcion') {
        
        $stmt = $pdo->prepare("
            UPDATE usuario 
            SET Id_Habilitar_preinscripcion = ? 
            WHERE Usuario = 'Admin'
        ");
        $stmt->execute([$estado]);
        
        $mensaje = $estado == 1 ? 'Preinscripciones habilitadas' : 'Preinscripciones deshabilitadas';
        
        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ]);
        
    } elseif ($tipo === 'certificados') {
        
        $stmt = $pdo->prepare("
            UPDATE usuario 
            SET Id_Habilitar_Certificado = ? 
            WHERE Usuario = 'Admin'
        ");
        $stmt->execute([$estado]);
        
        $mensaje = $estado == 1 ? 'Certificados habilitados' : 'Certificados deshabilitados';
        
        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ]);
        
    } else {
        throw new Exception('Tipo de configuración no válido');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log("Error PDO en actualizar_configuracion.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error en el sistema. Inténtalo de nuevo.'
    ]);
}
?>