<?php

class App_Forms_Login extends App_Forms_Base
{
	/**
	 * Unique form id
	 * 
	 * @var string
	 */
	public $Id = 'login';

	/**
	 * Form http method
	 * 
	 * @var string
	 */
	public $Method = SimpleForm::METHOD_POST;

	/**
	 * Template relative file path without extension
	 * 
	 * @var string
	 */
	public $TemplatePath = 'login/';

	/**
	 * Possible authenticated user info
	 * 
	 * @var array
	 */
	public $User = array();

	/**
	 * Translate field names in this form
	 * 
	 * @var bool
	 */
	public $Translate = TRUE;

	/**
	 * Singleton instance
	 * 
	 * @var App_Forms_Login
	 */
	protected static $instance = NULL;

    /**
     * Returns singleton instance
	 * 
	 * @return App_Forms_Login
     */
    public static function GetInstance (MvcCore_Controller & $controller) {
		if (!self::$instance) {
			self::$instance = new self($controller);
		}
		return self::$instance;
	}

	/**
	 * Init fields and routes
	 * 
	 * @return App_Forms_Login
	 */
	public function Init () {
		parent::Init();
		
		$this->Lang = App_Controllers_Base::$Lang;
		$this->GetUser();

		if (!$this->User) {
			$this->CssClass = 'minimized';
			$this->AddField(new SimpleForm_Text(array(
				'name'			=> 'username',
				'placeholder'	=> 'User',
			)));
			$this->AddField(new SimpleForm_Password(array(
				'name'			=> 'password',
				'placeholder'	=> 'Password',
			)));
			$this->AddField(new App_Forms_Fields_Submit(array(
				'name'			=> 'send',
				'value'			=> 'Sign In',
				'cssClasses'	=> array('button', 'button-green'),
			)));
			$this->Action = $this->Controller->Url('Login::SignIn');
		} else {
			$this->Action = $this->Controller->Url('Login::SignIn');
			$this->AddField(new App_Forms_Fields_Submit(array(
				'name'			=> 'logout',
				'value'			=> 'Log Out',
				'cssClasses'	=> array('button', 'button-green'),
			)));
			$this->Action = $this->Controller->Url('Login::SignOut');
		}

		$paramsClone = array_merge($this->Controller->GetRequest()->params);
		unset($paramsClone['controller'], $paramsClone['action']);
		$sourceUrl = $this->Controller->Url('self', $paramsClone);
		$this->AddField(new SimpleForm_Hidden(array(
			'name'			=> 'sourceUrl',
			'value'			=> $sourceUrl,
		)));

		return $this;
	}

	public function GetUser () {
		$loginSession = & self::GetSession();
		if (isset($loginSession->user)) $this->User = $loginSession->user;
		return $this->User;
	}

	public function Render () {
		if (!$this->initialized) $this->Init();
		if ($this->User) {
			$this->TemplatePath .= 'authenticated';
		} else {
			$this->TemplatePath .= 'not-authenticated';
		}
		return parent::Render();
	}

	public function SignInSubmit () {
		parent::Submit();
		if ($this->Result === SimpleForm::RESULT_SUCCESS) {

			// values are safe, now load credentials from config.ini and check username and password
			$cfg = App_Bootstrap::GetConfig();
			$allCredentials = $cfg->credentials;

			$success = FALSE;
			foreach ($allCredentials as & $credentials) {
				if ($credentials['username'] === $this->Data['username'] && $credentials['password'] === $this->Data['password']) {
					$success = TRUE;
					self::GetSession()->user = array(
						'username'	=> $credentials['username'],
						'fullname'	=> $credentials['fullname'],
					);
					break;
				}
			}

			if (!$success) $this->AddError('User name or password is incorrect.');
		}
		if ($this->Result !== SimpleForm::RESULT_SUCCESS) {
			sleep(3);
		}
		return array($this->Result, $this->Data, $this->Errors);
	}

	public function SignOutSubmit () {
		parent::Submit();
		if ($this->Result === SimpleForm::RESULT_SUCCESS) {
			unset(self::GetSession()->user);
		}
		return array($this->Result, $this->Data, $this->Errors);
	}

	public static function & GetSession () {
		$sessionKey = 'App_Forms_Login';
		if (!(isset($_SESSION[$sessionKey]) && !is_null($_SESSION[$sessionKey]))) {
			$_SESSION[$sessionKey] = new stdClass;
		}
		return $_SESSION[$sessionKey];
	}
}
