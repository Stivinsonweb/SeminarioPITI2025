<?php
ob_start();
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

require_once 'db_connect.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

ob_end_clean();

try {
    switch($action) {
        case 'get_testimonios':
            $sql = "SELECT 
                        t.Nombre,
                        t.Id_Avatar,
                        t.opinion_presentacion,
                        t.Valoracion,
                        t.Foto,
                        DATE_FORMAT(t.Fecha_de_creacion, '%d/%m/%Y') as fecha
                    FROM Testimonio t
                    ORDER BY t.Fecha_de_creacion DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $testimonios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = array_map(function($testimonio) {
                return [
                    'name' => $testimonio['Nombre'],
                    'id_avatar' => $testimonio['Id_Avatar'],
                    'opinion' => $testimonio['opinion_presentacion'],
                    'rating' => intval($testimonio['Valoracion']),
                    'photo' => $testimonio['Foto'],
                    'avatar_url' => 'SQL/get_avatar.php?id=' . $testimonio['Id_Avatar'],
                    'date' => $testimonio['fecha']
                ];
            }, $testimonios);
            
            echo json_encode($result);
            break;
            
        case 'save_testimonio':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
                exit;
            }
            
            if (empty($_POST['nombre']) || empty($_POST['id_avatar']) || empty($_POST['opinion']) || empty($_POST['valoracion'])) {
                echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
                exit;
            }

            $nombre = trim($_POST['nombre']);
            $id_avatar = intval($_POST['id_avatar']);
            $opinion = trim($_POST['opinion']);
            $valoracion = intval($_POST['valoracion']);
            $fotoPath = null;

            if ($valoracion < 1 || $valoracion > 5) {
                echo json_encode(['success' => false, 'message' => 'Valoracion invalida']);
                exit;
            }

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__) . '/src/assets/img/Noticias/testimonios/';
                
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($extension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido']);
                    exit;
                }

                if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'La imagen es demasiado grande (max 5MB)']);
                    exit;
                }

                $fileName = uniqid('testimonio_') . '.' . $extension;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $filePath)) {
                    $fotoPath = 'src/assets/img/Noticias/testimonios/' . $fileName;
                } else {
                    error_log("Error al mover archivo a: " . $filePath);
                }
            }

            $sql = "INSERT INTO Testimonio (Nombre, Id_Avatar, opinion_presentacion, Valoracion, Foto, Fecha_de_creacion) 
                    VALUES (:nombre, :id_avatar, :opinion, :valoracion, :foto, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':id_avatar' => $id_avatar,
                ':opinion' => $opinion,
                ':valoracion' => $valoracion,
                ':foto' => $fotoPath
            ]);

            echo json_encode([
                'success' => true, 
                'message' => 'Gracias por compartir tu experiencia!',
                'id' => $pdo->lastInsertId()
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Accion no valida']);
            break;
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
