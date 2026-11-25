<?php
require_once __DIR__ . '/../Config/conexion.php';

class Prestamo
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->conectar();
    }

    public function obtenerPrestamos()
    {
        $sql = "SELECT p.idPrestamos      AS idPrestamo,
                       p.idAsociado       AS documento,
                       CONCAT(a.nombres, ' ', a.apellidos) AS asociado,
                       p.ValorPrestamo    AS monto,
                       p.NumeroCuotas     AS plazo,
                       p.TasaInteres      AS tasaInteres,
                       p.FechaPrestamo    AS fechaSolicitud,
                       p.Proposito        AS proposito
                FROM prestamos p
                LEFT JOIN asociados a ON a.documento = p.idAsociado
                ORDER BY p.idPrestamos DESC";
        return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar(array $d)
    {
        $sql = "INSERT INTO prestamos (idAsociado, ValorPrestamo, NumeroCuotas, TasaInteres, FechaPrestamo, Proposito)
                VALUES (:documento, :monto, :plazo, :tasa, :fecha, :proposito)";
        return $this->conexion->prepare($sql)->execute([
            ":documento" => $d["documento"],
            ":monto"     => $d["monto"],
            ":plazo"     => $d["plazo"],
            ":tasa"      => $d["tasaInteres"],
            ":fecha"     => $d["fechaSolicitud"],
            ":proposito" => $d["proposito"],
        ]);
    }

    public function actualizar(array $d)
    {
        $sql = "UPDATE prestamos
                SET idAsociado = :documento, ValorPrestamo = :monto, NumeroCuotas = :plazo,
                    TasaInteres = :tasa, FechaPrestamo = :fecha, Proposito = :proposito
                WHERE idPrestamos = :id";
        return $this->conexion->prepare($sql)->execute([
            ":documento" => $d["documento"],
            ":monto"     => $d["monto"],
            ":plazo"     => $d["plazo"],
            ":tasa"      => $d["tasaInteres"],
            ":fecha"     => $d["fechaSolicitud"],
            ":proposito" => $d["proposito"],
            ":id"        => $d["idPrestamo"],
        ]);
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM prestamos WHERE idPrestamos = :id";
        return $this->conexion->prepare($sql)->execute([":id" => $id]);
    }
}