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
    public function Init ($minAndMaxAges = array()) {
		parent::Init();
		$this->initColumnsCount();
		Models\Person::GetMinAndMaxAges();
		$age = new Form\Range(array(
			'name'			=> 'age',
			'label'			=> 'Age',
			'min'			=> $minAndMaxAges[0],
			'max'			=> $minAndMaxAges[1],
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
		$submit = new Form\SubmitButton(array(
			'name'			=> 'refresh',
			'value'			=> 'Refresh results',
			'cssClasses'	=> array('button', 'button-green'),
		));
		$this->AddFields($age, $sex, $edu, $job, $submit);
		return $this;
	}
}
