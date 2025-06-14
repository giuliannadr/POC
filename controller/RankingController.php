<?php

class RankingController
{

    private $model;
    private $view;

    public function __construct($model, $view){
        $this->model = $model;
        $this->view = $view;
    }

    public function show(){
        $jugadores = $this->model->obtenerRanking();
        $this->view->render("headerGrandeSinBotones","ranking",["jugadores"=>$jugadores]);
    }
}