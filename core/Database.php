<?php

class Database
{

    private $conn;
    private static $instance = null;
    function __construct($servername, $username, $password, $dbname, $port)
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "postaochamuyo"; // <-- cambialo por el nombre real
        $port = 3308;

        $this->conn = new Mysqli($servername, $username, $password, $dbname, $port) or die("Error de conexion " . mysqli_connect_error());
    }

    /*public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->getConnection();
    }*/

    public function getConnection()
    {
        return $this->conn;
    }
    public function query($sql)
    {
        $result = $this->conn->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function execute($sql)
    {
        $this->conn->query($sql);
    }

    function __destruct()
    {
        $this->conn->close();
    }
    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }
    public function getInsertId() {
        return $this->conn->insert_id;
    }

}