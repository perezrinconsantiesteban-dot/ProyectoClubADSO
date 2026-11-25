<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

try {
    if (!isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para consultar los asociados");
    }

    $metodo = $_SERVER["REQUEST_METHOD"];
    if (!in_array($metodo, ["GET", "POST"], true)) {
        http_response_code(405);
        throw new Exception("Método no permitido");
    }

    if ($metodo === "POST" && $_SESSION["rol"] !== "admin") {
        http_response_code(403);
        throw new Exception("No tienes permisos para realizar esta acción");
    }

    require_once __DIR__ . "/Asociado.php";

    // LISTAR
    if ($metodo === "GET") {
        $asociado = new Asociado();
        echo json_encode([
            "success"   => true,
            "rol"       => $_SESSION["rol"],
            "asociados" => $asociado->obtenerAsociados(),
        ]);
        exit;
    }

    // GUARDAR
    $datos = json_decode(file_get_contents("php://input"), true);
    if (!is_array($datos)) {
        http_response_code(400);
        throw new Exception("Datos inválidos");
    }

    $campos = ["documento", "nombres", "apellidos", "fechaNacimiento", "genero",
               "direccionResidencia", "ciudad", "telefono", "email", "password", "confirmarPassword"];
    foreach ($campos as $campo) {
        if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === "") {
            http_response_code(400);
            throw new Exception("Falta el campo: " . $campo);
        }
    }

    if (!filter_var($datos["email"], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        throw new Exception("Email inválido");
    }
    if ($datos["password"] !== $datos["confirmarPassword"]) {
        http_response_code(400);
        throw new Exception("La contraseña y su confirmación no coinciden");
    }
    if (strlen($datos["password"]) < 4) {
        http_response_code(400);
        throw new Exception("La contraseña debe tener al menos 4 caracteres");
    }

    $asociado = new Asociado($datos);
    if ($asociado->guardar()) {
        http_response_code(201);
        echo json_encode(["success" => true, "mensaje" => "Asociado guardado con éxito"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "mensaje" => "Error al guardar el asociado"]);
    }
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? 0) == 1062) {
        http_response_code(409);
        echo json_encode(["success" => false, "mensaje" => "El documento o el correo ya existe"]);
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
