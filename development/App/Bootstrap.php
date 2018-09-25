<?php

namespace App;

class Bootstrap
{
	/**
	 * Called before application start, before \MvcCore\Application::GetInstance()->Run(); in index.php
	 * @return void
	 */
	public static function Init () {

		$app = \MvcCore\Application::GetInstance();
		
		if ($app->GetCompiled()) 
			\MvcCore\Config::SetEnvironment(\MvcCore\Config::ENVIRONMENT_PRODUCTION);
		
		// Patch core to use extended debug class:
		if (class_exists('\MvcCore\Ext\Debugs\Tracy')) {
			\MvcCore\Ext\Debugs\Tracy::$Editor = 'MSVS2017';
			$app->SetDebugClass('\MvcCore\Ext\Debugs\Tracy');
		}

		$app->SetRouterClass('\\MvcCore\\Ext\\Routers\\Media');
		
		/**
		 * Initialize authentication service extension.
		 * Set password hash salt, use users listed in system config ini and assegn translator.
		 */
		$translator = \App\Models\Translator::GetInstance();
		\MvcCore\Ext\Auths\Basic::GetInstance()
			->SetPasswordHashSalt('*5e8D5asPLKTSWGQ6sTH5_64MsEYr')
			->SetUserClass('\MvcCore\Ext\Auths\Users\SystemConfig')
			->SetTranslator(function ($key, $lang = NULL) use (& $translator) {
				return $translator->Translate($key, $lang);
			});

		\MvcCore\Router::GetInstance([
			'Index:Index'			=> "/",
			'Questionnaire:Submit'	=> [
				'match'					=> "#^/questions/(?<path>[a-zA-Z0-9\-_]*)/send#",
				'reverse'				=> '/questions/<path>/send',
			],
			'Questionnaire:Completed'=> [
				'match'					=> "#^/questions/(?<path>[a-zA-Z0-9\-_]*)/done#",
				'reverse'				=> '/questions/<path>/done',
			],
			'Questionnaire:Index'	=> [
				'match'					=> "#^/questions/(?<path>[a-zA-Z0-9\-_]*)#",
				'reverse'				=> '/questions/<path>',
			],
			'Statistics:Submit'		=> [
				'match'					=> "#^/results/(?<path>[a-zA-Z0-9\-_]*)/send#",
				'reverse'				=> '/results/<path>/send',
			],
			'Statistics:Index'		=> [
				'match'					=> "#^/results/(?<path>[a-zA-Z0-9\-_]*)#",
				'reverse'				=> '/results/<path>',
			],
		])
			->SetStricModeBySession(TRUE)
			->SetRouteToDefaultIfNotMatch(TRUE);

		/**
		 * Add post dispatch handler before 
		 * rendered content is sended to user
		 * and try to minify HTML content.
		 */
		/*$app->AddPostDispatchHandler(
			function (\MvcCore\Interfaces\IRequest & $request, \MvcCore\Interfaces\IResponse & $response) {
				$response->UpdateHeaders();
				if (!$response->IsRedirect() && $response->IsHtmlOutput()) {
					if (class_exists('\Minify_HTML')) 
						$response->SetBody(\Minify_HTML::minify($response->GetBody()));
				}
			}
		);*/
	}
}
