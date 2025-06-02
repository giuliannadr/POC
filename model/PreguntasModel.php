<?php

class PreguntasModel
{
    private $database;



    public function __construct($database )
    {
        $this->database = $database;


    }

    public function crearPartida($idJugador){
        $sql = "INSERT INTO Partida (id_jugador) 
                VALUES (?)";
        $stmt = $this->database->prepare($sql);
        if ($stmt === false) {
            die("Error al preparar consulta Partida: " . $this->database->error);
        }
        $stmt->bind_param("i", $idJugador);
        $stmt->execute();
        $idPartida = $this->database->getInsertId();
        $stmt->close();
        return $idPartida;
    }

//    public function preguntaNoRepetida($id_pregunta, $id_partida){
//        $stmt = $this->database->prepare("SELECT p.id_pregunta, p.id_partida
//                FROM PreguntaPartida p
//                WHERE p.id_pregunta = ? and p.id_partida = ?");
//        $stmt->bind_param("ii", $id_pregunta, $id_partida);
//        $stmt->execute();
//
//        $res = $stmt->get_result();
//        $stmt->close();
//
//        return $res->num_rows === 0;
//    }

    public function obtenerPreguntaNoRepetidaParaPartida($id_partida) {
        $sql = "SELECT p.id_pregunta, p.enunciado, c.nombre AS categoria
            FROM Pregunta p
            JOIN Categoria c ON p.id_categoria = c.id_categoria
            WHERE p.estado_pregunta = 'activa'
              AND p.id_pregunta NOT IN (
                  SELECT id_pregunta
                  FROM PreguntaPartida
                  WHERE id_partida = ?
              )
            ORDER BY RAND()
            LIMIT 1";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_partida);
        $stmt->execute();

        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }




    public function obtenerRespuestas($idPregunta) {
        $stmt = $this->database->prepare("SELECT texto, esCorrecta, id_respuesta FROM Respuesta WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function validarRespuesta($idRespuesta, $idPartida, $id_jugador) {
        // Obtener la pregunta asociada a la respuesta
        $stmt = $this->database->prepare("SELECT r.esCorrecta, r.id_pregunta FROM Respuesta r WHERE r.id_respuesta = ?");
        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            return false;
        }

        $fila = $res->fetch_assoc();

        // Antes de insertar, verificar si ya existe respuesta para esta pregunta y partida
        $stmtCheck = $this->database->prepare("SELECT 1 FROM RespuestaJugador WHERE id_partida = ? AND id_pregunta = ?");
        $stmtCheck->bind_param("ii", $idPartida, $fila['id_pregunta']);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();

        if ($resCheck->num_rows > 0) {
            // Ya hay respuesta registrada, no insertar duplicado
            return null; // o false o algún código para indicar ya respondido
        }
        $stmtCheck->close();

        $esCorrecta = (bool)$fila['esCorrecta'];
        $puntaje = $esCorrecta ? 100 : 0;
        $correctaInt = $esCorrecta ? 1 : 0;

        // Insertar respuesta
        $sql = "INSERT INTO RespuestaJugador (id_partida, id_jugador, id_pregunta, id_respuesta_elegida, correcta, puntaje) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        if ($stmt === false) {
            die("Error al preparar consulta Partida: " . $this->database->error);
        }
        $stmt->bind_param("iiiiii", $idPartida, $id_jugador, $fila['id_pregunta'], $idRespuesta, $correctaInt, $puntaje);
        $stmt->execute();
        $stmt->close();

        // Actualizar puntaje en partida
        $stmt = $this->database->prepare("UPDATE partida SET puntaje = puntaje + ? WHERE id_partida = ?");
        $stmt->bind_param("ii", $puntaje, $idPartida);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->database->prepare("UPDATE Jugador SET puntaje = puntaje + ? WHERE id_usuario = ?");
        $stmt->bind_param("ii", $puntaje, $id_jugador);
        $stmt->execute();
        $stmt->close();

        return $esCorrecta;
    }


    public function getPuntajePartida($id_partida){
        $sql = "SELECT puntaje FROM partida WHERE id_partida = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_partida);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($fila = $res->fetch_assoc()) {
            return (int)$fila['puntaje']; // devuelvo el valor directo y casteado a entero
        } else {
            return 0; // o null, o lanzar error si no existe partida
        }
    }

}

