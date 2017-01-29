<?php

namespace App\Controllers;

use \MvcCore\Ext\Form,
	\MvcCore\Ext\Auth;

class Base extends \MvcCore\Controller
{
	public static $Lang = 'cs';

	/** @var \App\Models\Translator */
	protected static $translator;

	/** @var \App\Models\XmlModel */
	protected $document;

	/** @var \MvcCore\Ext\Auth\Virtual\User */
	protected $user = NULL;

	protected $mediaSiteKey = '';

	public function Translate ($key, $lang = '') {
		return self::$translator->Translate($key, $lang ? $lang : self::$Lang);
	}
	public function Init () {
		parent::Init();
		self::$translator = \App\Models\Translator::GetInstance();
		\App\Forms\Base::AllFormsInit();
		$this->user = Auth::GetInstance()->GetUser();
		$this->mediaSiteKey = $this->request->MediaSiteKey;
	}
	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->viewEnabled) {

			$this->_setUpBundles();
			$this->_setUpCommonSeoProperties();
		
			$this->view->Request = $this->request;
			$this->view->MediaSiteKey = $this->request->MediaSiteKey;
			
			$cfg = \MvcCore\Config::GetSystem();
			$this->view->GoogleAnalyticsCode = $cfg->general->ga->code;

			$this->_setUpAuthForm();
		}
	}
	/********************************************************************************************/
	protected function addAsset ($assetsType = '', $assetsGroup = '', \SplFileInfo $file) {
		$tmpRelPath = self::$tmpPath . '/' . $file->getBasename();
		$tmpAbsPath = $this->request->AppRoot . $tmpRelPath;
		$appCompilled = \MvcCore::GetInstance()->GetCompiled();
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
	/********************************************************************************************/
	private function _setUpAuthForm () {
		// authentication form customization
		/** @var $form \MvcCore\Ext\Auth\SignInForm|\MvcCore\Ext\Auth\SignOutForm */
		$form = Auth::GetInstance()->GetForm();
		$form
			// initialize fields to customize them in lines bellow
			->Init()
			// add minimized class if form is not in signed in state
			->AddCssClass(is_null($this->user) ? 'minimized' : '')
			// set up default mode rendering mode
			->SetFieldsDefaultRenderMode(Form::FIELD_RENDER_MODE_LABEL_AROUND)
			// set directory, where are located all form templates
			->SetTemplateTypePath('Forms')
			// set signed-in/signed-out form template names
			->SetTemplatePath(
				'auth/' . (is_null($this->user) ? 'signed-out' : 'signed-in' )
			);
		// add green-button css class for send button
		$sendButton = $form->GetField('send')->AddCssClass('button-green');
		//$sendButton->SetTemplatePath
		// ini the form in view to render
		$this->view->AuthForm = $form;
	}
	private function _setUpBundles () {
		\MvcCore\Ext\View\Helpers\Assets::SetGlobalOptions(
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
			->AppendRendered($static . '/css/front/common.' . $this->mediaSiteKey . '.css');
		$this->view->Css('fixedHeadPrint')
			->AppendRendered($static . '/css/front/print.css', 'print');
		$this->view->Js('fixedHead')
			->Append($static . '/js/libs/Class.js');
		$this->view->Js('fixedFoot')
			->Append($static . '/js/front/AuthForm.js');
	}
	private function _setUpCommonSeoProperties () {
		$this->view->OgSiteName = '';
		$this->view->OgUrl = $this->request->RequestUrl;
		if ($this->document) {
			$this->document->OgImage = $this->request->DomainUrl . $this->document->OgImage;
		}
	}
}