<?php

class App_Models_Questionnaire_Answers_Resource extends App_Models_Base
{
	private $_personId = 0;
	private $_questions = array();
	private $_answers = array();

	public function __construct ($personId, $questions, $answers) {
		parent::__construct();
		$this->_personId = $personId;
		$this->_questions = $questions;
		$this->_answers = $answers;
    }

	public function SaveQuestionnaireExecuted () {
		$table = self::TABLE_EXECUTED;
		$personId = $this->_personId;
		$questionnaireId = current($this->_questions)->Questionnaire->Id;
		$sql = "INSERT INTO $table (IdPerson,IdQuestionnaire) VALUES ($personId, $questionnaireId);";
		$insertCmd = $this->db->prepare($sql);
		return $insertCmd->execute();
	}

	public function SaveQuestionnaireAnswers () {
		$table = self::TABLE_ANSWERS;
		$sqlItems = array();
		foreach ($this->_questions as $question) {

			$questionId = $question->Id;
			$questionnaireId = $question->Questionnaire->Id;
			$answer = NULL;
			if (isset($this->_answers[$questionId])) $answer = $this->_answers[$questionId];

			$prepareMethodName = '_prepareSql' . MvcCore_Tool::GetPascalCaseFromDashed($question->Type);
			list($columns, $valuesGroup) = $this->$prepareMethodName($question, $answer);

			$columns = array_merge(
				array('IdQuestionnaire', 'IdQuestion', 'IdPerson'),
				$columns
			);
			if ($this->cfg->driver == 'mysql') {
				$columnsStr = '`'.implode('`,`', $columns) . '`';
			} else if ($this->cfg->driver == 'mssql') {
				$columnsStr = '['.implode('],[', $columns) . ']';
			} else {
				$columnsStr = implode(',', $columns);
			}
			foreach ($valuesGroup as $values) {
				if (gettype($values) == 'array') {
					$valuesStr = implode(',', $values);
				} else {
					$valuesStr = (string) $values;
				}
				$valuesStr = "$questionnaireId, $questionId, $this->_personId, $valuesStr";
				$sqlItem = "INSERT INTO $table ($columnsStr) VALUES ($valuesStr)";
				$sqlItems[] = $sqlItem;
			}
		}
		if (!$sqlItems) return 0;
		$sql = implode(";\n", $sqlItems) . ';';
		$insertCmd = $this->db->prepare($sql);
		return $insertCmd->execute();
	}

	private function _prepareSqlRadios (App_Models_Question & $question, $answer) {
		$values = array();
		if (strlen($answer) > 0) {
			$values[] = intval($answer);
		}
		return array(
			array('Option'),
			$values
		);
	}
	private function _prepareSqlCheckboxes (App_Models_Question & $question, $answer) {
		$values = array();
		foreach ($answer as $optionIndex) {
			$values[] = array(intval($optionIndex), 1);
		}
		return array(
			array('Option', 'Boolean'),
			$values
		);
	}
	private function _prepareSqlConnections (App_Models_Question & $question, $bfuAnswers) {
		$values = array();
		$questionOptionsCount = count($question->Options);
		foreach ($bfuAnswers as $formFieldIndex => $bfuAnswerIndexStr) {
			$bfuAnswerIndexInt = intval($bfuAnswerIndexStr);
			if ($bfuAnswerIndexInt > 0 && $bfuAnswerIndexInt < $questionOptionsCount + 1) {
				$answerForComputer = $bfuAnswerIndexInt - 1; // bfu users don't know nothing about array indexing from zero
			} else {
				continue;
			}
			$values[] = array($answerForComputer, intval($formFieldIndex));
		}
		return array(
			array('Option', 'Integer'),
			$values
		);
	}
	private function _prepareSqlInteger (App_Models_Question & $question, $answer) {
		$values = array();
		if (strlen($answer) > 0) {
			$values[] = intval($answer);
		}
		return array(
			array('Integer'),
			$values
		);
	}
	private function _prepareSqlFloat (App_Models_Question & $question, $answer) {
		$values = array();
		if (strlen($answer) > 0) {
			$values[] = floatval($answer);
		}
		return array(
			array('Float'),
			$values
		);
	}
	private function _prepareSqlText (App_Models_Question & $question, $answer) {
		$values = array();
		if (isset($question->Delimiter) && $question->Delimiter) {
			$answersExploded = explode($question->Delimiter, $answer);
			foreach ($answersExploded as $answerExploded) {
				$answerExploded = trim($answerExploded);
				if ($answerExploded) $values[] = $this->db->quote($answerExploded);
			}
		} else {
			$values[] = $this->db->quote($answer);
		}
		return array(
			array('Varchar'),
			$values
		);
	}
	private function _prepareSqlTextarea (App_Models_Question & $question, $answer) {
		$values = array();
		if (strlen($answer) > 0) {
			$values[] = $this->db->quote($answer);
		}
		return array(
			array('Text'),
			$values
		);
	}
	private function _prepareSqlBoolean (App_Models_Question & $question, $answer) {
		if (strlen($answer) > 0) {
			$values = strtolower($answer) == 'yes' ? array(1) : array(0) ;
		} else {
			$values = array();
		}
		return array(
			array('Boolean'),
			$values
		);
	}
	private function _prepareSqlBooleanAndText (App_Models_Question & $question, $answer) {
		$columns = array('Boolean');
		$values = array();
		if (isset($answer[0]) > 0 && strlen($answer[0]) > 0) {
			$values[] = strtolower($answer[0]) == 'yes' ? 1 : 0 ;
		}
		if (isset($answer[1]) && mb_strlen($answer[1]) > 0) {
			$columns[] = 'Varchar';
			$values[0] = array($values[0], $this->db->quote($answer[1]));
		}
		return array(
			$columns,
			$values
		);
	}
}
