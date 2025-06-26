<?php

class GestionarPreguntasController
{
    private $view;
    private $model;

    public function __construct($view, $model){
        $this->view = $view;
        $this->model = $model;
    }

    public function show(){

        $preguntas=$this->model->obtenerTodasPreguntas();
        $this->view->render("headerAdminEditor","gestionarPreguntasEditor",['preguntas'=>$preguntas]);
    }
}