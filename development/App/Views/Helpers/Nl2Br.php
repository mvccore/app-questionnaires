<?php

class App_Views_Helpers_Nl2Br
{
	public function Nl2Br ($str = NULL) {

		return str_replace(
			array("\n\r", "\n"),
			array("\n", "<br />"),
			$str
		);
	}
}