<?php

class App_Views_Helpers_Text
{
	protected static $conjunctions = array(
		"cs"	=> "a,i,do,k,ke,v,ve,s,se,z,ze,že,si,ač,či,za,ať,kéž,ku,ba,pro,co,při,má",
	);
	protected static $units = array(
		"cs"	=> "mm,cm,dm,m,km,kg,g,dkg,ha,ar,l,ml,dcl,cm²,m²,km²,cm³,m³",
	);
	
    protected $lang = "";
	protected $text = "";
	protected $cfgs = array();

	public function __construct (MvcCore_View & $view) {
		$ctrl = $view->Controller;
		$this->lang = $ctrl::$Lang;
	}
	public function Text () {
		return $this;
	}

	protected function getConjunctionsAndUnits ($lang) {
		if (!isset($this->cfgs[$lang])) {
			$this->cfgs[$lang] = array(
				explode(',', self::$conjunctions[$lang]),
				explode(',', self::$units[$lang])
			);
		}
		return $this->cfgs[$lang];
	}

	public function FixedSpaces ($src, $lang = "") {
		$this->text = $src;
		$word = "";
		$lang = $lang ? $lang : $this->lang;
		list($conjunctions, $units) = $this->getConjunctionsAndUnits($lang);
			
		// pokud by se někde vyskytl jeden či více tabů vedle sebe - přeměň je na jednu mezeru
		$this->text = preg_replace("#\t+#mu", " ", $this->text);
			
		// pokud je někde v textu dvě a více mezer vedle sebe - přeměň je na jednu mezeru
		$this->text = preg_replace("#[ ]{2,}#mu", " ", $this->text);
				
		// pro všechny jednoslabičné spojky
		for ($i = 0, $l = count($conjunctions); $i < $l; $i += 1) {
				
			// načti si aktuální slovo do proměnné word
			$word = $conjunctions[$i];
				
			// zprocesuj text s jednoslabičnou spojkou
			$this->fixedSpacesConjunction($word);
				
			// převeď první písměno ve spojce na velkou abecedu
			$word = mb_strtoupper(mb_substr($word, 0, 1)) + mb_substr($word, 1);
				
			// zprocesuj text s jednoslabičnou spojkou pro velké písmeno na začátku
			$this->fixedSpacesConjunction($word);
		}
			
		// pro všechny jednotky, před kterými je jednoduchá dělitelná mezera a číslo
		for ($i = 0, $l = count($units); $i < $l; $i += 1) {
			// načti aktuální jednotku do proměnné word
			$word = $units[$i];
			// vytvoř instanci regulárního výrazu pro vyhledání jednotky, před kterým je mezera a jakákoliv číslice
			$regExp = "#([0-9])\\s(" . $word . ")#mu";
			// proveď nahrazení za nedělitelnou mezeru pro všechny výskyty daného výrazu v proměnné $this->text
			$this->text = preg_replace(
				$regExp, 
				"$1&nbsp;$2", 
				$this->text
			);
		}
			
		return $this->text;
	}
	protected function fixedSpacesConjunction ($word) {
			$index = 0;
			$text = ' ' . $this->text . ' ';
			// projdi celý text nekonečnou smyčkou a vyřeš pro dané slovo nedělitelné mezery
			while (TRUE) {
				$index = mb_strpos($text, ' ' . $word . ' ');
				if ($index !== FALSE) {
					// pokud se v textu vyskytuje jednoslabičná spojka a lomitelná mezera:
					// - vezmi text před spojkou, přičti spojku, přičti nedělitelnou mezeru 
					//   a přičti všechen zbylý text za jednoduchou mezerou co byla za jednoslabičnou spojkou
					$text = mb_substr($text, 0, $index + 1) . $word . '&nbsp;' . mb_substr($text, $index + 1 + mb_strlen($word) + 1);
					$index += 1 + mb_strlen($word) + 6; // posuň index za pozici, kde je již text zpracovaný
				} else {
					// v textu se již nevyskytuje další výskyt jednoslabičné spojky a následující obyčejné mezery
					break;
				}
			}
			$this->text = mb_substr($text, 1, mb_strlen($text) - 2);
		}
}
