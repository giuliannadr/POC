<?php
require_once("core/Session.php");
require_once("core/DataLobbys.php");
class LobbyJugController
{
    private $view;
    private $model;

    public function __construct($model,$view)
    {
        $this->view = $view;
        $this->model = $model;
    }

    public function show()
    {



        if (!Session::exists('usuario') || Session::get('tipo') !== 'jugador')  {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $usuario = Session::get('usuario');

        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyJugData($usuario);

        $this->view->render('headerGrande', 'lobbyJug', $data);
    }

}