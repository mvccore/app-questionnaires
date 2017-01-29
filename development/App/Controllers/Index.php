<?php

namespace App\Controllers;

use App\Models;

class Index extends Base
{
	protected $renderNotFoundIfNoDocument = TRUE;
	
	public function Init () {
		parent::Init();
        $documentPath = $this->request->Path;
        $indexStr = 'index.php';
        if (strrpos($documentPath, $indexStr) === strlen($documentPath) - strlen($indexStr)) {
            $documentPath = substr($documentPath, 0, strrpos($documentPath, $indexStr));
        }
		$this->document = Models\Document::GetByPath($documentPath);
	}
	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->document) {
			$this->view->Document = $this->document;	
			$this->view->Title = $this->document->Title;
		}
		if (!$this->ajax && $this->request->Method == \MvcCore\Request::METHOD_GET && !$this->document) {
			if ($this->renderNotFoundIfNoDocument) {
				$this->view->Document = new Models\Document();
				$this->renderNotFound();
			}
		}
	}
	public function IndexAction () {
		// all not routed requests are routed here by (Bootstrap.php):
		// \MvcCore\Router::GetInstance()->SetRouteToDefaultIfNotMatch();
		// to get xml file about document to display by request path or render not found page.
	}
	public function HomeAction () {
		$questionnaires =  Models\Questionnaire::GetAll();
		$questionnairesCount = count($questionnaires);
		if ($questionnairesCount === 0) {
			// display no questionnaires message
			$this->view->Questionnaires = array();
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
		$this->Render($this->controller, 'error');
	}
	public function ErrorAction () {
	}
}