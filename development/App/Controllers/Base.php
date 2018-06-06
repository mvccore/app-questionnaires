<?php

namespace App\Controllers;

use \MvcCore\Ext\Form,
	\MvcCore\Ext\Auth;

class Base extends \MvcCore\Controller
{
	/** @var \MvcCore\Ext\Auths\Basics\User|\MvcCore\Ext\Auths\Basics\IUser */
	protected $user = NULL;

	/** @var \App\Models\Document|\App\Models\Questionnaire|\App\Models\XmlModel|NULL */
	protected $document = NULL;

	/** @var \App\Models\Translator|\MvcCore\Interfaces\IModel */
	protected $translator = NULL;

	/**
	 * Default lang, changed later when document or questionnaire loaded.
	 * @var string
	 */
	protected $lang = 'en';

	/**
	 * Default locale, changed later when document or questionnaire loaded.
	 * @var string
	 */
	protected $locale = 'US';

	/**
	 * Media website version: `"full" | "mobile"`.
	 * @var string
	 */
	protected $mediaSiteVersion = NULL;

	public function Init () {
		parent::Init();
		$this->translator = \App\Models\Translator::GetInstance();
		\App\Forms\Base::AllFormsCustomizationInit($this->request);
		$this->mediaSiteVersion = $this->request->GetMediaSiteVersion();
	}

	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->viewEnabled) {

			$this->_setUpBundles();
			$this->_setUpCommonSeoProperties();
			
			$cfg = \MvcCore\Config::GetSystem();
			$this->view->googleAnalyticsCode = $cfg->general->ga->code;
			$this->view->mediaSiteVersion = $this->mediaSiteVersion;

			$this->_setUpAuthForm();
		}
	}

	public function Translate ($key, $lang = NULL) {
		return $this->translator->Translate($key, $lang);
	}

	/********************************************************************************************/

	protected function addAsset ($assetsType = '', $assetsGroup = '', \SplFileInfo $file) {
		$tmpRelPath = self::$tmpPath . '/' . $file->getBasename();
		$tmpAbsPath = $this->request->GetAppRoot() . $tmpRelPath;
		$appCompilled = $this->application->GetCompiled();
		if ((substr($appCompilled, 0, 3) !== 'PHP') && $appCompilled !== 'PHAR' && !file_exists($tmpAbsPath)) {
			\Nette\Utils\SafeStream::register();
			$tryCnt = 0;
			while ($tryCnt++ < 3) {
				if (copy($file->getPathname(), 'nette.safe://' . $tmpAbsPath)) break;
				usleep(100);
			}
		}
		if (!$this->view->$assetsType($assetsGroup)->Contains($tmpRelPath)) 
			$this->view->$assetsType($assetsGroup)->Append($tmpRelPath);
	}

	protected function setUpLangAndLocaleByDocument () {
		if ($this->document && preg_match('#^[a-z]{2,3}\-[A-Z]{2,3}$#', $this->document->Localization)) {
			list($this->lang, $this->locale) = explode('-', $this->document->Localization);
			$this->request
				->SetLang($this->lang)
				->SetLocale($this->locale);
		}
	}

	/********************************************************************************************/
	private function _setUpAuthForm () {
		// authentication form customization
		/** @var $form \MvcCore\Ext\Auths\Basics\SignInForm|\MvcCore\Ext\Auths\Basics\SignOutForm */
		$form = \MvcCore\Ext\Auths\Basic::GetInstance()->GetForm();
		$userAuthenticate = $this->user == NULL;
		$form
			// add minimized class if form is not in signed in state
			->SetCssClasses('authentication ' . ($userAuthenticate ? '' : 'minimized'))
			// set up default mode rendering mode
			->SetDefaultFieldsRenderMode(Form::FIELD_RENDER_MODE_LABEL_AROUND)
			// set signed-in/signed-out form template names
			->SetViewScript(
				'auth/' . ($this->user === NULL ? 'signed-out' : 'signed-in' )
			);
		// add green-button css class for send button
		$sendButton = $form->GetField('send')->AddCssClasses('button-green');
		//$sendButton->SetTemplatePath
		// ini the form in view to render
		$this->view->authForm = $form;
	}
	private function _setUpBundles () {
		\MvcCore\Ext\Views\Helpers\Assets::SetGlobalOptions(
			(array) \MvcCore\Config::GetSystem()->assets
		);
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
			->AppendRendered($static . '/css/front/common.' . $this->mediaSiteVersion . '.css');
		$this->view->Css('fixedHeadPrint')
			->AppendRendered($static . '/css/front/print.css', 'print');
		$this->view->Js('fixedHead')
			->Append($static . '/js/libs/Class.js');
		$this->view->Js('fixedFoot')
			->Append($static . '/js/front/AuthForm.js');
	}
	private function _setUpCommonSeoProperties () {
		$this->view->ogSiteName = '';
		$this->view->ogUrl = $this->request->GetRequestUrl();
		if ($this->document) {
			$this->document->OgImage = $this->request->GetDomainUrl() . $this->document->OgImage;
		}
	}
}
