<?php

class Email
{

    private $mail;
    private $password;

    function __construct($mail, $password)
    {
      $this->mail = $mail;
       $this->password = $password;
    }
public function getMail(){
        return $this->mail;
}
public function getPassword(){
        return $this->password;
}
}