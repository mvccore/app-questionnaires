<?php

require_once('Exception.php');
require_once('View.php');

class SimpleForm_Core_Field
{
	public $Id = '';
	public $Type = '';
	public $Name = '';
	public $Value = '';
	public $Label = '';
	public $LabelSide = 'left'; // right | left
	public $Required = null;
	public $Readonly = FALSE;
	public $Disabled = FALSE;
	public $Translate = null;
	public $RenderMode = null;
	public $CssClasses = array();
	public $ControlAttrs = array();
	public $LabelAttrs = array();
	public $Validators = array();
	public $Errors = array();
	public $TemplatePath = '';
	public $View = null;
	public $JsClass = '';
	public $Js = '';
	public $Css = '';
	/**
	 * @var SimpleForm
	 */
	public $Form = null;
	protected static $templates = array(
		'label'				=> '<label for="{id}"{attrs}>{label}</label>',
		'control'			=> '<input id="{id}" name="{name}" type="{type}" value="{value}"{attrs} />',
		'togetherLabelLeft'	=> '<label for="{id}"{attrs}><span>{label}</span>{control}</label>',
		'togetherLabelRight'=> '<label for="{id}"{attrs}>{control}<span>{label}</span></label>',
	);
	protected static $declaredProtectedProperties = array(
		'Id', 'View', 'Form', 'Field',
	);
	/* setters and getters ********************************************************************/
	public function SetName ($name) {
		$this->Name = $name;
		return $this;
	}
	public function SetType ($type) {
		$this->Type = $type;
		return $this;
	}
	public function SetLabel ($label) {
		$this->Label = $label;
		return $this;
	}
	public function SetLabelSide ($labelSide) {
		$this->LabelSide = $labelSide;
		return $this;
	}
	public function SetRequired ($required) {
		$this->Required = $required;
		return $this;
	}
	public function SetReadonly ($readonly) {
		$this->Readonly = $readonly;
		return $this;
	}
	public function SetRenderMode ($renderMode) {
		$this->RenderMode = $renderMode;
		return $this;
	}
	public function SetValue ($value) {
		$this->Value = $value;
		return $this;
	}
	public function GetValue () {
		return $this->Value;
	}
	public function SetTranslate ($translate) {
		$this->Translate = $translate;
		return $this;
	}
	public function SetDisabled ($disabled) {
		$this->Disabled = $disabled;
		return $this;
	}
	public function SetCssClasses ($cssClasses) {
		if (gettype($cssClasses) == 'array') {
			$this->CssClasses = $cssClasses;
		} else {
			$this->CssClasses = explode(' ', (string)$cssClasses);
		}
		return $this;
	}
	public function AddCssClass ($cssClass) {
		$this->CssClasses[] = $cssClass;
		return $this;
	}
	public function SetControlAttrs ($attrs = array()) {
		$this->ControlAttrs = $attrs;
		return $this;
	}
	public function AddControlAttr ($attr = array()) {
		$this->ControlAttrs[] = $attr;
		return $this;
	}
	public function SetLabelAttrs ($attrs = array()) {
		$this->LabelAttrs = $attrs;
		return $this;
	}
	public function AddLabelAttr ($attr = array()) {
		$this->LabelAttrs[] = $attr;
		return $this;
	}
	public function SetValidators ($validators) {
		$this->Validators = $validators;
		return $this;
	}
	public function AddValidators () {
		$args = func_get_args();
		foreach ($args as $arg) $this->Validators[] = $arg;
		return $this;
	}
	public function SetTemplatePath ($templatePath) {
		$this->TemplatePath = $templatePath;
		return $this;
	}
	public function SetJsClass ($jsClass) {
		$this->JsClass = $jsClass;
		return $this;
	}
	public function SetJs ($jsFullFile) {
		$this->Js = $jsFullFile;
		return $this;
	}
	public function SetCss ($cssFullFile) {
		$this->Css = $cssFullFile;
		return $this;
	}
	public function AddError ($errorText) {
		$this->Errors[] = $errorText;
		return $this;
	}
	/* core methods **************************************************************************/
    public function __construct ($cfg = array()) {
		static::$templates = (object) static::$templates;
		foreach ($cfg as $key => $value) {
			$propertyName = ucfirst($key);
			if (in_array($propertyName, static::$declaredProtectedProperties)) {
				$clsName = get_class($this);
				throw new SimpleForm_Core_Exception(
					"Property: '$propertyName' is protected, class: '$clsName'."
				);
			} else {
				$this->$propertyName = $value;
			}
		}
	}
	public function __set ($name, $value) {
		$this->$name = $value;
	}
	public function OnAdded (SimpleForm & $form) {
		if (!$this->Name) {
			$clsName = get_class($this);
			throw new SimpleForm_Core_Exception("No 'Name' defined for form field: '$clsName'.");
		}
		$this->Form = $form;
		$this->Id = implode(SimpleForm::HTML_IDS_DELIMITER, array(
			$form->Id,
			$this->Name
		));
		// if there is no specific render mode - set render mode by form
		if (is_null($this->RenderMode)) {
			$this->RenderMode = $form->FieldsDefaultRenderMode;
		}
		// if there is no specific required boolean - set required boolean by form
		$this->Required = is_null($this->Required) ? (is_null($form->Required) ? FALSE : $form->Required) : $this->Required ;
	}
	public function SetUp () {
		// translate only if Translate options is null or true and translator handler is defined
		$form = $this->Form;
		$translator = $form->Translator;
		if (
			(is_null($this->Translate) || $this->Translate === TRUE || $form->Translate) && 
			!is_null($translator)
		) {
			$this->Translate = TRUE;
		} else {
			$this->Translate = FALSE;
		}
		if ($this->Translate && $this->Label) {
			$this->Label = $translator($this->Label, $form->Lang);
		}
	}
	/* rendering ******************************************************************************/
	public function Render () {
		if ($this->TemplatePath) {
			return $this->RenderTemplate();
		} else {
			return $this->RenderNaturally();
		}
	}
	public function RenderTemplate () {
		$view = new SimpleForm_Core_View($this->Form);
		$this->Field = $this;
		$view->SetUp($this);
		return $view->Render($this->Form->TemplateTypePath, $this->TemplatePath);
	}
	public function RenderNaturally () {
		$result = '';
		if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NORMAL && $this->Label) {
			$result = $this->RenderLabelAndControl();
		} else if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_LABEL_AROUND && $this->Label) {
			$result = $this->RenderControlInsideLabel();
		} else if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NO_LABEL || !$this->Label) {
			$result = $this->RenderControl();
			$errors = $this->RenderErrors();
			if ($this->Form->ErrorsRenderMode !== SimpleForm::ERROR_RENDER_MODE_BEFORE_EACH_CONTROL) {
				$result = $errors . $result;
			} else if ($this->Form->ErrorsRenderMode !== SimpleForm::ERROR_RENDER_MODE_AFTER_EACH_CONTROL) {
				$result .= $errors;
			}
		}
		return $result;
	}
	public function RenderLabelAndControl () {
		$result = "";
		if ($this->LabelSide == 'left') {
			$result = $this->RenderLabel() . $this->RenderControl();
		} else {
			$result = $this->RenderControl() . $this->RenderLabel();
		}
		$errors = $this->RenderErrors();
		if ($this->Form->ErrorsRenderMode == SimpleForm::ERROR_RENDER_MODE_BEFORE_EACH_CONTROL) {
			$result = $errors . $result;
		} else if ($this->Form->ErrorsRenderMode == SimpleForm::ERROR_RENDER_MODE_AFTER_EACH_CONTROL) {
			$result .= $errors;
		}
		return $result;
	}
	public function RenderControlInsideLabel () {
		if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NO_LABEL) return $this->RenderControl();
		$attrsStr = $this->renderLabelAttrsWithFieldVars();
		$template = $this->LabelSide == 'left' ? static::$templates->togetherLabelLeft : static::$templates->togetherLabelRight;
		$result = $this->Form->View->Format($template, array(
			'id'		=> $this->Id, 
			'label'		=> $this->Label,
			'control'	=> $this->RenderControl(),
			'attrs'		=> $attrsStr ? " $attrsStr" : '', 
		));
		$errors = $this->RenderErrors();
		if ($this->Form->ErrorsRenderMode == SimpleForm::ERROR_RENDER_MODE_BEFORE_EACH_CONTROL) {
			$result = $errors . $result;
		} else if ($this->Form->ErrorsRenderMode == SimpleForm::ERROR_RENDER_MODE_AFTER_EACH_CONTROL) {
			$result .= $errors;
		}
		return $result;
	}
	public function RenderControl () {
		$attrsStr = $this->renderControlAttrsWithFieldVars();
		return $this->Form->View->Format(static::$templates->control, array(
			'id'		=> $this->Id, 
			'name'		=> $this->Name, 
			'type'		=> $this->Type,
			'value'		=> $this->Value,
			'attrs'		=> $attrsStr ? " $attrsStr" : '', 
		));
	}
	public function RenderLabel () {
		if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NO_LABEL) return '';
		$attrsStr = $this->renderLabelAttrsWithFieldVars();
		return $this->Form->View->Format(static::$templates->label, array(
			'id'		=> $this->Id, 
			'label'		=> $this->Label,
			'attrs'		=> $attrsStr ? " $attrsStr" : '', 
		));
	}
	public function RenderErrors () {
		$result = "";
		if ($this->Errors && $this->Form->ErrorsRenderMode !== SimpleForm::ERROR_RENDER_MODE_ALL_TOGETHER) {
			$result .= '<span class="errors">';
			foreach ($this->Errors as $key => $errorMessage) {
				$errorCssClass = 'error';
				if (isset($this->Fields[$key])) $errorCssClass .= " $key";
				$result .= "<span class=\"$errorCssClass\">$errorMessage</span>";
			}
			$result .= '</span>';
		}
		return $result;
	}
	/* protected renderers *******************************************************************/
	protected function renderLabelAttrsWithFieldVars ($fieldVars = array()) {
		return $this->renderAttrsWithFieldVars(
			$fieldVars, $this->LabelAttrs, $this->CssClasses
		);
	}
	protected function renderControlAttrsWithFieldVars ($fieldVars = array()) {
		return $this->renderAttrsWithFieldVars(
			$fieldVars, $this->ControlAttrs, $this->CssClasses
		);
	}
	protected function renderAttrsWithFieldVars (
		$fieldVars = array(), $fieldAttrs = array(), $cssClasses = array()
	) {
		$attrs = array();
		foreach ($fieldVars as $fieldVar) {
			if (!is_null($this->$fieldVar)) {
				$attrName = MvcCore::GetDashedFromPascalCase($fieldVar);
				$attrs[$attrName] = $this->$fieldVar;
			}
		}
		$boolFieldVars = array('Disabled', 'Readonly', 'Required');
		foreach ($boolFieldVars as $fieldVar) {
			if ($this->$fieldVar) {
				$attrName = lcfirst($fieldVar);
				$attrs[$attrName] = $attrName;
				$cssClasses[] = $attrName;
			}
		}
		$cssClasses[] = MvcCore::GetDashedFromPascalCase($this->Name);
		$attrs['class'] = implode(' ', $cssClasses);
		return SimpleForm_Core_View::RenderAttrs(
			array_merge($fieldAttrs, $attrs)
		);
	}
}
