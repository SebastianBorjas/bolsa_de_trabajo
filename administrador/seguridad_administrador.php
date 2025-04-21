<?php
session_start();

// Verifica si existe una sesión activa
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo_usuario'])) {
    // Si no hay sesión, redirige al login
    header("Location: ../index.php");
    exit();
}

// Verifica si el usuario es administrador
if ($_SESSION['tipo_usuario'] !== 'administrador') {
    // Si no es administrador, redirige a una página de acceso denegado o al index
    header("Location: ../index.php");
    exit();
}
// Si pasa las verificaciones, el código continúa ejecutándose
?>