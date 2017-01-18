<?php

class App_Controllers_Default extends App_Controllers_Base
{
	protected $renderNotFoundIfNoDocument = TRUE;
	
	public function Init () {
		parent::Init();
        $documentPath = $this->request->Path;
        $indexStr = 'index.php';
        if (strrpos($documentPath, $indexStr) === strlen($documentPath) - strlen($indexStr)) {
            $documentPath = substr($documentPath, 0, strrpos($documentPath, $indexStr));
        }
		$this->document = App_Models_Document::GetByPath($documentPath);
	}
	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->document) {
			$this->view->Document = $this->document;	
			$this->view->Title = $this->document->Title;
		}
		if (!$this->ajax && $this->request->Method == MvcCore_Request::METHOD_GET && !$this->document) {
			if ($this->renderNotFoundIfNoDocument) {
				$this->view->Document = new App_Models_Document();
				$this->renderNotFound();
			}
		}
	}
	public function DefaultAction () {
	}
	public function HomeAction () {
		$questionnaires =  App_Models_Questionnaire::GetAll();
		$questionnairesCount = count($questionnaires);
		if ($questionnairesCount === 0) {
			// display no questionnaires message
			$this->view->Questionnaires = array();
			$this->view->Path = App_Models_Questionnaire::GetDataPath();
		} else if ($questionnairesCount === 1) {
			// redirect to first questionnaire if there is only one
			self::Redirect(
				$questionnaires[0]->GetUrl(),
				303
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