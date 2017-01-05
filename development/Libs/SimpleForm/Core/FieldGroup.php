<?php

require_once('Field.php');
require_once('Exception.php');
require_once('View.php');

class SimpleForm_Core_FieldGroup extends SimpleForm_Core_Field
{
	public $Type = '';
	public $Value = array();
	public $Options = array();
	public $GroupCssClasses = array();
	public $GroupLabelAttrs = array();
	protected static $templates = array(
		'label'				=> '<label for="{id}"{attrs}>{label}</label>',
		'control'			=> '<input id="{id}" name="{name}" type="{type}" value="{value}"{checked}{attrs} />',
		'togetherLabelLeft'	=> '<label for="{id}"{attrs}><span>{label}</span>{control}</label>',
		'togetherLabelRight'=> '<label for="{id}"{attrs}>{control}<span>{label}</span></label>',
	);
	/* setters *******************************************************************************/
	public function SetOptions ($options) {
		$this->Options = $options;
		return $this;
	}
	public function SetGroupCssClasses ($cssClasses) {
		if (gettype($cssClasses) == 'array') {
			$this->GroupCssClasses = $cssClasses;
		} else {
			$this->GroupCssClasses = explode(' ', (string)$cssClasses);
		}
		return $this;
	}
	public function AddGroupCssClass ($cssClass) {
		$this->GroupCssClasses[] = $cssClass;
		return $this;
	}
	public function SetGroupLabelAttrs ($attrs = array()) {
		$this->GroupLabelAttrs = $attrs;
		return $this;
	}
	public function AddGroupLabelAttr ($attr = array()) {
		$this->GroupLabelAttrs[] = $attr;
		return $this;
	}
	/* core methods **************************************************************************/
	/*
	// use this constructor in extended class to merge control or label automatic templates
	public function __construct(array $cfg = array()) {
		parent::__construct($cfg);
		static::$templates = (object) array_merge((array)parent::$templates, (array)self::$templates);
	}
	*/
	public function OnAdded (SimpleForm & $form) {
		parent::OnAdded($form);
		if (!$this->Options) {
			$clsName = get_class($this);
			throw new SimpleForm_Core_Exception("No 'Options' defined for form field: '$clsName'.");
		}
	}
	public function SetUp () {
		parent::SetUp();
		if (!$this->Translate) return;
		$lang = $this->Form->Lang;
		$translator = $this->Form->Translator;
		foreach ($this->Options as $key => $value) {
			if (gettype($value) == 'string') {
				// most simple key/value array options configuration
				if ($value) $this->Options[$key] = $translator((string)$value, $lang);
			} else if (gettype($value) == 'array') {
				// advanced configuration with key, text, css class, and any other attributes for single option tag
				$optObj = (object) $value;
				$text = isset($optObj->text) ? $optObj->text : $key;
				if ($text) {
					$this->Options[$key]['text'] = $translator((string)$text, $lang);
				}
			}
		}
	}
	/* rendering ******************************************************************************/
	public function RenderNaturally () {
		$result = '';
		if (
			$this->Label && (
				$this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NORMAL ||
				$this->RenderMode == SimpleForm::FIELD_RENDER_MODE_LABEL_AROUND
			)
		) {
			$result = $this->RenderLabelAndControl();
		} else if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NO_LABEL || !$this->Label) {
			$result = $this->RenderControl();
			$errors = $this->RenderErrors();
			if ($this->Form->ErrorsRenderMode == SimpleForm::ERROR_RENDER_MODE_BEFORE_EACH_CONTROL) {
				$result = $errors . $result;
			} else if ($this->Form->ErrorsRenderMode == SimpleForm::ERROR_RENDER_MODE_AFTER_EACH_CONTROL) {
				$result .= $errors;
			}
		}
		return $result;
	}
	public function RenderControlInsideLabel () {
		if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NO_LABEL) return $this->RenderControl();
		$attrsStr = $this->renderAttrsWithFieldVars(
			array(), $this->GroupLabelAttrs, $this->GroupCssClasses
		);
		$template = $this->LabelSide == 'left' ? static::$templates->togetherLabelLeft : static::$templates->togetherLabelRight;
		$result = SimpleForm_Core_View::Format($template, array(
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
		$result = '';
		foreach ($this->Options as $key => $value) {
			$result .= $this->RenderControlItem($key, $value);
		}
		return $result;
	}
	public function RenderLabel () {
		if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NO_LABEL) return '';
		$attrsStr = $this->renderAttrsWithFieldVars(
			array(), $this->GroupLabelAttrs, $this->GroupCssClasses
		);
		return SimpleForm_Core_View::Format(static::$templates->label, array(
			'id'		=> $this->Id, 
			'label'		=> $this->Label,
			'attrs'		=> $attrsStr ? " $attrsStr" : '', 
		));
	}
	public function RenderControlItem ($key, $option) {
		$result = '';
		$itemControlId = implode(SimpleForm::HTML_IDS_DELIMITER, array(
			$this->Form->Id, $this->Name, $key
		));
		list(
			$itemLabelText, 
			$labelAttrsStr, 
			$controlAttrsStr
		) = $this->renderControlItemCompleteAttrsClassesAndText($key, $option);
		// render control, render label and put it together if necessary
		$checked = FALSE;
		if (gettype($this->Value) == 'array') {
			$checked = in_array($key, $this->Value);
		} else {
			$checked = $this->Value === $key;
		}
		$itemControl = SimpleForm_Core_View::Format(static::$templates->control, array(
			'id'		=> $itemControlId,
			'name'		=> $this->Name,
			'type'		=> $this->Type,
			'value'		=> $key,
			'checked'	=> $checked ? ' checked="checked"' : '',
			'attrs'		=> $controlAttrsStr ? " $controlAttrsStr" : '',
		));
		if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_NORMAL) {
			// control and label
			$itemLabel = SimpleForm_Core_View::Format(static::$templates->label, array(
				'id'		=> $itemControlId, 
				'label'		=> $itemLabelText,
				'attrs'		=> $labelAttrsStr ? " $labelAttrsStr" : '', 
			));
			$result = ($this->LabelSide == 'left') ? $itemControl . $itemLabel : $itemLabel . $itemControl;
		} else if ($this->RenderMode == SimpleForm::FIELD_RENDER_MODE_LABEL_AROUND) {
			// control inside label
			$result = SimpleForm_Core_View::Format(
				static::$templates->{'togetherLabel' . (($this->LabelSide == 'left') ? 'Right' : 'Left')}, 
				array(
					'id'		=> $itemControlId, 
					'label'		=> $itemLabelText,
					'control'	=> $itemControl,
					'attrs'		=> $labelAttrsStr ? " $labelAttrsStr" : '', 
				)
			);
		}
		return $result;
	}
	/* protected renderers *******************************************************************/
	protected function renderControlItemCompleteAttrsClassesAndText ($key, $option) {
		$optionType = gettype($option);
		$labelAttrsStr = '';
		$controlAttrsStr = '';
		$itemLabelText = '';
		$originalRequired = $this->Required;
		if ($this->Type == 'checkbox') $this->Required = FALSE;
		if ($optionType == 'string') {
			$itemLabelText = $option ? $option : $key;
			$labelAttrsStr = $this->renderLabelAttrsWithFieldVars();
			$controlAttrsStr = $this->renderControlAttrsWithFieldVars();
		} else if ($optionType == 'array') {
			$itemLabelText = $option['text'] ? $option['text'] : $key;
			$attrsArr = $this->ControlAttrs;
			$classArr = $this->CssClasses;
			if (isset($option['attrs']) && gettype($option['attrs']) == 'array') {
				$attrsArr = array_merge($this->ControlAttrs, $option['attrs']);
			}
			if (isset($option['class'])) {
				$classArrParam = array();
				if (gettype($option['class']) == 'array') {
					$classArrParam = $option['class'];
				} else if (gettype($option['class']) == 'string') {
					$classArrParam = explode(' ', $option['class']);
				}
				foreach ($classArrParam as $clsValue) if ($clsValue) $classArr[] = $clsValue;
			}
			$labelAttrsStr = $this->renderAttrsWithFieldVars(
				array(), $attrsArr, $classArr
			);
			$controlAttrsStr = $this->renderAttrsWithFieldVars(
				array(), $attrsArr, $classArr
			);
		}
		if ($this->Type == 'checkbox') $this->Required = $originalRequired;
		return array($itemLabelText, $labelAttrsStr, $controlAttrsStr);
	}
}