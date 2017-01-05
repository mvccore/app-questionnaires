<?php

class App_Models_Document extends App_Models_XmlModel
{
	protected static $dataDir = '/Var/Documents';
	public static function GetDataPath() {
		return static::$dataDir;
	}
	public function GetUrl () {
		return $this->Path;
	}
}