<?php

class App_Bootstrap
{
	/**
	 * All palication routes
	 * @var array[]
	 */
	protected static $routes = array();

	protected static $cfg = array();
	
	/**
	 * Called before application start, before MvcCore::GetInstance()->Run(); in index.php
	 * @return void
	 */
	public static function Init () {

		Nette_DebugAdapter::Init(MvcCore::GetEnvironment() == 'development');
		
		/**
		 * Place for manual routes setup
		 * or place to load any localized routes from database or cache
		 * before static routes is processed and current route catched and controller is dispatched
		 */
		self::setUpRoutes();
		
		/**
		 * Place to add, remove or change any request/response object values
		 * This closure function is called:
		 *	- before static routes has been processed
		 *  - before controller is created and dispatched by request params values
		 */
		MvcCore::SetPreRouteRequestHandler(function (& $request) {
			MvcCore::SessionStart();
			App_Controllers_Base::ProcessMediaSiteVersion($request);
		});
	}
	public static function GetConfig ()
	{
		if (!self::$cfg) {
			$cfgFullPath = MvcCore::GetRequest()->appRoot . '/App/config.ini';
			self::$cfg = (object) IniStruct::Parse($cfgFullPath, TRUE, MvcCore::GetEnvironment());
		}
		return self::$cfg;
	}
	/**
	 * Return all routes - called from base controller for extending Url function for mobile devices
	 * @return array[]
	 */
	public static function GetRoutes () {
		return self::$routes;
	}
	/**
	 * Set up all route data into router
	 * @return void
	 */
	protected static function setUpRoutes () {
		self::$routes = array(
			'Default::Home'	=> array(
				'pattern'		=> "#^/$#",
				'reverse'		=> '/', 
			),
			'Questionnaire::Submit'	=> array(
				'pattern'		=> "#^/dotaznik/([a-zA-Z0-9\-_]*)/odeslat#",
				'reverse'		=> '/dotaznik/{%path}/odeslat',
			),
			'Questionnaire::Completed'	=> array(
				'pattern'		=> "#^/dotaznik/([a-zA-Z0-9\-_]*)/hotovo#",
				'reverse'		=> '/dotaznik/{%path}/hotovo',
			),
			'Questionnaire::Default'	=> array(
				'pattern'		=> "#^/dotaznik/([a-zA-Z0-9\-_]*)#",
				'reverse'		=> '/dotaznik/{%path}',
			),
			'Statistics::Submit'	=> array(
				'pattern'		=> "#^/vysledky/([a-zA-Z0-9\-_]*)/odeslat#",
				'reverse'		=> '/vysledky/{%path}/odeslat',
			),
			'Statistics::Default'	=> array(
				'pattern'		=> "#^/vysledky/([a-zA-Z0-9\-_]*)#",
				'reverse'		=> '/vysledky/{%path}',
			),
			'Controller::Asset'	=> array(
				'pattern'		=> "#^/((static|Var/Tmp)+(.*))#",
				'reverse'		=> '{%path}',
				'params'		=> array('path' => ''),
			),
			'Default::NotFound'	=> array(
				'pattern'		=> "#^/404#",
				'reverse'		=> '/404', 
			),
		);
		MvcCore::SetRoutes(self::$routes);
	}
}