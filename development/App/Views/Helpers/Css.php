<?php

class App_Views_Helpers_Css extends App_Views_Helpers_Assets
{
	/**
	 * Allowed media types for <link> tag
	 *
	 * @var array
	 */
	private static $_allowedMediaTypes = array('all','aural','braille','handheld','projection','print','screen','tty','tv',);
	
	/**
	 * Array with all defined files to create specific link tags
	 * 
	 * @var $scriptsGroupContainer array 
	 */
	protected static $linksGroupContainer = array();
	
	/**
	 * View Helper Method, returns current object instance.
	 *
	 * @return $this
	 */
	public function Css ($groupName = self::GROUP_NAME_DEFAULT) {
		$this->actualGroupName = $groupName;
		if (!isset(self::$linksGroupContainer[$groupName])) {
			self::$linksGroupContainer[$groupName] = array();
		}
		return $this;
	}

	/**
	 * Check if stylesheet is already presented in stylesheets group
	 *
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $doNotMinify
	 *
	 * @return bool
	 */
	public function Contains ($path = '', $media = 'all', $doNotMinify = FALSE) {
		$result = FALSE;
		if (!isset(self::$linksGroupContainer[$this->actualGroupName])) {
			self::$linksGroupContainer[$this->actualGroupName] = array();
		} else {
			$linksGroup = self::$linksGroupContainer[$this->actualGroupName];
			foreach ($linksGroup as $item) {
				if ($item->path == $path) {
					if ($item->media == $media && $item->doNotMinify == $doNotMinify) {
						$result = TRUE;
						break;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Append stylesheet after all group stylesheets for later render process with php tags executing in given file
	 *
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Css
	 */
	public function AppendRendered($path = '', $media = 'all', $doNotMinify = FALSE) {
		return $this->Append($path, $media, TRUE, $doNotMinify);
	}

	/**
	 * Prepend stylesheet before all group stylesheets for later render process with php tags executing in given file
	 *
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $renderPhpTags
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Css
	 */
	public function PrependRendered($path = '', $media = 'all', $doNotMinify = FALSE) {
		return $this->Prepend($path, $media, TRUE, $doNotMinify);
	}

	/**
	 * Add stylesheet into given index of stylesheets group array for later render process with php tags executing in given file
	 *
	 * @param  int     $index
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $renderPhpTags
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Css
	 */
	public function OffsetSetRendered($index = 0, $path = '', $media = 'all', $doNotMinify = FALSE) {
		return $this->OffsetSet($index, $path, $media, TRUE, $doNotMinify);
	}

	/**
	 * Append stylesheet after all group stylesheets for later render process
	 *
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $renderPhpTags
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Css
	 */
	public function Append($path = '', $media = 'all', $renderPhpTags = FALSE, $doNotMinify = FALSE) {
		$item = $this->_completeItem($path, $media, $renderPhpTags, $doNotMinify);
		self::$linksGroupContainer[$this->actualGroupName][] = $item;
		return $this;
	}

	/**
	 * Prepend stylesheet before all group stylesheets for later render process
	 *
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $renderPhpTags
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Css
	 */
	public function Prepend($path = '', $media = 'all', $renderPhpTags = FALSE, $doNotMinify = FALSE) {
		$item = $this->_completeItem($path, $media, $renderPhpTags, $doNotMinify);
		array_unshift(self::$linksGroupContainer[$this->actualGroupName], $item);
		return $this;
	}

	/**
	 * Add stylesheet into given index of group stylesheets array for later render process
	 *
	 * @param  int     $index
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $renderPhpTags
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Css
	 */
	public function OffsetSet ($index = 0, $path = '', $media = 'all', $renderPhpTags = FALSE, $doNotMinify = FALSE) {
		$item = $this->_completeItem($path, $media, $renderPhpTags, $doNotMinify);
		$groupItems = self::$linksGroupContainer[$this->actualGroupName];
		$newItems = array();
		$added = FALSE;
		foreach ($groupItems as $key => $groupItem) {
			if ($key == $index) {
				$newItems[] = $item;
				$added = TRUE;
			}
			$newItems[] = $groupItem;
		}
		if (!$added) $newItems[] = $item;
		self::$linksGroupContainer[$this->actualGroupName] = $newItems;
		return $this;
	}

	/**
	 * Create data item to store for render process
	 *
	 * @param  string  $path
	 * @param  string  $media
	 * @param  boolean $render
	 * @param  boolean $doNotMinify
	 * 
	 * @return stdClass
	 */
	private function _completeItem ($path, $media, $render, $doNotMinify) {
		if (self::$fileCheckingAndRendering) {
			if (!$path) $this->exception('Path to *.css can\'t be an empty string.');
			if (!in_array($media, self::$_allowedMediaTypes)) $this->exception('Media could be only values: ' . implode(', ', self::$_allowedMediaTypes) . '.');
			$duplication = $this->_isDuplicateStylesheet($path);
			if ($duplication) $this->exception("Stylesheet '$path' is already added in css group: '$duplication'.");
		}
		return (object) array(
			'path'			=> $path,
			'media'			=> $media,
			'render'		=> $render,
			'doNotMinify'	=> $doNotMinify,
		);
	}
	
	/**
	 * Is the linked stylesheet duplicate?
	 *
	 * @param  string $path
	 * 
	 * @return string
	 */
	private function _isDuplicateStylesheet ($path) {
		$result = '';
		foreach (self::$linksGroupContainer as $groupName => $groupItems) {
			foreach ($groupItems as $item) {
				if ($item->path == $path) {
					$result = $groupName;
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Render link elements as html code with links to original files or temporary rendered files
	 *
	 * @param int $indent
	 * 
	 * @return string
	 */
	public function Render ($indent = 0) {
		if (count(self::$linksGroupContainer[$this->actualGroupName]) === 0) return '';
		$minify = (bool)self::$globalOptions['cssMinify'];
		$joinTogether = (bool)self::$globalOptions['cssJoin'];
		if ($joinTogether) {
			$result = $this->_renderItemsTogether(
				$this->actualGroupName, 
				self::$linksGroupContainer[$this->actualGroupName], 
				$indent, 
				$minify
			);
		} else {
			$result = $this->_renderItemsSeparated(
				$this->actualGroupName, 
				self::$linksGroupContainer[$this->actualGroupName], 
				$indent, 
				$minify
			);
		}
		return $result;
	}

	/**
	 * Minify stylesheet string and return minified result
	 * 
	 * @param string $css
	 * @param string $path 
	 * 
	 * @return string
	 */
	private function _minify (& $css, $path) {
		$result = '';
		if (!class_exists('Minify_CSS')) {
			$this->exception("Class 'Minify_CSS' doesn't exist, place library from 'https://github.com/mrclay/minify' into '/Libs/Minify/Css.php'.");
		}
		try {
			$result = Minify_CSS::minify($css);
		} catch (Exception $e) {
			$this->exception("Unable to minify stylesheet ('$path').");
		}
		return $result;
	}

	/**
	 * Render data items as one <link> html tag or all another <link> html tags after with files which is not possible to minify
	 * 
	 * @param string  $actualGroupName 
	 * @param array   $items 
	 * @param int     $indent 
	 * @param boolean $minify 
	 * 
	 * @return string
	 */
	private function _renderItemsTogether ($actualGroupName = '', $items = array(), $indent = 0, $minify = FALSE) {
		$appCompilation = MvcCore::GetCompiled();

		// some configurations is not possible to render together and minimized
		list($itemsToRenderMinimized, $itemsToRenderSeparately) = $this->filterItemsForNotPossibleMinifiedAndPossibleMinifiedItems($items);
		
		$indentStr = $this->getIndentString($indent);
		$resultItems = array();
		if (self::$fileCheckingAndRendering) $resultItems[] = '<!-- css group begin: ' . $actualGroupName . ' -->';
		
		// process array with groups, which are not possible to minimize
		foreach ($itemsToRenderSeparately as $attrHashKey => $itemsToRender) {
			foreach ($itemsToRender as $item) {
				if ($item->render || ($minify && !$item->doNotMinify)) {
					$item->href = $this->AssetUrl($this->_renderFileToTmpAndGetNewHref($item, $minify));
				} else {
					$item->href = $this->AssetUrl($item->path);
				}
				if (!$appCompilation) {
					$item->href = $this->addFileModificationTimeToHrefUrl($item->href, $item->path);
				}
				$resultItems[] = $this->_renderItemSeparated($item);
			}
		}

		// process array with groups to minimize
		foreach ($itemsToRenderMinimized as $attrHashKey => $itemsToRender) {
			$resultItems[] = $this->_renderItemsTogetherAsGroup($itemsToRender, $minify);
		}

		if (self::$fileCheckingAndRendering) $resultItems[] = '<!-- css group end: ' . $actualGroupName . ' -->';
		
		return $indentStr . implode(PHP_EOL . $indentStr, $resultItems);
	}
	
	/**
	 * Render all items in group together, when application is compiled, do not check source files and changes
	 * 
	 * @param array   $itemsToRender 
	 * @param boolean $minify 
	 * 
	 * @return string
	 */
	private function _renderItemsTogetherAsGroup ($itemsToRender = array(), $minify = FALSE) {
		// complete tmp filename by source filenames and source files modification times
		$filesGroupInfo = array();
		foreach ($itemsToRender as $item) {
			$fullPath = $this->getAppRoot() . $item->path;
			$filesGroupInfo[] = $item->path . '?_' . self::getFileImprint($fullPath);
			if (self::$fileCheckingAndRendering && !MvcCore::GetCompiled()) {
				if (!file_exists($fullPath)) {
					$this->exception("File not found in CSS view rendering process ('$fullPath').");
				}
			}
		}
		$tmpFileFullPath = $this->getTmpFileFullPathByPartFilesInfo($filesGroupInfo, $minify, 'css');
		/*
		echo '<pre><code>';
		var_dump($filesGroupInfo);
		var_dump($tmpFileFullPath);
		echo '</code></pre>';
		*/
		// check, if the rendered, together completed and minimized file is in tmp cache already
		if (self::$fileCheckingAndRendering) {
			if (!file_exists($tmpFileFullPath)) {
				// load all items and join them together
				$resultContent = '';
				foreach ($itemsToRender as $hashKey => $item) {
					$srcFileFullPath = $this->getAppRoot() . $item->path;
					if ($item->render) {
						$fileContent = $this->_renderFile($srcFileFullPath);
					} else if ($minify) {
						$fileContent = file_get_contents($srcFileFullPath);
					}
					$fileContent = $this->_convertStylesheetPathsFromRelatives2TmpAbsolutes(
						$fileContent, $item->path
					);
					if ($minify) $fileContent = $this->_minify($fileContent, $item->path);
					$resultContent .= PHP_EOL . "/* " . $item->path . " */" . PHP_EOL . $fileContent . PHP_EOL;
				}
				// save completed tmp file
				$this->saveFileContent($tmpFileFullPath, $resultContent);
				$this->log("Css files group rendered ('$tmpFileFullPath').", 'debug');
			}
		}
		// complete <link> tag with tmp file path in $tmpFileFullPath variable
		$firstItem = array_merge((array) $itemsToRender[0], array());
		$pathToTmp = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		$firstItem['href'] = $this->AssetUrl($pathToTmp);
		return $this->_renderItemSeparated((object) $firstItem);
	}
	
	/**
	 * Render css file by absolute path as php file and return rendered result as string
	 * 
	 * @param string $absolutePath
	 * @return string
	 */
	private function _renderFile ($absolutePath) {
		ob_start();
		try {
			include($absolutePath);
		}
		catch (Exception $e) {
			$this->exceptionHandler($e);
		}
		return ob_get_clean();
	}
	
	/**
	 * Converts all relative paths in all css rules to absolute paths with MvcCore Url structures
	 *
	 * @param mixed $fullPathContent css file full path
	 * @param mixed $href css file href value
	 * 
	 * @return string
	 *
	 */
	private function _convertStylesheetPathsFromRelatives2TmpAbsolutes (& $fullPathContent, $href) {
		$lastHrefSlashPos = mb_strrpos($href, '/');
		if ($lastHrefSlashPos === FALSE) return $fullPathContent;
		$stylesheetDirectoryRelative = mb_substr($href, 0, $lastHrefSlashPos + 1);
		
		// process content for all double dots
		$position = 0;
		while ($position < mb_strlen($fullPathContent)) {
			$doubleDotsPos = mb_strpos($fullPathContent, '../', $position);
			if ($doubleDotsPos === FALSE) break;
			
			// make sure that double dot string is in url('') or url("") block
			
			// try to find first occurance of url(" backwards
			$lastUrlBeginStrPos = mb_strrpos(mb_substr($fullPathContent, 0, $doubleDotsPos), 'url(');
			if ($lastUrlBeginStrPos === FALSE) {
				$position = $doubleDotsPos + 3;
				continue;
			}
			
			// then check if between that are only [\./ ]
			$beginOfUrlBlockChars = mb_substr($fullPathContent, $lastUrlBeginStrPos + 4, $doubleDotsPos - ($lastUrlBeginStrPos + 4));
			$beginOfUrlBlockChars = preg_replace("#[\./ \"'_\-]#", "", $beginOfUrlBlockChars);
			if (mb_strlen($beginOfUrlBlockChars) > 0) {
				$position = $lastUrlBeginStrPos + 4;
				continue;
			}
			
			// try to find first occurance of ")
			$firstUrlEndStrPos = mb_strpos($fullPathContent, ')', $doubleDotsPos);
			if ($firstUrlEndStrPos === FALSE) {
				$position = $doubleDotsPos + 3;
				continue;
			}
			
			// then check of between that are only [a-zA-Z\./ ]
			$endOfUrlBlockChars = mb_substr($fullPathContent, $doubleDotsPos + 3, $firstUrlEndStrPos - ($doubleDotsPos + 3));
			$endOfUrlBlockChars = preg_replace("#[a-zA-Z\./ \"'_\-\?\&]#", "", $endOfUrlBlockChars);
			if (mb_strlen($endOfUrlBlockChars) > 0) {
				$position = $firstUrlEndStrPos + 1;
				continue;
			}
			
			// if it is not the Url block, shift the position and continue
			
			// replace relative path to absolute path
			$lastUrlBeginStrPos += 4;
			$urlSubStr = mb_substr($fullPathContent, $lastUrlBeginStrPos, $firstUrlEndStrPos - $lastUrlBeginStrPos);
			
			// get double or single quotes or no quotes
			$firstStr = mb_substr($urlSubStr, 0, 1);
			$lastStr = mb_substr($urlSubStr, mb_strlen($urlSubStr) - 1, 1);
			if ($firstStr === '"' && $lastStr === '"') {
				$urlSubStr = mb_substr($urlSubStr, 1, mb_strlen($urlSubStr) - 2);
				$quote = '"';
			} else if ($firstStr === "'" && $lastStr === "'") {
				$urlSubStr = mb_substr($urlSubStr, 1, mb_strlen($urlSubStr) - 2);
				$quote = "'";
			} else {
				$quote = '"';
			}
			
			// translate relative to web absolute path
			$trimmedUrlSubStr = ltrim($urlSubStr, './');
			$trimmedPartLength = mb_strlen($urlSubStr) - mb_strlen($trimmedUrlSubStr);
			$trimmedPart = trim(mb_substr($urlSubStr, 0, $trimmedPartLength), '/');
			$subjectRestPath = trim(mb_substr($urlSubStr, $trimmedPartLength), '/');
			
			$urlFullBasePath = str_replace('\\', '/', realpath($this->getAppRoot() . $stylesheetDirectoryRelative . $trimmedPart));
			$urlFullPath = $urlFullBasePath . '/' . $subjectRestPath;
			
			// complete stylesheet new path
			$webPath = mb_substr($urlFullPath, mb_strlen($this->getAppRoot()));
			$webPath = $this->AssetUrl($webPath);

			// replace the url part
			$fullPathContent = mb_substr($fullPathContent, 0, $lastUrlBeginStrPos)
				. $quote . $webPath . $quote
				. mb_substr($fullPathContent, $firstUrlEndStrPos);
			
			// shift the position property
			$position = $lastUrlBeginStrPos + mb_strlen($webPath) + 3;
		}
		
		return $fullPathContent;
	}
	
	/**
	 * Render css file by path as php file and store result in tmp directory and return new href value
	 * 
	 * @param stdClass $item
	 * @param boolean  $minify
	 * 
	 * @return string
	 */
	private function _renderFileToTmpAndGetNewHref ($item, $minify = FALSE) {
		$path = $item->path;
		$tmpFileName = '/rendered_css_' . self::$systemConfigHash . '_' . trim(str_replace('/', '_', $path), "_");
		$srcFileFullPath = $this->getAppRoot() . $path;
		$tmpFileFullPath = $this->getTmpDir() . $tmpFileName;
		if (self::$fileCheckingAndRendering) {
			if (file_exists($srcFileFullPath)) {
				$srcFileModDate = filemtime($srcFileFullPath);
			} else {
				$srcFileModDate = 1;
			}
			if (file_exists($tmpFileFullPath)) {
				$tmpFileModDate = filemtime($tmpFileFullPath);
			} else {
				$tmpFileModDate = 0;
			}
			if ($srcFileModDate !== FALSE && $tmpFileModDate !== FALSE) {
				if ($srcFileModDate > $tmpFileModDate) {
					if ($item->render) {
						$fileContent = $this->_renderFile($srcFileFullPath);
					} else if ($minify) {
						$fileContent = file_get_contents($srcFileFullPath);
					}
					$fileContent = $this->_convertStylesheetPathsFromRelatives2TmpAbsolutes($fileContent, $path);
					if ($minify) $fileContent = $this->_minify($fileContent, $item->path);
					$this->saveFileContent($tmpFileFullPath, $fileContent);
					$this->log("Css file rendered ('$tmpFileFullPath').", 'debug');
				}
			}
		}
		$tmpPath = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		return $tmpPath;
	}
	
	/**
	 * Create HTML link element from data item
	 *
	 * @param  stdClass $item
	 * 
	 * @return string
	 */
	private function _renderItemSeparated (stdClass $item) {
		$result = '<link rel="stylesheet"';
		if ($item->media !== 'all') $result .= ' media="' . $item->media . '"';
		if (!$item->render && self::$fileCheckingAndRendering) {
			$fullPath = $this->getAppRoot() . $item->path;
			if (!file_exists($fullPath)) {
				$this->log("File not found in CSS view rendering process ('$fullPath').", 'error');
			}
		}
		$result .= ' href="' . $item->href . '" />';
		return $result;
	}

	/**
	 * Render data items as separated <link> html tags
	 * 
	 * @param string  $actualGroupName 
	 * @param array   $items 
	 * @param int     $indent 
	 * @param boolean $minify 
	 * 
	 * @return string
	 */
	private function _renderItemsSeparated ($actualGroupName = '', $items = array(), $indent = 0, $minify = FALSE) {
		$indentStr = $this->getIndentString($indent);
		$resultItems = array();
		if (self::$fileCheckingAndRendering) $resultItems[] = '<!-- css group begin: ' . $actualGroupName . ' -->';
		$appCompilation = MvcCore::GetCompiled();
		foreach ($items as $item) {
			if ($item->render || ($minify && !$item->doNotMinify)) {
				$item->href = $this->AssetUrl($this->_renderFileToTmpAndGetNewHref($item, $minify));
			} else {
				$item->href = $this->AssetUrl($item->path);
			}
			if (!$appCompilation) {
				$item->href = $this->addFileModificationTimeToHrefUrl($item->href, $item->path);
			}
			$resultItems[] = $this->_renderItemSeparated($item);
		}
		if (self::$fileCheckingAndRendering) $resultItems[] = '<!-- css group end: ' . $actualGroupName . ' -->';	
		return $indentStr . implode(PHP_EOL . $indentStr, $resultItems);
	}
}