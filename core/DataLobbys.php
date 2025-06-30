<?php
require_once("Session.php");
class DataLobbys{

    public function getLobbyJugData($usuario) {
        $usuario = Session::get('usuario');

        $usuario['tiene_foto'] = !empty($usuario['foto_perfil']);

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
//    public function getLobbyAdminData(){
//        $usuario = Session::get('usuario');
//        $periodo = $_GET['periodo'] ?? 'mes';
//        // Botones para el nav rol admin
//        // Hay que ver como se haria lo de los filtros
//        $botones = [
//            ['texto' => 'Dia', 'link' => '/POC/admin/dashboard?periodo=dia'],
//            ['texto' => 'Semana', 'link' => '/POC/admin/dashboard?periodo=semana'],
//            ['texto' => 'Mes', 'link' => '/POC/admin/dashboard?periodo=mes']
//        ];
//
//        $adminModel=new AdminModel($this->db);
//        $datos=$adminModel->obtenerEstadisticas($periodo);
//        $estadisticasPorEdad=$adminModel->obtenerEstadisticasPorEdad();
//        $usuariosPorPais=$adminModel->obtenerEstadisticasPorPais();
//        $total = $estadisticasPorEdad['menores'] + $estadisticasPorEdad['adultos'] + $estadisticasPorEdad['jubilados'];
//
//        $porcentajesEdad = [
//            'porcentajeMenores' => $total > 0 ? round(($estadisticasPorEdad['menores'] / $total) * 100, 1) : 0,
//            'porcentajeAdultos' => $total > 0 ? round(($estadisticasPorEdad['adultos'] / $total) * 100, 1) : 0,
//            'porcentajeJubilados' => $total > 0 ? round(($estadisticasPorEdad['jubilados'] / $total) * 100, 1) : 0
//        ];
//
//        $datosGenero = $adminModel->obtenerEstadisticasPorGenero();
//
//        $totalGenero = $datosGenero['hombres'] + $datosGenero['mujeres'] + $datosGenero['otros'];
//
//        $porcentajeHombres = $totalGenero > 0 ? round(($datosGenero['hombres'] / $totalGenero) * 100, 1) : 0;
//        $porcentajeMujeres = $totalGenero > 0 ? round(($datosGenero['mujeres'] / $totalGenero) * 100, 1) : 0;
//        $porcentajeOtros   = $totalGenero > 0 ? round(($datosGenero['otros'] / $totalGenero) * 100, 1) : 0;
//
//
//        return [
//            'usuario' => $usuario,
//            'botones' => $botones,
//            'totalJugadores' => $datos['totalJugadores'],
//            'totalPartidas'=> $datos['totalPartidas'],
//            'menores'=>$estadisticasPorEdad['menores'],
//            'adultos'=>$estadisticasPorEdad['adultos'],
//            'jubilados'=>$estadisticasPorEdad['jubilados'],
//            'usuariosPorPais'=>$usuariosPorPais,
//            'porcentajeMenores' => $porcentajesEdad['porcentajeMenores'],
//            'porcentajeAdultos' => $porcentajesEdad['porcentajeAdultos'],
//            'porcentajeJubilados' => $porcentajesEdad['porcentajeJubilados'],
//            'hombres' => $datosGenero['hombres'],
//            'mujeres' => $datosGenero['mujeres'],
//            'otros' => $datosGenero['otros'],
//            'porcentajeHombres' => $porcentajeHombres,
//            'porcentajeMujeres' => $porcentajeMujeres,
//            'porcentajeOtros' => $porcentajeOtros
//
//        ];
//    }
}
