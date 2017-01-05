<?php

class App_Views_Helpers_Js extends App_Views_Helpers_Assets
{	
	/**
	 * Whatever Expires header is send over http protocol, 
	 * minimal cache time for external files will be one 
	 * day from last download
	 * 
	 * @const integer
	 */
	const EXTERNAL_MIN_CACHE_TIME = 86400;

	/**
	 * Array with all defined files to create specific script tags
	 * 
	 * @var array 
	 */
	protected static $scriptsGroupContainer = array();

	/**
	 * View Helper Method, returns current object instance.
	 *
	 * @param  string $groupName string identifier
	 * 
	 * @return App_Views_Helpers_Js
	 */
	public function Js ($groupName = self::GROUP_NAME_DEFAULT) {
		$this->actualGroupName = $groupName;
		if (!isset(self::$scriptsGroupContainer[$groupName])) {
			self::$scriptsGroupContainer[$groupName] = array();
		}
		return $this;
	}

	/**
	 * Check if script is already presented in scripts group
	 *
	 * @param  string  $path
	 * @param  boolean $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 *
	 * @return bool
	 */
	public function Contains ($path = '', $async = FALSE, $defer = FALSE, $doNotMinify = FALSE) {
		$result = FALSE;
		if (!isset(self::$scriptsGroupContainer[$this->actualGroupName])) {
			self::$scriptsGroupContainer[$this->actualGroupName] = array();
		} else {
			$linksGroup = self::$scriptsGroupContainer[$this->actualGroupName];
			foreach ($linksGroup as $item) {
				if ($item->path == $path) {
					if ($item->async == $async && $item->defer == $defer && $item->doNotMinify == $doNotMinify) {
						$result = TRUE;
						break;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Append script after all group scripts for later render process with downloading external content
	 *
	 * @param  string  $path
	 * @param  boolean $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Js
	 */
	public function AppendExternal ($path = '', $async = FALSE, $defer = FALSE, $doNotMinify = FALSE) {
		return $this->Append($path, $async, $defer, $doNotMinify, TRUE);
	}

	/**
	 * Prepend script before all group scripts for later render process with downloading external content
	 *
	 * @param  string  $path
	 * @param  boolean $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Js
	 */
	public function PrependExternal ($path = '', $async = FALSE, $defer = FALSE, $doNotMinify = FALSE) {
		return $this->Prepend($path, $async, $defer, $doNotMinify, TRUE);
	}
	
	/**
	 * Add script into given index of scripts group array for later render process with downloading external content
	 *
	 * @param  integer $index
	 * @param  string  $path
	 * @param  boolean $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 * 
	 * @return App_Views_Helpers_Js
	 */
	public function OffsetExternal ($index = 0, $path = '', $async = FALSE, $defer = FALSE, $doNotMinify = FALSE) {
		return $this->Offset($index, $path, $async, $defer, $doNotMinify, TRUE);
	}

	/**
	 * Append script after all group scripts for later render process
	 *
	 * @param  string  $path
	 * @param  boolean $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 * @param  boolean $external
	 * 
	 * @return App_Views_Helpers_Js
	 */
	public function Append ($path = '', $async = FALSE, $defer = FALSE, $doNotMinify = FALSE, $external = FALSE) {
		$item = $this->_completeItem($path, $async, $defer, $doNotMinify, $external);
		self::$scriptsGroupContainer[$this->actualGroupName][] = $item;
		return $this;
	}

	/**
	 * Prepend script before all group scripts for later render process
	 *
	 * @param  string  $path
	 * @param  boolean $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 * @param  boolean $external
	 * 
	 * @return App_Views_Helpers_Js
	 */
	public function Prepend ($path = '', $async = FALSE, $defer = FALSE, $doNotMinify = FALSE, $external = FALSE) {
		$item = $this->_completeItem($path, $async, $defer, $doNotMinify, $external);
		array_unshift(self::$scriptsGroupContainer[$this->actualGroupName], $item);
		return $this;
	}

	/**
	 * Add script into given index of scripts group array for later render process
	 *
	 * @param  integer $index
	 * @param  string  $path
	 * @param  boolean $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 * @param  boolean $external
	 * 
	 * @return App_Views_Helpers_Js
	 */
	public function Offset ($index = 0, $path = '', $async = FALSE, $defer = FALSE, $doNotMinify = FALSE, $external = FALSE) {
		$item = $this->_completeItem($path, $async, $defer, $doNotMinify, $external);
		$groupItems = self::$scriptsGroupContainer[$this->actualGroupName];
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
		self::$scriptsGroupContainer[$this->actualGroupName] = $newItems;
		return $this;
	}
	
	/**
	 * Create data item to store for render process
	 *
	 * @param  string  $path
	 * @param  string  $async
	 * @param  boolean $defer
	 * @param  boolean $doNotMinify
	 * @param  boolean $external
	 * 
	 * @return stdClass
	 */
	private function _completeItem ($path, $async, $defer, $doNotMinify, $external) {
		if (self::$logingAndExceptions) {
			if (!$path) $this->exception('Path to *.js can\'t be an empty string.');
			$duplication = $this->_isDuplicateScript($path);
			if ($duplication) $this->exception("Script '$path' is already added in js group: '$duplication'.");
		}
		return (object) array(
			'path'			=> $path,
			'async'			=> $async,
			'defer'			=> $defer,
			'doNotMinify'	=> $doNotMinify,
			'external'		=> $external,
		);
	}

	/**
	 * Is the linked script duplicate?
	 *
	 * @param  string $path
	 * 
	 * @return string
	 */
	private function _isDuplicateScript ($path) {
		$result = '';
		foreach (self::$scriptsGroupContainer as $groupName => $groupItems) {
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
	 * Render script elements as html code with links to original files or temporary downloaded files
	 *
	 * @param  int $indent
	 * 
	 * @return string
	 */
	public function Render ($indent = 0) {
		if (count(self::$scriptsGroupContainer[$this->actualGroupName]) === 0) return '';
		$minify = (bool)self::$globalOptions['jsMinify'];
		$joinTogether = (bool)self::$globalOptions['jsJoin'];
		if ($joinTogether) {
			$result = $this->_renderItemsTogether(
				$this->actualGroupName, 
				self::$scriptsGroupContainer[$this->actualGroupName], 
				$indent, 
				$minify
			);
		} else {
			$result = $this->_renderItemsSeparated(
				$this->actualGroupName, 
				self::$scriptsGroupContainer[$this->actualGroupName], 
				$indent, 
				$minify
			);
		}
		return $result;
	}
	
	/**
	 * Render data items as separated <script> html tags
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
		if (self::$fileCheckingAndRendering) $resultItems[] = '<!-- js group begin: ' . $actualGroupName . ' -->';
		$appCompilation = MvcCore::GetCompiled();
		foreach ($items as $item) {
			if ($item->external) {
				$item->src = $this->AssetUrl($this->_downloadFileToTmpAndGetNewHref($item, $minify));
			} else if ($minify && !$item->doNotMinify) {
				$item->src = $this->AssetUrl($this->_renderFileToTmpAndGetNewHref($item, $minify));
			} else {
				$item->src = $this->AssetUrl($item->path);
			}
			if (!$appCompilation) {
				if ($item->external) {
					$tmpOrSrcPath = substr($item->src, strlen(self::$basePath));
				} else {
					$tmpOrSrcPath = $item->src;
				}
				$item->src = $this->addFileModificationTimeToHrefUrl($item->src, $item->path);
			}
			$resultItems[] = $this->_renderItemSeparated($item);
		}
		if (self::$fileCheckingAndRendering) $resultItems[] = '<!-- js group end: ' . $actualGroupName . ' -->';
		return $indentStr . implode(PHP_EOL . $indentStr, $resultItems);
	}
	
	/**
	 * Render js file by path and store result in tmp directory and return new href value
	 * 
	 * @param stdClass $item
	 * @param boolean  $minify
	 * 
	 * @return string
	 */
	private function _renderFileToTmpAndGetNewHref ($item, $minify = FALSE) {
		$path = $item->path;
		$tmpFileName = '/rendered_js_' . self::$systemConfigHash . '_' . trim(str_replace('/', '_', $path), "_");
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
					$fileContent = file_get_contents($srcFileFullPath);
					if ($minify) {
						$fileContent = $this->_minify($fileContent, $path);
					}
					$this->saveFileContent($tmpFileFullPath, $fileContent);
					$this->log("Js file rendered ('$tmpFileFullPath').", 'debug');
				}
			}
		}
		$tmpPath = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		return $tmpPath;
	}
	
	/**
	 * Download js file by path and store result in tmp directory and return new href value
	 * 
	 * @param stdClass $item
	 * @param boolean  $minify
	 * 
	 * @return string
	 */
	private function _downloadFileToTmpAndGetNewHref ($item, $minify = FALSE) {
		$path = $item->path;
		$tmpFileFullPath = $this->getTmpDir() . '/external_js_' . md5($path) . '.js';
		if (self::$fileCheckingAndRendering) {
			if (file_exists($tmpFileFullPath)) {
				$cacheFileTime = filemtime($tmpFileFullPath);
			} else {
				$cacheFileTime = 0;
			}
			if (time() > $cacheFileTime + self::EXTERNAL_MIN_CACHE_TIME) {
				while (TRUE) {
					$newPath = $this->_getPossiblyRedirectedPath($path);
					if ($newPath === $path) {
						break;
					} else {
						$path = $newPath;
					}
				}
				$fr = fopen($path, 'r');
				$fileContent = '';
				$bufferLength = 102400; // 100 KB
				$buffer = '';
				while ($buffer = fread($fr, $bufferLength)) {
					$fileContent .= $buffer;
				}
				fclose($fr);
				if ($minify) {
					$fileContent = $this->_minify($fileContent, $path);
				}
				$this->saveFileContent($tmpFileFullPath, $fileContent);
				$this->log("External js file downloaded ('$tmpFileFullPath').", 'debug');
			}
		}
		$tmpPath = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		return $tmpPath;
	}

	/**
	 * If there is any redirection in external content path - get redirect path
	 * 
	 * @param string $path 
	 * 
	 * @return string
	 */
	private function _getPossiblyRedirectedPath ($path = '') {
		$fp = fopen($path, 'r');
		$metaData = stream_get_meta_data($fp);
		foreach ($metaData['wrapper_data'] as $response) {
			// Were we redirected? */
			if (strtolower(substr($response, 0, 10)) == 'location: ') {
				// update $src with where we were redirected to
				$path = substr($response, 10);
			}
		}
		return $path;
	}
	
	/**
	 * Create HTML script element from data item
	 *
	 * @param  stdClass $item
	 * 
	 * @return string
	 */
	private function _renderItemSeparated (stdClass $item) {
		$result = '<script type="text/javascript"';
		if ($item->async) $result .= ' async="async"';
		if ($item->async) $result .= ' defer="defer"';
		if (!$item->external && self::$fileCheckingAndRendering) {
			$fullPath = $this->getAppRoot() . $item->path;
			if (!file_exists($fullPath)) {
				$this->log("File not found in CSS view rendering process ('$fullPath').", 'error');
			}
		}
		$result .= ' src="' . $item->src . '"></script>';
		return $result;
	}

	/**
	 * Minify javascript string and return minified result
	 * 
	 * @param string $js
	 * @param string $path
	 * 
	 * @return string
	 */
	private function _minify (& $js, $path) {
		$result = '';
		if (!class_exists('JSMin')) {
			$this->exception("Class 'JSMin' doesn't exist, place library from 'http://code.google.com/p/jsmin-php/' into '/Libs/JSMin.php'.");
		}
		try {
			$result = JSMin::minify($js);
		} catch (Exception $e) {
			$this->exception("Unable to minify javascript ('$path').");
		}
		return $result;
	}

	/**
	 * Render data items as one <script> html tag or all another <script> html tags after with files which is not possible to minify
	 * 
	 * @param string  $actualGroupName 
	 * @param array   $items 
	 * @param int     $indent 
	 * @param boolean $minify 
	 * 
	 * @return string
	 */
	private function _renderItemsTogether ($actualGroupName = '', $items = array(), $indent, $minify = FALSE) {
		$appCompilation = MvcCore::GetCompiled();
		
		// some configurations is not possible to render together and minimized
		list($itemsToRenderMinimized, $itemsToRenderSeparately) = $this->filterItemsForNotPossibleMinifiedAndPossibleMinifiedItems($items);
		
		$indentStr = $this->getIndentString($indent);
		$resultItems = array();
		if (self::$fileCheckingAndRendering) $resultItems[] = '<!-- js group begin: ' . $actualGroupName . ' -->';
		
		// process array with groups, which are not possible to minimize
		foreach ($itemsToRenderSeparately as $attrHashKey => $itemsToRender) {
			foreach ($itemsToRender as $item) {
				if ($item->external) {
					$item->src = $this->AssetUrl($this->_downloadFileToTmpAndGetNewHref($item, $minify));
				} else if ($minify && !$item->doNotMinify) {
					$item->src = $this->AssetUrl($this->_renderFileToTmpAndGetNewHref($item, $minify));
				} else {
					$item->src = $this->AssetUrl($item->path);
				}
				if (!$appCompilation) {
					if ($item->external) {
						$tmpOrSrcPath = substr($item->src, strlen(self::$basePath));
					} else {
						$tmpOrSrcPath = $item->src;
					}
					$item->src = $this->addFileModificationTimeToHrefUrl($tmpOrSrcPath, $item->path);
				}
				$resultItems[] = $this->_renderItemSeparated($item);
			}
		}

		// process array with groups to minimize
		foreach ($itemsToRenderMinimized as $attrHashKey => $itemsToRender) {
			$resultItems[] = $this->_renderItemsTogetherAsGroup($itemsToRender, $minify);
		}

		if (self::$fileCheckingAndRendering) $resultItems[] = $indentStr . '<!-- js group end: ' . $actualGroupName . ' -->';
		
		return $indentStr . implode(PHP_EOL, $resultItems);
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
			if ($item->external) {
				$srcFileFullPath = $this->_downloadFileToTmpAndGetNewHref($item, $minify);
				$filesGroupInfo[] = $item->path . '?_' . self::getFileImprint($this->getAppRoot() . $srcFileFullPath);
			} else {
				$fullPath = $this->getAppRoot() . $item->path;
				$filesGroupInfo[] = $item->path . '?_' . self::getFileImprint($fullPath);
				if (self::$fileCheckingAndRendering && !MvcCore::GetCompiled()) {
					if (!file_exists($fullPath)) {
						$this->exception("File not found in JS view rendering process ('$fullPath').");
					}
				}
			}
		}
		$tmpFileFullPath = $this->getTmpFileFullPathByPartFilesInfo($filesGroupInfo, $minify, 'js');
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
					if ($item->external) {
						$srcFileFullPath = $this->_downloadFileToTmpAndGetNewHref($item, $minify);
						$fileContent = file_get_contents($this->getAppRoot() . $srcFileFullPath);
					} else if ($minify) {
						$fileContent = file_get_contents($srcFileFullPath);
						if ($minify) $fileContent = $this->_minify($fileContent, $item->path);
					} else {
						$fileContent = file_get_contents($srcFileFullPath);
					}
					$resultContent .= PHP_EOL . "/* " . $item->path . " */" . PHP_EOL . $fileContent . PHP_EOL;
				}
				// save completed tmp file
				$this->saveFileContent($tmpFileFullPath, $resultContent);
				$this->log("Js files group rendered ('$tmpFileFullPath').", 'debug');
			}
		}
		// complete <link> tag with tmp file path in $tmpFileFullPath variable
		$firstItem = array_merge((array) $itemsToRender[0], array());
		$pathToTmp = substr($tmpFileFullPath, strlen($this->getAppRoot()));
		$firstItem['src'] = $this->AssetUrl($pathToTmp);
		/*
		echo '<pre><code>';
		var_dump($firstItem);
		echo '</code></pre>';
		*/
		return $this->_renderItemSeparated((object) $firstItem);
	}
}
