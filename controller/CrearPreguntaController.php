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

 public function show(){
     $this->view->render('headerGrandeSinBotones', 'crearPregunta');
 }


}


