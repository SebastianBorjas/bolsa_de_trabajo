<?php
require_once 'seguridad_administrador.php';
require_once '../conexion.php';

// Obtener lista de planteles
$stmt = $conn->prepare("SELECT id_plantel, nombre_plantel FROM planteles");
$stmt->execute();
$planteles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nombre = trim($_POST['nombre']);
    $apellido_p = trim($_POST['apellido_p']);
    $apellido_m = trim($_POST['apellido_m']);
    $nombre_completo = "$nombre $apellido_p $apellido_m";
    $correo = trim($_POST['correo']);
    $numero = trim($_POST['numero']);
    $id_plantel = $_POST['plantel'];

    // Validar que las contraseñas coincidan (en el servidor)
    if ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden";
    } else {
        try {
            $conn->beginTransaction();
            
            // Insertar en usuarios
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contraseña, tipo_usuario) 
                                  VALUES (:usuario, :password, 'moderador')");
            $stmt->execute([
                'usuario' => $usuario,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
            $id_usuario = $conn->lastInsertId();
            
            // Insertar en moderadores
            $stmt = $conn->prepare("INSERT INTO moderadores (id_usuario, id_plantel, nombre_completo, correo, numero) 
                                  VALUES (:id_usuario, :id_plantel, :nombre, :correo, :numero)");
            $stmt->execute([
                'id_usuario' => $id_usuario,
                'id_plantel' => $id_plantel,
                'nombre' => $nombre_completo,
                'correo' => $correo,
                'numero' => $numero
            ]);
            
            $conn->commit();
            $mensaje = "Moderador registrado exitosamente";
        } catch(PDOException $e) {
            $conn->rollBack();
            $mensaje = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Moderador</title>
    <link rel="stylesheet" href="estilos_administrador.css">
</head>
<body>
    <div class="container">
        <button class="menu-toggle">☰</button>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-buttons">
                <button onclick="window.location.href='registrar_plantel.php'">Registrar Plantel</button>
                <button onclick="window.location.href='ver_planteles.php'">Ver Planteles</button>
                <button onclick="window.location.href='inicio_administrador.php'">Volver al Inicio</button>
                <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
        <div class="content">
            <h1>Registrar Nuevo Moderador</h1>
            <?php if (isset($mensaje)): ?>
                <p class="mensaje <?php echo strpos($mensaje, 'Error') === false && strpos($mensaje, 'no coinciden') === false ? 'exito' : 'error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            <?php endif; ?>
            <form action="" method="POST" class="form-registro" onsubmit="return validarContraseñas()">
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required placeholder="Ingresa el usuario">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa la contraseña">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirma la contraseña">
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ingresa el nombre">
                </div>
                <div class="form-group">
                    <label for="apellido_p">Apellido Paterno:</label>
                    <input type="text" id="apellido_p" name="apellido_p" required placeholder="Ingresa el apellido paterno">
                </div>
                <div class="form-group">
                    <label for="apellido_m">Apellido Materno:</label>
                    <input type="text" id="apellido_m" name="apellido_m" required placeholder="Ingresa el apellido materno">
                </div>
                <div class="form-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" required placeholder="Ingresa el correo">
                </div>
                <div class="form-group">
                    <label for="numero">Número de Teléfono:</label>
                    <input type="text" id="numero" name="numero" required placeholder="Ingresa el número">
                </div>
                <div class="form-group">
                    <label for="plantel">Plantel:</label>
                    <select id="plantel" name="plantel" required>
                        <option value="" disabled selected>Selecciona un plantel</option>
                        <?php foreach ($planteles as $plantel): ?>
                            <option value="<?php echo $plantel['id_plantel']; ?>">
                                <?php echo htmlspecialchars($plantel['nombre_plantel']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="submit-button">Registrar</button>
            </form>
        </div>
    </div>
    <script src="js_administrador.js"></script>
    <script>
        function validarContraseñas() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>