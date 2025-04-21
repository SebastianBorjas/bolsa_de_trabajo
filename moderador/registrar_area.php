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

$id_usuario = $usuario['id_usuario'];

// Obtener el id_plantel del moderador (predeterminado)
$stmt_mod = $conn->prepare("SELECT id_plantel FROM moderadores WHERE id_usuario = :id_usuario");
$stmt_mod->execute(['id_usuario' => $id_usuario]);
$moderador = $stmt_mod->fetch(PDO::FETCH_ASSOC);

if ($moderador === false) {
    die("Error: No se encontró el moderador asociado al usuario actual.");
}

$id_plantel_predeterminado = $moderador['id_plantel'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['registrar_area'])) {
        // Registro de área
        $nombre_area = trim($_POST['nombre_area']);
        $id_plantel = $_POST['id_plantel'];
        
        try {
            $stmt = $conn->prepare("INSERT INTO areas (id_plantel, nombre_area) VALUES (:id_plantel, :nombre)");
            $stmt->bindParam(':id_plantel', $id_plantel);
            $stmt->bindParam(':nombre', $nombre_area);
            $stmt->execute();
            $mensaje_area = "Área registrada exitosamente";
        } catch(PDOException $e) {
            $mensaje_area = "Error al registrar área: " . $e->getMessage();
        }
    } elseif (isset($_POST['registrar_subarea'])) {
        // Registro de subárea
        $nombre_subarea = trim($_POST['nombre_subarea']);
        $descripcion = trim($_POST['descripcion']); // Nuevo campo
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

// Obtener los planteles para el select de áreas
try {
    $stmt_planteles = $conn->prepare("SELECT id_plantel, nombre_plantel FROM planteles ORDER BY nombre_plantel");
    $stmt_planteles->execute();
    $planteles = $stmt_planteles->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje_area = "Error al cargar planteles: " . $e->getMessage();
}

// Obtener las áreas para el select de subáreas (inicialmente solo del plantel predeterminado)
try {
    $stmt_areas = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE id_plantel = :id_plantel ORDER BY nombre_area");
    $stmt_areas->execute(['id_plantel' => $id_plantel_predeterminado]);
    $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensaje_subarea = "Error al cargar áreas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
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
                    <label for="id_plantel">Plantel:</label>
                    <select id="id_plantel" name="id_plantel" required>
                        <option value="">Selecciona un plantel</option>
                        <?php foreach ($planteles as $plantel): ?>
                            <option value="<?php echo $plantel['id_plantel']; ?>" 
                                <?php echo $plantel['id_plantel'] == $id_plantel_predeterminado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($plantel['nombre_plantel']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
    <script>
        // Actualizar las áreas dinámicamente cuando se cambia el plantel
        document.getElementById('id_plantel').addEventListener('change', function() {
            const idPlantel = this.value;
            const selectArea = document.getElementById('id_area');

            if (!idPlantel) {
                selectArea.innerHTML = '<option value="">Selecciona un área</option>';
                return;
            }

            // Hacer una llamada AJAX para obtener las áreas del plantel seleccionado
            fetch('obtener_areas.php?id_plantel=' + idPlantel)
                .then(response => response.json())
                .then(data => {
                    // Limpiar el select de áreas
                    selectArea.innerHTML = '<option value="">Selecciona un área</option>';
                    // Llenar el select con las áreas obtenidas
                    data.forEach(area => {
                        const option = document.createElement('option');
                        option.value = area.id_area;
                        option.textContent = area.nombre_area;
                        selectArea.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar áreas:', error);
                    selectArea.innerHTML = '<option value="">Error al cargar áreas</option>';
                });
        });

        // Disparar el evento change al cargar la página para llenar las áreas iniciales
        document.getElementById('id_plantel').dispatchEvent(new Event('change'));
    </script>
</body>
</html>