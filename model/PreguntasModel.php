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


    public function obtenerPreguntaNoRepetidaPorDificultad($id_jugador) {
        // 1. Obtener dificultad actual del jugador Y total respondidas
        $sqlJugador = "SELECT dificultad, cantPreguntas FROM jugador WHERE id_usuario = ?";
        $stmtJug = $this->database->prepare($sqlJugador);
        $stmtJug->bind_param("i", $id_jugador);
        $stmtJug->execute();
        $resJug = $stmtJug->get_result();
        $jugador = $resJug->fetch_assoc();
        $stmtJug->close();

        $cantRespondidas = (int)$jugador['cantPreguntas'];
        $dificultadJugador = $cantRespondidas >= 10
            ? $this->obtenerDificultadJugador($id_jugador) // Usa valor real si tiene suficiente data
            : 0; // Si no, se considera como "jugador nuevo"

        $usaDificultadJugador = $cantRespondidas >= 10;

        // 2. Contar total preguntas activas
        $sqlTotalPreguntas = "SELECT COUNT(*) AS total FROM Pregunta WHERE estado_pregunta = 'activa'";
        $stmtTotal = $this->database->prepare($sqlTotalPreguntas);
        $stmtTotal->execute();
        $resTotal = $stmtTotal->get_result();
        $filaTotal = $resTotal->fetch_assoc();
        $totalPreguntas = (int)$filaTotal['total'];
        $stmtTotal->close();


        // 3. Contar preguntas respondidas por el jugador
        $sqlResp = "SELECT COUNT(DISTINCT id_pregunta) AS respondidas FROM jugadorRespondePregunta WHERE id_jugador = ?";
        $stmtResp = $this->database->prepare($sqlResp);
        $stmtResp->bind_param("i", $id_jugador);
        $stmtResp->execute();
        $resResp = $stmtResp->get_result();
        $filaResp = $resResp->fetch_assoc();
        $respondidas = (int)$filaResp['respondidas'];
        $stmtResp->close();

        // 4. Reset si ya respondió todas
        if ($respondidas >= $totalPreguntas) {
            $sqlBorrar = "DELETE FROM jugadorRespondePregunta WHERE id_jugador = ?";
            $stmtBorrar = $this->database->prepare($sqlBorrar);
            $stmtBorrar->bind_param("i", $id_jugador);
            $stmtBorrar->execute();
            $stmtBorrar->close();
            $respondidas = 0;
        }

        // 5. Elegir consulta según experiencia del jugador
        if ($usaDificultadJugador) {
            // Rango +/- 5%
            $minDif = max(0, $dificultadJugador - 5);
            $maxDif = min(100, $dificultadJugador + 5);

            // Pregunta dentro del rango y que haya sido respondida >= 10 veces
            $sqlPregunta = "SELECT p.id_pregunta, p.enunciado, c.nombre AS categoria, p.dificultad
                        FROM Pregunta p
                        JOIN Categoria c ON p.id_categoria = c.id_categoria
                        WHERE p.estado_pregunta = 'activa'
                          AND p.dificultad BETWEEN ? AND ?
                          AND p.cantJugadores >= 10
                          AND p.id_pregunta NOT IN (
                              SELECT id_pregunta FROM jugadorRespondePregunta WHERE id_jugador = ?
                          )
                        ORDER BY RAND()
                        LIMIT 1";
            $stmtPregunta = $this->database->prepare($sqlPregunta);
            $stmtPregunta->bind_param("iii", $minDif, $maxDif, $id_jugador);
        } else {
            // Jugador novato: solo preguntas fáciles y con poco historial
            $sqlPregunta = "SELECT p.id_pregunta, p.enunciado, c.nombre AS categoria, p.dificultad
                        FROM Pregunta p
                        JOIN Categoria c ON p.id_categoria = c.id_categoria
                        WHERE p.estado_pregunta = 'activa'
                          AND p.dificultad <= 30
                          AND p.cantJugadores < 10
                          AND p.id_pregunta NOT IN (
                              SELECT id_pregunta FROM jugadorRespondePregunta WHERE id_jugador = ?
                          )
                        ORDER BY RAND()
                        LIMIT 1";
            $stmtPregunta = $this->database->prepare($sqlPregunta);
            $stmtPregunta->bind_param("i", $id_jugador);
        }

        $stmtPregunta->execute();
        $resPregunta = $stmtPregunta->get_result();
        $pregunta = $resPregunta->fetch_assoc();
        $stmtPregunta->close();

        // Fallback si no encuentra ninguna
        if (!$pregunta) {
            $sqlFallback = "SELECT p.id_pregunta, p.enunciado, c.nombre AS categoria, p.dificultad
                        FROM Pregunta p
                        JOIN Categoria c ON p.id_categoria = c.id_categoria
                        WHERE p.estado_pregunta = 'activa'
                          AND p.id_pregunta NOT IN (
                              SELECT id_pregunta FROM jugadorRespondePregunta WHERE id_jugador = ?
                          )
                        ORDER BY RAND()
                        LIMIT 1";
            $stmtFallback = $this->database->prepare($sqlFallback);
            $stmtFallback->bind_param("i", $id_jugador);
            $stmtFallback->execute();
            $resFallback = $stmtFallback->get_result();
            $pregunta = $resFallback->fetch_assoc();
            $stmtFallback->close();
        }

        // Actualizar dificultad real de la pregunta
        if ($pregunta) {
            $this->obtenerDificultadPregunta($pregunta['id_pregunta']);
        }

        return $pregunta;
    }



    public function obtenerRespuestas($idPregunta) {
        $stmt = $this->database->prepare("SELECT texto, esCorrecta, id_respuesta FROM Respuesta WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $res = $stmt->get_result();
        $resultados = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $resultados;
    }

    public function validarRespuesta($idRespuesta, $idPartida, $id_jugador, $idPregunta) {
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

        $sql = "INSERT INTO jugadorRespondePregunta (id_pregunta, id_jugador) VALUES (?, ?)";
        $stmt = $this->database->prepare($sql);
        if ($stmt === false) {
            die("Error al preparar consulta jugadorRespondePregunta: " . $this->database->error);
        }
        $stmt->bind_param("ii", $idPregunta, $id_jugador);
        $stmt->execute();
        $stmt->close();

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

        $stmt = $this->database->prepare("UPDATE Jugador SET puntaje = puntaje + ?,
                      cantPreguntas = cantPreguntas + 1
                    WHERE id_usuario = ?");
        $stmt->bind_param("ii", $puntaje, $id_jugador);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->database->prepare("UPDATE Pregunta 
                      SET cantJugadores = cantJugadores + 1 
                     WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $stmt->close();
        if ($esCorrecta){
            $stmt = $this->database->prepare("UPDATE Pregunta 
                      SET cantRespuestasCorrectas = cantRespuestasCorrectas + 1 
                     WHERE id_pregunta = ?");
            $stmt->bind_param("i", $idPregunta);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->database->prepare("UPDATE Jugador 
                      SET cantRespuestasCorrectas = cantRespuestasCorrectas + 1 
                     WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_jugador);
            $stmt->execute();
            $stmt->close();

        }

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

    public function obtenerRespuestaCorrecta($idPregunta) {
        $sql = "SELECT r.id_respuesta 
            FROM Respuesta r 
            JOIN Pregunta p ON r.id_pregunta = p.id_pregunta 
            WHERE r.esCorrecta = 1 AND p.id_pregunta = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($fila = $res->fetch_assoc()) {
            return (int)$fila['id_respuesta'];
        }

        return null;
    }

    public function getPreguntaPorId($idPregunta)
    {
        $sql = "SELECT p.id_pregunta, p.enunciado, c.nombre AS categoria 
                FROM Pregunta p 
                    join Categoria c on p.id_categoria = c.id_categoria
                WHERE id_pregunta = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();

        $resultado = $stmt->get_result();
        if ($fila = $resultado->fetch_assoc()) {
            return $fila;
        }

        return null; // o podés manejarlo como error si no existe
    }

    public function obtenerDificultadJugador($id_jugador) {
        // Paso 2: Obtener historial previo guardado
        $sql = "SELECT cantPreguntas, cantRespuestasCorrectas FROM jugador WHERE id_usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_jugador);
        $stmt->execute();
        $res = $stmt->get_result();
        $resultado = $res->fetch_assoc();

        $total = (int) $resultado['cantPreguntas'];
        $correctas = (int) $resultado['cantRespuestasCorrectas'];

        // Evitar división por 0
        $porcentaje = $total > 0 ? round(100 * $correctas / $total) : 0;

        // Paso 3: Actualizar tabla jugador
        $updateSql = "UPDATE jugador 
                  SET dificultad = ? 
                  WHERE id_usuario = ?";
        $updateStmt = $this->database->prepare($updateSql);
        $updateStmt->bind_param("ii", $porcentaje, $id_jugador);
        $updateStmt->execute();

        return $porcentaje;
    }


    public function obtenerDificultadPregunta($id_pregunta) {

        $sql = "SELECT cantJugadores, cantRespuestasCorrectas FROM pregunta WHERE id_pregunta = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $res = $stmt->get_result();
        $resultado = $res->fetch_assoc();

        $total = (int) $resultado['cantJugadores'];
        $correctas = (int) $resultado['cantRespuestasCorrectas'];

        // Evitar división por 0
        $porcentaje = $total > 0 ? round(100 * $correctas / $total) : 0;

        // Paso 3: Actualizar tabla pregunta
        $updateSql = "UPDATE pregunta 
                  SET dificultad = ? 
                  WHERE id_pregunta = ?";
        $updateStmt = $this->database->prepare($updateSql);
        $updateStmt->bind_param("ii", $porcentaje, $id_pregunta);
        $updateStmt->execute();

        return $porcentaje;
    }



}

