<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    $datos = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
        http_response_code(400);
        throw new Exception("Datos inválidos");
    }

    $email = trim($datos['email'] ?? "");
    $password = $datos['password'] ?? "";

    if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || !is_string($password) || $password === "") {
        http_response_code(400);
        throw new Exception("Email o contraseña inválidos");
    }

    require_once __DIR__ . '/../Config/conexion.php';
    $conexion = (new Conexion())->conectar();

    $consulta = $conexion->prepare(
        "SELECT documento, nombres, apellidos, email, password, rol
         FROM asociados
         WHERE email = :email"
    );
    $consulta->execute([':email' => $email]);
    $asociado = $consulta->fetch();

    if (!$asociado || !password_verify($password, $asociado['password'])) {
        http_response_code(401);
        throw new Exception("Correo o contraseña incorrectos");
    }

    $_SESSION['rol'] = strtolower($asociado['rol'] ?? 'asociado');
    $_SESSION['documento'] = $asociado['documento'];

    echo json_encode([
        "success" => true,
        "rol" => $_SESSION["rol"],
        "nombre" => $asociado['nombres'] . ' ' . $asociado['apellidos'],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode([
        "success" => false,
        "mensaje" => "Error de conexión con la base de datos",
    ]);
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ]);
}