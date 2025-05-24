<?php

class MustachePresenter{
    private $mustache;
    private $partialsPathLoader;

    public function __construct($partialsPathLoader){
        Mustache_Autoloader::register();
        $this->mustache = new Mustache_Engine(
            array(
            'partials_loader' => new Mustache_Loader_FilesystemLoader( $partialsPathLoader )
        ));
        $this->partialsPathLoader = $partialsPathLoader;
    }

    public function render($header, $contentFile , $data = array() ){
        echo  $this->generateHtml(  $this->partialsPathLoader . '/' . $header . ".mustache",$this->partialsPathLoader . '/' . $contentFile . "View.mustache" , $data);
    }

    public function generateHtml($header,$contentFile, $data = array()) {
        $contentAsString = file_get_contents($header);
        $contentAsString .= file_get_contents( $contentFile );
        return $this->mustache->render($contentAsString, $data);
    }
}