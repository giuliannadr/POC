<?php

foreach (glob("model/*.php") as $filename) {
    require_once $filename;
}
foreach (glob("controller/*.php") as $filename) {
    require_once $filename;
}
foreach (glob("core/*.php") as $filename) {
    require_once $filename;
}


include_once('vendor/mustache/src/Mustache/Autoloader.php');

class Configuration
{
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
        return new RegistroController(new UsuarioModel($this->getDatabase()),$this->getViewer());
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
}