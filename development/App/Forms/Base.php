<?php

namespace App\Forms;

use \MvcCore\Ext\Forms;

class Base extends \MvcCore\Ext\Form
{
	protected static $validatorsNamespaces = [
		'\\MvcCore\\Ext\\Forms\\Validators\\',
		'\\App\\Forms\\CustomValidators\\'
	];

	protected static $jsSupportFilesRootDir = __DIR__ . '/../../static/js/front/forms-assets';

	protected $defaultFieldsRenderMode = self::FIELD_RENDER_MODE_LABEL_AROUND;

	protected $formColumnsCount = NULL;
	
	// TODO: replace with svg button backgrounds:
	public static function AllFormsCustomizationInit (\MvcCore\Interfaces\IRequest & $request) {
		// customize templates for all forms in whole application
		$btnCustomTmpl = '<button id="{id}" name="{name}" type="{type}"{attrs}><span><b>{value}</b></span></button>';
		Forms\Fields\SubmitButton::SetTemplate('control', $btnCustomTmpl);
		Forms\Fields\ResetButton::SetTemplate('control', $btnCustomTmpl);
	}
	
	protected function initColumnsCount () {
		if ($this->request->GetMediaSiteVersion() != 'full')
			$this->formColumnsCount = 1;
	}
}
