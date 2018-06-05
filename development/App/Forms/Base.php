<?php

namespace App\Forms;

use \MvcCore\Ext\Forms;

class Base extends \MvcCore\Ext\Form
{
	protected $defaultFieldsRenderMode = self::FIELD_RENDER_MODE_LABEL_AROUND;

	protected $formColumnsCount = NULL;

	public static function AllFormsCustomizationInit (\MvcCore\Interfaces\IRequest & $request) {
		// customize templates for all forms in whole application
		$btnCustomTmpl = '<button id="{id}" name="{name}" type="{type}"{attrs}><span><b>{value}</b></span></button>';
		Forms\Fields\SubmitButton::SetTemplate('control', $btnCustomTmpl);
		Forms\Fields\ResetButton::SetTemplate('control', $btnCustomTmpl);
		\MvcCore\Ext\Form::SetJsSupportFilesRootDir($request->GetAppRoot() . '/static/js/front/forms-assets');
	}
	
	protected function initColumnsCount () {
		if ($this->request->GetMediaSiteVersion() != 'full')
			$this->formColumnsCount = 1;
	}
}
