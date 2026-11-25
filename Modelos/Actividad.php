<?php
require_once __DIR__ . '/../Config/conexion.php';

class Actividad
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->conectar();
    }

    public function obtenerActividades()
    {
        $sql = "SELECT idactividad, nombreActividad, FechaEvento, municipio, TotalRecaudo
                FROM actividades ORDER BY FechaEvento DESC";
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar($datos)
    {
        $sql = "INSERT INTO actividades (nombreActividad, FechaEvento, municipio, TotalRecaudo)
                VALUES (:nombre, :fecha, :municipio, :recaudo)";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            ":nombre"    => $datos["nombreActividad"],
            ":fecha"     => $datos["FechaEvento"] ?? $datos["fechaEvento"],
            ":municipio" => $datos["municipio"],
            ":recaudo"   => $datos["TotalRecaudo"] ?? $datos["totalRecaudado"] ?? 0
        ]);
    }

    public function actualizar($datos)
    {
        $sql = "UPDATE actividades
                SET nombreActividad = :nombre,
                    FechaEvento = :fecha,
                    municipio = :municipio,
                    TotalRecaudo = :recaudo
                WHERE idactividad = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            ":id"        => $datos["idActividad"] ?? $datos["idactividad"],
            ":nombre"    => $datos["nombreActividad"],
            ":fecha"     => $datos["FechaEvento"] ?? $datos["fechaEvento"],
            ":municipio" => $datos["municipio"],
            ":recaudo"   => $datos["TotalRecaudo"] ?? $datos["totalRecaudado"] ?? 0
        ]);
    }

    public function inscribir($idAsociado, $idActividad)
    {
        $sql = "INSERT INTO actividades_asociado (idAsociado, idActividad) VALUES (:a, :b)";
        return $this->conexion->prepare($sql)->execute([":a" => $idAsociado, ":b" => $idActividad]);
    }
}