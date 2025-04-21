<?php
require_once '../conexion.php'; // Ajusta la ruta según tu estructura

header('Content-Type: application/json');

if (!isset($_GET['id_area'])) {
    echo json_encode([]);
    exit;
}

$id_area = $_GET['id_area'];

try {
    $stmt = $conn->prepare("SELECT id_subarea, nombre_subarea FROM subareas WHERE id_area = :id_area ORDER BY nombre_subarea");
    $stmt->execute(['id_area' => $id_area]);
    $subareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($subareas);
} catch (PDOException $e) {
    echo json_encode([]);
    error_log("Error al obtener subáreas: " . $e->getMessage());
}
?>