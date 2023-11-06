<?php

class FridgeController extends Controller
{

    public function calculateHalfLife($date_manufacture, $date_purchase, $date_min_expire, $date_expire) {

        $firstDate = $date_manufacture ? new DateTime($date_manufacture) : new DateTime($date_purchase);
    	$secondDate = $date_expire ? new DateTime($date_expire) : new DateTime($date_min_expire);
    
        $shelfLife = $firstDate->diff($secondDate);

        $halfLife = clone $firstDate;
        
        // Výpočet polovičního času (polovina celkového rozdílu v čase)
        $halfLifeInterval = ceil($shelfLife->days / 2);
        $halfLife->add(new DateInterval("P{$halfLifeInterval}D"));
        
        return $halfLife->format('Y-m-d');

    }

    public function process($parametry)
	{

		// Hlavička stránky
		$this->head = array(
			'title' => 'Obsah ledničky',
			'description' => '',
			'keywords' => '',
		);

        $this->template = 'fridge';

        $foodManager = new FoodManager();



        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"]) && isset($_POST["date_purchase"]) && isset($_POST["date_min_expire"])) {

            // Získání dat z formuláře
            $tablekeys = array('id', 'name', 'date_manufacture', 'date_purchase', 'date_min_expire', 'date_expire', 'image');
			$hodnoty = array_intersect_key($_POST, array_flip($tablekeys));
            if ($hodnoty['id'] == '') $hodnoty['id'] = NULL;
            if ($hodnoty['date_manufacture'] == '') $hodnoty['date_manufacture'] = NULL;
			if ($hodnoty['date_expire'] == '') $hodnoty['date_expire'] = NULL;

            // Nahrání obrázku
            if (isset($_FILES["image"])) {
                $targetDirectory = "uploads/"; // Cílový adresář pro nahrávání obrázku
                $targetFile = $targetDirectory . basename($_FILES["image"]["name"]); // Cesta k cílovému souboru
            
                // Kontrola, zda je soubor obrázek
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    echo "Pouze soubory typu JPG, JPEG, PNG a GIF jsou povoleny.";
                } else {
                    // Pokus o nahrání souboru
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                        $hodnoty['image'] = $targetFile;
                    } else {
                        echo "Chyba při nahrávání obrázku.";
                    }
                }
            }
        
            $foodManager->saveItem($_POST["id"], $hodnoty);

            $this->redirect('/fridge');

        }



        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {

            $foodManager->deleteItem($_GET['delete']);
            $this->redirect('/fridge');

        }



        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {

            $edit = $foodManager->returnItem($_GET['edit']);
            $this->data['edit'] = $edit;

        }



		$food = $foodManager->returnFood();



        foreach ($food as &$item) {
            
            $item['halfLife'] = self::calculateHalfLife($item['date_manufacture'], $item['date_purchase'], $item['date_expire'], $item['date_min_expire']);

            // Porovnání PS s aktuálním datem
            $currentDate = new DateTime();
            $psDate = new DateTime($item['halfLife']);
            $secondDate = $item['date_expire'] ? new DateTime($item['date_expire']) : new DateTime($item['date_min_expire']);
            
            if ($secondDate < $currentDate) {
                $item['rowClass'] = 'table-danger';
                $item['colour'] = 'danger'; 
            } elseif ($psDate < $currentDate) {
                $item['rowClass'] = 'table-warning';
                $item['colour'] = 'warning'; 
            } else {
                $item['rowClass'] = '';
                $item['colour'] = 'info'; 
            }
            
        }



        // Rozdělení potravin na dvě skupiny
        $currentDate = strtotime('now');
        $foodsFuture = array();
        $foodsPast = array();

        foreach ($food as $item) {
            $expiryDate = strtotime($item['date_expire'] ?? $item['date_min_expire']);
            if ($expiryDate >= $currentDate) {
                $foodsFuture[] = $item;
            } else {
                $foodsPast[] = $item;
            }
        }

        // Seřazení potravin podle (DS ? DS : DMT)
        usort($foodsFuture, function($a, $b) {
            $aDateToCompare = $a['date_expire'] ?? $a['date_min_expire'];
            $bDateToCompare = $b['date_expire'] ?? $b['date_min_expire'];
            return strtotime($aDateToCompare) - strtotime($bDateToCompare);
        });

        // Výpočet a seřazení podle (PS)
        usort($foodsFuture, function($a, $b) {
            $aPS = strtotime(self::calculateHalfLife($a['date_manufacture'], $a['date_purchase'], $a['date_min_expire'], $a['date_expire']));
            $bPS = strtotime(self::calculateHalfLife($b['date_manufacture'], $b['date_purchase'], $b['date_min_expire'], $b['date_expire']));
            return $aPS - $bPS;
        });
        


        // Kombinace výsledků z obou skupin
        $this->data['food'] = array_merge($foodsPast, $foodsFuture);




	}

}