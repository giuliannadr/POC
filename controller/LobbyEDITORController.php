<?php
require_once("core/Session.php");
class LobbyEDITORController
{
    private $view;
    private $model;

    public function __construct($view,$model)
    {
        $this->view = $view;
        $this->model = $model;

    }

    public function show()
    {


        // Redirige si no hay usuario o si el tipo no es 'editor'


        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData();

        $this->view->render('headerAdminEditor', 'lobbyEDITOR', $data);
    }

    public function gestionarPreguntas() {

        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData('gestionarPreguntas'); // Trae lo mismo que usás en el método show()

        $data['seccionActiva'] = 'gestionarPreguntas';
        $busqueda = $_POST['buscarPregunta'] ?? null;


        if ($busqueda) {
            $data['preguntas'] = $this->model->buscarPreguntas($busqueda);
        } else {
            $data['preguntas'] = $this->model->obtenerTodasPreguntas();
        }
        $data['buscarPregunta'] = $busqueda;
        $this->view->render('headerAdminEditor', 'gestionarPreguntasEditor', $data);
    }

    public function editarPregunta() {
        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData('editarPregunta');
        $idPregunta= $_POST['id'] ?? null;
        $pregunta=$this->model->obtenerPreguntaCompleta($idPregunta);


        $data = [
            'id' => $pregunta['idpregunta'],
            'enunciado' => $pregunta['enunciado'],
            'categoriaDeporte' => $pregunta['categoria'] === 'Deporte',
            'categoriaHistoria' => $pregunta['categoria'] === 'Historia',
            'categoriaGeografia' => $pregunta['categoria'] === 'Geografía',
            'respuestas' => [
                ['letra' => 'A', 'texto' => $pregunta->respuestas[0]],
                ['letra' => 'B', 'texto' => $pregunta->respuestas[1]],
                ['letra' => 'C', 'texto' => $pregunta->respuestas[2]],
                ['letra' => 'D', 'texto' => $pregunta->respuestas[3]]
            ],
            'opciones' => [
                ['letra' => 'A', 'seleccionada' => $pregunta['respuesta'] === 'A'],
                ['letra' => 'B', 'seleccionada' => $pregunta['respuesta'] === 'B'],
                ['letra' => 'C', 'seleccionada' => $pregunta['respuesta'] === 'C'],
                ['letra' => 'D', 'seleccionada' => $pregunta['respuesta'] === 'D']
            ]
        ];

        $this->view->render('headerAdminEditor', 'editarPregunta', $data);
    }

    public function preguntasSugeridas()
    {
        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData('preguntasSugeridas'); // Trae lo mismo que usás en el método show()

        $data['seccionActiva'] = 'preguntasSugeridas';
        $buscar= $_POST['buscarPregunta'] ?? null;

        if($buscar){
            $data['preguntas']=$this->model->buscarSugerencia($buscar);
        }else {
            $data['preguntas'] = $this->model->obtenerSugeridas();
        }
        $this->view->render('headerAdminEditor', 'preguntasSugeridas', $data);
    }

    public function aprobarPregunta() {
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
             $idPregunta = $_POST["id"];
             $this->model->aprobar($idPregunta);
            $this->gestionarPreguntas();

        }
    }

    public function eliminarPregunta() {
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
            $idPregunta = $_POST["id"];
            $this->model->eliminar($idPregunta);
            $this->gestionarPreguntas();
        }
    }

    public function eliminarSugerencia() {
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
            $idSugerencia = $_POST["id"];
            $this->model->eliminarSugerencia($idSugerencia);
            $this->gestionarPreguntas();
        }
    }

    public function crearPregunta() {
        if($_SERVER["REQUEST_METHOD"]) {
            $usuario = Session::get('usuario');
            $enunciado = $_POST['enunciado'];
            $categoria = $_POST['categoria'];
            $respuestas = $_POST['respuesta'];
            $correcta = $_POST['respuestaCorrecta'];

            $indexMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
            $textoRespuestaCorrecta = $respuestas[$indexMap[$correcta]];

            // Pasás todo a la función que guardará la pregunta en revisión
            $this->model->crearPreguntaDesdeEditor($enunciado, $categoria, $respuestas, $indexMap[$correcta], $usuario['id_usuario'], $textoRespuestaCorrecta);

            $enviada = true;

            $botones = new DataLobbys();
            $lobbyeditor = $botones->getLobbyEditorData();
            $data = $lobbyeditor;
            $data['enviada'] = $enviada;

            $this->view->render('headerAdminEditor', 'gestionarPreguntasEditor', $data);
        }
    }
}
