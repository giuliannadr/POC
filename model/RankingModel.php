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

    public function obtenerPosicionUsuario($nombre_usuario)
    {
        $sql = "SELECT u.nombre_usuario, j.puntaje, 
                (SELECT COUNT(*) + 1 FROM jugador j2 
                 INNER JOIN usuario u2 ON u2.id_usuario = j2.id_usuario 
                 WHERE j2.puntaje > j.puntaje) as posicion
                FROM jugador j 
                INNER JOIN usuario u ON u.id_usuario = j.id_usuario
                WHERE u.nombre_usuario = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($fila = $result->fetch_assoc()) {
            return $fila["posicion"];
        }

        return 0; // si no se encuentra el usuario
    }

}