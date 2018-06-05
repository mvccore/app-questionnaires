<?php

namespace App\Controllers;

use App\Models;

class Index extends Base
{
	protected $renderNotFoundIfNoDocument = TRUE;
	
	public function Init () {
		parent::Init();
		$documentPath = $this->request->GetPath();
		$indexStr = 'index.php';
		if (strrpos($documentPath, $indexStr) === strlen($documentPath) - strlen($indexStr)) {
			$documentPath = substr($documentPath, 0, strrpos($documentPath, $indexStr));
		}
		$this->document = Models\Document::GetByPath($documentPath);
		$this->setUpLangAndLocaleByDocument();
	}
	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->document) {
			$this->view->Document = $this->document;	
			$this->view->Title = $this->document->Title;
		}
		if (!$this->ajax && $this->request->GetMethod() == \MvcCore\Request::METHOD_GET && !$this->document) {
			if ($this->renderNotFoundIfNoDocument) {
				$this->view->Document = new Models\Document();
				$this->RenderNotFound();
			}
		}
	}
	public function IndexAction () {
		// All not routed requests are routed here by `Bootstrap` class configuration:
		// `\MvcCore\Ext\Routers\Media::GetInstance()->SetRouteToDefaultIfNotMatch(TRUE);`.
		if ($this->document->Path == '/') {
			$this->HomeAction();
			$this->Render('home');
		}
	}
	public function HomeAction () {
		$questionnaires =  Models\Questionnaire::GetAll();
		$questionnairesCount = count($questionnaires);
		if ($questionnairesCount === 0) {
			// display no questionnaires message
			$this->view->Questionnaires = [];
			$this->view->Path = Models\Questionnaire::GetDataPath();
		} else if ($questionnairesCount === 1) {
			// redirect to first questionnaire if there is only one
			self::Redirect(
				$questionnaires[0]->GetUrl()
			);
		} else {
			// list links to questionnaires
			$this->view->Questionnaires = $questionnaires;
		}
	}
	public function NotFoundAction () {
		$this->ErrorAction();
	}
	public function ErrorAction () {
		$code = $this->response->GetCode();
		$message = $this->request->GetParam('message', '\\a-zA-Z0-9_;, /\-\@\:');
		$message = preg_replace('#`([^`]*)`#', '<code>$1</code>', $message);
		$message = str_replace("\n", '<br />', $message);
		$this->view->Title = "Error $code";
		$this->view->Message = $message;
		$this->Render('error');
	}
}
