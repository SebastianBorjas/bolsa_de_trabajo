<?php
require_once '../conexion.php';

// Datos hardcoded
$usuario = "admin";
$password = "123";
$tipo_usuario = "administrador";

// Cifrar la contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contraseña, tipo_usuario) 
                           VALUES (:usuario, :contrasena, :tipo_usuario)");
    
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':contrasena', $password_hash);
    $stmt->bindParam(':tipo_usuario', $tipo_usuario);
    
    $stmt->execute();
    echo "Administrador registrado exitosamente";
} catch(PDOException $e) {
    echo "Error al registrar: " . $e->getMessage();
}

$conn = null;
?>