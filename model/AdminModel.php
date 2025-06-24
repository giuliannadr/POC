<?php
use Dompdf\Dompdf;
class AdminModel
{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    public function obtenerEstadisticas(){
        $sql="SELECT (SELECT COUNT(*) FROM jugador WHERE activado = 1) as totalJugadores,
            (SELECT COUNT(*) FROM partida) as totalPartidas";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerEstadisticasPorEdad(){


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
        WHERE fecha_nac IS NOT NULL AND activado = 1
    ");
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerEstadisticasPorPais(){
        $stmt = $this->db->prepare("SELECT pais, COUNT(*) AS cantidad
        FROM jugador
        WHERE pais IS NOT NULL AND activado = 1
        GROUP BY pais
        ORDER BY cantidad DESC
");
        $stmt->execute();
        $resultado= $stmt->get_result();
        $paises=[];

        while($fila=$resultado->fetch_assoc()){
            $paises[]=$fila;
        }
        return $paises;
    }

    public function obtenerEstadisticasPorGenero(){

        $stmt = $this->db->prepare("SELECT 
            SUM(CASE WHEN sexo = 'Masculino' THEN 1 ELSE 0 END) AS hombres,
            SUM(CASE WHEN sexo = 'Femenino' THEN 1 ELSE 0 END) AS mujeres,
            SUM(CASE WHEN sexo NOT IN ('Masculino', 'Femenino') THEN 1 ELSE 0 END) AS otros
        FROM jugador
        WHERE activado = 1 AND sexo IS NOT NULL ");
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