<?php

class App_Models_Person_Resource extends App_Models_Base
{
    public function InsertNew (stdClass $formData)
	{
		$this->db->beginTransaction();

		$currentDatetime = date('Y-m-d H:i:s', time());
		if ($this->cfg->driver == 'mysql') {
			$currentDatetime = 'NOW()';
		} else if ($this->cfg->driver == 'mssql') {
			$currentDatetime = 'GETDATE()';
		}
		
		$table = self::TABLE_PERSONS;
		$insertCmd = $this->db->prepare(
			"INSERT INTO $table (Created,Sex,Age,Education,Job) VALUES ($currentDatetime,:sex,:age,:edu,:job)"
		);
		$insertCmd->execute(array(
			':sex' => $formData->sex,
			':age' => $formData->age,
			':edu' => $formData->edu,
			':job' => $formData->job,
		));

		$sql = "SELECT Id FROM $table ORDER BY Id DESC";
		$select = $this->db->prepare($sql);
		$select->execute(array());
		$lastIdResult = $select->fetch();
		$lastInsertedId = $lastIdResult['Id'];
		
		$this->db->commit();

		return intval($lastInsertedId);
	}
	public function GetById ($id)
	{
		$table = self::TABLE_PERSONS;
		$sql = "SELECT Id,Created,Sex,Age,Education,Job FROM $table WHERE Id=:id";
		$select = self::GetDb()->prepare($sql);
		$select->execute(array(
			':id'	=> $id,	
		));
		return $select->fetch(PDO::FETCH_ASSOC);
	}
	public function GetMinAndMaxAges () {
		$table = self::TABLE_PERSONS;
		$sql = "SELECT 
			MIN(Age) AS MinAge,
			MAX(Age) AS MaxAge
		FROM 
			$table";
		$select = self::GetDb()->prepare($sql);
		$select->execute();
		return $select->fetch(PDO::FETCH_ASSOC);
	}
}
