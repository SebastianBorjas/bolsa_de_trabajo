<?php
require_once 'seguridad_moderador.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/x-icon" href="../imagenes/logo-formulario.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Moderador</title>
    <link rel="stylesheet" href="inicio1.css?v=<?php echo time(); ?>">
    
</head>
<body>
    <div class="background-logo">
        <img src="../imagenes/logo-fondo.png" alt="Logo de fondo">
    </div>
    <div class="container">
        <div class="welcome-text">
            <h1>Ingresaste como moderador</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
        </div>
        
        <div class="button-container">
            <div class="button-wrapper">
                <button class="main-button curriculum-btn" onclick="window.location.href='ver_curriculums.php'"></button>
                <label class="button-label">Ver Currículums</label>
            </div>
            <div class="button-wrapper">
                <button class="main-button area-btn" onclick="window.location.href='registrar_area.php'"></button>
                <label class="button-label">Registrar Área</label>
            </div>
            <div class="button-wrapper">
                <button class="main-button sugerencia-btn" onclick="window.location.href='ver_sugerencias.php'"></button>
                <label class="button-label">Ver Sugerencias</label>
            </div>
        </div>
        <button class="logout-btn" onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
    </div>
</body>
</html>