<?php
require_once("core/Session.php");
require_once("core/DataLobbys.php");
require_once("vendor/phpqrcode/qrlib.php");


class PerfilController
{
    private $model;
    private $view;

    public function __construct($model, $view)
    {
        $this->model = $model;
        $this->view = $view;
    }

    public function mostrar()
    {

        $usuario = Session::get('usuario'); // Esto devuelve el array completo (o null si no existe)


        if ($usuario && isset($usuario['id_usuario'])) {
            $id_usuario = $usuario['id_usuario'];
        } else {
            $id_usuario = null; // o manejar error si no existe
        }

        // Obtener datos del perfil
        $datos = $this->model->obtenerDatosPerfil($id_usuario);
        $datos['modo_edicion'] = false;

        if (empty($datos['foto_perfil']) || $datos['foto_perfil'] === 'img/sinperfil.png') {
            $datos['foto_perfil'] = false;
        }

        $this->view->render('headerPerfil', 'perfil', $datos);

    }

    public function editar()
    {
        $usuario = Session::get('usuario');

        if ($usuario && isset($usuario['id_usuario'])) {
            $id_usuario = $usuario['id_usuario'];
        } else {
            $id_usuario = null; // o manejar error si no existe
        }

        $datos = $this->model->obtenerDatosPerfil($id_usuario);

        if (empty($datos['foto_perfil']) || $datos['foto_perfil'] === 'img/sinperfil.png') {
            $datos['foto_perfil'] = false;
        }

        $datos['modo_edicion'] = true;

        // Marcar selección del sexo
        $sexo_normalizado = strtolower(trim($datos['sexo']));

        $datos['sexo_masculino'] = $sexo_normalizado === 'masculino';
        $datos['sexo_femenino'] = $sexo_normalizado === 'femenino';
        $datos['sexo_otro'] = $sexo_normalizado === 'prefiero no cargarlo';

        $this->view->render('headerPerfil', 'perfil', $datos);
    }


    public function guardar()
    {

        $usuario = Session::get('usuario');

        if ($usuario && isset($usuario['id_usuario'])) {
            $id_usuario = $usuario['id_usuario'];
        } else {
            $id_usuario = null; // o manejar error si no existe
        }
        // agarro los datos del formulario
        $datos = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'sexo' => $_POST['sexo'],
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'ciudad' => $_POST['ciudad'],
            'pais' => $_POST['pais']
        ];

        // Procesar imagen si se subió
        if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
            $tipo = $_FILES['profilePic']['type']; // ej. image/jpeg
            $contenido = file_get_contents($_FILES['profilePic']['tmp_name']);
            $base64 = 'data:' . $tipo . ';base64,' . base64_encode($contenido);
            $datos['foto_perfil'] = $base64;
        }

        // Guardar datos en la base de datos
        $datos_actualizados = $this->model->actualizarPerfil($id_usuario, $datos);

        // ACTUALIZAR DATOS EN SESIÓN
        $usuario_actual = Session::get('usuario');
        $usuario_actual = array_merge($usuario_actual, $datos_actualizados ?? []);
        Session::set('usuario', $usuario_actual);

        // redirige al perfil con los datos actualizados
        $this->mostrar();
    }

    public function generarQR() {

        $usuario = Session::get('usuario');


        $id = $usuario['nombre_usuario'];
        $url = "http://localhost/POC/perfil/mostrar/" . $id;

        ob_clean(); // limpia buffer de salida previa

        header('Content-Type: image/png');
        QRcode::png($url);
    }

    public function generarQRDeOtroUsuario($params)
    {
        ob_clean(); // limpia buffer de salida previa

        header('Content-Type: image/png');

        $url = "http://localhost/POC/ranking/verPerfil?nombre_usuario=" . urlencode($params['nombre_usuario']);

        QRcode::png($url);
    }


}
