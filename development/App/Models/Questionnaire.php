<?php

namespace App\Models;

class Questionnaire extends Document
{
	/**
	 * Public vars bellow is not necessary to define here manualy,
	 * variables are initialized automaticly by xml file loading,
	 * but here they are for IDE whispering
	 */
	/** @var int */
	public $Id;
	/* @var string */
	public $Lang;
	/* @var bool */
	public $PersonsForm;
	/* @var string */
	public $Title;
	/* @var string */
	public $Description;
	/* @var string */
	public $Keywords;
	/* @var string */
	public $Robots;
	/* @var string */
	public $OgImage;
	/* @var string */
	public $OgTitle;
	/* @var string */
	public $OgDescription;
	/* @var string */
	public $ItempropName;
	/* @var string */
	public $ItempropDescription;
	/* @var bool */
	public $FacebookShare;
	/* @var string */
	public $Perex;
	/* @var string */
	public $Body;

	protected static $dataDir = '/Var/Questionnaires';

	private static $_appInstance;
	private $_url = null;
	private $_questions = null;

	public static function GetAll () {
		$result = array();
		$fullPath = \MvcCore::GetInstance()->GetRequest()->AppRoot . self::$dataDir;
		$di = new \DirectoryIterator($fullPath);
		foreach ($di as $item) {
			if ($item->isDir()) continue;
			$fileName = $item->getFilename();
			$path = preg_replace("#(.*)\.xml$#", "$1", $fileName);
			$resultItem = self::GetByPath('/' . $path);
			if ($resultItem !== FALSE) {
				if (isset($result[$resultItem->Id])) {
					$id = $resultItem->Id;
					throw new \Exception ("[".__CLASS__."] Questionnaire with Id: $id already defined.");
				} else {
					$result[$resultItem->Id] = $resultItem;
				}
			}
		}
		ksort($result);
		return $result;
	}
	public function GetUrl () {
		if (is_null($this->_url)) {
			$pathWithoutFirstSlashDigitsAndDash = preg_replace("#^/([0-9]*)\-(.*)$#", "$2", $this->Path);
			$this->_url = \MvcCore::GetInstance()->GetController()->Url(
				'Questionnaire:Index', 
				array('path' => $pathWithoutFirstSlashDigitsAndDash)
			);
		}
		return $this->_url;
	}
	public function GetQuestions () {
		if (is_null($this->_questions)) $this->_loadQuestions();
		return $this->_questions;
	}
	public function GetQuestion ($index = 0) {
		if (is_null($this->_questions)) $this->_loadQuestions();
		if (isset($this->_questions[$index])) {
			return $this->_questions[$index];
		} else {
			return FALSE;
		}
	}
	private function _loadQuestions () {
		$result = array();
		$fullPath = \MvcCore::GetInstance()->GetRequest()->AppRoot . self::$dataDir . $this->Path;
		$di = new \DirectoryIterator($fullPath);
		foreach ($di as $item) {
			if ($item->isDir()) continue;
			$fileName = $item->getFilename();
			$path = $this->Path . '/' . preg_replace("#(.*)\.xml$#", "$1", $fileName);
			$resultItem = \App\Models\Question::GetByPath($path);
			if ($resultItem !== FALSE) {
				if (isset($result[$resultItem->Id])) {
					$id = $resultItem->Id;
					throw new \Exception ("[".__CLASS__."] Question with Id: $id already defined in path: '$path'.");
				} else {
					$resultItem->Path = $path;
					$resultItem->Questionnaire = $this;
					$result[$resultItem->Id] = $resultItem;
				}
			}
		}
		$this->_questions = $result;
	}
}