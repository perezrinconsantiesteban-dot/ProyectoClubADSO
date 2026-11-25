<?php

class Conexion
{
    public function conectar(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $baseDatos = getenv('DB_NAME') ?: 'club';
        $user = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        $dsn = "mysql:host={$host};dbname={$baseDatos};charset=utf8mb4";

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}