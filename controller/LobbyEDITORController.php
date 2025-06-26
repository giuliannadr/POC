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
}
