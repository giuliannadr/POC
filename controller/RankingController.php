<?php

class RankingController
{

    private $model;
    private $view;
    private $jugadorModel;

    public function __construct($model, $view, $jugadorModel){
        $this->model = $model;
        $this->view = $view;
        $this->jugadorModel = $jugadorModel;
    }

    public function show(){
        $jugadores = $this->model->obtenerRanking();
        $this->view->render("headerGrandeSinBotones","ranking",["jugadores"=>$jugadores]);
    }

    public function verPerfil($params){
        if(isset($params['nombre_usuario'])) {
            $nombre_usuario = $params['nombre_usuario'];

            // Obtener datos del perfil del jugador
            $datos = $this->jugadorModel->obtenerDatosPerfilPorUsuario($nombre_usuario);

            if($datos) {
                // Obtener número de partidas jugadas
                $partidas_jugadas = $this->jugadorModel->obtenerPartidasJugadas($nombre_usuario);

                // Preparar datos para la vista
                $datos['partidas_jugadas'] = $partidas_jugadas;
                $datos['modo_edicion'] = false;

                // Renderizar la vista
                $this->view->render("headerGrandeSinBotones", "perfilJugador", $datos);
            } else {
                // Si no se encuentra el jugador, redirigir al ranking
                header("Location: /POC/Ranking/show");
                exit;
            }
        } else {
            // Si no se proporciona nombre de usuario, redirigir al ranking
            header("Location: /POC/Ranking/show");
            exit;
        }
    }
}
