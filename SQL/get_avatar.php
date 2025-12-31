<?php
include 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    die('ID invalido');
}

try {
    $sql = "SELECT Avatar FROM Avatar WHERE Id_Avatar = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $avatar = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$avatar || empty($avatar['Avatar'])) {
        http_response_code(404);
        die('Avatar no encontrado');
    }
    
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=31536000');
    echo $avatar['Avatar'];
    
} catch(PDOException $e) {
    http_response_code(500);
    error_log('Error en get_avatar.php: ' . $e->getMessage());
    die('Error al obtener avatar');
}