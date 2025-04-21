<?php
session_start();

// Verifica si existe una sesión activa
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo_usuario'])) {
    // Si no hay sesión, redirige al login
    header("Location: ../index.php");
    exit();
}

// Verifica si el usuario es moderador
if ($_SESSION['tipo_usuario'] !== 'moderador') {
    // Si no es moderador, redirige al index
    header("Location: ../index.html");
    exit();
}
// Si pasa las verificaciones, el código continúa ejecutándose
?>