<?php
require_once("core/Session.php");
require_once("core/DataLobbys.php");

class LobbyJugController
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
        if (!Session::exists('usuario') || Session::get('tipo') !== 'jugador') {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }
        $usuario = Session::get('usuario');

        if ($usuario && isset($usuario['id_usuario'])) {
            $id_usuario = $usuario['id_usuario'];
        } else {
            $id_usuario = null; // o manejar error si no existe
        }


        $usuario['puntaje'] = $this->model->obtenerPuntaje($id_usuario);
        Session::set('usuario', $usuario);

        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyJugData($usuario);
        $this->view->render('headerGrande', 'lobbyJug', $data);
    }

}