<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['rol_id'] != 2) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['accion'])) {
        throw new Exception('Acción no especificada');
    }
    
    $accion = $data['accion'];
    
    if ($accion === 'crear') {
        
        if (!isset($data['usuario']) || !isset($data['password']) || !isset($data['rol'])) {
            throw new Exception('Todos los campos son obligatorios');
        }
        
        $usuario = trim($data['usuario']);
        $password = trim($data['password']);
        $rol = $data['rol'];
        
        if (empty($usuario) || empty($password)) {
            throw new Exception('Usuario y contraseña no pueden estar vacíos');
        }
        
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE Usuario = ?");
        $stmt_check->execute([$usuario]);
        
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception('El usuario ya existe');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO usuario (Usuario, Contraseña, Id_rol, Id_Habilitar_preinscripcion, Id_Habilitar_Certificado) 
            VALUES (?, ?, ?, 3, 3)
        ");
        $stmt->execute([$usuario, $password, $rol]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente'
        ]);
        
    } elseif ($accion === 'editar') {
        
        if (!isset($data['id']) || !isset($data['usuario']) || !isset($data['rol'])) {
            throw new Exception('Datos incompletos');
        }
        
        $id = $data['id'];
        $usuario = trim($data['usuario']);
        $password = trim($data['password'] ?? '');
        $rol = $data['rol'];
        
        if (empty($usuario)) {
            throw new Exception('El usuario no puede estar vacío');
        }
        
        if (!empty($password)) {
            $stmt = $pdo->prepare("
                UPDATE usuario 
                SET Usuario = ?, Contraseña = ?, Id_rol = ? 
                WHERE ID = ?
            ");
            $stmt->execute([$usuario, $password, $rol, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE usuario 
                SET Usuario = ?, Id_rol = ? 
                WHERE ID = ?
            ");
            $stmt->execute([$usuario, $rol, $id]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
        
    } elseif ($accion === 'eliminar') {
        
        if (!isset($data['id'])) {
            throw new Exception('ID no especificado');
        }
        
        $id = $data['id'];
        
        $stmt = $pdo->prepare("DELETE FROM usuario WHERE ID = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente'
        ]);
        
    } else {
        throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log("Error PDO en gestionar_usuario.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error en el sistema. Inténtalo de nuevo.'
    ]);
}
?>