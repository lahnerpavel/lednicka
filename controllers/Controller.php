<?php

abstract class Controller
{

	// Proměnné hodnoty webu
	public $web = array(
		'name' => 'Lednička',
		'url' => '/',
		'adminurl' => '/admin'
	);

	// Pole, jehož indexy jsou poté viditelné v šabloně jako běžné proměnné
    protected $data = array();
	// Název šablony bez přípony
    protected $template = "";
	// Hlavička HTML stránky
	public $head = array('title' => 'Lednička', 'description' => 'Správce potravin.', 'keywords' => 'food manager, správce potravin');



	// Ošetří proměnnou pro výpis do HTML stránky
	private function osetri($x = null)
	{
		if (!isset($x))
			return null;
		elseif (is_string($x))
			return htmlspecialchars($x, ENT_QUOTES);
		elseif (is_array($x))
		{
			foreach($x as $k => $v)
			{
				$x[$k] = $this->osetri($v);
			}
			return $x;
		}
		else
			return $x;
	}

	// Vyrenderuje pohled
    public function displayView()
    {
        if ($this->template)
        {
        	// Vloží proměnou web do dat
        	$this->data['web'] = $this->web;

            extract($this->osetri($this->data));
			extract($this->data, EXTR_PREFIX_ALL, "");
            require("templates/" . $this->template . ".phtml");
        }
    }

	// Přidá zprávu pro uživatele
	public function pridejZpravu($zprava)
	{
		if (isset($_SESSION['zpravy']))
			$_SESSION['zpravy'][] = $zprava;
		else
			$_SESSION['zpravy'] = array($zprava);
	}

	// Vrátí zprávy pro uživatele
	public function vratZpravy()
	{
		if (isset($_SESSION['zpravy']))
		{
			$zpravy = $_SESSION['zpravy'];
			unset($_SESSION['zpravy']);
			return $zpravy;
		}
		else
			return array();
	}

	// Přesměruje na dané URL
	public function redirect($url, $type = NULL)
	{
		if ($type==301) header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url");
		header("Connection: close");
        exit;
	}
	public function presmeruj($url, $type = NULL)
	{
		$this->redirect($url, $type);
	}

	// Ověří, zda je přihlášený uživatel, případně přesměruje na login
	public function overUzivatele($admin = false)
	{
		$spravceUzivatelu = new SpravceUzivatelu();
		$uzivatel = $spravceUzivatelu->vratUzivatele();
		if (!$uzivatel || ($admin && !$uzivatel['admin']))
		{
			$this->pridejZpravu('Nedostatečná oprávnění.');
			$this->presmeruj('prihlaseni');
		}
	}

	public function overPristup($admin = false)
	{
		$spravceUzivatelu = new SpravceUzivatelu();
		$uzivatel = $spravceUzivatelu->vratUzivatele();

		if ($uzivatel)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	public function shortDescription($description)
	{
		$description = explode('</p>', $description);
		$description = strip_tags($description[0]);
		$poc = 0;
		$desc = '';
		if ( strlen ( $description ) > 160 ) {
			while(($desc!=' ')&&($desc!='.')&&($desc!=',')){ $poc++; $desc=substr($description, 150+$poc, 1); };
			$description=substr($description, 0, 150+$poc).'…';
		};
    	return $description;
	}

	public function showImage($image)
	{
		if ( file_exists( ltrim( $image, '/') ) && $image != NULL )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}

	}

	public function getImage($image)
	{
		if ( file_exists( ltrim( $image, '/') ) && $image != NULL )
		{
			return $image;
		}
		else
		{
			return '/images/altimage.jpg';
		}

	}

	public function showValue($value)
	{
		if ( $value != '' && $value != NULL )
		{
			return TRUE;
		}

	}

	// Hlavní metoda controlleru
    abstract function process($parametry);

}