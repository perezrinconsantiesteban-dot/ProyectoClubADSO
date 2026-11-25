<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");

function leerDatos(): array
{
    $raw = file_get_contents("php://input");
    if ($raw === false || trim($raw) === "") {
        return [];
    }

    $datos = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
        http_response_code(400);
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    return $datos;
}

function validarActividad(array $datos): array
{
    $nombreActividad = trim($datos["nombreActividad"] ?? "");
    $fechaEvento     = trim($datos["fechaEvento"] ?? $datos["FechaEvento"] ?? "");
    $municipio       = trim($datos["municipio"] ?? "");
    $totalRecaudado  = $datos["totalRecaudado"] ?? $datos["TotalRecaudo"] ?? null;

    if ($nombreActividad === "" || $fechaEvento === "" || $municipio === "") {
        http_response_code(400);
        throw new Exception("Nombre de actividad, fecha del evento y municipio son obligatorios.");
    }

    if ($totalRecaudado === "" || $totalRecaudado === null || !is_numeric($totalRecaudado)) {
        http_response_code(400);
        throw new Exception("El total recaudado debe ser un número válido.");
    }

    return [
        "nombreActividad" => $nombreActividad,
        "fechaEvento"     => $fechaEvento,
        "municipio"       => $municipio,
        "totalRecaudado"  => (float) $totalRecaudado,
    ];
}

try {
    if (!isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para consultar las actividades");
    }

    $metodo = $_SERVER["REQUEST_METHOD"];

    if (!in_array($metodo, ["GET", "POST", "PUT", "DELETE"], true)) {
        http_response_code(405);
        throw new Exception("Método no permitido");
    }

    if ($metodo !== "GET" && $_SESSION["rol"] !== "admin") {
        http_response_code(403);
        throw new Exception("No tienes permisos para realizar esta acción");
    }

    require_once __DIR__ . "/Actividad.php";
    $actividad = new Actividad();

    switch ($metodo) {
        case "GET":
            $resultado = [
                "success"     => true,
                "rol"         => $_SESSION["rol"],
                "actividades" => $actividad->obtenerActividades(),
            ];
            http_response_code(200);
            break;

        case "POST":
            $datos = validarActividad(leerDatos());
            $actividad->guardar($datos);
            $resultado = [
                "success" => true,
                "message" => "Actividad creada exitosamente",
            ];
            http_response_code(201);
            break;

        case "PUT":
            $raw = leerDatos();
            $id = $raw["idActividad"] ?? $raw["idactividad"] ?? null;
            if (empty($id)) {
                http_response_code(400);
                throw new Exception("Falta el ID de la actividad para actualizar");
            }
            $datos = validarActividad($raw);
            $datos["idActividad"] = $id;
            $actividad->actualizar($datos);
            $resultado = [
                "success" => true,
                "message" => "Actividad actualizada exitosamente",
            ];
            http_response_code(200);
            break;

        default:
            http_response_code(405);
            throw new Exception("Método no implementado");
    }

    echo json_encode($resultado);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error en la base de datos: " . $e->getMessage(),
    ]);
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
    ]);
}