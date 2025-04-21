<?php
$servername = "localhost"; // Host con puerto
$username = "root"; // Nombre de usuario proporcionado por Plesk
$password = ""; // Reemplaza esto con la contraseña real
$dbname = "curriculums3"; // Nombre de la base de datos

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa"; // Puedes descomentar esto para probar la conexión
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>