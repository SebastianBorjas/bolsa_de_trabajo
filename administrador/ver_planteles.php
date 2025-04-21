<?php
require_once 'seguridad_administrador.php';
require_once '../conexion.php';

// Consultar todos los planteles
$stmt = $conn->prepare("SELECT id_plantel, nombre_plantel FROM planteles ORDER BY nombre_plantel");
$stmt->execute();
$planteles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar moderadores por plantel
$moderadores_por_plantel = [];
foreach ($planteles as $plantel) {
    $stmt = $conn->prepare("
        SELECT m.nombre_completo, m.correo, m.numero 
        FROM moderadores m 
        WHERE m.id_plantel = :id_plantel
    ");
    $stmt->execute(['id_plantel' => $plantel['id_plantel']]);
    $moderadores_por_plantel[$plantel['id_plantel']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Planteles</title>
    <link rel="stylesheet" href="estilos_administrador.css">
</head>
<body>
    <div class="container">
        <button class="menu-toggle">☰</button>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-buttons">
                <button onclick="window.location.href='registrar_plantel.php'">Registrar Plantel</button>
                <button onclick="window.location.href='registrar_moderador.php'">Registrar Moderador</button>
                <button onclick="window.location.href='inicio_administrador.php'">Volver al Inicio</button>
                <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
        <div class="content">
            <h1>Planteles y Moderadores</h1>

            <?php if (empty($planteles)): ?>
                <p class="mensaje error">No hay planteles registrados.</p>
            <?php else: ?>
                <div class="planteles-container">
                    <?php foreach ($planteles as $plantel): ?>
                        <div class="plantel-table">
                            <h2><?php echo htmlspecialchars($plantel['nombre_plantel']); ?></h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre Completo</th>
                                        <th>Correo</th>
                                        <th>Número</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($moderadores_por_plantel[$plantel['id_plantel']])): ?>
                                        <tr>
                                            <td colspan="3">No hay moderadores en este plantel.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($moderadores_por_plantel[$plantel['id_plantel']] as $moderador): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($moderador['nombre_completo']); ?></td>
                                                <td><?php echo htmlspecialchars($moderador['correo']); ?></td>
                                                <td><?php echo htmlspecialchars($moderador['numero']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="js_administrador.js"></script>
</body>
</html>