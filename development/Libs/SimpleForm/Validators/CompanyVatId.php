<?php

require_once('/../../SimpleForm.php');
require_once('CompanyTaxId.php');

class SimpleForm_Validators_CompanyVatId extends SimpleForm_Validators_CompanyTaxId
{
	protected static $exceptionMessage = "No company VAT ID verification method for language: '{lang}'.";
	protected static $errorMessageKey = SimpleForm::VAT_ID;
	protected function validate_CS ($id = '')
	{
		$id = preg_replace('#\s+#', '', $id);
		if (substr($id, 0, 2) == 'CZ') {
			$id = substr($id, 2);
			return parent::validate_CS($id);
		} else {
			return FALSE;
		}
	}
}
