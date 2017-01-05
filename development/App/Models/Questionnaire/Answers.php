<?php

class App_Models_Questionnaire_Answers extends App_Models_Base
{
	public static function Create ($personId, $questions, $answers) {
		$db = self::getDb();
		$resource = self::getResource(func_get_args());
		$db->beginTransaction();
		$executedInsertResult = $resource->SaveQuestionnaireExecuted();
		$answersInsertCount = $resource->SaveQuestionnaireAnswers();
		$db->commit();
		return array($executedInsertResult, $answersInsertCount);
	}
}