<?php

namespace App\Forms;

use \App\Forms\Fields,
	\App\Models,
	\MvcCore\Ext\Form;

class Questionnaire extends Base
{
	public $Id = 'questionnaire';
	public $CssClass = 'questionnaire';
	public $TemplatePath = 'questionnaire';
	public $Translate = TRUE;

	public $PersonsForm = TRUE;
	/** @var \App\Models\Questionnaire */
	protected $questionnaire;
	/** @var \App\Models\Question[] */
	protected $questions = array();

	public function SetQuestionnaire (Models\Questionnaire $questionnaire) {
		$this->questionnaire = $questionnaire;
		$this->questions = $questionnaire->GetQuestions();
		$this->PersonsForm = $this->questionnaire->PersonsForm;
		return $this;
	}
	public function Init () {
		parent::Init();
		$this->initColumnsCount();
		$this->AddField(new Form\ResetButton(array(
			'name'			=> 'reset',
			'value'			=> 'Reset questionnaire',
			'cssClasses'	=> array(
				'button', 'button-green', 
				$this->Controller->GetView()->DisplayFacebookShare ? 'fb-share-beside' : '',
				$this->PersonsForm ? '' : 'no-person-form'
			),
		)));
		if ($this->PersonsForm) $this->initPersonFields();
		$this->initQuestionsFields();
		$this->AddField(new Form\SubmitButton(array(
			'name'			=> 'send',
			'value'			=> 'Send questionnaire',
			'cssClasses'	=> array('button', 'button-green'),
		)));
		return $this;
	}
	protected function initPersonFields () {
		$age = new Form\Number(array(
			'name'			=> 'person_age',
			'label'			=> 'Age',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'number', 'age'),
			'controlWrapper'=> '{control}&nbsp;' . call_user_func($this->Translator, 'years'),
		));
		$sex = new Form\RadioGroup(array(
			'name'			=> 'person_sex',
			'label'			=> 'Gender',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'radio-group', 'sex'),
			'options'		=> Models\Person::$SexOptions,
		));
		$edu = new Form\RadioGroup(array(
			'name'			=> 'person_edu',
			'label'			=> 'Highest education level',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'radio-group', 'edu'),
			'options'		=> Models\Person::$EducationOptions,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> !empty($this->formColumnsCount) ? $this->formColumnsCount : 1,
			'columns'		=> empty($this->formColumnsCount) ? 3 :  $this->formColumnsCount
		));
		$job = new Form\RadioGroup(array(
			'name'			=> 'person_job',
			'label'			=> 'I am',
			'required'		=> TRUE,
			'cssClasses'	=> array('person', 'radio-group', 'job'),
			'options'		=> Models\Person::$JobOptions,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount) ? 3 :  $this->formColumnsCount
		));
		$this->AddFields($age, $sex, $edu, $job);
	}
	protected function initQuestionsFields () {
		foreach ($this->questions as $key => $question) {
			$typedInitMethod = 'initQuestionField' . \MvcCore\Tool::GetPascalCaseFromDashed($question->Type);
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
	protected function initQuestionFieldConnections ($key, Models\Question & $question) {
		return new Fields\Connections(array(
			'options'		=> $question->Options,
			'connections'	=> $question->Connections,
		));
	}
	protected function initQuestionFieldBoolean ($key, Models\Question & $question) {
		return new Fields\Boolean(array(
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		));
	}
	protected function initQuestionFieldText ($key, Models\Question & $question) {
		return new Form\Text(array(
			'maxlength'	=> $question->MaxLength,
			'renderMode'=> Form::FIELD_RENDER_MODE_NORMAL,
		));
	}
	protected function initQuestionFieldBooleanAndText ($key, Models\Question & $question) {
		return new Fields\BooleanAndText(array(
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		));
	}
	protected function initQuestionFieldCheckboxes ($key, Models\Question & $question) {
		return new Form\CheckboxGroup(array(
			'options'		=> $question->Checkboxes,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		));
	}
	protected function initQuestionFieldInteger ($key, Models\Question & $question) {
		return new Form\Number(array(
			'min'		=> $question->Min,
			'max'		=> $question->Max,
			'wrapper'	=> $question->Body,
			'renderMode'=> Form::FIELD_RENDER_MODE_NORMAL,
		));
	}
	protected function initQuestionFieldFloat ($key, Models\Question & $question) {
		return $this->initQuestionFieldInteger($key, $question);
	}
	protected function initQuestionFieldRadios ($key, Models\Question & $question) {
		return new Form\RadioGroup(array(
			'options'		=> $question->Radios,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		));
	}
	protected function initQuestionFieldTextarea ($key, Models\Question & $question) {
		return new Form\Textarea(array(
			'maxlength'	=> $question->MaxLength,
			'renderMode'=> Form::FIELD_RENDER_MODE_NORMAL,
		));
	}
}