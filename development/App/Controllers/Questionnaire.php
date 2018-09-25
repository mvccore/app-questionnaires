<?php

namespace App\Controllers;

use \App\Forms,
	\App\Models,
	\MvcCore\Ext\Form;

class Questionnaire extends Base
{
	protected $renderNotFoundIfNoDocument = FALSE;

	protected $path;
	protected $questionnaire;
	protected $questions;
	/**
	 * @var \App\Forms\Questionnaire
	 */
	private $_questionnaireForm;
	
	public function Init () {
		parent::Init();
		$this->initSetUpQuestionnaireAndQuestions();
	}

	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->viewEnabled) {
			$this->view->document = $this->questionnaire;
		}
		$this->setUpForm();
		$this->setUpAssets();
	}
	
	public function IndexAction () {
		// xx($this->questionnaire);
		$this->view->questionnaire = $this->questionnaire;
		$this->view->questionnaireForm = $this->_questionnaireForm;
		$this->view->displayFacebookShare = isset($this->questionnaire->FacebookShare) && $this->questionnaire->FacebookShare;
	}
	public function SubmitAction () {
		list ($result, $data, $errors) = $this->_questionnaireForm->Submit();
		//x([$result, $data, $errors]);
		if ($result === Form::RESULT_SUCCESS) {
			$personData = $this->_submitCompleteData($data, 'person_', 'string');
			$answerData = $this->_submitCompleteData($data, 'question_', 'int');
			$person = Models\Person::Create($personData);
			Models\Questionnaire\Answers::Create($person->Id, $this->questions, $answerData);
		}
		$this->_questionnaireForm->ClearSession();
		$this->_questionnaireForm->SubmittedRedirect();
	}
	public function CompletedAction () {
		$this->view->backLink = $this->questionnaire->GetUrl();
		$this->view->path = $this->path;
	}

	protected function initSetUpQuestionnaireAndQuestions () {
		$this->path = $this->GetParam('path', "a-zA-Z0-9_\-");
		
		$matchedQrs = Models\Questionnaire::GetByPathMatch(
			"^([0-9]*)\-" . str_replace('-', '\\-', $this->path) . "$"
		);
		
		if (count($matchedQrs) === 0) {
			\MvcCore\Debug::Log("[".__CLASS__."] No questionnaire found in path: '$this->path'.");
			$this->document = new Models\Document();
			$this->renderNotFound();
		} else if (count($matchedQrs) > 1) {
			\MvcCore\Debug::Log("[".__CLASS__."] Ambiguous request to the questionnaire in path: '$this->path'..");
		}
		
		$this->setUpQuestionnaireAndQuestions($matchedQrs[0]);
	}
	protected function setUpQuestionnaireAndQuestions (Models\Questionnaire $questionnaire) {
		$title = strip_tags($questionnaire->Title);
		$description = strip_tags($questionnaire->Description);
		if (!$questionnaire->Keywords) $questionnaire->Keywords = $description;
		if (!$questionnaire->OgTitle) $questionnaire->OgTitle = $title;
		if (!$questionnaire->OgDescription) $questionnaire->OgDescription = $description;
		if (!$questionnaire->ItempropName) $questionnaire->ItempropName = $title;
		if (!$questionnaire->ItempropDescription) $questionnaire->ItempropDescription = $description;

		$this->questionnaire = $questionnaire;
		$this->questions = $this->questionnaire->GetQuestions();
		$this->document = $this->questionnaire;
		$this->setUpLangAndLocaleByDocument();
	}
	protected function setUpAssets () {
		if ($this->viewEnabled) {
			$static = self::$staticPath;
			$this->view->Css('varHead')
				->AppendRendered($static . '/css/front/person.all.css')
				->AppendRendered($static . '/css/front/person.' . $this->mediaSiteVersion . '.css')
				->AppendRendered($static . '/css/front/questionnaire.all.css')
				->AppendRendered($static . '/css/front/questionnaire.' . $this->mediaSiteVersion . '.css');
		}
	}
	protected function setUpForm () {
		$form = new Forms\Questionnaire($this);
		$form
			->SetTranslator(function ($key, $lang = NULL) {
				return $this->Translate($key, $lang);
			})
			->SetJsSupportFilesRenderer(function (\SplFileInfo $jsFile) {
				$this->addAsset('Js', 'varHead', $jsFile);
			})
			->SetLang($this->request->GetLang())
			->SetMethod(\MvcCore\Ext\Form::METHOD_POST)
			->SetAction($this->Url('Questionnaire:Submit', ['path' => $this->path]))
			->SetSuccessUrl($this->Url('Questionnaire:Completed', ['path' => $this->path]))
			->SetErrorUrl($this->Url('Questionnaire:Index', ['path' => $this->path]))
			->SetQuestionnaire($this->questionnaire);
		$this->_questionnaireForm = $form;
	}

	private function _submitCompleteData ($data, $keyPrefix, $resultKeyType = 'string') {
		$result = [];
		$keyPrefixLen = strlen($keyPrefix);
		foreach ($data as $key => $value) {
			if (strpos($key, $keyPrefix) === 0) {
				$resultKey = substr($key, $keyPrefixLen);
				if ($resultKeyType == 'int') {
					$resultKey = intval($resultKey);
				}
				$result[$resultKey] = $value;
			}
		}
		return $result;
	}
}
