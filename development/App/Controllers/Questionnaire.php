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
	
	public function PreDispatch () {
		parent::PreDispatch();
		$this->setUpQuestionnaireAndQuestions();
		$this->setUpForm();
		$this->setUpAssets();
	}
	
	public function IndexAction () {
		$this->view->Questionnaire = $this->questionnaire;
		$this->view->QuestionnaireForm = $this->_questionnaireForm;
	}
	public function SubmitAction () {
		list ($result, $data, $errors) = $this->_questionnaireForm->Submit();
		if ($result === Form::RESULT_SUCCESS) {
			$personData = $this->_submitCompleteData($data, 'person_', 'string');
			$answerData = $this->_submitCompleteData($data, 'question_', 'int');
			$person = Models\Person::Create($personData);
			Models\Questionnaire\Answers::Create($person->Id, $this->questions, $answerData);
		}
		$this->_questionnaireForm->ClearSession();
		$this->_questionnaireForm->RedirectAfterSubmit();
	}
	public function CompletedAction () {
		$this->view->BackLink = $this->questionnaire->GetUrl();
		$this->view->Path = $this->path;
	}

	protected function setUpQuestionnaireAndQuestions() {
		$this->path = str_replace('-', '\\-', $this->GetParam('path', "a-zA-Z0-9_\-"));

		$matchedQrs = Models\Questionnaire::GetByPathMatch("^([0-9]*)\-$this->path$");
		
		if (count($matchedQrs) === 0) {
			\MvcCore\Debug::Log("[".__CLASS__."] No questionnaire found in path: '$this->path'.");
			$this->view->Document = new Models\Document();
			$this->renderNotFound();
		} else if (count($matchedQrs) > 1) {
			\MvcCore\Debug::Log("[".__CLASS__."] Ambiguous request to the questionnaire in path: '$this->path'..");
		}

		$this->path = str_replace('\\-', '-', $this->path);

		$questionnaire = $matchedQrs[0];
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
		if (!$this->ajax) $this->view->Document = $this->questionnaire;
	}
	protected function setUpAssets () {
		if ($this->viewEnabled) {
			$static = self::$staticPath;
			$this->view->Css('varHead')
				->AppendRendered($static . '/css/front/person.all.css')
				->AppendRendered($static . '/css/front/person.' . $this->mediaSiteKey . '.css')
				->AppendRendered($static . '/css/front/questionnaire.all.css')
				->AppendRendered($static . '/css/front/questionnaire.' . $this->mediaSiteKey . '.css');
		}
	}
	protected function setUpForm () {
		$form = new Forms\Questionnaire($this);

		$form
			->SetTranslator(function ($key = '', $lang = '') {
				return $this->Translate($key, $lang ? $lang : Base::$Lang);
			})
			->SetJsRenderer(function (\SplFileInfo $jsFile) {
				$this->addAsset('Js', 'varHead', $jsFile);
			})
			->SetLang(Base::$Lang)
			->SetMethod(\MvcCore\Ext\Form::METHOD_POST)
			->SetAction($this->Url('Questionnaire:Submit', array('path' => $this->path)))
			->SetSuccessUrl($this->Url('Questionnaire:Completed', array('path' => $this->path)))
			->SetErrorUrl($this->Url('Questionnaire:Index', array('path' => $this->path)))
			->SetQuestions($this->questions);
		$this->_questionnaireForm = $form;
	}

	private function _submitCompleteData ($data, $keyPrefix, $resultKeyType = 'string') {
		$result = array();
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