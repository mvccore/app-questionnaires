<?php

namespace App\Models\Person;

use \App\Models;

class Resource extends Models\Base
{
    public function InsertNew (\stdClass $formData)
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
		return $select->fetch(\PDO::FETCH_ASSOC);
	}
	public function GetMinAndMaxAges ($idQuestionnaire) {
		$personsTable = self::TABLE_PERSONS;
		$executedTable = self::TABLE_EXECUTED;
		$sql = "SELECT 
			MIN(Age) AS MinAge,
			MAX(Age) AS MaxAge
		FROM
			$personsTable AS p
		JOIN
			$executedTable AS e ON
				p.Id = e.IdPerson AND
				e.IdQuestionnaire = :idQuestionnaire";
		$select = self::GetDb()->prepare($sql);
		$select->execute(array(
			':idQuestionnaire'	=> $idQuestionnaire,
		));
		return $select->fetch(\PDO::FETCH_ASSOC);
	}
	public function GetMinAndMaxDates ($idQuestionnaire) {
		$personsTable = self::TABLE_PERSONS;
		$executedTable = self::TABLE_EXECUTED;
		$sql = "SELECT
			MIN(Created) AS MinDate,
			MAX(Created) AS MaxDate
		FROM
			$personsTable AS p
		JOIN
			$executedTable AS e ON
				p.Id = e.IdPerson AND
				e.IdQuestionnaire = :idQuestionnaire";
		$select = self::GetDb()->prepare($sql);
		$select->execute(array(
			':idQuestionnaire'	=> $idQuestionnaire,
		));
		return $select->fetch(\PDO::FETCH_ASSOC);
	}
}
