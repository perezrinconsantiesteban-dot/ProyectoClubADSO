<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../Config/conexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    $conexionDB = new Conexion();
    $db = $conexionDB->conectar();

    $totalPrestado = (float) ($db->query("SELECT COALESCE(SUM(ValorPrestamo), 0) FROM prestamos")->fetchColumn() ?? 0);
    $totalPagado = (float) ($db->query("SELECT COALESCE(SUM(ValorCuota), 0) FROM pagos_cuotas")->fetchColumn() ?? 0);
    $carteraPendiente = max(0, $totalPrestado - $totalPagado);
    $recaudoActividades = (float) ($db->query("SELECT COALESCE(SUM(TotalRecaudo), 0) FROM actividades")->fetchColumn() ?? 0);

    $sqlCartera = "
        SELECT
            a.idAsociado,
            a.documento,
            CONCAT(COALESCE(a.nombres, ''), ' ', COALESCE(a.apellidos, '')) AS asociado,
            COALESCE(a.telefono, '') AS telefono,
            COALESCE(p_agg.num_creditos, 0) AS creditos,
            COALESCE(p_agg.total_prestado, 0) AS totalPrestado,
            COALESCE(pag_agg.total_pagado, 0) AS totalPagado,
            GREATEST(COALESCE(p_agg.total_prestado, 0) - COALESCE(pag_agg.total_pagado, 0), 0) AS saldoPendiente
        FROM asociados a
        LEFT JOIN (
            SELECT
                idAsociado,
                COUNT(*) AS num_creditos,
                COALESCE(SUM(ValorPrestamo), 0) AS total_prestado
            FROM prestamos
            GROUP BY idAsociado
        ) p_agg ON p_agg.idAsociado = a.idAsociado
        LEFT JOIN (
            SELECT
                pr.idAsociado,
                COALESCE(SUM(pc.ValorCuota), 0) AS total_pagado
            FROM pagos_cuotas pc
            INNER JOIN prestamos pr ON pr.idPrestamos = pc.idPrestamo
            GROUP BY pr.idAsociado
        ) pag_agg ON pag_agg.idAsociado = a.idAsociado
        ORDER BY a.nombres ASC, a.apellidos ASC
    ";
    $cartera = $db->query($sqlCartera)->fetchAll(PDO::FETCH_ASSOC);

    $sqlActividades = "
        SELECT
            act.idactividad,
            act.nombreActividad AS actividad,
            act.FechaEvento AS fecha,
            act.municipio,
            COALESCE(act.TotalRecaudo, 0) AS totalRecaudado,
            COALESCE(part_agg.participantes, 0) AS participantes,
            COALESCE(act.TotalRecaudo, 0) * 0.5 AS mitadClub,
            COALESCE(act.TotalRecaudo, 0) * 0.5 AS mitadDistribuida,
            CASE
                WHEN COALESCE(part_agg.participantes, 0) > 0
                    THEN (COALESCE(act.TotalRecaudo, 0) * 0.5) / part_agg.participantes
                ELSE 0
            END AS gananciaPorAsociado
        FROM actividades act
        LEFT JOIN (
            SELECT
                idActividad,
                COUNT(*) AS participantes
            FROM actividades_asociado
            GROUP BY idActividad
        ) part_agg ON part_agg.idActividad = act.idactividad
        ORDER BY act.FechaEvento DESC, act.idactividad DESC
    ";
    $actividades = $db->query($sqlActividades)->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "kpis" => [
            "totalPrestado" => $totalPrestado,
            "totalPagado" => $totalPagado,
            "carteraPendiente" => $carteraPendiente,
            "recaudoActividades" => $recaudoActividades,
        ],
        "cartera" => $cartera,
        "actividades" => $actividades,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "mensaje" => "Error en la base de datos: " . $e->getMessage(),
    ]);
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage(),
    ]);
}