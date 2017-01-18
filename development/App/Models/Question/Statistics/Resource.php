<?php

class App_Models_Question_Statistics_Resource extends App_Models_Base
{
	const LEVENSTHEIN_COMPARATION_TOLERANCE_DEFAULT = 2;
	/**
	 * @var array
	 */
	protected static $columnsTypes = array();
	/**
	 * @var App_Models_Question
	 */
	protected $question = NULL;
	/**
	 * @var int
	 */
	protected $idQuestionnaire = 0;
	/**
	 * @var int
	 */
	protected $idQuestion = 0;
	/**
	 * @var string
	 */
	protected $filterCondition = '';
	/**
	 * @var array
	 */
	protected $filterData = array();
	/**
	 * @var array
	 */
	protected $user = array();

	// faster method variant:
	public static final function GetInstance () {
		/** @var $user MvcCoreExt_Auth_User */
		list($question, $filterData, $user) = func_get_args();
		$instanceIndex = md5(implode('_', array(
			__CLASS__,
			$question->Questionnaire->Id,
			$question->Id,
			serialize($filterData),
			isset($user->UserName) ? $user->UserName : ''
		)));
		if (!isset(self::$instances[$instanceIndex])) {
			self::$instances[$instanceIndex] = new static($question, $filterData, $user);
		}
		return self::$instances[$instanceIndex];
	}
	public function __construct (App_Models_Question & $question = NULL, $filterData = NULL, $user = array()) {
		parent::__construct();
		$this->question = $question;
		$this->idQuestionnaire = $question->Questionnaire->Id;
		$this->idQuestion = $question->Id;
		$this->filterData = $filterData;
		$this->user = $user;
		$this->_initFilterCondition();
		$this->_initColumnsTypes();
	}
    private function _initFilterCondition () {
		if (!$this->filterCondition) {
			$filterConditions = array();
			foreach ($this->filterData as $key => $values) {
				$filterCondition = '';
				$ucfKey = ucfirst($key);
				if ($key == 'age') {
					$values = gettype($values) == 'array' ? $values : array($values);
					$filterCondition = "p.$ucfKey >= " . $values[0];
					if (isset($values[1])) $filterCondition = "($filterCondition AND p.$ucfKey <= " . $values[1] . ')';
				} else {
					$filterCondition = "p.$ucfKey IN ('" . implode("', '", $values) . "')";
				}
				$filterConditions[] = $filterCondition;
			}
			$this->filterCondition = implode(' AND ', $filterConditions);
		}
	}
	private function _initColumnsTypes () {
		$driver = $this->cfg->driver;
		$dbname = $this->cfg->dbname;
		$answersTable = self::TABLE_ANSWERS;
		$sql = "SELECT 
			COLUMN_NAME AS ColumnName, 
			DATA_TYPE AS DataType
		FROM 
			INFORMATION_SCHEMA.COLUMNS AS c 
		WHERE 
			c.TABLE_CATALOG = '$dbname' AND
			c.TABLE_NAME = '$answersTable';";
		if ($driver == 'mssql') {
			$sql = "SELECT 
				COLUMN_NAME AS ColumnName, 
				DATA_TYPE AS DataType
			FROM 
				INFORMATION_SCHEMA.COLUMNS AS c 
			WHERE 
				c.TABLE_CATALOG = '$dbname' AND
				c.TABLE_NAME = '$answersTable';";
		} else if ($driver == 'mysql') {
			$dbname = strtolower($dbname);
			$sql = "SELECT 
				COLUMN_NAME AS ColumnName, 
				DATA_TYPE AS DataType
			FROM 
				INFORMATION_SCHEMA.COLUMNS AS c 
			WHERE 
				c.TABLE_SCHEMA = '$dbname' AND
				c.TABLE_NAME = '$answersTable';";
		}
		$select = $this->db->prepare($sql);
		$select->execute();
		$rawData = $select->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rawData as $item) {
			self::$columnsTypes[$item['ColumnName']] = $item['DataType'];
		}
	}
	protected static function setUpResultTypes ($result = array(), array $columnNamesAndTypes = array()) {
		$types = array_merge(self::$columnsTypes, $columnNamesAndTypes);
		foreach ($result as & $resultItem) {
			foreach ($resultItem as $itemName => & $itemValue) {
				if (isset($types[$itemName])) {
					settype($itemValue, $types[$itemName]);
				} else if (is_numeric($itemValue)) {
					if (strpos($itemValue, '.') !== FALSE) {
						settype($itemValue, 'float');
					} else {
						settype($itemValue, 'int');
					}
				}
			}
		}
		return $result;
	}
	public function LoadQuestionAnsweringPersonsCount ()
	{
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		// complete how many persons for this questionaire, question and condition are there
		$sql = "
			SELECT
				COUNT(r.Id) AS count
			FROM (
				SELECT
					p.Id
				FROM
					$personsTable AS p
				JOIN
					$answersTable AS a ON
						a.IdPerson = p.Id
				WHERE 
					a.IdQuestionnaire = :id_questionnaire AND 
					a.IdQuestion = :id_question";
		if ($this->filterCondition) $sql .= " AND (
						$this->filterCondition 
					)";
		$sql .= "
				GROUP BY
					p.Id
			) AS r;
		";
		//xxx($sql);
		$select = $this->db->prepare($sql);
		$select->execute(array(
			':id_questionnaire'	=> $this->idQuestionnaire,
			':id_question'		=> $this->idQuestion,
		));
		// xcv(str_replace(array(':id_questionnaire', ':id_question'), array($this->idQuestionnaire, $this->idQuestion), $sql));
		return intval($select->fetchColumn());
	}
	public function LoadAllQuestionnairePersonsCount ()
	{
		$executedTable = self::TABLE_EXECUTED;
		$personsTable = self::TABLE_PERSONS;
		// complete how many persons for this questionaire, question and condition are there
		$sql = "
			SELECT
				COUNT(e.IdPerson) AS count
			FROM 
				$executedTable AS e
			JOIN
				$personsTable AS p ON
					e.IdPerson = p.Id
			WHERE 
				e.IdQuestionnaire = :id_questionnaire";
		if ($this->filterCondition) {
			$sql .= " AND (
					$this->filterCondition 
				)";
		}
		$select = $this->db->prepare($sql);
		$select->execute(array(
			':id_questionnaire'	=> $this->idQuestionnaire,
		));
		// xcv(str_replace(array(':id_questionnaire', ':id_question'), array($this->idQuestionnaire, $this->idQuestion), $sql));
		return intval($select->fetchColumn());
	}
	protected function getStatisticsAllAnswers ($resultColumns, $groupByColumn, $nullCondition = '', $fetchMethod = 'fetchAll') {
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$sql = "
			SELECT 
				" . implode(", 
				", $resultColumns) . " 
			FROM
				$answersTable AS a
			JOIN
				$personsTable AS p ON
					a.IdPerson = p.Id
			WHERE
				a.IdQuestionnaire = :id_questionnaire AND 
				a.IdQuestion = :id_question";
		if ($nullCondition) $sql .= " AND 
				($nullCondition)";
		if ($this->filterCondition) $sql .= " AND 
				($this->filterCondition)";
		if ($groupByColumn) $sql .= " 
			GROUP BY 
				$groupByColumn";
		$select = $this->db->prepare($sql);
		$select->execute(array(
			':id_questionnaire'	=> $this->idQuestionnaire,
			':id_question'		=> $this->idQuestion,
		));
		/*if ($this->question->Id == 6) {
			xcv(str_replace(array(':id_questionnaire', ':id_question'), array($this->idQuestionnaire, $this->idQuestion), $sql));
		}*/
		if ($fetchMethod == 'fetchAll') {
			return self::setUpResultTypes($select->fetchAll(PDO::FETCH_ASSOC));
		} else if ($fetchMethod == 'fetchColumn') {
			return intval($select->fetchColumn());
		} else {
			return $select->fetch();
		}
	}
	protected function getStatisticsForSelectedOptionsCountsInAnswer () {
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$sql = "
			SELECT
				r.Count AS Value,
				COUNT(r.Id) AS Count
			FROM (
				SELECT 
					a.IdPerson AS Id,
					COUNT(a.IdPerson) AS Count
				FROM
					$answersTable AS a
				JOIN
					$personsTable AS p ON
						a.IdPerson = p.Id
				WHERE
					a.IdQuestionnaire = :id_questionnaire AND 
					a.IdQuestion = :id_question";
		if ($this->filterCondition) $sql .= " AND 
				($this->filterCondition)";
		$sql .= "
				GROUP BY 
					a.IdPerson
			) AS r
			GROUP BY 
				r.Count;
		";
		$select = $this->db->prepare($sql);
		$select->execute(array(
			':id_questionnaire'	=> $this->idQuestionnaire,
			':id_question'		=> $this->idQuestion,
		));
		/*if ($this->question->Id == 14) {
			xcv(str_replace(array(':id_questionnaire', ':id_question'), array($this->idQuestionnaire, $this->idQuestion), $sql));
		}*/
		return self::setUpResultTypes($select->fetchAll(PDO::FETCH_ASSOC));
	}
	protected function getStatisticsForConnectionsPeopleAnswering ($onlyCorrectAnswers = TRUE) {
		// complete all possible answered counts sql array command
		$sqlAnsweredOptsCount = array();
		for ($i = count($this->question->Options); $i > -1; $i -= 1) $sqlAnsweredOptsCount[] = $i;
		$sqlAnsweredOptsCountCmd = "SELECT " . implode(" AS Value UNION SELECT ", $sqlAnsweredOptsCount) . " AS Value";
		// complete solution condition
		$sqlSolutionCondition = '';
		if ($onlyCorrectAnswers) {
			$sqlSolutionConditionItems = array();
			foreach ($this->question->Solution as $optionKey => $optionAnswer) {
				$sqlSolutionConditionItems[] = "(a.[Option] = $optionKey AND a.[Integer] = $optionAnswer)";
			}
			$sqlSolutionCondition = '(' . implode(' OR ', $sqlSolutionConditionItems) . ')';
		}
		// prepare complete sql command
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$sql = "
			SELECT
				scale.Value AS Value,
				ISNULL(data.personsCount, 0) AS Count
			FROM (
				$sqlAnsweredOptsCountCmd
			) AS scale
			LEFT JOIN (
				SELECT
					allPersonsCorrectAnswersCounts.Count AS Count,
					COUNT(allPersonsCorrectAnswersCounts.IdPerson) AS personsCount
				FROM (
					SELECT 
						p.Id AS IdPerson,
						COUNT(p.Id) AS Count
					FROM
						$personsTable AS p
					LEFT JOIN
						$answersTable AS a ON
							a.IdPerson = p.Id
					WHERE
						a.IdQuestionnaire = :id_questionnaire AND 
						a.IdQuestion = :id_question";
		if ($this->filterCondition) $sql .= " AND 
						(
							$this->filterCondition
						)";
		if ($onlyCorrectAnswers)
			$sql .= " AND (
							$sqlSolutionCondition
						)";
		$sql .= "
					GROUP BY
						p.Id
				) AS allPersonsCorrectAnswersCounts
				GROUP BY
					allPersonsCorrectAnswersCounts.Count
			) AS data ON
				scale.Value = data.Count
			ORDER BY
				scale.Value ASC;
		";
		return $sql;
	}
	protected function getStatisticsForConnectionsOptionsCorrectness () {
		// complete all options sql array command
		$sqlAllOptsArr = array_keys($this->question->Options);
		$sqlAllOptsArrCmd = "SELECT " . implode(" AS Value UNION SELECT ", $sqlAllOptsArr) . " AS Value";
		// complete solution condition
		$sqlSolutionConditionItems = array();
		foreach ($this->question->Solution as $optionKey => $optionAnswer) {
			$sqlSolutionConditionItems[] = "(a.[Option] = $optionKey AND a.[Integer] = $optionAnswer)";
		}
		$sqlSolutionCondition = '(' . implode(' OR ', $sqlSolutionConditionItems) . ')';
		// prepare complete sql command
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$sql = "
			SELECT
				allOptions.Value AS [Value],
				ISNULL(allAnswers.allAnswersCount, 0) AS AllAnswersCount,
				ISNULL(correctAnswers.correctAnswersCount, 0) AS CorrectAnswersCount
			FROM (
				$sqlAllOptsArrCmd
			) AS allOptions
			LEFT JOIN (
				SELECT 
					a.[Option],
					COUNT(a.[Option]) AS allAnswersCount
				FROM
					$answersTable AS a
				JOIN
					$personsTable AS p ON
						a.IdPerson = p.id
				WHERE
					a.IdQuestionnaire = :id_questionnaire1 AND 
					a.IdQuestion = :id_question1";
		if ($this->filterCondition) $sql .= " AND 
					$this->filterCondition ";
		$sql .= "
				GROUP BY
					a.[Option]
			) AS allAnswers ON
				allOptions.Value = allAnswers.[Option]
			LEFT JOIN (
				SELECT 
					a.[Option],
					COUNT(a.[Option]) AS correctAnswersCount
				FROM
					$answersTable AS a
				JOIN
					$personsTable AS p ON
						a.IdPerson = p.id
				WHERE 
					a.IdQuestionnaire = :id_questionnaire2 AND 
					a.IdQuestion = :id_question2";
		if ($this->filterCondition) $sql .= " AND (
						$this->filterCondition
					) ";
		$sql .= " AND (
						$sqlSolutionCondition
					)
				GROUP BY
					a.[Option]
			) AS correctAnswers ON
				allOptions.Value = correctAnswers.[Option]
		";
		return $sql;
	}
	protected function getStatisticsForConnectionsMostOfftenConnections () {
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$sql = "SELECT 
			CONCAT(a.[Option], '_', a.[Integer]) AS OptionAndInteger,
			COUNT(p.Id) AS [Count]
		FROM
			$personsTable AS p
		LEFT JOIN
			$answersTable AS a ON
				a.IdPerson = p.Id
		WHERE
			a.IdQuestionnaire = :id_questionnaire AND 
			a.IdQuestion = :id_question";
		if ($this->filterCondition) $sql .= " AND (
				$this->filterCondition
			) ";
		$sql .= "
		GROUP BY
			CONCAT(a.[Option], '_', a.[Integer])
		ORDER BY
			Count DESC;";
		return $sql;
	}
	protected function getStatisticsForCheckboxesOptionsCorrectness () {
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$personsCount = $this->LoadQuestionAnsweringPersonsCount();
		// complete all possible answered counts sql array command
		$sqlAnswerOpts = array();
		for ($i = 0, $l = count($this->question->Checkboxes); $i < $l; $i += 1) $sqlAnswerOpts[] = $i;
		$sqlAnswerOptsCmd = "SELECT " . implode(" AS [Option] UNION SELECT ", $sqlAnswerOpts) . " AS [Option]";
		$sql = "
			SELECT
				allOptions.[Option] AS Value,
				ISNULL(r.Positive, 0) AS Count1,
				($personsCount - ISNULL(r.Positive, 0)) AS Count0
			FROM (
				$sqlAnswerOptsCmd
			) AS allOptions 
			LEFT JOIN (
				SELECT
					a.[Option],
					COUNT(p.Id) AS Positive
				FROM
					$personsTable AS p
				JOIN
					$answersTable AS a ON
						a.IdPerson = p.Id
				WHERE 
					a.IdQuestionnaire = :id_questionnaire AND 
					a.IdQuestion = :id_question";
		if ($this->filterCondition) $sql .= " AND (
						$this->filterCondition 
					)";
		$sql .= "
				GROUP BY
					a.[Option]
			) AS r ON
				r.[Option] = allOptions.[Option]
			/*ORDER BY
				Count1 DESC, 
				Count0 DESC,
				Value ASC*/;
		";
		return $sql;
	}
	protected function getStatisticsForCheckboxesPeopleCorrectness () {
		// complete solution conditions
		$sqlSolutionConditionItemsCorrect = array();
		$sqlSolutionConditionItemsIncorrect = array();
		foreach ($this->question->Solution as $option) {
			$sqlSolutionConditionItemsCorrect[] = "a.[Option] = $option";
			$sqlSolutionConditionItemsIncorrect[] = "a.[Option] != $option";
		}
		$sqlSolutionConditionCorrect = implode(' OR ', $sqlSolutionConditionItemsCorrect);
		$sqlSolutionConditionIncorrect = implode(' AND ', $sqlSolutionConditionItemsIncorrect);
		// prepare complete sql command
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$sql = "
			SELECT
				*
			FROM (
				SELECT
					COUNT(r.Id) AS PersonsCount,
					CONCAT(r.PersonsCorrectAnswersCounts, '_', r.PersonsIncorrectAnswersCounts) AS PersonsCorrectAndIncorrectAnswersCounts
				FROM (
					SELECT 
						p.Id,
						ISNULL(personsCorrectAnswersCounts.Count, 0) AS PersonsCorrectAnswersCounts,
						ISNULL(personsIncorrectAnswersCounts.Count, 0) AS PersonsIncorrectAnswersCounts
					FROM 
						$personsTable AS p
					LEFT JOIN (
						SELECT 
							p.Id,
							COUNT(p.Id) AS Count
						FROM
							$answersTable AS a
						JOIN
							$personsTable AS p ON
								a.IdPerson = p.Id
						WHERE
							a.IdQuestionnaire = :id_questionnaire1 AND 
							a.IdQuestion = :id_question1 AND (
								$sqlSolutionConditionCorrect
							)
						GROUP BY
							p.Id
					) AS personsCorrectAnswersCounts ON
						personsCorrectAnswersCounts.Id = p.Id
					LEFT JOIN (
						SELECT 
							p.Id,
							COUNT(p.Id) AS Count
						FROM
							$answersTable AS a
						JOIN
							$personsTable AS p ON
								a.IdPerson = p.Id
						WHERE
							a.IdQuestionnaire = :id_questionnaire2 AND 
							a.IdQuestion = :id_question2 AND (
								$sqlSolutionConditionIncorrect
							)
						GROUP BY
							p.Id
					) AS personsIncorrectAnswersCounts ON
						personsIncorrectAnswersCounts.Id = p.Id";
		if ($this->filterCondition) $sql .= "
					WHERE 
						$this->filterCondition ";
		$sql .= "
				) AS r
				GROUP BY 
					CONCAT(r.PersonsCorrectAnswersCounts, '_', r.PersonsIncorrectAnswersCounts)
			) AS r
			WHERE
				r.PersonsCorrectAndIncorrectAnswersCounts != '0_0';
		";
		return $sql;
	}
	protected function getAllTextStatisticsCompared ($allAnswersNotCompared) {
		$levTolerance = isset($this->question->LevenshteinComparationTolerance) ? $this->question->LevenshteinComparationTolerance : self::LEVENSTHEIN_COMPARATION_TOLERANCE_DEFAULT;
		$levToleranceAddOne = $levTolerance + 1;
		$allAnswersCompared = array();
		$allAnswersNotComparedCount = count($allAnswersNotCompared);
		if (isset($this->question->Solution) && $this->user) {
			$solutions = explode(',', mb_strtolower($this->question->Solution));
			foreach ($solutions as $solution) {
				$solution = trim($solution);
				foreach ($allAnswersNotCompared as & $answerNotCompared) {
					if (isset($answerNotCompared['Correct']) && $answerNotCompared['Correct']) continue;
					$correct = levenshtein($solution, $answerNotCompared['ValueLowerCase']) < $levToleranceAddOne;
					$answerNotCompared['Correct'] = $correct;
				}
			}
		}
		unset($answerNotCompared);
		for ($key1 = 0; $key1 < $allAnswersNotComparedCount; $key1 += 1) {
			if (!isset($allAnswersNotCompared[$key1])) continue;
			$answerNotCompared = $allAnswersNotCompared[$key1];
			$similarAnswers = array();
			$primaryValue = $answerNotCompared['ValueLowerCase'];
			for ($key2 = 0; $key2 < $allAnswersNotComparedCount; $key2 += 1) {
				if (!isset($allAnswersNotCompared[$key2])) continue;
				if ($key1 == $key2) continue;
				$item = $allAnswersNotCompared[$key2];
				$oppositeValue = $item['ValueLowerCase'];
				$levRatio = levenshtein($primaryValue, $oppositeValue);
				if ($levRatio < $levToleranceAddOne) {
					$item['BaseKey'] = $key2;
					$similarAnswers[] = $item;
				}
			}
			if (!count($similarAnswers)) {
				unset($answerNotCompared['ValueLowerCase']);
				$allAnswersCompared[] = $answerNotCompared;
				unset($allAnswersNotCompared[$key1]);
			} else {
				$answerNotCompared['BaseKey'] = $key1;
				$similarAnswers[] = $answerNotCompared;
				uasort($similarAnswers, function ($a, $b) {
					if ($a['Count'] == $b['Count']) return 0;
					return ($a['Count'] < $b['Count']) ? 1 : -1;
				});
				$similarAnswers = array_values($similarAnswers);
				$mostAnswered = $similarAnswers[0];
				for ($i = 1, $l = count($similarAnswers); $i < $l; $i += 1) {
					$item = $similarAnswers[$i];
					$mostAnswered['Count'] += $item['Count'];
					unset($allAnswersNotCompared[$item['BaseKey']]);
				}
				$baseKey = $mostAnswered['BaseKey'];
				unset($mostAnswered['BaseKey'], $mostAnswered['ValueLowerCase']);
				$allAnswersCompared[] = $mostAnswered;
				unset($allAnswersNotCompared[$baseKey]);
			}
		}
		return $allAnswersCompared;
	}
	protected function handleBooleanOverviewResults (& $result)
	{
		foreach ($result->Overview as & $item) settype($item['Value'], 'boolean');
		$itemsCount = count($result->Overview);
		// set up missing values with zeros
		if ($itemsCount < 2) {
			if ($itemsCount == 1) {
				$result->Overview[] = array('Value' => !$result->Overview[0]['Value'], 'Count' => 0);
			} else {
				$result->Overview = array(
					array('Value' => TRUE, 'Count' => 0),
					array('Value' => FALSE, 'Count' => 0),
				);
			}
		}
		// make positive values always first
		if (!$result->Overview[0]['Value']) {
			$negativeRecord = $result->Overview[0];
			$result->Overview[0] = $result->Overview[1];
			$result->Overview[1] = $negativeRecord;
		}
		// translate true and false into words
		$translator = App_Models_Translator::GetInstance();
		foreach ($result->Overview as & $item) {
			$item['Value'] = $translator->Translate($item['Value'] ? 'Yes' : 'No');
		}
		return $result;
	}
	protected function getDatabaseLevenshteinExists () {
		$sql = "
			SELECT 
				COUNT(r.ROUTINE_NAME) AS Count
			FROM 
				INFORMATION_SCHEMA.ROUTINES AS r
			WHERE 
				r.ROUTINE_TYPE='FUNCTION' AND 
				r.ROUTINE_NAME='Levenshtein';";
		$select = $this->db->prepare($sql);
		$select->execute(array());
		return boolval($select->fetchColumn());
	}
	protected function getStatisticsForTextCorrectPersonsCount () {
		$databaseLevenshteinExists = $this->getDatabaseLevenshteinExists();
		$levTolerance = isset($this->question->LevenshteinComparationTolerance) ? $this->question->LevenshteinComparationTolerance : self::LEVENSTHEIN_COMPARATION_TOLERANCE_DEFAULT;
		$levToleranceAddOne = $levTolerance + 1;
		$solutions = explode(',', $this->question->Solution);
		foreach ($solutions as $key => $solution) $solutions[$key] = strtolower(trim($solution));
		$answersTable = self::TABLE_ANSWERS;
		$personsTable = self::TABLE_PERSONS;
		$sqlParams = array(
			':id_questionnaire' => $this->question->Questionnaire->Id,
			':id_question'		=> $this->question->Id,
		);
		$childSql = "SELECT
							p.Id,
							LOWER(a.[Varchar]) AS [Varchar]
						FROM
							$answersTable AS a
						JOIN
							$personsTable AS p ON
								a.IdPerson = p.Id
						WHERE
							a.IdQuestionnaire = :id_questionnaire AND 
							a.IdQuestion = :id_question AND 
							(a.[Varchar] IS NOT NULL)";
		if ($this->filterCondition) $childSql .= " AND (
								{$this->filterCondition} 
							)";
		if ($databaseLevenshteinExists) {
			$levenshteinSqlItems = array();
			foreach ($solutions as $key => $solution) {
				$levenshteinSqlItems[] = "dbo.Levenshtein(
							LOWER(srcData.[Varchar]), :solution$key, 100
						) < " . $levToleranceAddOne;
				$sqlParams[':solution' . $key] = $solution;
			}
			$levenshteinSql = implode(" OR ", $levenshteinSqlItems);
			$parentSql = "
				SELECT 
					COUNT(cnts.CorrectPersonAnswers) AS Count
				FROM (
					SELECT
						COUNT(srcData.[Varchar]) AS CorrectPersonAnswers
					FROM (
						$childSql
					) AS srcData
					WHERE
						$levenshteinSql
					GROUP BY
						srcData.Id
				) AS cnts;
			";
			if ($this->cfg->driver == 'mysql') $parentSql = str_replace(array('[Varchar]', 'dbo.Levenshtein('), array('`Varchar`', 'Levenshtein('), $parentSql);
			//xxx(array($parentSql, $sqlParams));
			$select = $this->db->prepare($parentSql);
			$select->execute($sqlParams);
			$result = intval($select->fetchColumn());
		} else {
			$parentSql = "$childSql;";
			$select = $this->db->prepare($parentSql);
			$select->execute($sqlParams);
			$rawResult = $select->fetchAll(PDO::FETCH_ASSOC);
			$personsIds = array();
			foreach ($rawResult as & $item) {
				$primaryValue = $item['Varchar'];
				foreach ($solutions as $solution) {
					$levRatio = levenshtein($primaryValue, $solution);
					if ($levRatio < $levToleranceAddOne) {
						$personsIds[$item['Id']] = TRUE;
					}
				}
			}
			$result = count($personsIds);
		}
		return $result;
	}
}
