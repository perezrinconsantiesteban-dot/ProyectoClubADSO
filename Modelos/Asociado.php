<?php

require_once __DIR__ . '/../Config/conexion.php';

class Asociado
{
    private $documento;
    private $nombres;
    private $apellidos;
    private $fechaNacimiento;
    private $genero;
    private $direccionResidencia;
    private $ciudad;
    private $telefono;
    private $email;
    private $password;

    private $conexion;

    public function __construct($datos = [])
    {
        $this->documento = $datos['documento'] ?? '';
        $this->nombres = $datos['nombres'] ?? '';
        $this->apellidos = $datos['apellidos'] ?? '';
        $this->fechaNacimiento = $datos['fechaNacimiento'] ?? '';
        $this->genero = $datos['genero'] ?? '';
        $this->direccionResidencia = $datos['direccionResidencia'] ?? '';
        $this->ciudad = $datos['ciudad'] ?? '';
        $this->email = $datos['email'] ?? '';
        $this->telefono = $datos['telefono'] ?? '';
        $this->password = isset($datos['password']) && $datos['password'] !== ''
            ? password_hash($datos['password'], PASSWORD_DEFAULT)
            : '';

        $conexionDB = new Conexion();
        $this->conexion = $conexionDB->conectar();
    }

    public function obtenerAsociados()
    {
        $sql = "SELECT documento, nombres, apellidos, telefono, email
                FROM asociados
                ORDER BY nombres ASC, apellidos ASC";
        $consulta = $this->conexion->query($sql);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar()
    {
        $sql = "INSERT INTO asociados (
                    documento,
                    nombres,
                    apellidos,
                    fechaNacimiento,
                    genero,
                    direccionResidencial,
                    ciudad,
                    email,
                    telefono,
                    password
                ) VALUES (
                    :documento,
                    :nombres,
                    :apellidos,
                    :fechaNacimiento,
                    :genero,
                    :direccionResidencial,
                    :ciudad,
                    :email,
                    :telefono,
                    :password
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":documento" => $this->documento,
            ":nombres" => $this->nombres,
            ":apellidos" => $this->apellidos,
            ":fechaNacimiento" => $this->fechaNacimiento,
            ":genero" => $this->genero,
            ":direccionResidencial" => $this->direccionResidencia,
            ":ciudad" => $this->ciudad,
            ":email" => $this->email,
            ":telefono" => $this->telefono,
            ":password" => $this->password
        ]);
    }
}