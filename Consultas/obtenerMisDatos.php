
<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

try {
    if (!isset($_SESSION["documento"])) {
        http_response_code(401);
        throw new Exception("Debes iniciar sesión");
    }

    require_once __DIR__ . "/../Config/conexion.php";

    $conexion = (new Conexion())->conectar();

    $consulta = $conexion->prepare(
        "SELECT documento, nombres, apellidos, fechaNacimiento,
                genero, direccionResidencial, ciudad, telefono, email
         FROM asociados
         WHERE documento = :documento"
    );

    $consulta->execute([
        ":documento" => $_SESSION["documento"]
    ]);

    $asociado = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$asociado) {
        http_response_code(404);
        throw new Exception("No se encontraron los datos del asociado");
    }

    echo json_encode([
        "success" => true,
        "asociado" => $asociado
    ]);

} catch (Throwable $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }

    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ]);
}
