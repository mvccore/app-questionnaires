<?php

namespace App\Views\Helpers;

class Nl2Br
{
	public function Nl2Br ($str = NULL) {

		return str_replace(
			["\n\r", "\n"],
			["\n", "<br />"],
			$str
		);
	}
}