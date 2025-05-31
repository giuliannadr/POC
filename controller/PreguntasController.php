<?php

class PreguntasController
{
    private $view;
    private $model;

    public function __construct($model, $view)
    {
        $this->view = $view;
        $this->model = $model;

    }

    public function crearPartida()
    {
        session_start(); // Necesario para acceder a $_SESSION


        if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'jugador') {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $usuario = $_SESSION['usuario'];

        $partida = $this->model->crearPartida($usuario['id_usuario']);

        $this->obtenerPreguntaNoRepetida($partida);


    }

    public function obtenerPreguntaNoRepetida($partida){
        $pregunta = $this->model->obtenerPreguntaNoRepetidaParaPartida($partida);

        if ($pregunta) {
            $this->jugar($pregunta, $partida);
        } else {
            // Ya respondió todas las preguntas activas
            $this->view->render('headerGrande', 'finPartida', [
                'mensaje' => '¡Felicidades! Respondiste todas las preguntas disponibles.'
            ]);
        }
    }

    public function jugar($pregunta, $partida) {

// Categoría
        $categoria = $pregunta['categoria']; // asumimos que hacés JOIN con Categoria

// Mapeo de categoría a estilos
        $mapaFondos = [
            'DEPORTE' => 'deporte',
            'ARTE' => 'arte',
            'HISTORIA' => 'historia',
            'CIENCIA' => 'ciencia',
            'GEOGRAFIA' => 'geografia',
            'ENTRETENIMIENTO' => 'entretenimiento',

        ];

        $mapaClasesPregunta = [
            'DEPORTE' => 'p-deporte',
            'ARTE' => 'p-arte',
            'HISTORIA' => 'p-historia',
            'CIENCIA' => 'p-ciencia',
            'GEOGRAFIA' => 'p-geografia',
            'ENTRETENIMIENTO' => 'p-entretenimiento',
        ];

        $mapaSVG = [
            'DEPORTE' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><circle cx="24" cy="24" r="21.5" fill="none" stroke="#010a7e" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path fill="none" stroke="#010a7e" stroke-linecap="round" stroke-linejoin="round" d="M2.558 25.58c7.417-13.344 27.41-17.984 42.182-7.267" stroke-width="2"/><path fill="none" stroke="#010a7e" stroke-linecap="round" stroke-linejoin="round" d="M25.966 2.59c-6.046 1.91-8.5 14.547 2.96 21.292c6.691 3.939 9.018 1.267 15.506 6.827" stroke-width="2"/><path fill="none" stroke="#010a7e" stroke-linecap="round" stroke-linejoin="round" d="M17.55 3.485c-3.932 8.108-2.562 29.798 17.523 38.948M6.449 11.579c5.8.126 7.085 6.666 5.632 12.592s-3.766 9.363-.71 17.231" stroke-width="2"/></svg>',
            'ARTE' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#010a7e" d="M4 22q-.825 0-1.412-.587T2 20V8q0-.825.588-1.412T4 6h4l4-4l4 4h4q.825 0 1.413.588T22 8v12q0 .825-.587 1.413T20 22zm0-2h16V8H4zm2-2h12l-3.75-5l-3 4L9 14zm11.5-5q.625 0 1.063-.437T19 11.5t-.437-1.062T17.5 10t-1.062.438T16 11.5t.438 1.063T17.5 13m-7.4-7h3.8L12 4.1zM4 20V8z"/></svg>',
            'HISTORIA' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="#010a7e" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="#010a7e"><path d="M19.146 22L17.393 9.98c-.142-.972-.213-1.457-.06-1.905c.318-.935 1.315-1.291 1.779-2.094c.068-.118.103-.251.17-.518l.567-2.22c.146-.572.22-.858.066-1.05C19.762 2 19.46 2 18.859 2h-1.064c-.67 0-.7.02-.948.629L16.34 3.87c-.249.61-.279.629-.948.629h-.514c-.688 0-.863-.127-1-.804l-.183-.892c-.138-.677-.313-.804-1-.804h-1.39c-.687 0-.862.127-1 .804l-.182.892c-.139.677-.313.804-1.001.804h-.514c-.67 0-.7-.02-.948-.629l-.507-1.24C6.904 2.019 6.874 2 6.205 2H5.141c-.602 0-.903 0-1.056.192c-.153.193-.08.479.066 1.05l.566 2.22c.069.268.103.401.171.52c.464.802 1.461 1.158 1.78 2.093c.152.448.081.933-.06 1.905L4.854 22"/><path d="m9 22l.608-3.039c.143-.718.215-1.076.407-1.342C10.494 16.96 11.262 17 12 17s1.507-.04 1.985.62c.192.264.264.623.407 1.341L15 22M3 22h18M7 8h10m-2 3h2M7 13h2"/></g></svg>',
            'CIENCIA' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#010a7e" fill-rule="evenodd" d="M3.211 19.7Q3.773 21 5.12 21h13.76q1.348 0 1.909-1.3t-.37-2.367L15.465 11V5h1.668a.92.92 0 0 0 .69-.283A1 1 0 0 0 18.096 4a1 1 0 0 0-.273-.717a.92.92 0 0 0-.69-.283H6.867a.92.92 0 0 0-.69.283A1 1 0 0 0 5.905 4q0 .434.273.717a.92.92 0 0 0 .69.283h1.668v6L3.58 17.333q-.93 1.066-.369 2.367m4.457-4.423h8.664l-2.792-3.544V5h-3.08v6.733z" clip-rule="evenodd"/></svg>',
            'GEOGRAFIA' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="#010a7e" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 6a3.5 3.5 0 1 0 0 7a3.5 3.5 0 0 0 0-7m-1.5 3.5a1.5 1.5 0 1 1 3 0a1.5 1.5 0 0 1-3 0"/><path d="M12 2C7.828 2 4.5 5.483 4.5 9.712c0 2.189 1.214 4.389 2.565 6.286c.923 1.296 2.01 2.578 2.957 3.694c.44.52.85 1.003 1.2 1.436l.778.964l.778-.964c.35-.433.76-.916 1.2-1.436c.947-1.116 2.034-2.398 2.957-3.694C18.286 14.1 19.5 11.9 19.5 9.712C19.5 5.483 16.172 2 12 2M6.5 9.712C6.5 6.527 8.992 4 12 4s5.5 2.527 5.5 5.712c0 1.519-.875 3.274-2.194 5.125c-.877 1.231-1.855 2.384-2.772 3.465q-.272.318-.534.63l-.534-.63c-.917-1.08-1.895-2.234-2.772-3.465C7.376 12.987 6.5 11.231 6.5 9.712"/></g></svg>',
//            'ENTRETENIMIENTO' => '<svg xmlns="http://www.w3.org/2000/svg" width="2048" height="2048" viewBox="0 0 2048 2048"><path fill="#010a7e" d="M1920 896v832q0 40-15 75t-41 61t-61 41t-75 15H320q-40 0-75-15t-61-41t-41-61t-15-75v-507q0-37 1-67t2-59t1-60t-4-67q-2-21-6-38t-8-34t-10-32t-11-38L22 541l1738-434l124 497L713 896zM681 508l-321 80l352 176l321-80zm543 129l322-81l-352-175l-322 80zm-1046 4l61 241l282-70zm1489-379l-282 71l342 171zm125 762H256v704q0 26 19 45t45 19h1408q26 0 45-19t19-45z"/></svg>',
        ];

        $pregunta['fondoPregunta'] = isset($mapaFondos[$categoria]) ? $mapaFondos[$categoria] : 'fondo-default';
        $pregunta['clasePregunta'] = isset($mapaClasesPregunta[$categoria]) ? $mapaClasesPregunta[$categoria] : 'p-default';
        $pregunta['svgCategoria'] = isset($mapaSVG[$categoria]) ? $mapaSVG[$categoria] : '';
        $puntaje = $this->model->getPuntajePartida($partida);

        $respuestas = $this->model->obtenerRespuestas($pregunta['id_pregunta']);

        shuffle($respuestas);

        $this->view->render('headerGrandePreguntas', 'pregunta', [
            'pregunta' => $pregunta,
            'fondoPregunta' => $pregunta['fondoPregunta'],
            'clasePregunta' => $pregunta['clasePregunta'],
            'svgCategoria' => $pregunta['svgCategoria'],
            'respuestas' => array_map(function ($r) {
                return [
                    'id_respuesta' => $r['id_respuesta'],       // id de la respuesta
                    'texto' => $r['texto']  // texto para mostrar
                ];
            }, $respuestas),
            'partida' => $partida,
            'puntaje' => $puntaje,
        ]);


    }

public function validarRespuesta()
{

    session_start(); // Necesario para acceder a $_SESSION


    if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'jugador') {
        $this->view->render('headerChico', 'homeLogin');
        exit;
    }

    $usuario = $_SESSION['usuario'];

    $idPartida = isset($_POST['id_partida']) ? $_POST['id_partida'] : null;
    $idRespuesta = isset($_POST['respuesta']) ? $_POST['respuesta'] : null;
    $id_jugador = $usuario['id_usuario'];
$esCorrecta = false;
if($this->model->validarRespuesta($idRespuesta, $idPartida, $id_jugador)) {
   $esCorrecta = true;
   $this->obtenerPreguntaNoRepetida($idPartida);
}else{
    header("Location: /POC/lobbyJug/show");
    exit;
}


}

}