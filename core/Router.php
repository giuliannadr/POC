<?php

class Router
{
    private $defaultController;
    private $defaultMethod;
    private $configuration;

    public function __construct($defaultController, $defaultMethod, $configuration)
    {
        $this->defaultController = $defaultController;
        $this->defaultMethod = $defaultMethod;
        $this->configuration = $configuration;
    }

    public function go($controllerName, $methodName)
    {
        // Si no recibo controlador o método, uso los defaults
        if (!$controllerName) {
            $controllerName = substr($this->defaultController, 3, -10);
            // Si defaultController es "getHomeController"
            // substr quita "get" y "Controller" para quedarme solo con "home"
        }

        if (!$methodName) {
            $methodName = $this->defaultMethod;
        }

        $controller = $this->getControllerFrom($controllerName);
        $this->executeMethodFromController($controller, $methodName);
    }


    private function getControllerFrom($controllerName)
    {
        $controllerName = 'get' . ucfirst($controllerName) . 'Controller';
        $validController = method_exists($this->configuration, $controllerName) ? $controllerName : $this->defaultController;
        return call_user_func(array($this->configuration, $validController));
    }

    private function executeMethodFromController($controller, $method)
    {
        $validMethod = method_exists($controller, $method) ? $method : $this->defaultMethod;

        // Check if the method accepts parameters
        $reflection = new ReflectionMethod($controller, $validMethod);

        if ($reflection->getNumberOfParameters() > 0) {
            // Pass $_GET as parameter if the method accepts parameters
            call_user_func(array($controller, $validMethod), $_GET);
        } else {
            // Call method without parameters
            call_user_func(array($controller, $validMethod));
        }
    }
}
