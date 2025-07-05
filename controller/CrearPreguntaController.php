<?php

require_once("core/Session.php");
require_once("core/DataLobbys.php");

class CrearPreguntaController
{
    private $view;
    private $model;


    public function __construct($model, $view)
    {
        $this->view = $view;
        $this->model = $model;

    }

    public function show()
    {
        $esEditor = Session::get('tipo') === 'editor';
        $data['esEditor'] = $esEditor;

        // Definir la acción del formulario según el tipo de usuario
        $data['action'] = $esEditor ? '/POC/lobbyEditor/crearPregunta' : '/POC/crearPregunta/crear';

        $this->view->render('headerGrandeSinBotones', 'crearPreguntas', $data);
    }

    public function crear()
    {

        $usuario = Session::get('usuario');
        $enunciado = $_POST['enunciado'];
        $categoria = $_POST['categoria'];
        $respuestas = $_POST['respuesta'];
        $correcta = $_POST['respuestaCorrecta'];

        $indexMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
        $textoRespuestaCorrecta = $respuestas[$indexMap[$correcta]];

        // Pasás todo a la función que guardará la pregunta en revisión
        $this->model->mandarPreguntaARevision($enunciado, $categoria, $respuestas, $indexMap[$correcta], $usuario['id_usuario'], $textoRespuestaCorrecta);

        $enviada = true;

        $botones = new DataLobbys();
        $lobbyJug = $botones->getLobbyJugData();
        $data = $lobbyJug;
        $data['enviada'] = $enviada;

        $this->view->render('headerGrande', 'lobbyJug', $data);
    }


}


