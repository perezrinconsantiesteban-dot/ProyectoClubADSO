
<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

try {
    if (!isset($_SESSION["documento"])) {
        http_response_code(401);
        throw new Exception("Debes iniciar sesión");
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        throw new Exception("Método no permitido");
    }

    require_once __DIR__ . "/../Config/conexion.php";

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!$datos) {
        throw new Exception("No se recibieron datos");
    }

    $conexion = (new Conexion())->conectar();

    $consulta = $conexion->prepare(
        "UPDATE asociados SET
            nombres = :nombres,
            apellidos = :apellidos,
            fechaNacimiento = :fechaNacimiento,
            genero = :genero,
            direccionResidencial = :direccionResidencial,
            ciudad = :ciudad,
            telefono = :telefono,
            email = :email
        WHERE documento = :documento"
    );

    $consulta->execute([
        ":nombres" => trim($datos["nombres"] ?? ""),
        ":apellidos" => trim($datos["apellidos"] ?? ""),
        ":fechaNacimiento" => $datos["fechaNacimiento"] ?? "",
        ":genero" => $datos["genero"] ?? "",
        ":direccionResidencial" => trim($datos["direccionResidencial"] ?? ""),
        ":ciudad" => trim($datos["ciudad"] ?? ""),
        ":telefono" => trim($datos["telefono"] ?? ""),
        ":email" => trim($datos["email"] ?? ""),
        ":documento" => $_SESSION["documento"]
    ]);

    echo json_encode([
        "success" => true,
        "mensaje" => "Datos actualizados correctamente"
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
