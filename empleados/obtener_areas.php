<?php
require_once '../conexion.php';

header('Content-Type: application/json');

if (isset($_GET['id_plantel'])) {
    $id_plantel = $_GET['id_plantel'];

    // Obtener áreas
    $stmt_areas = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE id_plantel = :id_plantel ORDER BY nombre_area");
    $stmt_areas->execute(['id_plantel' => $id_plantel]);
    $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);

    // Obtener subáreas con descripción
    $stmt_subareas = $conn->prepare("SELECT id_subarea, id_area, nombre_subarea, descripcion FROM subareas WHERE id_area IN (SELECT id_area FROM areas WHERE id_plantel = :id_plantel) ORDER BY nombre_subarea");
    $stmt_subareas->execute(['id_plantel' => $id_plantel]);
    $subareas_raw = $stmt_subareas->fetchAll(PDO::FETCH_ASSOC);

    // Organizar subáreas por id_area
    $subareas = [];
    foreach ($subareas_raw as $subarea) {
        $subareas[$subarea['id_area']][] = [
            'id_subarea' => $subarea['id_subarea'],
            'nombre_subarea' => $subarea['nombre_subarea'],
            'descripcion' => $subarea['descripcion']
        ];
    }

    // Devolver áreas y subáreas como JSON
    echo json_encode([
        'areas' => $areas,
        'subareas' => $subareas
    ]);
} else {
    echo json_encode(['areas' => [], 'subareas' => []]);
}
?>