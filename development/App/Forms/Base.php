<?php

class App_Forms_Base extends SimpleForm
{
	public $TemplateTypePath = 'Forms';
	public $FieldsDefaultRenderMode = SimpleForm::FIELD_RENDER_MODE_LABEL_AROUND;

	protected $formColumnsCount;

	public function __construct (/*MvcCore_Controller*/ & $controller) {
		parent::__construct($controller);
		static::$jsAssetsRootDir = MvcCore::GetRequest()->appRoot . '/static/js/front';
	}
	protected function initColumnsCount () {
		$mediaSiteKey = $this->Controller->GetRequest()->mediaSiteKey;
		$this->formColumnsCount = $mediaSiteKey == 'full' ? 3 : 1;
	}
}
