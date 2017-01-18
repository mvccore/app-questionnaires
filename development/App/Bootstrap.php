<?php

class App_Bootstrap
{
	/**
	 * All palication routes
	 * @var array[]
	 */
	protected static $routes = array();
	
	/**
	 * Called before application start, before MvcCore::GetInstance()->Run(); in index.php
	 * @return void
	 */
	public static function Init () {

		$app = MvcCore::GetInstance();
		
		if ($app->GetCompiled()) MvcCore_Config::SetEnvironment(MvcCore_Config::ENVIRONMENT_PRODUCTION);

		$app->SetDebugClass(MvcCoreExt_Tracy::class)
			->SetRequestClass(MvcCoreExt_ApacheDpi::class)
			->SetRouterClass(MvcCoreExt_MediaAddress::class);

		MvcCore_View::AddHelpersClassBases('MvcCoreExt_ViewHelpers');

		MvcCoreExt_MediaAddress::GetInstance()->SetStricModeBySession();

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
		MvcCore::AddPreRouteHandler(function (MvcCore_Request & $request, MvcCore_Response & $response) {
		});
		
		/**
		 * Place to add, remove or change any request/response object values
		 * This closure function is called:
		 *	- after static routes has been processed and request object is completed by current route
		 *  - before controller is created and dispatched by request params values
		 */
		MvcCore::AddPreDispatchHandler(function (MvcCore_Request & $request, MvcCore_Response & $response) {
		});

		/**
		 * Place to add, remove or change any request/response object values
		 * This closure function is called:
		 *	- after controller and action is dispatched and rendered
		 *  - before all headers are send and before response body is send
		 */
		MvcCore::AddPostDispatchHandler(function (MvcCore_Request & $request, MvcCore_Response & $response) {
			$response->UpdateHeaders();
			if (!$response->IsRedirect() && $response->IsHtmlOutput()) {
				if (class_exists('Minify_HTML')) $response->Body = Minify_HTML::minify($response->Body);
			}
		});

		/**
		 * Initialize authentication service extension
		 * and set translator to translate sign in/sign out form visible elements.
		 */
		MvcCoreExt_Auth::GetInstance()->Init()->SetTranslator(function ($key, $lang = NULL) {
			return App_Models_Translator::GetInstance()->Translate($key, $lang);
		});
	}
	/**
	 * Set up all route data into router
	 * @return void
	 */
	protected static function setUpRoutes () {
		self::$routes = array(
			'Default::Home'			=> "#^/$#",
			'Questionnaire::Submit'	=> array(
				'pattern'			=> "#^/dotaznik/([a-zA-Z0-9\-_]*)/odeslat#",
				'reverse'			=> '/dotaznik/{%path}/odeslat',
			),
			'Questionnaire::Completed'	=> array(
				'pattern'			=> "#^/dotaznik/([a-zA-Z0-9\-_]*)/hotovo#",
				'reverse'			=> '/dotaznik/{%path}/hotovo',
			),
			'Questionnaire::Default'=> array(
				'pattern'			=> "#^/dotaznik/([a-zA-Z0-9\-_]*)#",
				'reverse'			=> '/dotaznik/{%path}',
			),
			'Statistics::Submit'	=> array(
				'pattern'			=> "#^/vysledky/([a-zA-Z0-9\-_]*)/odeslat#",
				'reverse'			=> '/vysledky/{%path}/odeslat',
			),
			'Statistics::Default'	=> array(
				'pattern'			=> "#^/vysledky/([a-zA-Z0-9\-_]*)#",
				'reverse'			=> '/vysledky/{%path}',
			),
		);
		MvcCore_Router::GetInstance()
			->AddRoutes(self::$routes, TRUE)
			->SetRouteToDefaultIfNotMatch();
	}
}