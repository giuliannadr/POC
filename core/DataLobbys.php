<?php
require_once("Session.php");
class DataLobbys{

    public function getLobbyJugData($usuario) {
       // $usuario = Session::get('usuario');

        $usuario['tiene_foto'] = !empty($usuario['foto_perfil']) && $usuario['foto_perfil'] !== 'img/sinperfil.png';

        $puntaje = 0;
        if (isset($usuario['puntaje']) && !is_array($usuario['puntaje']) && $usuario['puntaje'] !== null) {
            $puntaje = $usuario['puntaje'];
        }
        $usuario['puntaje_mostrar'] = $puntaje . "pts";


        define("BASE_URL", "/POC/");

        $botones = [
            ['texto' => 'Ver ranking', 'link' => BASE_URL . 'ranking/show'],
            ['texto' => 'Historial de partidas', 'link' => BASE_URL . 'historial/show'],
            ['texto' => 'Crear pregunta', 'link' => BASE_URL . 'crearPregunta/show']
        ];

        return [
            'usuario' => $usuario,
            'botones' => $botones
        ];
    }

    public function getLobbyEditorData()
    {
        $usuario = Session::get('usuario');

        // Botones en el nav segun el rol
        $botones = [
            ['texto' => 'Gestionar Preguntas', 'link' => '/POC/lobbyEditor/gestionarPreguntas'],
            ['texto' => 'Preguntas Reportadas', 'link' => '/POC/lobbyEditor/preguntasReportadas'],
            ['texto' => 'Preguntas Sugeridas', 'link' => '/POC/lobbyEditor/preguntasSugeridas'],
        ];
        return [
            'usuario' => $usuario,
            'botones' => $botones
        ];
    }

    public function getLobbyAdminData(){
        $usuario = Session::get('usuario');

        // Botones para el nav rol admin
        // Hay que ver como se haria lo de los filtros
        $botones = [
            ['texto' => 'Dia', 'link' => '/POC/lobbyAdmin/filtroDia'],
            ['texto' => 'Semana', 'link' => '/POC/lobbyAdmin/filtroSemana'],
            ['texto' => 'Mes', 'link' => '/POC/lobbyAdmin/filtroMes']
        ];
        return [
            'usuario' => $usuario,
            'botones' => $botones
        ];
    }



//    public function getLobbyEditorData($seccionActiva = null)
//    {
//        $usuario = Session::get('usuario');
//        $seccionActiva = $data['seccionActiva'] ?? null;
//
//
//        // Botones en el nav segun el rol
//        $botones = [
//            ['texto' => 'Gestionar Preguntas', 'link' => '/POC/LobbyEDITOR/gestionarPreguntas','activo' => $seccionActiva === 'gestionarPreguntas'],
//            ['texto' => 'Preguntas Reportadas', 'link' => '/editor/preguntasReportadas'],
//            ['texto' => 'Preguntas Sugeridas', 'link' => '/POC/LobbyEDITOR/preguntasSugeridas'],
//        ];
//
//        $preguntaModel=new PreguntasModel($this->db);
//
//        $preguntasActivas = $preguntaModel->contarActivas();
//        $reportesPendientes = $preguntaModel->contarReportesPendientes();
//        $sugerenciasPendientes= $preguntaModel->contarSugerenciasPendientes();
//
//
//        return [
//            'usuario' => $usuario,
//            'botones' => $botones,
//            'preguntasActivas' => $preguntasActivas,
//            'reportes' => $reportesPendientes,
//            'sugerenciasPendientes' => $sugerenciasPendientes
//        ];
//    }
//

}
