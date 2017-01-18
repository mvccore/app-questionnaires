<?php

class App_Forms_Statistics extends App_Forms_Base
{
	public $Id = 'statistics-persons-filter';
	public $CssClass = 'statistics';
	public $TemplatePath = 'statistics';
	public $Method = SimpleForm::METHOD_POST;
	public $Translate = TRUE;
    public function Init ($minAndMaxAges = array()) {
		parent::Init();
		$this->initColumnsCount();
		App_Models_Person::GetMinAndMaxAges();
		$age = new SimpleForm_Range(array(
			'name'			=> 'age',
			'label'			=> 'Age',
			'min'			=> $minAndMaxAges[0],
			'max'			=> $minAndMaxAges[1],
			'step'			=> 1,
			'multiple'		=> TRUE,
			'cssClasses'	=> array('person', 'range', 'age'),
			'controlWrapper'=> '{control}&nbsp;' . call_user_func($this->Translator, 'years'),
		));
		$sex = new SimpleForm_CheckboxGroup(array(
			'name'			=> 'sex',
			'label'			=> 'Sex',
			'cssClasses'	=> array('person', 'radio-group', 'sex'),
			'options'		=> App_Models_Person::$SexOptions,
		));
		$edu = new SimpleForm_CheckboxGroup(array(
			'name'			=> 'education',
			'label'			=> 'Highest education level',
			'cssClasses'	=> array('person', 'radio-group', 'edu'),
			'options'		=> App_Models_Person::$EducationOptions,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> $this->formColumnsCount,
		));
		$job = new SimpleForm_CheckboxGroup(array(
			'name'			=> 'job',
			'label'			=> 'I am',
			'cssClasses'	=> array('person', 'radio-group', 'job'),
			'options'		=> App_Models_Person::$JobOptions,
			'templatePath'	=> 'fields/field-group-with-columns',
			'columns'		=> $this->formColumnsCount,
		));
		$submit = new App_Forms_Fields_Submit(array(
			'name'			=> 'refresh',
			'value'			=> 'Refresh results',
			'cssClasses'	=> array('button', 'button-green'),
		));
		$this->AddFields($age, $sex, $edu, $job, $submit);
		return $this;
	}
}
