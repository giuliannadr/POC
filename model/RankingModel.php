<?php

class RankingModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function obtenerRanking()
    {
        $sql = "SELECT u.nombre_usuario, j.puntaje FROM jugador j 
                INNER JOIN usuario u ON u.id_usuario = j.id_usuario
                GROUP BY u.nombre_usuario, j.puntaje 
                ORDER BY j.puntaje desc LIMIT 10";
        $stmt = $this->database->prepare($sql);
        $stmt->execute();

        $result=$stmt->get_result();

        $jugadores = [];
        $posicion=1;

        while ($fila = $result->fetch_assoc()) {
            $fila["posicion"] = $posicion++;
            $jugadores[] = $fila;
        }

        return $jugadores;
    }

}