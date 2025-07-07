<?php

use Dompdf\Dompdf;

class AdminModel
{
    private $db;

    public function __construct( $db)
    {
        $this->db = $db;
    }

    /* public function obtenerEstadisticas(){
         $sql="SELECT (SELECT COUNT(*) FROM jugador WHERE activado = 1) as totalJugadores,
             (SELECT COUNT(*) FROM partida) as totalPartidas";
         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         return $stmt->get_result()->fetch_assoc();
     }*/

    public function obtenerEstadisticas($periodo)
    {
        $fechaInicio = $this->calcularFechaInicio($periodo);

        $sql = "SELECT 
        (SELECT COUNT(*) FROM jugador WHERE activado = 1 AND fecha_registro >= ?) AS totalJugadores,
        (SELECT COUNT(*) FROM partida WHERE fecha_inicio >= ?) AS totalPartidas";


        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $fechaInicio, $fechaInicio);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerEstadisticasPorEdad($periodo)
    {
        $fechaInicio = $this->calcularFechaInicio($periodo);

        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE 
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE()) < 18 THEN 1
                    ELSE 0
                END) AS menores,
                SUM(CASE 
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE()) BETWEEN 18 AND 64 THEN 1
                    ELSE 0
                END) AS adultos,
                SUM(CASE 
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE()) >= 65 THEN 1
                    ELSE 0
                END) AS jubilados
            FROM jugador
            WHERE fecha_nac IS NOT NULL 
                AND activado = 1
                AND fecha_registro >= ?
        ");
        $stmt->bind_param('s', $fechaInicio);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerEstadisticasPorPais($periodo)
    {
        $fechaInicio = $this->calcularFechaInicio($periodo);

        $stmt = $this->db->prepare("
            SELECT pais, COUNT(*) AS cantidad
            FROM jugador
            WHERE pais IS NOT NULL
                AND activado = 1
                AND fecha_registro >= ?
            GROUP BY pais
            ORDER BY cantidad DESC
        ");
        $stmt->bind_param('s', $fechaInicio);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $paises = [];
        while ($fila = $resultado->fetch_assoc()) {
            $paises[] = $fila;
        }
        return $paises;
    }

    public function obtenerEstadisticasPorGenero($periodo)
    {
        $fechaInicio = $this->calcularFechaInicio($periodo);

        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN sexo = 'Masculino' THEN 1 ELSE 0 END) AS hombres,
                SUM(CASE WHEN sexo = 'Femenino' THEN 1 ELSE 0 END) AS mujeres,
                SUM(CASE WHEN sexo NOT IN ('Masculino', 'Femenino') THEN 1 ELSE 0 END) AS otros
            FROM jugador
            WHERE activado = 1
                AND sexo IS NOT NULL
                AND fecha_registro >= ?
        ");
        $stmt->bind_param('s', $fechaInicio);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function calcularFechaInicio($periodo)
    {
        $hoy = new DateTime();

        switch ($periodo) {
            case 'dia':
                $hoy->setTime(0, 0);
                break;
            case 'semana':
                $hoy->modify('-6 days')->setTime(0, 0);
                break;
            case 'mes':
                $hoy->modify('-29 days')->setTime(0, 0);
                break;
            case 'anio':
                $hoy->modify('-1 year')->setTime(0, 0);
                break;
            default:
                $hoy->modify('-29 days')->setTime(0, 0);
                break;
        }

        return $hoy->format('Y-m-d H:i:s');
    }

    public function obtenerUsuariosNuevosPorPeriodo($periodo)
    {
        $fechaInicio = $this->calcularFechaInicio($periodo);
        $sql = "SELECT COUNT(*) AS usuariosNuevos FROM jugador WHERE activado = 1 AND fecha_registro >= ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $fechaInicio);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerUsuariosNuevosUltimos15Dias()
    {
        $fechaInicio = (new DateTime())->modify('-15 days')->setTime(0, 0)->format('Y-m-d H:i:s');

        $sql = "SELECT COUNT(*) AS usuariosNuevos FROM jugador WHERE activado = 1 AND fecha_registro >= ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $fechaInicio);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }



    /* private function obtenerFiltroPorPeriodo(string $periodo, string $columnaFecha = 'fecha_registro'): string {
         switch ($periodo) {
             case 'dia':
                 return "$columnaFecha >= CURDATE()";
             case 'semana':
                 return "$columnaFecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
             default:
                 return "$columnaFecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
         }
     }*/
}