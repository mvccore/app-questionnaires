<?php

class App_Models_Object extends App_Models_Base
{
    public static function GetByIds ($ids = array(), $key = 'Id') {
		$records = self::getResource()->GetByIds($ids);
		if (!$records) return array();
		$result = array();
		foreach ($records as $record) {
			$instance = new static();
			$instance->setUp($record);
			if ($key) {
				$keyValue = $record[$key];
				$result[$keyValue] = $instance;
			} else {
				$result[] = $instance;
			}
		}
		return $result;
	}
	public static function GetById ($id = 0) {
		$record = self::getResource()->GetById($id);
		if (!$record) return FALSE;
		$instance = new static();
		$instance->setUp($record);
		return $instance;
	}
}