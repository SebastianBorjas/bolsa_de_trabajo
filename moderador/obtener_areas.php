<?php
require_once '../conexion.php'; // Ajusta la ruta según tu estructura

header('Content-Type: application/json');

if (!isset($_GET['id_plantel'])) {
    echo json_encode([]);
    exit;
}

$id_plantel = $_GET['id_plantel'];

try {
    $stmt = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE id_plantel = :id_plantel ORDER BY nombre_area");
    $stmt->execute(['id_plantel' => $id_plantel]);
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($areas);
} catch (PDOException $e) {
    echo json_encode([]);
    error_log("Error al obtener áreas: " . $e->getMessage());
}
?>