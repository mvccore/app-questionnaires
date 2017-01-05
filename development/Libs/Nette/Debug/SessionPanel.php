<?php

include_once(__DIR__.'/IDebugPanel.php');

class SessionPanel implements IDebugPanel
{
	public $now = 0;
	
	public static $SessionMaxTime = 0;

	public static $SessionMetaStore = array();
	
	public static $Session = array();

	public function __construct ()
	{
		$this->now = time();
	}
	
	public function getId()
	{
		return 'session-panel';
	}

	public function getTab()
	{
		ob_start();
		require(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'bar.session.tab.phtml');
		return ob_get_clean();
	}

	public function getPanel()
	{
		$this->readSession();
		if (!self::$Session) return '';
		ob_start();
		require(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'bar.session.panel.phtml');
		return ob_get_clean();
	}
	
	protected function readSession ()
	{
		self::$Session = array();
		if (!isset($_SESSION) || (isset($_SESSION) && is_null($_SESSION))) return;
		$sessionMetaStore = isset($_SESSION['__ZF']) ? $_SESSION['__ZF'] : array();

		foreach ($_SESSION as $sectionName => $section) {
		
			if ($sectionName === '__ZF') continue;
			
			$sectionMetaInfo = new stdClass;
			if (isset($sessionMetaStore[$sectionName]['ENT'])) {
				$sectionMetaInfo->lifeType = 'time';
				$timestamp = $sessionMetaStore[$sectionName]['ENT'] - $this->now;
				if ($timestamp > self::$SessionMaxTime) self::$SessionMaxTime = $timestamp;
				$sectionMetaInfo->timestamp = $timestamp;
				$sectionMetaInfo->timeFormated = self::FormateDate($timestamp);
			} else if ((isset($sessionMetaStore[$sectionName]['B'])) && ($sessionMetaStore[$sectionName]['B'] === TRUE)) {
				$sectionMetaInfo->lifeType = 'browser';
			} else {
				$sectionMetaInfo->lifeType = 'nothing';
			}
			self::$SessionMetaStore[$sectionName] = $sectionMetaInfo;
			
			if (in_array(gettype($section), array('string', 'int', 'bool'))) {
				$dumpedSection[$sectionName] = array('PURE_PHP', $section);
			} else {
				$dumpedSection = array();
				foreach ($section as $key => $value) {
					$valueType = '';
					if (gettype($value) == 'string') {
						// try to unserialize
						$phpValue = unserialize($value);
						if ($phpValue !== FALSE && $value !== 'b:0;') {
							$valueType = 'SERIALIZED';
						} else {
							$phpValue = $value;
						}
						// try  to encode json
						if (!$valueType && (substr($value, 0, 1) == '{' || substr($value, 0, 1) == '[')) {
							$phpValue = json_decode($value);
							if ($phpValue !== NULL && strtolower($value) !== 'null') {
								$valueType = 'JSON ENCODED';
							} else {
								$phpValue = $value;
							}
						}
						if (!$valueType) {
							$valueType = 'PURE PHP';
							$phpValue = $value;
						}
					} else {
						$valueType = 'PURE PHP';
						$phpValue = $value;
					}
					$dumpedSection[$key] = array($valueType, $phpValue);
				}
			}
			self::$Session[$sectionName] = $dumpedSection;	
		}
	}
	
	public function FormateDate ($timestamp = 0)
	{
		$timeFormated = '';
		if ($timestamp >= 31557600) {
			$localVal = floor($timestamp / 31557600);
			$timeFormated .= $localVal . ' year' . (($localVal > 1) ? 's' : '');
			$timestamp = $timestamp - (floor($timestamp / 31557600) * 31557600);
		}
		if ($timestamp >= 2592000) {
			$localVal = floor($timestamp / 2592000);
			$timeFormated .= ((strlen($timeFormated) > 0) ? ', ' : '') . $localVal . ' month' . (($localVal > 1) ? 's' : '');
			$timestamp = $timestamp - (floor($timestamp / 2592000) * 2592000);
		}
		if ($timestamp >= 86400) {
			$localVal = floor($timestamp / 86400);
			$timeFormated .= ((strlen($timeFormated) > 0) ? ', ' : '') . $localVal . ' day' . (($localVal > 1) ? 's' : '');
			$timestamp = $timestamp - (floor($timestamp / 86400) * 86400);
		}
		if ($timestamp >= 3600) {
			$localVal = floor($timestamp / 3600);
			$timeFormated .= ((strlen($timeFormated) > 0) ? ', ' : '') . $localVal . ' hour' . (($localVal > 1) ? 's' : '');
			$timestamp = $timestamp - (floor($timestamp / 3600) * 3600);
		}
		if ($timestamp >= 60) {
			$localVal = floor($timestamp / 60);
			$timeFormated .= ((strlen($timeFormated) > 0) ? ', ' : '') . $localVal . ' minute' . (($localVal > 1) ? 's' : '');
			$timestamp = $timestamp - (floor($timestamp / 60) * 60);
		}
		if ($timestamp > 0) {
			$timeFormated .= ((strlen($timeFormated) > 0) ? ', ' : '') . floor($timestamp) . ' second' . (($timestamp > 1) ? 's' : '');
		}
		return $timeFormated;
	}

}