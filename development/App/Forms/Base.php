<?php

namespace App\Forms;

use \MvcCore\Ext\Form;

class Base extends Form
{
	public $TemplateTypePath = 'Forms';
	public $FieldsDefaultRenderMode = Form::FIELD_RENDER_MODE_LABEL_AROUND;

	protected $formColumnsCount = NULL;

	public static function AllFormsInit () {
		// customize templates for all forms in whole application
		$btnCustomTmpl = '<button id="{id}" name="{name}" type="{type}"{attrs}><span><b>{value}</b></span></button>';
		Form\SubmitButton::$Templates = (object) Form\SubmitButton::$Templates;
		Form\ResetButton::$Templates = (object) Form\ResetButton::$Templates;
		Form\SubmitButton::$Templates->control = $btnCustomTmpl;
		Form\ResetButton::$Templates->control = $btnCustomTmpl;
	}

	public function __construct (/*\MvcCore\Controller*/ & $controller) {
		parent::__construct($controller);
		$this->jsAssetsRootDir = \MvcCore::GetInstance()->GetRequest()->AppRoot . '/static/js/front';
	}
	protected function initColumnsCount () {
		if ($this->Controller->GetRequest()->MediaSiteKey != 'full') {
			$this->formColumnsCount = 1;
		}
	}
}
