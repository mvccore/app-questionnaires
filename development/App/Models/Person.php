<?php

namespace App\Models;

class Person extends Base
{
	/** @var int */
	public $Id;
	/** @var int */
	public $Created;
	/** @var string */
    public $Sex;
	/** @var int */
    public $Age;
	/** @var string */
	public $Education;
	/** @var string */
    public $Job;

	public static $SexOptions = array(
		'F'						=> 'Female',
		'M'						=> 'Male',
		'O'						=> 'Other'
	);
	public static $EducationOptions = array(
		'none'					=> 'None',
		'elementary'			=> 'Elementary School',
		'middle-apprenticeship'	=> 'Middle school with apprenticeship certificate',
		'middle-graduation'		=> 'Middle school with graduation',
		'grammar-school'		=> 'Gymnasium',
		'higher-vocational'		=> 'Higher vocational school',
		'collage-bachelor'		=> 'College - bachelor',
		'collage-magister'		=> 'College - magister',
		'collage-doctor'		=> 'College - doctor and higher',
	);
	public static $JobOptions = array(
		'student'				=> 'Student',
		'no-job'				=> 'No job',
		'employed'				=> 'Employed',
		'businessman'			=> 'Businessman',
		'business-company'		=> 'I care about/I own company',
		'pensioner'				=> 'Pensioner',
	);

	public static function Create ($formData) {
		$newPersonId = self::GetResource()->InsertNew((object) $formData);
		return self::GetById($newPersonId);
	}
	public static function GetById ($id) {
		$data = self::GetResource()->GetById($id);
		$result = new static();
		$result->setUp($data);
		return $result;
	}
	public static function GetMinAndMaxAges () {
		$minMax = (object) self::GetResource()->GetMinAndMaxAges();
		return array(intval($minMax->MinAge), intval($minMax->MaxAge));
	}
}

