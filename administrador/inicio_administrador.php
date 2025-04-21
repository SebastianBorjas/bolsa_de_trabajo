<?php
require_once 'seguridad_administrador.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <link rel="stylesheet" href="estilos_administrador.css">
</head>
<body>
    <div class="container">
        <button class="menu-toggle">☰</button>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-buttons">
                <button onclick="window.location.href='registrar_plantel.php'">Registrar Plantel</button>
                <button onclick="window.location.href='registrar_moderador.php'">Registrar Moderador</button>
                <button onclick="window.location.href='ver_planteles.php'">Ver Planteles</button>
                <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
        <div class="content">
            <h1>Ingresaste como administrador</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
        </div>
    </div>
    <script src="js_administrador.js"></script>
</body>
</html>