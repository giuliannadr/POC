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

    public function editarPregunta()
    {
        $idPregunta = $_POST['id'] ?? null; // o $_GET['id'] si usas GET

        if (!$idPregunta) {
            die("ID de pregunta no enviado");
        }

        $filas = $this->model->obtenerPreguntaCompleta($idPregunta);
        $esSugerencia=false;

        if (empty($filas)) {
           $filas=$this->model->obtenerPreguntaSugeridaCompleta($idPregunta);
           $esSugerencia=true;
        }

        if(empty($filas)){
            die("Pregunta no encontrada");
        }

        $primeraFila = $filas[0];
        $id = $primeraFila['idpregunta'] ?? null;
        $enunciado = $primeraFila['enunciado'] ?? '';


        $mapaCategorias = [
            'HISTORIA' => 1,
            'GEOGRAFIA' => 2,
            'ARTE' => 3,
            'DEPORTES' => 4,
            'ENTRETENIMIENTO' => 5,
            'CIENCIA' => 6,
        ];

        $categoriaTexto = strtoupper(trim($primeraFila['categoria']));
        $categoria = $mapaCategorias[$categoriaTexto] ?? 0;


        // Obtener respuestas con sus IDs y cuál es correcta
        $respuestas = [];
        $respuestaCorrecta = '';

        $letras = ['A', 'B', 'C', 'D'];
        foreach ($filas as $i => $fila) {
            $respuestas[] = [
                'id_respuesta' => $fila['idrespuesta'] ?? null,
                'texto' => $fila['respuesta'] ?? ''
            ];
            if ($fila['esCorrecta']) {
                $respuestaCorrecta = $letras[$i] ?? '';
            }
        }

        // Completar hasta 4 respuestas (en caso de que haya menos)
        for ($i = count($respuestas); $i < 4; $i++) {
            $respuestas[] = [
                'id_respuesta' => null,
                'texto' => ''
            ];
        }

        $datosPregunta = [
            'id' => $id,
            'enunciado' => $enunciado,
            'categoria' => $categoria,
            'categoriaHistoria' => $categoria == 1 ? true : false,
            'categoriaGeografia' => $categoria == 2 ? true : false,
            'categoriaArte' => $categoria == 3 ? true : false,
            'categoriaDeporte' => $categoria == 4 ? true : false,
            'categoriaEntretenimiento' => $categoria == 5 ? true : false,
            'categoriaCiencia' => $categoria == 6 ? true : false,


            'respuestas' => $respuestas,
            'respuestaCorrecta' => $respuestaCorrecta,
            'opciones' => [
                ['letra' => 'A', 'seleccionada' => $respuestaCorrecta === 'A'],
                ['letra' => 'B', 'seleccionada' => $respuestaCorrecta === 'B'],
                ['letra' => 'C', 'seleccionada' => $respuestaCorrecta === 'C'],
                ['letra' => 'D', 'seleccionada' => $respuestaCorrecta === 'D'],
            ]
        ];

        // Combinar con datos del lobby o contexto general si usás
        $dataLobby = new DataLobbys();
        $dataLobbyArray = $dataLobby->getLobbyEditorData();

        $data = array_merge($dataLobbyArray, $datosPregunta);

        $this->view->render('headerAdminEditor', 'editarPregunta', $data);
    }

    public function guardarEdicionPregunta()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            die("Falta el ID de la pregunta.");
        }

        // === 1. Enunciado ===
        $enunciado = trim($_POST['enunciado'] ?? '');
        $enunciadoOriginal = trim($_POST['enunciado_original'] ?? '');

        if ($enunciado === '') {
            $enunciado = $enunciadoOriginal;
        }

        // === 2. Categoría ===
        $categoria = $_POST['categoria'] ?? '';
        $categoriaOriginal = $_POST['categoria_original'] ?? '';

        if ($categoria === '') {
            $categoria = $categoriaOriginal;
        }else {
            // Asegurar que sea entero (por seguridad)
            $categoria = (int) $categoria;
        }

        // === 3. Respuestas ===
        $respuestas = $_POST['respuesta'] ?? [];
        $respuestasOriginal = $_POST['respuesta_original'] ?? [];

        $respuestasFinales = [];

        for ($i = 0; $i < 4; $i++) {
            $r = trim($respuestas[$i] ?? '');
            $respuestasFinales[] = $r !== '' ? $r : ($respuestasOriginal[$i] ?? '');
        }

        // === 4. Respuesta correcta ===
        $respuestaCorrecta = $_POST['respuestaCorrecta'] ?? '';
        $respuestaCorrectaOriginal = $_POST['respuestaCorrecta_original'] ?? '';

        if (!in_array($respuestaCorrecta, ['A', 'B', 'C', 'D'])) {
            $respuestaCorrecta = $respuestaCorrectaOriginal;
        }

        // === 5. Guardar en base de datos ===
        // Primero actualizás enunciado y categoría
        $this->model->actualizarPregunta($id, $enunciado, $categoria);

        // Después actualizás las respuestas
        $idRespuestas = $_POST['id_respuesta'] ?? [];

        $this->model->desmarcarTodasRespuestasComoIncorrectas($id);
        foreach (['A', 'B', 'C', 'D'] as $i => $letra) {
            $texto = $respuestasFinales[$i];
            $esCorrecta = ($respuestaCorrecta === $letra) ? 1 : 0;
            $idRespuesta = $idRespuestas[$i] ?? null;

            if ($idRespuesta) {
                $this->model->actualizarRespuesta($id, $idRespuesta, $texto, $esCorrecta);
            } else {
                // Si no existe idRespuesta, insertá una nueva respuesta ligada a la pregunta
                $this->model->insertarRespuesta($id, $texto, $esCorrecta);
            }
        }

        $dataLobby = new DataLobbys();
        $dataLobbyArray = $dataLobby->getLobbyEditorData();

        $busqueda = $_POST['buscarPregunta'] ?? null;
        $preguntas = $busqueda ? $this->model->buscarPreguntas($busqueda) : $this->model->obtenerTodasPreguntas();

        $data = array_merge(
            $dataLobbyArray,
            [
                'seccionActiva' => 'gestionarPreguntas',
                'preguntas' => $preguntas,
                'buscarPregunta' => $busqueda,
                'editada' => true
            ]
        );


        // Redirigimos o mostramos mensaje de éxito
        $this->view->render('headerAdminEditor', 'gestionarPreguntasEditor', $data);
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
