<?php

class App_Views_Helpers_Facebook
{
    private $_baseUrlEncoded;
    private $_facebookAppId;
	public function __construct (MvcCore_View & $view) {
		$this->_baseUrlEncoded = urlencode($view->Controller->GetRequest()->RequestUrl);
		$cfg = MvcCore_Config::GetSystem();
		$this->_facebookAppId = (string) $cfg->general->fb->appId;
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
