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

    /*
    public function show()
    {

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
*/


    public function show()
    {

        $usuario = Session::get('usuario');

        if ($usuario && isset($usuario['id_usuario'])) {
            $id_usuario = $usuario['id_usuario'];
        } else {
            $id_usuario = null; // o manejar error si no existe
        }

        $usuarioActualizado = $this->model->obtenerDatosPerfil($id_usuario);

        $usuarioActualizado['id_usuario'] = $id_usuario;

        $usuarioActualizado['puntaje'] = $this->model->obtenerPuntaje($id_usuario);

        Session::set('usuario', $usuarioActualizado);

        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyJugData($usuarioActualizado);
        $this->view->render('headerGrande', 'lobbyJug', $data);
    }


}