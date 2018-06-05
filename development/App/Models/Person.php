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

	public static $SexOptions = [
		'F'						=> 'Female',
		'M'						=> 'Male',
		'O'						=> 'Other'
	];
	public static $EducationOptions = [
		'none'					=> 'None',
		'elementary'			=> 'Elementary School',
		'middle-apprenticeship'	=> 'Middle school with apprenticeship certificate',
		'middle-graduation'		=> 'Middle school with graduation',
		'grammar-school'		=> 'Gymnasium',
		'higher-vocational'		=> 'Higher vocational school',
		'collage-bachelor'		=> 'College - bachelor',
		'collage-magister'		=> 'College - magister',
		'collage-doctor'		=> 'College - doctor and higher',
	];
	public static $JobOptions = [
		'student'				=> 'Student',
		'no-job'				=> 'No job',
		'employed'				=> 'Employed',
		'businessman'			=> 'Businessman',
		'business-company'		=> 'I care about/I own company',
		'pensioner'				=> 'Pensioner',
	];

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
	/**
	 * @return int[]
	 */
	public static function GetMinAndMaxAges ($idQuestionnaire) {
		$minMax = (object) self::GetResource()->GetMinAndMaxAges($idQuestionnaire);
		return [intval($minMax->MinAge), intval($minMax->MaxAge)];
	}
	/**
	 * @return \DateTime[]
	 */
	public static function GetMinAndMaxDates ($idQuestionnaire) {
		$minMax = (object) self::GetResource()->GetMinAndMaxDates($idQuestionnaire);
		$nowStr = date('Y-m-d H:i:s', time());
		$minMax->MinDate = is_null($minMax->MinDate) ? $nowStr : $minMax->MinDate ;
		$minMax->MaxDate = is_null($minMax->MaxDate) ? $nowStr : $minMax->MaxDate ;
		$minDate = \DateTime::createFromFormat('Y-m-d H:i:s', $minMax->MinDate);
		$maxDate = \DateTime::createFromFormat('Y-m-d H:i:s', $minMax->MaxDate);
		return [$minDate, $maxDate];
	}
}

