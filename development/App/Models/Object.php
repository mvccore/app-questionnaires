<?php

namespace App\Models;

class Object extends Base
{
    public static function GetByIds ($ids = [], $key = 'Id') {
		$records = self::GetResource()->GetByIds($ids);
		if (!$records) return [];
		$result = [];
		foreach ($records as $record) {
			$instance = new static();
			$instance->SetUp($record);
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
		$record = self::GetResource()->GetById($id);
		if (!$record) return FALSE;
		$instance = new static();
		$instance->SetUp($record);
		return $instance;
	}
}
