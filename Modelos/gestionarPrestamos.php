<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

function leerDatos(): array
{
    $datos = json_decode(file_get_contents("php://input"), true);
    if (!is_array($datos)) {
        http_response_code(400);
        throw new Exception("Datos inválidos");
    }
    return $datos;
}

function validarPrestamo(array $d): array
{
    foreach (["documento", "monto", "plazo", "tasaInteres", "fechaSolicitud", "proposito"] as $c) {
        if (!isset($d[$c]) || trim((string)$d[$c]) === "") {
            http_response_code(400);
            throw new Exception("Falta el campo: " . $c);
        }
    }
    foreach (["monto", "plazo", "tasaInteres"] as $c) {
        if (!is_numeric($d[$c]) || $d[$c] < 0) {
            http_response_code(400);
            throw new Exception("El campo $c debe ser un número válido");
        }
    }
    return [
        "documento"      => trim($d["documento"]),
        "monto"          => (float)$d["monto"],
        "plazo"          => (int)$d["plazo"],
        "tasaInteres"    => (float)$d["tasaInteres"],
        "fechaSolicitud" => trim($d["fechaSolicitud"]),
        "proposito"      => trim($d["proposito"]),
    ];
}

try {
    if (!isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para consultar los préstamos");
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

    require_once __DIR__ . "/Prestamo.php";
    $prestamo = new Prestamo();

    switch ($metodo) {
        case "GET":
            $resultado = [
                "success"   => true,
                "rol"       => $_SESSION["rol"],
                "prestamos" => $prestamo->obtenerPrestamos(),
            ];
            http_response_code(200);
            break;

        case "POST":
            $prestamo->guardar(validarPrestamo(leerDatos()));
            $resultado = ["success" => true, "mensaje" => "Préstamo registrado correctamente"];
            http_response_code(201);
            break;

        case "PUT":
            $raw = leerDatos();
            if (empty($raw["idPrestamo"])) {
                http_response_code(400);
                throw new Exception("Falta el ID del préstamo");
            }
            $datos = validarPrestamo($raw);
            $datos["idPrestamo"] = $raw["idPrestamo"];
            $prestamo->actualizar($datos);
            $resultado = ["success" => true, "mensaje" => "Préstamo actualizado correctamente"];
            http_response_code(200);
            break;

        case "DELETE":
            $id = $_GET["id"] ?? null;
            if (empty($id)) {
                http_response_code(400);
                throw new Exception("Falta el ID del préstamo");
            }
            $prestamo->eliminar($id);
            $resultado = ["success" => true, "mensaje" => "Préstamo eliminado correctamente"];
            http_response_code(200);
            break;
    }

    echo json_encode($resultado);
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? 0) == 1452) {
        http_response_code(400);
        echo json_encode(["success" => false, "mensaje" => "El documento no corresponde a ningún asociado registrado"]);
    } else {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(["success" => false, "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
    }
} catch (Throwable $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}