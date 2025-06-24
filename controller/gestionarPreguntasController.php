<?php

class gestionarPreguntasController
{
    private $view;
    private $model;

    public function __construct($view, $model){
        $this->view = $view;
        $this->model = $model;
    }

    public function show(){

        $preguntas=$this->model->obtenerTodasPreguntas();
        $this->view->render("headerGrande","gestionarPreguntasEditor",['preguntas'=>$preguntas]);
    }
}