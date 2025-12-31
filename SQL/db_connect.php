<?php

$host     = "localhost:3306";
$dbname   = "seminarioclasesp_db";
$username = "seminarioclasesp_user_PITI";
$password = "PITI2025@.";

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    
    die("Error de conexión: " . $e->getMessage());
}


function cerrarConexion() {
    global $pdo;
    $pdo = null;
}
?>