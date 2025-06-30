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

        $preguntasActivas = $this->model->contarActivas();
        $preguntasReportadas = $this->model->contarReportesPendientes();
        $preguntasSugeridas = $this->model->contarSugerenciasPendientes();
        $dataLobby = new DataLobbys();
        $data = array_merge(
            $dataLobby->getLobbyEditorData(),
            [
                'preguntasActivas' => $preguntasActivas,
                'reportes' => $preguntasReportadas,
                'sugerenciasPendientes' => $preguntasSugeridas
            ]
        );
        $this->view->render('headerAdminEditor', 'lobbyEDITOR', $data);
    }

    public function gestionarPreguntas() {

        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData(); // Trae lo mismo que usás en el método show()


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
        $dataLobbyArray = $dataLobby->getLobbyEditorData('editarPregunta');

        $idPregunta = $_POST['id'] ?? null;

        if (!$idPregunta) {
            die("ID de pregunta no enviado");
        }

        $filas = $this->model->obtenerPreguntaCompleta($idPregunta);

        if (empty($filas)) {
            die("Pregunta no encontrada");
        }

        // Tomamos los datos generales de la primera fila
        $primeraFila = $filas[0];
        $id = $primeraFila['idpregunta'] ?? null;
        $enunciado = $primeraFila['enunciado'] ?? '';
        $categoria = $primeraFila['categoria'] ?? '';

        // Obtenemos respuestas (hasta 4) y detectamos cuál es la correcta
        $respuestas = [];
        $respuestaCorrecta = '';

        $letras = ['A', 'B', 'C', 'D'];
        foreach ($filas as $i => $fila) {
            $texto = $fila['respuesta'] ?? '';
            $respuestas[] = $texto;

            if ($fila['esCorrecta']) {
                $respuestaCorrecta = $letras[$i] ?? '';
            }
        }

        // Rellenar hasta 4 respuestas si faltan
        for ($i = count($respuestas); $i < 4; $i++) {
            $respuestas[] = '';
        }

        $datosPregunta = [
            'id' => $id,
            'enunciado' => $enunciado,
            // Comparar con mayúsculas exactas para que coincida con los values del template
            'categoriaHistoria' => strtoupper($categoria) === 'HISTORIA',
            'categoriaCiencia' => strtoupper($categoria) === 'CIENCIA',
            'categoriaDeporte' => strtoupper($categoria) === 'DEPORTE',
            'categoriaArte' => strtoupper($categoria) === 'ARTE',
            'categoriaEntretenimiento' => strtoupper($categoria) === 'ENTRETENIMIENTO',
            'categoriaGeografia' => strtoupper($categoria) === 'GEOGRAFIA',

            'respuestas' => [
                ['letra' => 'A', 'texto' => $respuestas[0]],
                ['letra' => 'B', 'texto' => $respuestas[1]],
                ['letra' => 'C', 'texto' => $respuestas[2]],
                ['letra' => 'D', 'texto' => $respuestas[3]],
            ],
            'opciones' => [
                ['letra' => 'A', 'seleccionada' => $respuestaCorrecta === 'A'],
                ['letra' => 'B', 'seleccionada' => $respuestaCorrecta === 'B'],
                ['letra' => 'C', 'seleccionada' => $respuestaCorrecta === 'C'],
                ['letra' => 'D', 'seleccionada' => $respuestaCorrecta === 'D'],
            ],
        ];


        // Combinar con datos del lobby
        $data = array_merge($dataLobbyArray, $datosPregunta);

        $this->view->render('headerAdminEditor', 'editarPregunta', $data);
    }




    public function preguntasSugeridas()
    {
        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData(); // Trae lo mismo que usás en el método show()

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

            $this->preguntasSugeridas();
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
            $data['mostrarPopupExito'] = true;  // o false si no quieres mostrar


            $this->view->render('headerAdminEditor', 'lobbyEDITOR', $data);
        }
    }

    public function preguntasReportadas()
    {
        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData();

        $data['seccionActiva'] = 'preguntasReportadas';
        $buscar = $_POST['buscarPregunta'] ?? null;

        if ($buscar) {
            // Si buscás por idpregunta, debe coincidir con el parámetro en buscarReporte
            $data['preguntas'] = $this->model->buscarReporte($buscar);
        } else {
            $data['preguntas'] = $this->model->obtenerReportes();
        }

        $this->view->render('headerAdminEditor', 'preguntasReportadas', $data);
    }

    public function eliminarReporte()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
            $idReporte = $_POST["id"];
            $this->model->eliminarReporte($idReporte);  // Aquí llamás a eliminarReporte, no eliminar
            $this->preguntasReportadas();
        }
    }

}
