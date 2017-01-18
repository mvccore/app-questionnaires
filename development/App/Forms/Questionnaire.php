<?php

class App_Forms_Questionnaire extends App_Forms_Base
{
	public $Id = 'questionnaire';
	public $CssClass = 'questionnaire';
	public $TemplatePath = 'questionnaire';
	public $Translate = TRUE;
	protected $questions = array();
	public function SetQuestions (array $questions = array()) {
		$this->questions = $questions;
		return $this;
	}
	public function Init () {
		parent::Init();
		$this->initColumnsCount();
		$this->AddField(new SimpleForm_ResetButton(array(
			'name'			=> 'reset',
			'value'			=> 'Reset questionnaire',
			'cssClasses'	=> array('button', 'button-green'),
		)));
		$this->initPersonFields();
		$this->initQuestionsFields();
		$this->AddField(new App_Forms_Fields_Submit(array(
			'name'			=> 'send',
			'value'			=> 'Send questionnaire',
			'cssClasses'	=> array('button', 'button-green'),
		)));
		return $this;
	}
	protected function initPersonFields () {
		$age = new SimpleForm_Number(array(
			'name'			=> 'person_age',
			'label'			=> 'Age',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'number', 'age'),
			'controlWrapper'=> '{control}&nbsp;' . call_user_func($this->Translator, 'years'),
		));
		$sex = new SimpleForm_RadioGroup(array(
			'name'			=> 'person_sex',
			'label'			=> 'Sex',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'radio-group', 'sex'),
			'options'		=> App_Models_Person::$SexOptions,
		));
		$edu = new SimpleForm_RadioGroup(array(
			'name'			=> 'person_edu',
			'label'			=> 'Highest education level',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'radio-group', 'edu'),
			'options'		=> App_Models_Person::$EducationOptions,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> $this->formColumnsCount,
		));
		$job = new SimpleForm_RadioGroup(array(
			'name'			=> 'person_job',
			'label'			=> 'I am',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'radio-group', 'job'),
			'options'		=> App_Models_Person::$JobOptions,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> $this->formColumnsCount,
		));
		$this->AddFields($age, $sex, $edu, $job);
	}
	protected function initQuestionsFields () {
		foreach ($this->questions as $key => $question) {
			$typedInitMethod = 'initQuestionField' . MvcCore_Tool::GetPascalCaseFromDashed($question->Type);
			$field = $this->$typedInitMethod($key, $question);
			if ($field) {
				$questionNumberAndText = $this->completeQuestionNumberAndTextCode($key, $question->Text);
				$field
					->SetName('question_' . $question->Id)
					->SetLabel($questionNumberAndText)
					->SetTranslate(FALSE)
					->AddCssClass($question->Type);
				if (isset($question->Required)) {
					$field->SetRequired($question->Required);
				}
				$this->AddField($field);
			}
		}
	}
	protected function completeQuestionNumberAndTextCode ($key, $text) {
		$text = $this->Controller->GetView()->Nl2Br($text);
		return '<span class="question-number-and-text">'.
			'<span class="question-number-and-text-row">'.
				'<span class="question-number">'.intval($key + 1).'.</span>'.
				'<span class="question-text">'.$text.'</span>'.
			'</span>'.
		'</span>';
	}
	protected function initQuestionFieldConnections ($key, App_Models_Question & $question) {
		return new App_Forms_Fields_Connections(array(
			'options'		=> $question->Options,
			'connections'	=> $question->Connections,
		));
	}
	protected function initQuestionFieldBoolean ($key, App_Models_Question & $question) {
		return new App_Forms_Fields_Boolean(array(
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> $this->formColumnsCount,
		));
	}
	protected function initQuestionFieldText ($key, App_Models_Question & $question) {
		return new SimpleForm_Text(array(
			'maxlength'	=> $question->MaxLength,
			'renderMode'=> SimpleForm::FIELD_RENDER_MODE_NORMAL,
		));
	}
	protected function initQuestionFieldBooleanAndText ($key, App_Models_Question & $question) {
		return new App_Forms_Fields_BooleanAndText(array(
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> $this->formColumnsCount,
		));
	}
	protected function initQuestionFieldCheckboxes ($key, App_Models_Question & $question) {
		return new SimpleForm_CheckboxGroup(array(
			'options'		=> $question->Checkboxes,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> $this->formColumnsCount,
		));
	}
	protected function initQuestionFieldInteger ($key, App_Models_Question & $question) {
		return new SimpleForm_Number(array(
			'min'		=> $question->Min,
			'max'		=> $question->Max,
			'wrapper'	=> $question->Body,
			'renderMode'=> SimpleForm::FIELD_RENDER_MODE_NORMAL,
		));
	}
	protected function initQuestionFieldFloat ($key, App_Models_Question & $question) {
		return $this->initQuestionFieldInteger($key, $question);
	}
	protected function initQuestionFieldRadios ($key, App_Models_Question & $question) {
		return new SimpleForm_RadioGroup(array(
			'options'		=> $question->Radios,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> 1,
		));
	}
	protected function initQuestionFieldTextarea ($key, App_Models_Question & $question) {
		return new SimpleForm_Textarea(array(
			'maxlength'	=> $question->MaxLength,
			'renderMode'=> SimpleForm::FIELD_RENDER_MODE_NORMAL,
		));
	}
}