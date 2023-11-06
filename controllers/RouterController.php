<?php

class RouterController extends Controller
{
	// Instance controlleru
	protected $controller;
	protected $controlersURL = array(
		'cs' => array( 'product'=>'projekty' ),
		'cs' => array(),
		'cs' => array()
	);
	
	// Metoda převede pomlčkovou variantu controlleru na název třídy
	protected function pomlckyDoVelbloudiNotace($text)
	{
		$veta = str_replace('-', ' ', $text);
		$veta = ucwords($veta);
		$veta = str_replace(' ', '', $veta);
		return $veta;
	}
	
	// Naparsuje URL adresu podle lomítek a vrátí pole parametrů
	protected function parsujURL($url)
	{
		// Naparsuje jednotlivé části URL adresy do asociativního pole
        $naparsovanaURL = parse_url($url);
		// Odstranění počátečního lomítka
		$naparsovanaURL["path"] = ltrim($naparsovanaURL["path"], "/");
		// Odstranění bílých znaků kolem adresy
		$naparsovanaURL["path"] = trim($naparsovanaURL["path"]);
		// Rozbití řetězce podle lomítek
		$rozdelenaCesta = explode("/", $naparsovanaURL["path"]);
		return $rozdelenaCesta;
	}

	public function getTitle()
	{
		if ( $_SERVER['REQUEST_URI'] == '/' )
			return $this->head['title'] .' - '. $this->controller->head['title'];
		else
			return $this->controller->head['title'] .' - '. $this->head['title'];
	}

    // Naparsování URL adresy a vytvoření příslušného controlleru
    public function process($parametry)
    {

    		$this->head['title'] = $this->web['name'];
    		$this->template = 'rozlozeni';


    		
    		$naparsovanaURL = $this->parsujURL($parametry[0]);

			if (empty($naparsovanaURL[0]))
			{
				$controllerName = 'HomeController';
			}
			else
			{
					
				// kontroler je 1. parametr URL
				$controllerName = $this->pomlckyDoVelbloudiNotace(array_shift($naparsovanaURL)) . 'Controller';

			}
			
			if (file_exists('controllers/' . $controllerName . '.php'))
				$this->controller = new $controllerName;
			else
				$this->controller = new ErrorController;
				/*$this->presmeruj('chyba');*/
			
			// Volání controlleru
	        $this->controller->process($naparsovanaURL);
			
			// Nastavení proměnných pro šablonu
			$this->data['title'] = $this->getTitle();
			$this->data['description'] = $this->controller->head['description'];
			$this->data['keywords'] = $this->controller->head['keywords'];
			$this->data['zpravy'] = $this->vratZpravy();
			// Nastavení hlavní šablony
			$this->template = 'body';

			//$this->data['uzivatel'] = $spravceUzivatelu->vratUzivatele();
			//unset($this->data['uzivatel']['password']);
			//$this->data['uzivatel']['gravatar'] = $spravceUzivatelu->getGravatar('pavel@lahner.cz');

			$this->data['controller'] = $controllerName;



    }

}