<?php

class ErrorController extends Controller
{
    public function process($parametry)
    {
		// Hlavička požadavku
		header("HTTP/1.0 404 Not Found");
		// Hlavička stránky
		$this->head['title'] = 'Chyba 404';
		// Nastavení šablony
		$this->template = 'chyba';
    }
}