<?php

namespace App\Forms;

use \App\Forms\CustomFields,
	\App\Models,
	\MvcCore\Ext\Form,
	\MvcCore\Ext\Forms\Fields;

class Questionnaire extends Base
{
	protected $id = 'questionnaire';

	/** @var bool */
	protected $personsForm = TRUE;
	/** @var \App\Models\Questionnaire */
	protected $questionnaire;
	/** @var \App\Models\Question[] */
	protected $questions = [];

	protected $viewScript = TRUE;

	public function SetQuestionnaire (Models\Questionnaire $questionnaire) {
		$this->questionnaire = $questionnaire;
		$this->questions = $questionnaire->GetQuestions();
		$this->personsForm = $this->questionnaire->PersonsForm;
		return $this;
	}

	public function Init () {
		parent::Init();
		$this->initColumnsCount();
		$this->AddField(new Fields\ResetButton([
			'name'			=> 'reset',
			'value'			=> 'Reset questionnaire',
			'cssClasses'	=> [
				'button', 'button-green', 
				$this->parentController->GetView()->DisplayFacebookShare ? 'fb-share-beside' : '',
				$this->personsForm ? '' : 'no-person-form'
			],
		]));
		if ($this->personsForm) $this->initPersonFields();
		$this->initQuestionsFields();
		$this->AddField(new Fields\SubmitButton([
			'name'			=> 'send',
			'value'			=> 'Send questionnaire',
			'cssClasses'	=> ['button', 'button-green'],
		]));
		return $this;
	}
	
	public function PreDispatch () {
		parent::PreDispatch();
		$this->view->personsForm = $this->personsForm;
	}
	
	protected function initPersonFields () {
		$age = new Fields\Number([
			'name'			=> 'person_age',
			'label'			=> 'Age',
			'required'		=> TRUE,
			'cssClasses'	=> ['person', 'number', 'age'],
			'wrapper'		=> '{control}&nbsp;' . $this->Translate('years'),
		]);
		$sex = new Fields\RadioGroup([
			'name'			=> 'person_sex',
			'label'			=> 'Gender',
			'required'		=> TRUE,
			'cssClasses'	=> ['person', 'radio-group', 'sex'],
			'options'		=> Models\Person::$SexOptions,
		]);
		$edu = new Fields\RadioGroup([
			'name'			=> 'person_edu',
			'label'			=> 'Highest education level',
			'required'		=> TRUE,
			'cssClasses'	=> ['person', 'radio-group', 'edu'],
			'options'		=> Models\Person::$EducationOptions,
			'viewScript'	=> 'field-group-with-columns',
			//'columns'		=> !empty($this->formColumnsCount) ? $this->formColumnsCount : 1,
			'columns'		=> empty($this->formColumnsCount) ? 3 :  $this->formColumnsCount
		]);
		$job = new Fields\RadioGroup([
			'name'			=> 'person_job',
			'label'			=> 'I am',
			'required'		=> TRUE,
			'cssClasses'	=> ['person', 'radio-group', 'job'],
			'options'		=> Models\Person::$JobOptions,
			'viewScript'	=> 'field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount) ? 3 :  $this->formColumnsCount
		]);
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
					->AddCssClasses($question->Type);
				if (isset($question->Required)) {
					$field->SetRequired($question->Required);
				}
				$this->AddField($field);
			}
		}
	}
	
	protected function completeQuestionNumberAndTextCode ($key, $text) {
		$text = $this->parentController->GetView()->Nl2Br($text);
		return '<span class="question-number-and-text">'.
			'<span class="question-number-and-text-row">'.
				'<span class="question-number">'.intval($key + 1).'.</span>'.
				'<span class="question-text">'.$text.'</span>'.
			'</span>'.
		'</span>';
	}
	protected function initQuestionFieldConnections ($key, Models\Question & $question) {
		return new CustomFields\Connections([
			'options'		=> $question->Options,
			'connections'	=> $question->Connections,
		]);
	}
	protected function initQuestionFieldBoolean ($key, Models\Question & $question) {
		return new CustomFields\Boolean([
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		]);
	}
	protected function initQuestionFieldText ($key, Models\Question & $question) {
		return new Fields\Text([
			'maxLength'	=> $question->MaxLength,
			'renderMode'=> Form::FIELD_RENDER_MODE_NORMAL,
		]);
	}
	protected function initQuestionFieldBooleanAndText ($key, Models\Question & $question) {
		return new CustomFields\BooleanAndText([
			'viewScript'	=> 'field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		]);
	}
	protected function initQuestionFieldCheckboxes ($key, Models\Question & $question) {
		return new Fields\CheckboxGroup([
			'options'		=> $question->Checkboxes,
			'viewScript'	=> 'field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		]);
	}
	protected function initQuestionFieldInteger ($key, Models\Question & $question) {
		return new Fields\Number([
			'min'		=> $question->Min,
			'max'		=> $question->Max,
			'wrapper'	=> $question->Body,
			'renderMode'=> Form::FIELD_RENDER_MODE_NORMAL,
		]);
	}
	protected function initQuestionFieldFloat ($key, Models\Question & $question) {
		return $this->initQuestionFieldInteger($key, $question);
	}
	protected function initQuestionFieldRadios ($key, Models\Question & $question) {
		return new Fields\RadioGroup([
			'options'		=> $question->Radios,
			'viewScript'	=> 'field-group-with-columns',
			'columns'		=> empty($this->formColumnsCount)
				? (!empty($question->Columns) ? $question->Columns : 3)
				:  $this->formColumnsCount,
		]);
	}
	protected function initQuestionFieldTextarea ($key, Models\Question & $question) {
		return new Fields\Textarea([
			'maxLength'	=> $question->MaxLength,
			'renderMode'=> Form::FIELD_RENDER_MODE_NORMAL,
		]);
	}
}
