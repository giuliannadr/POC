<?php

require_once("core/Session.php");
require_once("core/DataLobbys.php");

class HistorialController
{
    private $view;
    private $model;


    public function __construct($model, $view)
    {
        $this->view = $view;
        $this->model = $model;

    }

    public function show(){

        $usuario = Session::get('usuario');
        $historial = $this->model->getHistorial($usuario['id_usuario']);
        $this->view->render('headerGrandeSinBotones', 'historial', ['historial' => $historial]);
    }


}
