<?php

namespace App;

class Bootstrap
{
	/**
	 * All palication routes
	 * @var array[]
	 */
	protected static $routes = array();
	
	/**
	 * Called before application start, before \MvcCore::GetInstance()->Run(); in index.php
	 * @return void
	 */
	public static function Init () {

		$app = \MvcCore::GetInstance();
		
		if ($app->GetCompiled()) \MvcCore\Config::SetEnvironment(\MvcCore\Config::ENVIRONMENT_PRODUCTION);
		
		$app->SetDebugClass(\MvcCore\Ext\Debug\Tracy::class)
			->SetRequestClass(\MvcCore\Ext\Request\ApacheDpi::class)
			->SetRouterClass(\MvcCore\Ext\Router\Media::class);

		\MvcCore\Ext\Router\Media::GetInstance()->SetStricModeBySession();

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
		\MvcCore::AddPreRouteHandler(function (\MvcCore\Request & $request, \MvcCore\Response & $response) {
		});
		
		/**
		 * Place to add, remove or change any request/response object values
		 * This closure function is called:
		 *	- after static routes has been processed and request object is completed by current route
		 *  - before controller is created and dispatched by request params values
		 */
		\MvcCore::AddPreDispatchHandler(function (\MvcCore\Request & $request, \MvcCore\Response & $response) {
		});

		/**
		 * Place to add, remove or change any request/response object values
		 * This closure function is called:
		 *	- after controller and action is dispatched and rendered
		 *  - before all headers are send and before response body is send
		 */
		\MvcCore::AddPostDispatchHandler(function (\MvcCore\Request & $request, \MvcCore\Response & $response) {
			$response->UpdateHeaders();
			if (!$response->IsRedirect() && $response->IsHtmlOutput()) {
				if (class_exists('\Minify_HTML')) $response->Body = \Minify_HTML::minify($response->Body);
			}
		});

		/**
		 * Initialize authentication service extension
		 * and set translator to translate sign in/sign out form visible elements.
		 */
		\MvcCore\Ext\Auth::GetInstance()->Init()->SetTranslator(function ($key, $lang = NULL) {
			return \App\Models\Translator::GetInstance()->Translate($key, $lang);
		});
	}
	/**
	 * Set up all route data into router
	 * @return void
	 */
	protected static function setUpRoutes () {
		self::$routes = array(
			'Index:Home'			=> "#^/$#",
			'Questionnaire:Submit'	=> array(
				'pattern'			=> "#^/questions/([a-zA-Z0-9\-_]*)/send#",
				'reverse'			=> '/questions/{%path}/send',
			),
			'Questionnaire:Completed'	=> array(
				'pattern'			=> "#^/questions/([a-zA-Z0-9\-_]*)/done#",
				'reverse'			=> '/questions/{%path}/done',
			),
			'Questionnaire:Index'=> array(
				'pattern'			=> "#^/questions/([a-zA-Z0-9\-_]*)#",
				'reverse'			=> '/questions/{%path}',
			),
			'Statistics:Submit'	=> array(
				'pattern'			=> "#^/results/([a-zA-Z0-9\-_]*)/send#",
				'reverse'			=> '/results/{%path}/send',
			),
			'Statistics:Index'	=> array(
				'pattern'			=> "#^/results/([a-zA-Z0-9\-_]*)#",
				'reverse'			=> '/results/{%path}',
			),
		);
		\MvcCore\Router::GetInstance()
			->AddRoutes(self::$routes, TRUE)
			->SetRouteToDefaultIfNotMatch();
	}
}