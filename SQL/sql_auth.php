<?php
session_start();
include '../ruta.php';
include 'db_connect.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth/login.php?error=invalid');
    exit();
}


if (empty($_POST['usuario']) || empty($_POST['password'])) {
    header('Location: ../auth/login.php?error=empty');
    exit();
}

$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);

try {
    
    $sql = "SELECT u.id, u.usuario, u.contraseña, u.rol, r.id as rol_id, r.nombre as rol_nombre 
            FROM usuario u 
            INNER JOIN rol r ON u.rol = r.id 
            WHERE u.usuario = :usuario";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    
    if (!$user) {
        header('Location: ../auth/login.php?error=invalid');
        exit();
    }
    
    
    if (password_verify($password, $user['contraseña']) || $password === $user['contraseña']) {
        
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol_id'] = $user['rol_id'];
        $_SESSION['rol_nombre'] = $user['rol_nombre'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        
        header('Location: ../Pages/dashboard.php');
        exit();
        
    } else {
        
        header('Location: ../auth/login.php?error=invalid');
        exit();
    }
    
} catch (PDOException $e) {
    error_log("Error de login: " . $e->getMessage());
    header('Location: ../auth/login.php?error=system');
    exit();
}
?>