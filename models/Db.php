<?php

class Db {

	// Individuální nastavení webu
	public static $dbpr = '';

	// Databázové spojení
    private static $connection;

	// Výchozí nastavení ovladače
    private static $settings = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
		PDO::ATTR_EMULATE_PREPARES => false,
	);

	// Připojí se k databázi pomocí daných údajů
    public static function join($host, $user, $password, $database) {
		if (!isset(self::$connection)) {
			self::$connection = @new PDO(
				"mysql:host=$host;dbname=$database",
				$user,
				$password,
				self::$settings
			);
		}
	}

	// Nahradí obecné dbpr_ za požadovaný prefix
	public static function replaceDbPrefix($dotaz) {
		return str_replace('dbpr_', self::$dbpr, $dotaz);
	}

	// Spustí dotaz a vrátí z něj první řádek
    public static function dotazJeden($dotaz, $parametry = array()) {
    	$dotaz = self::replaceDbPrefix($dotaz);
		$navrat = self::$connection->prepare($dotaz);
		$navrat->execute($parametry);
		return $navrat->fetch();
	}

	// Spustí dotaz a vrátí všechny jeho řádky jako pole asociativních polí
    public static function dotazVsechny($dotaz, $parametry = array()) {
    	$dotaz = self::replaceDbPrefix($dotaz);
		$navrat = self::$connection->prepare($dotaz);
		$navrat->execute($parametry);
		return $navrat->fetchAll();
	}

	// Spustí dotaz a vrátí z něj první sloupec prvního řádku
    public static function dotazSamotny($dotaz, $parametry = array()) {
    	$dotaz = self::replaceDbPrefix($dotaz);
		$vysledek = self::dotazJeden($dotaz, $parametry);
		return $vysledek[0];
	}

	// Spustí dotaz a vrátí počet ovlivněných řádků
	public static function dotaz($dotaz, $parametry = array()) {
		$dotaz = self::replaceDbPrefix($dotaz);
		$navrat = self::$connection->prepare($dotaz);
		$navrat->execute($parametry);
		return $navrat->rowCount();
	}

	// Vloží do tabulky nový řádek jako data z asociativního pole
	public static function vloz($tabulka, $parametry = array()) {
		return self::dotaz("INSERT INTO `$tabulka` (`".
		implode('`, `', array_keys($parametry)).
		"`) VALUES (".str_repeat('?,', sizeOf($parametry)-1)."?)",
			array_values($parametry));
	}

	// Změní řádek v tabulce tak, aby obsahoval data z asociativního pole
	public static function zmen($tabulka, $hodnoty = array(), $podminka, $parametry = array()) {
		return self::dotaz("UPDATE `$tabulka` SET `".
		implode('` = ?, `', array_keys($hodnoty)).
		"` = ? " . $podminka,
		array_merge(array_values($hodnoty), $parametry));
	}

	// Vrací ID posledně vloženého záznamu
	public static function getLastId()
	{
		return self::$connection->lastInsertId();
	}

}