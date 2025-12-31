<?php
session_start();
include '../ruta.php';
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../src/Pages/login.php?error=invalid');
    exit();
}

if (empty($_POST['usuario']) || empty($_POST['password'])) {
    header('Location: ../src/Pages/login.php?error=empty');
    exit();
}

$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);

try {
    $sql = "SELECT u.ID, u.Usuario, u.Contraseña, u.Id_rol, r.ID as rol_id, r.Rol as rol_nombre 
            FROM usuario u 
            INNER JOIN rol r ON u.Id_rol = r.ID 
            WHERE u.Usuario = :usuario";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: ../src/Pages/login.php?error=invalid');
        exit();
    }
    
    if (password_verify($password, $user['Contraseña']) || $password === $user['Contraseña']) {
        
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['usuario'] = $user['Usuario'];
        $_SESSION['rol_id'] = $user['rol_id'];
        $_SESSION['rol_nombre'] = $user['rol_nombre'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        header('Location: ../src/Pages/dashboard.php');
        exit();
        
    } else {
        header('Location: ../src/Pages/login.php?error=invalid');
        exit();
    }
    
} catch (PDOException $e) {
    error_log("Error de login: " . $e->getMessage());
    header('Location: ../src/Pages/login.php?error=system');
    exit();
}
?>