<?php

namespace App\Views\Helpers;

class Facebook extends \MvcCore\Ext\Views\Helpers\AbstractHelper
{
	protected static $instance = NULL;

	private $_facebookAppId = NULL;

	private $_baseUrlEncoded = NULL;

	public function & SetView (\MvcCore\Interfaces\IView & $view) {
		parent::SetView($view);
		if ($this->_facebookAppId === NULL) {
			$cfg = (object) \MvcCore\Config::GetSystem();
			$this->_facebookAppId = (string) $cfg->general->fb->appId;
			$this->_baseUrlEncoded = urlencode($this->request->GetFullUrl());
		}
		return $this;
	}

	public function Facebook () {
		return $this;
	}

	public function ShareButton ($cssClass = '') {
		$r = '<iframe src="https://www.facebook.com/plugins/share_button.php?href='
			. $this->_baseUrlEncoded
			. '&layout=box_count&size=large&mobile_iframe=true&appId='
			. $this->_facebookAppId
			. '&width=72&height=60" width="72" height="60" class="'
			. 'facebook-share-btn-large ' . $cssClass . '" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
		return $r;
	}
}
