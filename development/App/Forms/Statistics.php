<?php

namespace App\Forms;

use \App\Forms\Fields,
	\MvcCore\Ext\Form,
	\App\Models;

class Statistics extends Base
{
	public $Id = 'statistics-persons-filter';
	public $CssClass = 'statistics';
	public $TemplatePath = 'statistics';
	public $Method = Form::METHOD_POST;
	public $Translate = TRUE;

	/** @var bool */
	public $Authenticated = FALSE;
	/** @var bool */
	public $PersonsForm = FALSE;

	/** @var int[] */
	protected $minMaxAges = array();
	/** @var \App\Models\Questionnaire */
	protected $questionnaire;

	public function SetAuthenticated ($authenticated) {
		$this->Authenticated = $authenticated;
		return $this;
	}
	public function SetQuestionnaire (Models\Questionnaire $questionnaire) {
		$this->questionnaire = $questionnaire;
		$this->PersonsForm = $questionnaire->PersonsForm;
		return $this;
	}
	public function SetMinMaxAges ($minMaxAges = array()) {
		$this->minMaxAges = $minMaxAges;
		return $this;
	}
    public function Init () {
		parent::Init();
		$this->initColumnsCount();
		if ($this->PersonsForm) {
			$age = new Form\Range(array(
				'name'			=> 'age',
				'label'			=> 'Age',
				'min'			=> $this->minMaxAges[0],
				'max'			=> $this->minMaxAges[1],
				'step'			=> 1,
				'multiple'		=> TRUE,
				'cssClasses'	=> array('person', 'range', 'age'),
				'controlWrapper'=> '{control}&nbsp;' . call_user_func($this->Translator, 'years'),
			));
			$sex = new Form\CheckboxGroup(array(
				'name'			=> 'sex',
				'label'			=> 'Sex',
				'cssClasses'	=> array('person', 'radio-group', 'sex'),
				'options'		=> Models\Person::$SexOptions,
			));
			$edu = new Form\CheckboxGroup(array(
				'name'			=> 'education',
				'label'			=> 'Highest education level',
				'cssClasses'	=> array('person', 'radio-group', 'edu'),
				'options'		=> Models\Person::$EducationOptions,
				'templatePath'	=> 'fields/field-group-with-columns',
				'columns'		=> $this->formColumnsCount,
			));
			$job = new Form\CheckboxGroup(array(
				'name'			=> 'job',
				'label'			=> 'I am',
				'cssClasses'	=> array('person', 'radio-group', 'job'),
				'options'		=> Models\Person::$JobOptions,
				'templatePath'	=> 'fields/field-group-with-columns',
				'columns'		=> $this->formColumnsCount,
			));
			$this->AddFields($age, $sex, $edu, $job);
		}
		if ($this->Authenticated) {
			$from = new Form\DateTime(array(
				'name'			=> 'from',
				'label'			=> 'From',
				'cssClasses'	=> array('person', 'from'),
			));
			$to = new Form\DateTime(array(
				'name'			=> 'to',
				'label'			=> 'To',
				'cssClasses'	=> array('person', 'to'),
			));
			$this->AddFields($from, $to);
		}
		$submit = new Form\SubmitButton(array(
			'name'			=> 'refresh',
			'value'			=> 'Refresh results',
			'cssClasses'	=> array('button', 'button-green'),
		));
		$this->AddFields($submit);
		return $this;
	}
}
