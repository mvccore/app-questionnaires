<?php

namespace App\Models\Questionnaire\Answers;

use App\Models;

class Resource extends Models\Base
{
	private $_personId = 0;
	private $_questions = [];
	private $_answers = [];

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
		$sqlItems = [];
		foreach ($this->_questions as $question) {

			$questionId = $question->Id;
			$questionnaireId = $question->Questionnaire->Id;
			$answer = NULL;
			if (isset($this->_answers[$questionId])) $answer = $this->_answers[$questionId];

			$prepareMethodName = '_prepareSql' . \MvcCore\Tool::GetPascalCaseFromDashed($question->Type);
			list($columns, $valuesGroup) = $this->$prepareMethodName($question, $answer);

			$columns = array_merge(
				['IdQuestionnaire', 'IdQuestion', 'IdPerson'],
				$columns
			);
			if ($this->config->driver == 'mysql') {
				$columnsStr = '`'.implode('`,`', $columns) . '`';
			} else if ($this->config->driver == 'sqlsrv') {
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

	private function _prepareSqlRadios (Models\Question & $question, $answer) {
		$values = [];
		if (strlen($answer) > 0) {
			$values[] = intval($answer);
		}
		return [
			['Option'],
			$values
		];
	}
	private function _prepareSqlCheckboxes (Models\Question & $question, $answer) {
		$values = [];
		foreach ($answer as $optionIndex) {
			$values[] = [intval($optionIndex), 1];
		}
		return [
			['Option', 'Boolean'],
			$values
		];
	}
	private function _prepareSqlConnections (Models\Question & $question, $bfuAnswers) {
		$values = [];
		$questionOptionsCount = count($question->Options);
		foreach ($bfuAnswers as $formFieldIndex => $bfuAnswerIndexStr) {
			$bfuAnswerIndexInt = intval($bfuAnswerIndexStr);
			if ($bfuAnswerIndexInt > 0 && $bfuAnswerIndexInt < $questionOptionsCount + 1) {
				$answerForComputer = $bfuAnswerIndexInt - 1; // bfu users don't know nothing about array indexing from zero
			} else {
				continue;
			}
			$values[] = [$answerForComputer, intval($formFieldIndex)];
		}
		return [
			['Option', 'Integer'],
			$values
		];
	}
	private function _prepareSqlInteger (Models\Question & $question, $answer) {
		$values = [];
		if (strlen($answer) > 0) {
			$values[] = intval($answer);
		}
		return [
			['Integer'],
			$values
		];
	}
	private function _prepareSqlFloat (Models\Question & $question, $answer) {
		$values = [];
		if (strlen($answer) > 0) {
			$values[] = floatval($answer);
		}
		return [
			['Float'],
			$values
		];
	}
	private function _prepareSqlText (Models\Question & $question, $answer) {
		$values = [];
		if (isset($question->Delimiter) && $question->Delimiter) {
			$answersExploded = explode($question->Delimiter, $answer);
			foreach ($answersExploded as $answerExploded) {
				$answerExploded = trim($answerExploded);
				if ($answerExploded) $values[] = $this->db->quote($answerExploded);
			}
		} else {
			$values[] = $this->db->quote($answer);
		}
		return [
			['Varchar'],
			$values
		];
	}
	private function _prepareSqlTextarea (Models\Question & $question, $answer) {
		$values = [];
		if (strlen($answer) > 0) {
			$values[] = $this->db->quote($answer);
		}
		return [
			['Text'],
			$values
		];
	}
	private function _prepareSqlBoolean (Models\Question & $question, $answer) {
		if (strlen($answer) > 0) {
			$values = strtolower($answer) == 'yes' ? [1] : [0] ;
		} else {
			$values = [];
		}
		return [
			['Boolean'],
			$values
		];
	}
	private function _prepareSqlBooleanAndText (Models\Question & $question, $answer) {
		$columns = ['Boolean'];
		$values = [];
		if (isset($answer[0]) > 0 && strlen($answer[0]) > 0) {
			$values[] = strtolower($answer[0]) == 'yes' ? 1 : 0 ;
		}
		if (isset($answer[1]) && mb_strlen($answer[1]) > 0) {
			$columns[] = 'Varchar';
			$values[0] = [$values[0], $this->db->quote($answer[1])];
		}
		return [
			$columns,
			$values
		];
	}
}
