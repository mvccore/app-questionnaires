<?php

class App_Controllers_Login extends App_Controllers_Base
{
    public function SignInAction () {
		$form = App_Forms_Login::GetInstance($this);
		list ($result, $data, $errors) = $form->SignInSubmit();
		if ($result !== SimpleForm::RESULT_SUCCESS) {
			// here you can count bad login requests to ban user for some time or anything else...
		}
		$form->SuccessUrl = $data['sourceUrl'];
		$form->ErrorUrl = $data['sourceUrl'];
		$form->Data = array(); // to remove all submited data from session
		$form->RedirectAfterSubmit();
	}
	public function SignOutAction () {
		$form = App_Forms_Login::GetInstance($this);
		list ($result, $data, $errors) = $form->SignOutSubmit();
		$form->SuccessUrl = $data['sourceUrl'];
		$form->ErrorUrl = $data['sourceUrl'];
		$form->Data = array(); // to remove all submited data from session
		$form->RedirectAfterSubmit();
	}
}
