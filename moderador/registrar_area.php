<?php
require_once 'seguridad_moderador.php';
require_once '../conexion.php';

// Obtener el usuario de la sesión
$usuario_sesion = $_SESSION['usuario'];

// Obtener el id_usuario desde la tabla usuarios
$stmt_usuario = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = :usuario AND tipo_usuario = 'moderador'");
$stmt_usuario->execute(['usuario' => $usuario_sesion]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

if ($usuario === false) {
    die("Error: No se encontró el usuario moderador en la base de datos.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['registrar_area'])) {
        // Registro de área
        $nombre_area = trim($_POST['nombre_area']);
        $id_plantel = 1; // Fijar id_plantel a 1
        
        try {
            $stmt = $conn->prepare("INSERT INTO areas (id_plantel, nombre_area) VALUES (:id_plantel, :nombre)");
            $stmt->bindParam(':id_plantel', $id_plantel, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre_area);
            $stmt->execute();
            $mensaje_area = "Área registrada exitosamente";
        } catch(PDOException $e) {
            $mensaje_area = "Error al registrar área: " . $e->getMessage();
        }
    } elseif (isset($_POST['registrar_subarea'])) {
        // Registro de subárea
        $nombre_subarea = trim($_POST['nombre_subarea']);
        $descripcion = trim($_POST['descripcion']);
        $id_area = $_POST['id_area'];
        
        try {
            $stmt = $conn->prepare("INSERT INTO subareas (id_area, nombre_subarea, descripcion) VALUES (:id_area, :nombre, :descripcion)");
            $stmt->bindParam(':id_area', $id_area);
            $stmt->bindParam(':nombre', $nombre_subarea);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            $mensaje_subarea = "Subárea registrada exitosamente";
        } catch(PDOException $e) {
            $mensaje_subarea = "Error al registrar subárea: " . $e->getMessage();
        }
    }
}

// Obtener las áreas para el select de subáreas (solo de id_plantel = 1)
try {
    $stmt_areas = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE id_plantel = 1 ORDER BY nombre_area");
    $stmt_areas->execute();
    $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje_subarea = "Error al cargar áreas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/x-icon" href="../imagenes/logo-formulario.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Área y Subárea</title>
    <link rel="stylesheet" href="regarea3.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <button class="menu-toggle">☰</button>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-buttons">
                <button onclick="window.location.href='ver_curriculums.php'">Ver Currículums</button>
                <button onclick="window.location.href='ver_sugerencias.php'">Ver Sugerencias</button>
                <button onclick="window.location.href='inicio_moderador.php'">Volver al Inicio</button>
                <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
        <div class="content">
            <h1>Registrar Nueva Área o Subárea</h1>

            <!-- Formulario para registrar área -->
            <h2>Registrar Área</h2>
            <?php if (isset($mensaje_area)): ?>
                <p class="mensaje <?php echo strpos($mensaje_area, 'Error') === false ? 'exito' : 'error'; ?>">
                    <?php echo htmlspecialchars($mensaje_area); ?>
                </p>
            <?php endif; ?>
            <form action="" method="POST" class="form-registro">
                <div class="form-group">
                    <label for="nombre_area">Nombre del Área:</label>
                    <input type="text" id="nombre_area" name="nombre_area" required placeholder="Ingresa el nombre del área">
                </div>
                <button type="submit" name="registrar_area" class="submit-button">Registrar Área</button>
            </form>

            <!-- Formulario para registrar subárea -->
            <h2>Registrar Subárea</h2>
            <?php if (isset($mensaje_subarea)): ?>
                <p class="mensaje <?php echo strpos($mensaje_subarea, 'Error') === false ? 'exito' : 'error'; ?>">
                    <?php echo htmlspecialchars($mensaje_subarea); ?>
                </p>
            <?php endif; ?>
            <form action="" method="POST" class="form-registro">
                <div class="form-group">
                    <label for="id_area">Área:</label>
                    <select id="id_area" name="id_area" required>
                        <option value="">Selecciona un área</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?php echo $area['id_area']; ?>">
                                <?php echo htmlspecialchars($area['nombre_area']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nombre_subarea">Nombre de la Subárea:</label>
                    <input type="text" id="nombre_subarea" name="nombre_subarea" required placeholder="Ingresa el nombre de la subárea">
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Ingresa una descripción (máximo 255 caracteres)" maxlength="255"></textarea>
                </div>
                <button type="submit" name="registrar_subarea" class="submit-button">Registrar Subárea</button>
            </form>
        </div>
    </div>
    <script src="js_moderador.js"></script>
</body>
</html>