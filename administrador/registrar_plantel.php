<?php
require_once 'seguridad_administrador.php';
require_once '../conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_plantel = trim($_POST['nombre_plantel']);
    
    try {
        $stmt = $conn->prepare("INSERT INTO planteles (nombre_plantel) VALUES (:nombre)");
        $stmt->bindParam(':nombre', $nombre_plantel);
        $stmt->execute();
        $mensaje = "Plantel registrado exitosamente";
    } catch(PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Plantel</title>
    <link rel="stylesheet" href="estilos_administrador.css">
</head>
<body>
    <div class="container">
        <button class="menu-toggle">☰</button>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-buttons">
                <button onclick="window.location.href='registrar_moderador.php'">Registrar Moderador</button>
                <button onclick="window.location.href='ver_planteles.php'">Ver Planteles</button>
                <button onclick="window.location.href='inicio_administrador.php'">Volver al Inicio</button>
                <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
        <div class="content">
            <h1>Registrar Nuevo Plantel</h1>
            <?php if (isset($mensaje)): ?>
                <p class="mensaje <?php echo strpos($mensaje, 'Error') === false ? 'exito' : 'error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>
            <form action="" method="POST" class="form-registro">
                <div class="form-group">
                    <label for="nombre_plantel">Nombre del Plantel:</label>
                    <input type="text" id="nombre_plantel" name="nombre_plantel" required placeholder="Ingresa el nombre del plantel">
                </div>
                <button type="submit" class="submit-button">Registrar</button>
            </form>
        </div>
    </div>
    <script src="js_administrador.js"></script>
</body>
</html>