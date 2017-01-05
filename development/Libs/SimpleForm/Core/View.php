<?php

require_once('MvcCore/View.php');

class SimpleForm_Core_View extends MvcCore_View
{
	/**
	 * @var SimpleForm
	 */
	public $Form = null;
	/**
	 * @var MvcCore_View|mixed
	 */
	public $View = null;
    public function __construct (SimpleForm & $form) {
		$ctrl = $form->Controller;
		if (class_exists('MvcCore_Controller') && $ctrl instanceof MvcCore_Controller) {
			parent::__construct($ctrl);
		} else {
			$this->Controller = $ctrl;
		}
		$this->Form = $form;
		$this->View = SimpleForm_Core_Helpers::GetControllerView($ctrl);
	}
	public function __call ($method, $arguments) {
		if (isset($this->Field) && method_exists($this->Field, $method)) {
			return call_user_func_array(array($this->Field, $method), $arguments);
		} else {
			return parent::__call($method, $arguments);
		}
	}
	public function RenderTemplate () {
		return $this->Render($this->Form->TemplateTypePath, $this->Form->TemplatePath);
	}
	public function RenderNaturally () {
		return $this->RenderBegin() . $this->RenderErrors() . $this->RenderContent() . $this->RenderEnd();
	}
	public function RenderBegin () {
		$result = "<form";
		$attrs = array();
		$form = $this->Form;
		$formProperties = array('Id', 'Action', 'Method', 'Enctype');
		foreach ($formProperties as $property) {
			if ($form->$property) $attrs[strtolower($property)] = $form->$property;
		}
		if ($form->CssClass) $attrs['class'] = $form->CssClass;
		foreach ($form->Attributes as $key => $value) {
			if (!in_array($key, $formProperties)) $attrs[$key] = $value;
		}
		$attrsStr = self::RenderAttrs($attrs);
		if ($attrsStr) $result .= ' ' . $attrsStr;
		$result .= '>';
		$result .= $this->RenderCsrf();
		return $result;
	}
	public function RenderCsrf () {
		list ($name, $value) = $this->Form->SetUpCsrf();
		return '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
	}
	public function GetCsrf () {
		return $this->Form->GetCsrf();
	}
	public function RenderErrors () {
		$result = "";
		if ($this->Form->Errors && $this->Form->ErrorsRenderMode == SimpleForm::ERROR_RENDER_MODE_ALL_TOGETHER) {
			$result .= '<div class="errors">';
			foreach ($this->Form->Errors as $fieldName => $errorMessage) {
				$result .= '<div class="error ' . $fieldName . '">'.$errorMessage.'</div>';
			}
			$result .= '</div>';
		}
		return $result;
	}
	public function RenderContent () {
		$result = "";
		$fieldRendered = "";
		foreach ($this->Form->Fields as $field) {
			$fieldRendered = $field->Render();
			if (!($field instanceof SimpleForm_Hidden)) {
				$fieldRendered = "<div>".$fieldRendered."</div>";
			}
			$result .= $fieldRendered;
		}
		return $result;
	}
	public function RenderEnd () {
		$result = "</form>";
		if ($this->Js) $result .= $this->Form->RenderJs();
		if ($this->Css) $result .= $this->Form->RenderCss();
		return $result;
	}
	public static function Format ($str = '', array $args = array()) {
		foreach ($args as $key => $value) {
			$str = str_replace('{'.$key.'}', (string)$value, $str);
		}
		return $str;
	}
	public static function RenderAttrs (array $atrributes = array()) {
		$result = array();
		foreach ($atrributes as $attrName => $attrValue) {
			$result[] = $attrName.'="'.$attrValue.'"';
		}
		return implode(' ', $result);
	}
}
