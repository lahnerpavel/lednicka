<?php

session_start();
 
// Nastavení interního kódování pro funkce pro práci s řetězci
mb_internal_encoding("UTF-8");

// Automatické načítání tříd controllerů a modelů
function autoloadFunkce($className)
{
	// Končí název třídy řetězcem "Controller" ?
    if (preg_match('/Controller$/', $className))
        require("controllers/" . $className . ".php");
    else
        require("models/" . $className . ".php");
}

spl_autoload_register("autoloadFunkce");

// Připojení k databázi
Db::join("localhost", "root", "", "fridge");

// Vytvoření routeru a zpracování parametrů od uživatele z URL
$router = new RouterController();
$router->process(array($_SERVER['REQUEST_URI']));

// Vyrenderování šablony
$router->displayView();