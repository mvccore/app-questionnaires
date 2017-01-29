<?php

namespace App\Models;

class Document extends XmlModel
{
	protected static $dataDir = '/Var/Documents';
	public static function GetDataPath() {
		return static::$dataDir;
	}
	public function GetUrl () {
		return $this->Path;
	}
}