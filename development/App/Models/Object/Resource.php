<?php

class App_Models_Object_Resource extends App_Models_Base
{
	protected static $commonTable = 'Object';
	protected static $localizedTable = 'ObjectLocalized';
    public function GetByIds ($ids = array()) {
		$params = array();
		foreach ($ids as $key => $id) {
			$params[':id' . $key] = $id;
		}
		$sql = "SELECT * FROM " . static::$commonTable . " WHERE Id IN (" . implode(', ', array_keys($params)) . ');';
		$select = $this->db->prepare($sql);
		$select->execute($params);
		return $select->fetchAll(PDO::FETCH_ASSOC);
	}
	public function GetById ($id = 0) {
		$sql = "SELECT * FROM " . static::$commonTable . " WHERE Id = :id";
		$select = $this->db->prepare($sql);
		$select->execute(array(':id' => $id));
		return $select->fetch();
	}
}
