<?php

include_once(__DIR__.'/IDebugPanel.php');

// class IncludePanel implements Nette\Diagnostics\IBarPanel
class IncludePanel implements IDebugPanel
{
	protected static $includedFiles = array();
	protected static $includedFilesCount = 0;
	
	public function getPanel()
	{
		$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
		$list = self::getFiles();
		$userListCodes = array_map(function($item) use($docRoot) {
			$item = str_replace('\\', '/', $item);
			$text = str_replace($docRoot, '', $item);
			$href = DebugHelpers::editorLink($item, 1);
			return '<a href="javascript:(function(){var%20w=window.open(\''.$href.'\',\'_blank\');setTimeout(function(){w.close();},1);})();void(0);"><nobr>'.$text.'</nobr></a><br />';
		}, $list);
		$userListCode = join("", $userListCodes);
		return "<div class=''><div class='h1'>Included files ("
			.self::getCount()
			.")</div><div class='nette-inner'><code>"
			.$userListCode
			."<code></div></div>";
	}

	public function getTab()
	{
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACt0lEQVQ4y62TW0iTcRjGn28nv43NzW14nM506s0UJDMVyo4k2RwolpJUhEUSyyiCCsSrSsrIKLuqoDAsBCVIQsWyoDLLQyhGU1Gz2jyxT79t7vR9/y5Kc+ZFFz2XD8//B+/z/l8h1pFMJhOEqtS5m3fkWyL1KSZm3s75vJ4x/ItCQ5WywsMnm1rffia9Ux7S9MlJjtW1EXl0wnmYLYK1eeFaIyvvwN0z1bWlaQmR4AhgZzmwkgiMDfVtxWAH7XWxADC+nA8iSqVSQ9qukjJXQATrnB9fZv2YYjjYWA5SQUBSfrziommfqZXWRhUsvxGtBvj9fiMXZhBY5/wQCygseHhMMRysk3bo3R8QF18M44ZoWh8f/+h2wxMTHNNdQSPQNA1EpJ7wKRMFNpbDhCOA0W+z0PVUIik1A+aC/Xjd/R5CWiYZHJ/SBXJLmqjVgENFKRkajfBli80kV+mSQS+MwOhsQZghFQcrriEpOmola9i2p3smbuPulQ4qy1P3nisTdwjcC3P24WcvhK+qF7eH25BRXAVzaQ0UAgWmp13o7f0BsVgICtSfDqpOpx8t2eK6VfMwMNHImevzjPZLWrVadurCVbQ9H8X1y29ACIHFkoWRkXkYjVrwhPzagkoRkpypd9w8e0883Ejyr/Aiyf3szE1yQ2KiyMV60Nk5Crfbh6goOez2RQwN2eF0LoH8BogY1ms98iC8iEnIFhEe7XxzXSAkpxY+XwBLSx6oVBJoNDRMpiTYbC7QtBAsuwoAAPMDPe0Y6FkpyOv1w+Pxg2EWUViYAAAQiznExoYgJiYODgcDnuf//gfLcru9iIjWoaHlcZD/fXIC76zjHymRZJERK74SQtYH9Pf3IUcqh06tCfKdM9NwKGOaqHD9HQLC88033NR6ACp9Zw6l1BrXvTal1so/re/C/9JPOxcb0VoXrMkAAAAASUVORK5CYII="/>' . self::getCount();
	}

	public function getId()
	{
		return 'include-panel';
	}
	
	protected static function getCount ()
	{
		if (!self::$includedFilesCount) {
			self::$includedFilesCount = count(self::getFiles());
		}
		return self::$includedFilesCount;
	}
	
	protected static function getFiles ()
	{
		if (!self::$includedFiles) {
			self::$includedFiles = get_included_files();
		}
		return self::$includedFiles;
	}

}

