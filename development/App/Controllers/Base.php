<?php

class App_Controllers_Base extends MvcCore_Controller
{
	const SESSION_EXPIRATION_SECONDS = 3600; // hour

	public static $Lang = 'cs';

	public static $AllowedSiteKeys = array(
		'mobile'	=> '/m',
		'tablet'	=> '/t',
		'full'		=> '', 
	);
	protected static $mediaSiteKeyName = 'mediaSiteKey';
	protected static $mediaSiteKeySwitchUriParam = 'media_site_key';
	protected static $mediaSiteKey = '';
	protected static $translator;
	protected static $session;

	protected $document;
	protected $user = array();
	protected $minifyHtml = TRUE;

	private static $_routeKeys = array();
	/********************************************************************************************/
	public static function GetSession () {
		if (!self::$session) {
			self::$session = new Zend_Session_Namespace('App_Controllers_Base');
			self::$session->SetExpirationSeconds(self::SESSION_EXPIRATION_SECONDS);
		}
		return self::$session;
	}
	public static function ProcessMediaSiteVersion (& $request)
	{
		// init request site key by request path and correct the path
		foreach (self::$AllowedSiteKeys as $mediaSiteKey => $requestPathPrefix) {
			if (mb_strpos($request->path, $requestPathPrefix . '/') === 0) {
				$request->mediaSiteKey = $mediaSiteKey;
				$request->path = mb_substr($request->path, strlen($requestPathPrefix));
				break;
			}
		}
		// if request is not get - set full version and do  not anything else
		$isGet = (strtolower($_SERVER['REQUEST_METHOD']) == 'get') ? TRUE : FALSE ;
		// look into $_GET param with just swithed site version
		$mediaSiteKeySwitchUriParam = isset($_GET[self::$mediaSiteKeySwitchUriParam]) ? strtolower($_GET[self::$mediaSiteKeySwitchUriParam]) : FALSE;
		// look into session if there are any record about recognized device
		$sessionSiteKey = isset($_SESSION[self::$mediaSiteKeyName]) ? $_SESSION[self::$mediaSiteKeyName] : '';
		if ($isGet && $mediaSiteKeySwitchUriParam !== FALSE && isset(self::$AllowedSiteKeys[$mediaSiteKeySwitchUriParam])) {
			self::$mediaSiteKey = $mediaSiteKeySwitchUriParam;
			// store switched site key into session
			$_SESSION[self::$mediaSiteKeyName] = self::$mediaSiteKey;
			unset($_GET[self::$mediaSiteKeySwitchUriParam]);
			// unset site key switch param and redirect to no switch param uri version
			$query = count($_GET) > 0 ? '?' . http_build_query($_GET) : '';
			$targetUri = $request->scheme . '://' . $request->host . $request->basePath . self::$AllowedSiteKeys[self::$mediaSiteKey] . $request->path . $query;
			self::redirect($targetUri);
		} else if ($isGet && $sessionSiteKey === '' || !isset(self::$AllowedSiteKeys[$sessionSiteKey])) {
			// detect device and store dvice type record in session, than redirect to proper site version
			$detect = new Mobile_Detect();
			$mediaSiteKeys = array_keys(self::$AllowedSiteKeys);
			if ($detect->isMobile()) {
				self::$mediaSiteKey = $mediaSiteKeys[0];
			} else if ($detect->isTablet()) {
				self::$mediaSiteKey = $mediaSiteKeys[1];
			} else {
				self::$mediaSiteKey = $mediaSiteKeys[2];
			}
			// store recognized site key into session in current domain
			$_SESSION[self::$mediaSiteKeyName] = self::$mediaSiteKey;
		} else {
			// check if we are on the session site version - if not - redirect to proper site version
			self::$mediaSiteKey = $sessionSiteKey;
		}
		// check if we are on the session site version - if not - redirect to proper site version
		if ($request->mediaSiteKey !== self::$mediaSiteKey) {
			$targetUri = $request->scheme . '://' . $request->host . $request->basePath . self::$AllowedSiteKeys[self::$mediaSiteKey] . $request->path;
			$targetUri .= $request->query ? '?' . $request->query : '';
			//yxcv($targetUri);
			if ($isGet) self::redirect($targetUri);
		}
		$request->mediaSiteKey = self::$mediaSiteKey;
	}
	public function Translate ($key, $lang = '')
	{
		return self::$translator->Translate($key, $lang ? $lang : self::$Lang);
	}
	/********************************************************************************************/
	// MvcCore_Controller::Url() override - to handle proper media site version
	public function Url ($name = '', $params = array()) {
		if (isset($params[self::$mediaSiteKeyName])) {
			$medisSiteKey = $params[self::$mediaSiteKeyName];
			unset($params[self::$mediaSiteKeyName]);
		} else {
			$medisSiteKey = self::$mediaSiteKey;
		}
		$url = MvcCore::GetInstance()->Url($name, $params);
		if (!in_array($name, self::$_routeKeys) && $name != 'self') return $url;
		if (strpos($name, 'Assets::') === 0) return $url;
		return self::$AllowedSiteKeys[$medisSiteKey] . $url;
	}
	public function Init () {
		parent::Init();
		if ($this->request->method == 'POST') $this->DisableView();
		self::$_routeKeys = array_keys(App_Bootstrap::GetRoutes());
		self::$translator = App_Models_Translator::GetInstance();
		$this->user = App_Forms_Login::GetInstance($this)->GetUser();
	}
	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->viewEnabled) {
			$this->_setUpBundles();
			$this->_setUpCommonSeoProperties();
		
			$this->view->Request = $this->request;
			$this->view->MediaSiteKey = self::$mediaSiteKey;
			$cfg = App_Bootstrap::GetConfig();
			$this->view->GoogleAnalyticsCode = $cfg->general['ga']['code'];
			$this->view->LoginForm = App_Forms_Login::GetInstance($this);
		}
	}
	/********************************************************************************************/
	protected function addAsset ($assetsType = '', $assetsGroup = '', SplFileInfo $file) {
		$tmpRelPath = self::$tmpPath . '/' . $file->getBasename();
		$tmpAbsPath = $this->request->appRoot . $tmpRelPath;
		if (MvcCore::GetEnvironment() == 'development' && !file_exists($tmpAbsPath)) {
			Nette_Utils_SafeStream::register();
			copy($file->getPathname(), 'nette.safe://' . $tmpAbsPath);
		}
		if (!$this->view->$assetsType($assetsGroup)->Contains($tmpRelPath)) 
			$this->view->$assetsType($assetsGroup)->Append($tmpRelPath);
	}
	/********************************************************************************************/
	private function _setUpBundles ()
	{
		$cfg = App_Bootstrap::GetConfig();
		$cfgAssets = (object) $cfg->assets;
		array_walk($cfgAssets, function (& $v, $k) { $v = intval($v); });
		$cfgAssets->tmpDir = self::$tmpPath;

		// uncomment line bellow for PHAR packing:
		//$cfgAssets->fileChecking = 'md5_file';

		App_Views_Helpers_Assets::SetGlobalOptions((array) $cfgAssets);
		$static = self::$staticPath;
		$this->view->Css('fixedHead')
			->AppendRendered($static . '/fonts/myriadwebpro/declarations/bold.css')
			->AppendRendered($static . '/fonts/myriadwebpro/declarations/semibold.css')
			->AppendRendered($static . '/fonts/myriadwebpro/declarations/regular.css')
			->AppendRendered($static . '/css/components/resets.css')
			->AppendRendered($static . '/css/components/fonts-settings.css')
			->AppendRendered($static . '/css/components/common-elements.css')
			->AppendRendered($static . '/css/components/custom-shorthands.css')
			->AppendRendered($static . '/css/components/button.css')
			->AppendRendered($static . '/css/front/common.all.css')
			->AppendRendered($static . '/css/front/common.' . self::$mediaSiteKey . '.css');
		$this->view->Css('fixedHeadPrint')
			->AppendRendered($static . '/css/front/print.css', 'print');
		$this->view->Js('fixedHead')
			->Append($static . '/js/libs/Class.js');
		$this->view->Js('fixedFoot')
			->Append($static . '/js/front/LoginForm.js');
	}
	private function _setUpCommonSeoProperties ()
	{
		$this->view->OgSiteName = '';
		$domainUri = $this->request->scheme . '://' . $this->request->host;
		$this->view->OgUrl = $domainUri . $this->request->path;
		if ($this->document) $this->document->OgImage = $domainUri . $this->document->OgImage;
	}
}