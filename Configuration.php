<?php

require_once __DIR__ . '/vendor/autoload.php';

foreach (glob("model/*.php") as $filename) {
    require_once $filename;
}
foreach (glob("controller/*.php") as $filename) {
    require_once $filename;
}
foreach (glob("core/*.php") as $filename) {
    require_once $filename;
}


class Configuration
{
    private $db;
    private $email;



    public function __construct()
    {
        $this->db = $this->getDatabase();
        $this->email = $this->getEmail();
    }

    public function getDatabase()
    {
        $config = $this->getIniConfig();

        return new Database(
            $config['database']['server'],
            $config['database']['user'],
            $config['database']['pass'],
            $config['database']['dbname'],
            $config['database']['port']
        );
    }

    public function getEmail()
    {
        $config = $this->getIniConfig();

        return new Email(
            $config['email']['email'],
            $config['email']['password']
            
        );
    }


    public function getIniConfig()
    {
        return parse_ini_file("configuration/config.ini", true);
    }


    public function getHomeController()
    {
        return new HomeController($this->getViewer());
    }

    public function getRegistroController()
    {
        return new RegistroController(new JugadorModel($this->db, $this->email), $this->getViewer());
    }

    public function getLoginController()
    {
        return new LoginController(new UsuarioModel($this->db), $this->getViewer());
    }

    public function getLobbyEDITORController()
    {
        return new LobbyEDITORController($this->getViewer(), new PreguntasModel($this->db));
    }

    public function getLobbyADMController()
    {
        return new LobbyADMController($this->getViewer(), new AdminModel($this->db));
    }


    public function getRouter()
    {
        return new Router("getHomeController", "show", $this);
    }

    public function getViewer()
    {
        //return new FileView();
        return new MustachePresenter("view");
    }

    public function getPerfilController()
    {
        return new PerfilController(new JugadorModel($this->db, $this->email), $this->getViewer());
    }

    public function getLobbyJugController() {
        return new LobbyJugController(new JugadorModel($this->db, $this->email),$this->getViewer());
    }

    public function getPreguntasController(){
        return new PreguntasController(new PreguntasModel($this->db),$this->getViewer());
    }

    public function getRankingController(){
        return new RankingController(
            new RankingModel($this->db),
            $this->getViewer(),
            new JugadorModel($this->db, $this->email)
        );
    }

    public function getCrearPreguntaController(){
        return new CrearPreguntaController(new PreguntasModel($this->db),$this->getViewer());
    }
    public function getHistorialController(){
        return new HistorialController(new JugadorModel($this->db, $this->email),$this->getViewer());
    }

    public function getGestionarPreguntasController(){
        return new GestionarPreguntasController($this->getViewer(),new PreguntasModel($this->db));
    }
}