<?php

class App_Controllers_Questionnaire extends App_Controllers_Base
{
	protected $renderNotFoundIfNoDocument = FALSE;

	protected $path;
	protected $questionnaire;
	protected $questions;
	/**
	 * @var App_Forms_Questionnaire
	 */
	private $_questionnaireForm;
	
	public function PreDispatch () {
		parent::PreDispatch();
		$this->setUpQuestionnaireAndQuestions();
		$this->setUpForm();
		$this->setUpAssets();
	}
	
	public function DefaultAction () {
		$this->view->Questionnaire = $this->questionnaire;
		$this->view->QuestionnaireForm = $this->_questionnaireForm;
	}
	public function SubmitAction () {
		list ($result, $data, $errors) = $this->_questionnaireForm->Submit();
		if ($result === SimpleForm::RESULT_SUCCESS) {
			$personData = $this->_submitCompleteData($data, 'person_', 'string');
			$answerData = $this->_submitCompleteData($data, 'question_', 'int');
			$person = App_Models_Person::Create($personData);
			App_Models_Questionnaire_Answers::Create($person->Id, $this->questions, $answerData);
		}
		$this->_questionnaireForm->ClearSession();
		$this->_questionnaireForm->RedirectAfterSubmit();
	}
	public function CompletedAction () {
		$this->view->BackLink = $this->questionnaire->GetUrl();
		$this->view->Path = $this->path;
	}

	protected function setUpQuestionnaireAndQuestions()
	{
		$this->path = str_replace('-', '\\-', $this->GetParam('path', "a-zA-Z0-9_\-"));

		$matchedQrs = App_Models_Questionnaire::GetByPathMatch("^([0-9]*)\-$this->path$");
		
		$debugExists = class_exists('Debug');
		if (count($matchedQrs) === 0) {
			if ($debugExists) Debug::log("[App_Controllers_Questionnaire] No questionnaire found in path: '$this->path'.");
			$this->view->Document = new App_Models_Document();
			$this->renderNotFound();
		} else if (count($matchedQrs) > 1) {
			if ($debugExists) Debug::log("[App_Controllers_Questionnaire] Ambiguous request to the questionnaire in path: '$this->path'..");
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
				->AppendRendered($static . '/css/front/person.' . self::$mediaSiteKey . '.css')
				->AppendRendered($static . '/css/front/questionnaire.all.css')
				->AppendRendered($static . '/css/front/questionnaire.' . self::$mediaSiteKey . '.css');
		}
	}
	protected function setUpForm ()
	{
		$form = new App_Forms_Questionnaire($this);

		$form
			->SetTranslator(function ($key = '', $lang = '') {
				return $this->Translate($key, $lang ? $lang : App_Controllers_Base::$Lang);
			})
			->SetJsRenderer(function (SplFileInfo $jsFile) {
				$this->addAsset('Js', 'varHead', $jsFile);
			})
			->SetLang(App_Controllers_Base::$Lang)
			->SetMethod(SimpleForm::METHOD_POST)
			->SetAction($this->Url('Questionnaire::Submit', array('path' => $this->path)))
			->SetSuccessUrl($this->Url('Questionnaire::Completed', array('path' => $this->path)))
			->SetErrorUrl($this->Url('Questionnaire::Default', array('path' => $this->path)))
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