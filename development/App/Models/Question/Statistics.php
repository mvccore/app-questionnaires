<?php

class App_Models_Question_Statistics extends App_Models_Base
{
	public $IdQuestionnaire;
	public $IdQuestion;

	/**
	 * @var MvcCoreExt_Auth_User
	 */
	protected $user = array();
	/**
	 * @var App_Models_Question
	 */
	protected $question = NULL;
	/**
	 * App_Models_Question_Statistics_(MsSql|MySql)_Resource
	 * 
	 * @var App_Models_Question_Statistics_Resource
	 */
	protected $resource = NULL;
	/**
	 * @var int
	 */
	protected $questionAnsweringPersonsCount = -1;
	/**
	 * @var int
	 */
	protected $questionnaireAnsweringPersonsCount = -1;

	// faster parent method variant:
	public static final function GetInstance () {
		list($user, $question) = func_get_args();
		$username = !is_null($user) ? $user->UserName : '';
		$instanceIndex = __CLASS__.".$username.{$question->Questionnaire->Id}.{$question->Id}";
		if (!isset(self::$instances[$instanceIndex])) {
			self::$instances[$instanceIndex] = new self($user, $question);
		}
		return self::$instances[$instanceIndex];
	}
	public function __construct (MvcCoreExt_Auth_User & $user = NULL, App_Models_Question & $question = NULL) {
		$this->user = & $user;
		$this->question = & $question;
		$this->IdQuestionnaire = $question->Questionnaire->Id;
		$this->IdQuestion = $question->Id;

		//parent::__construct(); // do not create resource in standard way
		$this->cfg = self::getCfg(self::$connectionIndex);
		$this->db = self::getDb(self::$connectionIndex);
		return $this;
	}
	public function Load (& $filterData) {
		$resource = self::_getResource(array($this->question, $filterData, $this->user));
		$this->questionnaireAnsweringPersonsCount	= $resource->LoadAllQuestionnairePersonsCount();
		$this->questionAnsweringPersonsCount		= $resource->LoadQuestionAnsweringPersonsCount();
		$methodsNameLastPart = MvcCore_Tool::GetPascalCaseFromDashed($this->question->Type);
		$loadMethodName = 'LoadStatisticsFor' . $methodsNameLastPart;
		$handleMethodName = 'HandleStatisticsFor' . $methodsNameLastPart;
		$resourceData = $resource->$loadMethodName();
		return $this->$handleMethodName($resourceData);
	}
	public function HandleStatisticsForConnections (& $result) {
		$translator = App_Models_Translator::GetInstance();
		$this->_setUpGraphsValuesByQuestionOptions($result->PresentedOptionsCounts, 'Options');
		$this->_setUpGraphsValuesByQuestionOptions($result->PresentedAnswersCounts, 'Connections');
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->PresentedOptionsCounts, FALSE, $this->questionnaireAnsweringPersonsCount);
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->PresentedAnswersCounts, FALSE, $this->questionnaireAnsweringPersonsCount);
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->PersonsAnswersCounts, FALSE, $this->questionnaireAnsweringPersonsCount);
		$this->_handleStatisticsForConnectionsManageMostOfftenConnections($result);
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->MostOfftenConnections, FALSE, $this->questionnaireAnsweringPersonsCount);
		if (count($result->MostOfftenConnections) > 0) {
			$lastMostOfftenConnection = $result->MostOfftenConnections[count($result->MostOfftenConnections) - 1];
		}
		if (count($result->MostOfftenConnections) > 0 && isset($lastMostOfftenConnection->Count)) 
			$lastMostOfftenConnection->Label = (string) $lastMostOfftenConnection->Count;
		if ($this->user && isset($this->question->Solution)) {
			$this->_handleStatisticsForConnectionsManageConnectionsCorrectness($result);
			$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->ConnectionsCorrectness, FALSE, $this->questionnaireAnsweringPersonsCount);
			$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->PeopleCorrectness, FALSE, $this->questionnaireAnsweringPersonsCount);
			$completlyCorrectConnectionsCount = $result->PeopleCorrectness[count($result->PeopleCorrectness) - 1]->Count;
			$result->CorrectAnswers = array(
				array( 'Value'	=> $translator->Translate('Completely correct answers count'), 'Count'	=> $completlyCorrectConnectionsCount, ),
				array( 'Value'	=> $translator->Translate('Incorrect answers count'), 'Count'	=> $this->questionnaireAnsweringPersonsCount - $completlyCorrectConnectionsCount, ),
			);
			$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->CorrectAnswers, FALSE, $this->questionnaireAnsweringPersonsCount);
		}
		foreach ($result->PersonsAnswersCounts as & $item) {
			$key = (($item->Value === 1) ? '1 answered connection' : ($item->Value > 1 && $item->Value < 5 ? '{0} answered connections (plural: 2-4)' : '{0} answered connections (plural: 0,5-Infinite)' ));
			$item->Value = str_replace('{0}', $item->Value, $translator->Translate($key));
		}
		if ($this->user) {
			$answeredPersonsPercentage = round(($this->questionAnsweringPersonsCount / $this->questionnaireAnsweringPersonsCount) * 1000) / 10;
			$result->Summary = array(
				array('Total respondents count',	$this->questionnaireAnsweringPersonsCount),
				array('Total answers count',		$this->questionAnsweringPersonsCount, $answeredPersonsPercentage + ' %'),
				array('Completely no answers count',$this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount, 100 - $answeredPersonsPercentage),
			);
			if (isset($this->question->Solution) && isset($result->PeopleCorrectness)) {
				$result->Summary[] = array('Completely correct answers count', $result->CorrectAnswers[0]->Count, $result->CorrectAnswers[0]->Percentage);
				$result->Summary[] = array('Incorrect answers count', $result->CorrectAnswers[1]->Count, $result->CorrectAnswers[1]->Percentage);
			}
		}
		$this->_addTranslationsIntoResult($result, array(
			'Question Options', 'Question Answers', 'Question Option', 'Question Answer',
			'Answers counts, where option was presented', 'Answers percentages, where option was presented', 
			'Any presented option count', 'Any presented option percentage',
			'Connections Counts In Answer', 'Answered Connections Counts', 'Answered Connections Percentages',
			'Most Often Answered Connections', 'Options Connections', 'Number Of Correct And Incorrect Respondents',
			'Correctly answered respondents', 'Incorrectly answered respondents', 
			'Respondents Counts', 'Correctly Connected Options Count',
			'1 right answer', '{0} right answers (plural: 2-4)', '{0} right answers (plural: 0,5-Infinite)',
		));
		$this->_setUpInvolvedPieGraphDataAndTranslations($result);
		return $result;
	}
	public function HandleStatisticsForCheckboxes (& $result) {
		$translator = App_Models_Translator::GetInstance();
		$this->_setUpGraphsValuesByQuestionOptions($result->Overview, 'Checkboxes');
		if ($this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount > 0) {
			array_unshift($result->SelectedOptionsCountsInAnswer, array(
				'Value'	=> 0,
				'Count'	=> $this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount,
			));
		}
		if (isset($result->OptionsCorrectness)) $this->_setUpGraphsValuesByQuestionOptions($result->OptionsCorrectness, 'Checkboxes');
		$this->_setUpGraphsPercentageValuesInAllGraphsData($result);
		if (isset($result->OptionsCorrectness)) $this->_setUpGraphsPercentageValuesInSingleGraphsData($result->OptionsCorrectness, FALSE, $this->questionAnsweringPersonsCount);
		foreach ($result->SelectedOptionsCountsInAnswer as & $item) {
			$translationKey = ($item->Value === 1 ? '1 selected option' : ($item->Value > 1 && $item->Value < 5) ? '{0} selected options (plural: 2-4)' : '{0} selected options (plural: 0,5-Infinite)');
			$item->Value = str_replace('{0}', $item->Value, $translator->Translate($translationKey));
		}
		$overviewData = & $result->Overview;
		// complete summary if user authenticated
		if ($this->user) {
			$answeredPersonsPercentage = round(($this->questionAnsweringPersonsCount / $this->questionnaireAnsweringPersonsCount) * 1000) / 10;
			$result->Summary = array(
				array('Total respondents count',$this->questionnaireAnsweringPersonsCount),
				array('Total answers count',	$this->questionAnsweringPersonsCount, $answeredPersonsPercentage + ' %'),
				array('No answers count',		$this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount, 100 - $answeredPersonsPercentage),
			);
			if (isset($this->question->Solution) && isset($result->PeopleCorrectness)) {
				$solutionCount = count($this->question->Solution);
				foreach ($result->PeopleCorrectness as $peopleCorrectnessItem) {
					if ($peopleCorrectnessItem->CorrectlyAnsweredOptions === $solutionCount && $peopleCorrectnessItem->IncorrectlyAnsweredOptions === 0) {
						$incorrectAnswersCount = $this->questionAnsweringPersonsCount - $peopleCorrectnessItem->PersonsCount;
						$result->Summary[] = array('Completely correct answers count', $peopleCorrectnessItem->PersonsCount, $peopleCorrectnessItem->PersonsPercentage);
						$result->Summary[] = array('Incorrect answers count', $incorrectAnswersCount, 100 - $peopleCorrectnessItem->PersonsPercentage);
						break;
					}
				}
			}
		}
		$this->_addTranslationsIntoResult($result, array(
			'Selections Percentages', 'Selections Counts', 'Question Options',
			'Selected Options Counts In Answer', 'Respondents Percentages', 'Respondents Counts',
			'Number Of Correct And Incorrect Respondents', 'Correctly answered respondents', 'Incorrectly answered respondents',
			'Combination Of Correctly And Incorrectly Answered Options Counts', 'Completely correct answers count',
			'1 right answer', '{0} right answers (plural: 2-4)', '{0} right answers (plural: 0,5-Infinite)',
			'1 wrong answer', '{0} wrong answers (plural: 2-4)', '{0} wrong answers (plural: 0,5-Infinite)',
		));
		$this->_setUpInvolvedPieGraphDataAndTranslations($result);
		return $result;
	}
	public function HandleStatisticsForRadios (& $result) {
		$translator = App_Models_Translator::GetInstance();
		$this->_setUpGraphsValuesByQuestionOptions($result->Overview, 'Radios');
		if ($this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount > 0) {
			array_unshift($result->Overview, array(
				'Value'	=> $translator->Translate('No answer'),
				'Count'	=> $this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount,
			));
		}
		if ($this->user) {
			if (isset($this->question->Solution) && isset($result->CorrectAnswersCount)) {
				$correctAnswersCount = $result->CorrectAnswersCount;
				$incorrectAnswersCount = $this->questionnaireAnsweringPersonsCount - $correctAnswersCount;
				unset($result->CorrectAnswersCount);
				$result->CorrectAnswers = array(
					array( 'Value'	=> $translator->Translate('Correct answers count'), 'Count'	=> $correctAnswersCount, ),
					array( 'Value'	=> $translator->Translate('Incorrect answers count'), 'Count'	=> $incorrectAnswersCount, ),
				);
			}
		}
		$this->_setUpGraphsPercentageValuesInAllGraphsData($result, FALSE, $this->questionnaireAnsweringPersonsCount);
		$this->_setUpInvolvedPieGraphDataAndTranslations($result);
		if ($this->user) {
			$answeredPersonsPercentage = round(($this->questionAnsweringPersonsCount / $this->questionnaireAnsweringPersonsCount) * 1000) / 10;
			$result->Summary = array(
				array('Total respondents count',$this->questionnaireAnsweringPersonsCount),
				array('Total answers count',	$this->questionAnsweringPersonsCount, $answeredPersonsPercentage + ' %'),
				array('No answers count',		$this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount, 100 - $answeredPersonsPercentage),
			);
			if (isset($this->question->Solution)) {
				$correctAnswersCountPercentage = round($result->CorrectAnswers[0]->Count / $this->questionnaireAnsweringPersonsCount * 1000) / 10;
				$incorrectAnswersCountPercentage = 100 - $correctAnswersCountPercentage;
				$result->Summary[] = array('Correct answers count', $result->CorrectAnswers[0]->Count, $correctAnswersCountPercentage);
				$result->Summary[] = array('Incorrect answers count', $result->CorrectAnswers[1]->Count, $incorrectAnswersCountPercentage);
				
			}
		}
		$this->_addTranslationsIntoResult($result, array(
			'Presented values', 'Respondents Percentages', 'Respondents Counts', 
			'Presented value', 'Respondents Count', 'Respondents Percentage',
		));
		return $result;
	}
	public function HandleStatisticsForInteger (& $result) {
		return $this->_handleStatisticsForIntegerAndFloat($result);
	}
	public function HandleStatisticsForFloat (& $result) {
		return $this->_handleStatisticsForIntegerAndFloat($result);
	}
	public function HandleStatisticsForBoolean (& $result) {
		// add no answers counts
		$translator = App_Models_Translator::GetInstance();
		$result->Overview[] =  array(
			'Value'	=> $translator->Translate('No answer'),
			'Count'	=> $this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount,
		);
		// set up percentage and label values (by user authentication) and unset counts if necessary
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->Overview, TRUE, $this->questionnaireAnsweringPersonsCount);
		$overviewData = & $result->Overview;
		// complete summary if user authenticated
		if ($this->user) {
			$answeredPersonsPercentage = round((100 - $overviewData[2]->Percentage) * 10) / 10;
			$result->Summary = array(
				array('Total respondents count',$this->questionnaireAnsweringPersonsCount),
				array('Total answers count',	$this->questionAnsweringPersonsCount, $answeredPersonsPercentage + ' %'),
				array('No answers count',		$overviewData[2]->Count, $overviewData[2]->Percentage),
				array('Positive answers count',	$overviewData[0]->Count, $overviewData[0]->Percentage),
				array('Negative answers count',	$overviewData[1]->Count, $overviewData[1]->Percentage),
			);
			if (isset($this->question->Solution)) {
				$solution = boolval($this->question->Solution);
				$correctRecord = $overviewData[$solution ? 0 : 1];
				$correctAnswersCount = $correctRecord->Count;
				$incorrectAnswersCount = $this->questionnaireAnsweringPersonsCount - $correctAnswersCount;
				$incorrectAnswersPercentage = 100 - $correctRecord->Percentage;
				$correctAnswersPercentage = 100 - $incorrectAnswersPercentage;
				$result->Summary[] = array('Correct answers count',		$correctAnswersCount, $correctAnswersPercentage);
				$result->Summary[] = array('Incorrect answers count',	$incorrectAnswersCount, $incorrectAnswersPercentage);
			}
		}
		return $result;
	}
	public function HandleStatisticsForBooleanAndText (& $result) {
		$translator = App_Models_Translator::GetInstance();
		if ($this->user) {
			if (isset($this->question->Solution)) {
				$correctPersonsCount = $result->CorrectPersonsCount;
				$incorectPersonscount = $this->questionnaireAnsweringPersonsCount - $result->CorrectPersonsCount;
				$result->CorrectAnswers = array(
					array( 'Value'	=> $translator->Translate('Number of people with at least one correct answer'), 'Count'	=> $correctPersonsCount, ),
					array( 'Value'	=> $translator->Translate('Number of people with no one right answer'), 'Count'	=> $incorectPersonscount, ),
				);
				$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->CorrectAnswers, TRUE, $this->questionnaireAnsweringPersonsCount);
			}
		}
		$result = $this->HandleStatisticsForBoolean($result);
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->AllTextAnswers, FALSE, $this->questionnaireAnsweringPersonsCount);
		$this->_addTranslationsIntoResult($result, array(
			'Answered Texts', 'Respondents Percentages', 'Respondents Counts',
			'Counts', 'Percentages', 'Correct', 'Yes', 'No',
		));
		return $result;
	}
	public function HandleStatisticsForText (& $result) {
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->Overview, FALSE, $this->questionnaireAnsweringPersonsCount);
		if ($this->user) {
			$answeredPersonsPercentage = round(($this->questionAnsweringPersonsCount / $this->questionnaireAnsweringPersonsCount) * 1000) / 10;
			$noAnswersCount = $this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount;
			$result->Summary = array(
				array('Total respondents count',$this->questionnaireAnsweringPersonsCount),
				array('Total answers count',	$this->questionAnsweringPersonsCount, $answeredPersonsPercentage + ' %'),
				array('No answers count',		$noAnswersCount, 100 - $answeredPersonsPercentage),
			);
			if (isset($this->question->Solution)) {
				$translator = App_Models_Translator::GetInstance();
				$correctPersonsCount = $result->CorrectPersonsCount;
				$incorectPersonscount = $this->questionnaireAnsweringPersonsCount - $result->CorrectPersonsCount;
				$result->CorrectAnswers = array(
					array( 'Value'	=> $translator->Translate('Number of people with at least one correct answer'), 'Count'	=> $correctPersonsCount, ),
					array( 'Value'	=> $translator->Translate('Number of people with no one right answer'), 'Count'	=> $incorectPersonscount, ),
				);
				$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->CorrectAnswers, TRUE, $this->questionnaireAnsweringPersonsCount);

				$correctPersonsPercentage = round(($result->CorrectPersonsCount / $this->questionnaireAnsweringPersonsCount) * 1000) / 10;
				$incorrectPersonsPercentage = 100 - $correctPersonsPercentage;
				$result->Summary[] = array('Correct persons count',		$result->CorrectPersonsCount, $correctPersonsPercentage);
				$result->Summary[] = array('Incorrect persons count',	$this->questionnaireAnsweringPersonsCount - $result->CorrectPersonsCount, $incorrectPersonsPercentage);
				$result->Summary[] = array('Correct text answers count',$result->CorrectAnswersCount, );
			}
		}
		$this->_addTranslationsIntoResult($result, array(
			'Answered Texts', 'Respondents Percentages', 'Respondents Counts',
			'Counts', 'Percentages', 'Correct', 'Yes', 'No',
		));
		$this->_setUpInvolvedPieGraphDataAndTranslations($result);
		return $result;
	}

	public function HandleStatisticsForTextarea (& $result) {
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->Overview, FALSE, $this->questionnaireAnsweringPersonsCount);
		$this->_addTranslationsIntoResult($result, array(
			'Answered Texts', 'Respondents Percentages', 'Respondents Counts',
			'Counts', 'Percentages',
		));
		$this->_setUpInvolvedPieGraphDataAndTranslations($result);
		return $result;
	}
	private function _handleStatisticsForIntegerAndFloat (& $result) {
		$translator = App_Models_Translator::GetInstance();
		if ($this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount > 0) {
			array_unshift($result->Overview, array(
				'Value'	=> $translator->Translate('No answer'),
				'Count'	=> $this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount,
			));
		}
		if ($this->user) {
			if (isset($this->question->Solution) && isset($result->CorrectAnswersCount)) {
				$correctAnswersCount = $result->CorrectAnswersCount;
				$incorrectAnswersCount = $this->questionnaireAnsweringPersonsCount - $correctAnswersCount;
				unset($result->CorrectAnswersCount);
				$result->CorrectAnswers = array(
					array( 'Value'	=> $translator->Translate('Correct answers count'), 'Count'	=> $correctAnswersCount, ),
					array( 'Value'	=> $translator->Translate('Incorrect answers count'), 'Count'	=> $incorrectAnswersCount, ),
				);
			}
		}
		$this->_setUpGraphsPercentageValuesInAllGraphsData($result, FALSE, $this->questionnaireAnsweringPersonsCount);
		$this->_setUpInvolvedPieGraphDataAndTranslations($result);
		if ($this->user) {
			$answeredPersonsPercentage = round(($this->questionAnsweringPersonsCount / $this->questionnaireAnsweringPersonsCount) * 1000) / 10;
			$result->Summary = array(
				array('Total respondents count',$this->questionnaireAnsweringPersonsCount),
				array('Total answers count',	$this->questionAnsweringPersonsCount, $answeredPersonsPercentage + ' %'),
				array('No answers count',		$this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount, 100 - $answeredPersonsPercentage),
			);
			if (isset($this->question->Solution)) {
				$correctAnswersCountPercentage = round($result->CorrectAnswers[0]->Count / $this->questionnaireAnsweringPersonsCount * 1000) / 10;
				$incorrectAnswersCountPercentage = 100 - $correctAnswersCountPercentage;
				$result->Summary[] = array('Correct answers count', $result->CorrectAnswers[0]->Count, $correctAnswersCountPercentage);
				$result->Summary[] = array('Incorrect answers count', $result->CorrectAnswers[1]->Count, $incorrectAnswersCountPercentage);
				
			}
		}
		$this->_addTranslationsIntoResult($result, array(
			'Presented values', 'Respondents Percentages', 'Respondents Counts', 
			'Presented value', 'Respondents Count', 'Respondents Percentage',
		));
		return $result;
	}
	private function _handleStatisticsForConnectionsManageMostOfftenConnections (& $result) {
		$translator = App_Models_Translator::GetInstance();
		$counter = 0;
		$newMostOfftenConnections = array();
		$addCorrectness = $this->user && isset($this->question->Solution);
		foreach ($result->MostOfftenConnections as & $item) {
			if ($counter > 10 || $item['Count'] === 1) {
				break;
			}
			$optionAndInteger = explode('_', $item['OptionAndInteger']);
			$optionIndex = intval($optionAndInteger[0]);
			$integerIndex = intval($optionAndInteger[1]);
			$option = trim($this->question->Options[$optionIndex]);
			$answer = trim($this->question->Connections[$integerIndex]);
			$item['Option'] = $translator->Translate($option);
			$item['Answer'] = $translator->Translate($answer);
			$item['Value'] = $item['Option']. ': ' . $item['Answer'];
			unset($item['OptionAndInteger']);
			$counter += 1;
			if ($addCorrectness) {
				$correct = 0;
				if (isset($this->question->Solution[$optionIndex]) && $this->question->Solution[$optionIndex] === $integerIndex) {
					$correct = 1;
				}
				$item['Correct'] = $correct;
			}
			$newMostOfftenConnections[] = $item;
		}
		if ($counter  < count($result->MostOfftenConnections)) {
			$restConnectionsCount = 0;
			for ($i = $counter, $l = count($result->MostOfftenConnections); $i < $l; $i += 1) {
				$restConnectionsCount += $result->MostOfftenConnections[$i]['Count'];
			}
			$newMostOfftenConnections[] = array(
				'Count'	=> $restConnectionsCount,
				'Option'=> $translator->Translate('Other Rest Options Combinations'),
				'Answer'=> $translator->Translate('Other Rest Answers Combinations'),
				'Value'	=> $translator->Translate('Other Rest Connections'),
			);
			if ($addCorrectness) {
				$newMostOfftenConnections[count($newMostOfftenConnections) - 1]['Correct'] = -1;
			}
		}
		$result->MostOfftenConnections = $newMostOfftenConnections;
	}
	private function _handleStatisticsForConnectionsManageConnectionsCorrectness (& $result) {
		$translator = App_Models_Translator::GetInstance();
		foreach ($result->ConnectionsCorrectness as & $item) {
			$optionIndex = $item['Value'];
			$questionSolutionOptionValue = $this->question->Solution[$optionIndex];
			$option = trim($this->question->Options[$optionIndex]);
			$answer = trim($this->question->Connections[$questionSolutionOptionValue]);
			$item['Option'] = $translator->Translate($option);
			$item['Answer'] = $translator->Translate($answer);
			$item['Value'] = $item['Option'] . ': ' . $item['Answer'];
			$item['Count1'] = $item['CorrectAnswersCount'];
			$item['Count0'] = $item['AllAnswersCount'] - $item['CorrectAnswersCount'];
			unset($item['AllAnswersCount'], $item['CorrectAnswersCount']);
		}
	}
	private function _setUpGraphsPercentageValuesInAllGraphsData (& $result, $includeValuesInLabels = FALSE, $totalCount = -1) {
		foreach ($result as & $graphData) {
			if (gettype($graphData) != 'array') continue;
			$this->_setUpGraphsPercentageValuesInSingleGraphsData($graphData, $includeValuesInLabels, $totalCount);
		}
	}
	private function _setUpGraphsPercentageValuesInSingleGraphsData (& $graphData, $includeValuesInLabels = FALSE, $totalCount = -1) {
		// for each property and value containing 'count' word in $key - complete total count
		$totalCounts = array();
		if ($totalCount == -1) {
			foreach ($graphData as $dataKey1 => & $dataItem1) {
				foreach ($dataItem1 as $key => $item) {
					if (strpos($key, 'Count') !== FALSE) {
						if (!isset($totalCounts[$key])) $totalCounts[$key] = 0;
						$totalCounts[$key] += $item;
					}
				}
			}
		}
		// for each property and value containing 'count' word in $key - make percentage and label equivalent
		foreach ($graphData as $dataKey2 => & $dataItem2) {
			$newDataItem = new stdClass;
			foreach ($dataItem2 as $key => $item) {
				if (strpos($key, 'Count') !== FALSE) {
					$itemTotalCount = $totalCount > -1 ? $totalCount : $totalCounts[$key];
					$percentage = $itemTotalCount > 0 ? round(($item / $itemTotalCount) * 1000) / 10 : 0;
					$valueKey = str_replace('Count', 'Value', $key);
					$percentageKey = str_replace('Count', 'Percentage', $key);
					$labelKey = str_replace('Count', 'Label', $key);
					$newDataItem->$percentageKey = $percentage;
					$labelData = $includeValuesInLabels ? array($dataItem2[$valueKey]) : array();
					if ($this->user) {
						$labelData[] = $item;
						$labelData[] = "(" . $percentage . " %)";
						$newDataItem->$key = $item;
					} else {
						$labelData[] = $percentage . ' %';
					}
					$newDataItem->$labelKey = implode("\n", $labelData);
				} else if (strpos($key, 'Percentage') === FALSE && strpos($key, 'Label') === FALSE) {
					$newDataItem->$key = $item;
				}
			}
			$graphData[$dataKey2] = $newDataItem;
		}
	}
	private function _setUpGraphsValuesByQuestionOptions (& $graphData, $optionsKey = '') {
		$questionOptions = $this->question->$optionsKey;
		foreach ($graphData as $dataKey => & $dataItem) {
			$value = $dataItem['Value'];
			if (isset($questionOptions[$value])) {
				$dataItem['Value'] = trim($questionOptions[$value]);
			}
		}
	}
	private function _setUpInvolvedPieGraphDataAndTranslations (& $result) {
		$translator = App_Models_Translator::GetInstance();
		$result->Involved = array(
			array(
				'Value' => $translator->Translate('Answered'),
				'Count'	=> $this->questionAnsweringPersonsCount,
			),
			array(
				'Value' => $translator->Translate('Did not answer'),
				'Count'	=> $this->questionnaireAnsweringPersonsCount - $this->questionAnsweringPersonsCount,
			),
		);
		$this->_setUpGraphsPercentageValuesInSingleGraphsData($result->Involved, TRUE);
	}
	private function _addTranslationsIntoResult (& $result, $translations = array()) {
		if (!isset($result->Translations)) $result->Translations = array();
		$translator = App_Models_Translator::GetInstance();
		foreach ($translations as $translation) {
			$result->Translations[$translation] = $translator->Translate($translation);
		}
	}
	private static function _getResource () {
		$cfg = self::getCfg();
		$resourceClassPath = '_Resource';
		if ($cfg->driver == 'mysql') {
			$resourceClassPath .= '_MySql';
		} else if ($cfg->driver == 'mssql') {
			$resourceClassPath .= '_MsSql';
		}
		return parent::getResource(func_get_arg(0), __CLASS__, $resourceClassPath);
	}
}