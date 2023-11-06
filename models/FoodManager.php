<?php

class FoodManager
{

	public function returnFood()
	{
		return Db::dotazVsechny('
			SELECT `id`, `name`, `date_manufacture`, `date_purchase`, `date_min_expire`, `date_expire`, `image`
			FROM `food`
			ORDER BY `id` ASC
		');
	}
	
	public function returnItem($id)
	{
		return Db::dotazJeden('
			SELECT `id`, `name`, `date_manufacture`, `date_purchase`, `date_min_expire`, `date_expire`, `image`
			FROM `food` 
			WHERE `id` = ?
		', array($id));
	}
	
	public function saveItem($id, $item)
	{
		if (!$id)
			Db::vloz('food', $item);
		else
			Db::zmen('food', $item, 'WHERE id = ?', array($id));
	}
	
	public function deleteItem($id)
	{
		Db::dotaz('
			DELETE FROM food
			WHERE id = ?
		', array($id));
	}

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
	
}