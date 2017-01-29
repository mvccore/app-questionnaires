<?php

namespace App\Models\Questionnaire;

use App\Models;

class Answers extends Models\Base
{
	public static function Create ($personId, $questions, $answers) {
		$db = self::GetDb();
		$resource = self::GetResource(func_get_args());
		$db->beginTransaction();
		$executedInsertResult = $resource->SaveQuestionnaireExecuted();
		$answersInsertCount = $resource->SaveQuestionnaireAnswers();
		$db->commit();
		return array($executedInsertResult, $answersInsertCount);
	}
}