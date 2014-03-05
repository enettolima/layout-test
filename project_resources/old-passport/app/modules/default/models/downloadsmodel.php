<?php
class DocumentAccessException extends Exception { 
	
}

class DownloadsModel {
	const MATCH_EXACT = 0;
	const MATCH_REGEX = 1;
	const MATCH_GLOB = 2;
	const MATCH_DIR = 7;
	
	const SCOPE_RECURSIVE = 3;
	const SCOPE_LOCAL = 4;
	
	const ACCESS_ALLOW = 5;
	const ACCESS_DENY = 6;
	
	const TYPE_USER = 8;
	const TYPE_ROLE = 9;
	const TYPE_ALL = 10;
	
	const ACTION_DOWNLOAD = 1;
	const ACTION_UPLOAD = 2;
	
	private $downloadsRoot, $cwd, $rules;
	
	function DownloadsModel($cwd, $identity) {
		$this->downloadsRoot = "c:/web/downloads";
		$this->cwd = '/' . ltrim(rtrim($cwd, '/') . '/', '/');
		$this->identity = $identity;
		
		$this->loadRules();
	}
	
	function getCwd($as_array = false) {
		if (!$as_array)
			return $this->cwd;
		
		$cwdArray = split('/', ltrim(rtrim($this->cwd, '/'), '/'));
		if (empty($cwdArray[0]))
			$cwdArray[0] = '/';
		else
			array_unshift($cwdArray, '/');
		
		return $cwdArray;
	}
	
	function loadRules() {
		$this->rules = array();

		$relPath = '';
		foreach ($this->getCwd(true) as $folder) {
			$relPath = rtrim('/' . ltrim($relPath .= "/$folder", '/'), '/') . '/';
			$accessPath = $this->downloadsRoot . $relPath . '/access.ini';
			
			if (file_exists($accessPath)) {
				foreach (file($accessPath) as $line) {
					if ($commentStart = strpos($line, '#') !== false)
						$line = substr($line, 0, $commentStart);

					$line = trim(strtolower($line));
						
					if (empty($line))
						continue;
						
					// <ACTION R|W|RW> <ACCESS Allow|Deny> <TYPE User|Role|ALL> [ID] [PATTERN blah.txt|/^MA/]
					$args = split(' ', $line, 5);
					
					$action = array_shift($args);
					$action = (strpos($action, 'd') !== false ?DownloadsModel::ACTION_DOWNLOAD : 0) | 
						(strpos($action, 'u') !== false ? DownloadsModel::ACTION_UPLOAD : 0);
					
					$access = array_shift($args);
					$access = ($access[0] == 'a' ? DownloadsModel::ACCESS_ALLOW : DownloadsModel::ACCESS_DENY);
					
					$type = array_shift($args);
					if ($type[0] == 'u') {
						$type = DownloadsModel::TYPE_USER;
						$id = array_shift($args);
					} else if ($type[0] == 'r') {
						$type = DownloadsModel::TYPE_ROLE;
						$id = array_shift($args);
					} else {
						$type = DownloadsModel::TYPE_ALL;
						$id = false;
					}
					
					$pattern = null;
					if (count($args) && $action != DownloadsModel::ACTION_DOWNLOAD) {
						$pattern = array_shift($args);
						
						if (preg_match('/^\/(.+)\/(\w*)$/', $pattern))
							$ruleType = DownloadsModel::MATCH_REGEX;
						else
							$ruleType = DownloadsModel::MATCH_EXACT;
						
					} else
						$ruleType = DownloadsModel::MATCH_DIR;
						
					$rule = (object)array(
						'action' => $action,
						'access' => $access,
						'cwd' => $relPath,
						'type' => $type,
						'id' => $id,
						'match' => $ruleType,
						'pattern' => $pattern);
					
					array_unshift($this->rules, $rule);
				}
			}
		}
	}
	
	function isAllowed($action, $file = null) {
		if (isset($file))
			$file = strtolower($file);
		
		foreach ($this->rules as $rule) {
			$actionMatch = ($rule->action & $action) == $action;
			$typeMatch = 
				($rule->type == DownloadsModel::TYPE_ALL) ||
				($rule->type == DownloadsModel::TYPE_USER && $rule->id == $this->identity->USER_ID) || 
				($rule->type == DownloadsModel::TYPE_ROLE && in_array($rule->id, $this->identity->ROLES));
			
			if ($actionMatch && $typeMatch) {
				$fileMatch = 
					($rule->match == DownloadsModel::MATCH_DIR) || 
					(isset($file) && $rule->match == DownloadsModel::MATCH_EXACT && $file == $rule->pattern) ||
					(isset($file) && $rule->match == DownloadsModel::MATCH_REGEX && preg_match($rule->pattern, $file));
					
				if ($fileMatch)
					return $rule->access == DownloadsModel::ACCESS_ALLOW;
			}
		}
		
		return DownloadsModel::ACCESS_ALLOW;
	}
	
	function downloadFile($filename) {
		if (!$this->isAllowed(DownloadsModel::ACTION_DOWNLOAD, $filename)) {
			throw new DocumentAccessException($filename);
			return false;
		}
			
		$filePath = $this->downloadsRoot . $this->cwd . $filename;
		if (!$fd = fopen($filePath, 'r'))
			return false;
			
		//$finfo = new finfo(FILEINFO_MIME, 'c:\web\php\extras\magic');
		//$mimeType = $finfo->file($filePath);
		
		$mimeTypes = array(
			'pdf' => 'application/pdf',
			'xls' => 'application/vnd.ms-excel');
			
		if (preg_match('/.+\.(\w+)$/', $filename, $matches) && isset($mimeTypes[$matches[1]])) {
			$mimeType = $mimeTypes[$matches[1]];
		} else {
			$mimeType = 'application/x-unknown';
		}
		
		$filename = urlencode($filename);
		/* Output the file data */
		header("Content-type: {$mimeType}");
		header("Content-disposition: attachment; filename={$filename}");
		while ($chunk = fread($fd, 4096)) {
			echo $chunk;
		}
		
		/* Exit */
		exit();
		return true;
	}
	
	function getFiles($sort, $desc, $page = null, $pageSize = null) {	
		$files = (object)array(
			'files' => array(),
			'folders' => array());
		
		$d = dir($this->downloadsRoot . $this->cwd);
		while ($file = $d->read()) {
			if ($file == '.' || $file == '..')
				continue;
			
			if (!$this->isAllowed(DownloadsModel::ACTION_DOWNLOAD, $file))
				continue;
				
			$filePath = $this->downloadsRoot . $this->cwd . $file;
			$fileObj = (object)array(
				'mtime' => filemtime($filePath),
				'filename' => $file);
			
			if (is_dir($filePath))
				$files->folders[] = $fileObj;
			else {
				$fileObj->size = filesize($filePath);
				$files->files[] = $fileObj;
			}
		}
		
		$d->close();
		
		if (!in_array($sort, array('filename', 'mtime', 'size')))
			$sort = 'filename';
			
		if ($desc !== true && $desc !== false)
			$desc = true;

		usort($files->files, create_function(
			'$a, $b',
			'if ($a->' . $sort . ' == ' .  '$b->' . $sort . ') return 0;' .
			'return ($a->' . $sort . ($desc ? ' < ' : ' > ') . '$b->' . $sort . ' ? 1 : -1);'));
		
		if (isset($pageSize) && $pageSize > 0) {
			$folderCount = count($files->folders);
			$fileCount = count($files->folders);
			$start = $pageSize * $page;
			$end = ($pageSize * ($page + 1)) - 1;
			
			if ($start <= $folderCount - 1) {
				if ($end < $folderCount - 1)
					$files->folders = array_slice($files->folders, $start, $end - $start);
				else
					$files->folders = array_slice($files->folders, $start);
			} else
				$files->folders = array();
			
			$start -= $folderCount;
			$end -= $folderCount;
			
			if ($start > 0) {
				if ($end < $fileCount - 1)
					$files->folders = array_slice($files->files, $start, $end - $start);
				else
					$files->folders = array_slice($files->$fileCount, $start);
			} else
				$files->files = array();
		}
		
		/*echo "<pre>";
		var_dump($files);
		echo "</pre>";*/
		
		return $files;
	}
	
	function moveFile($filename, $new) {
		if (!$this->isAllowed(DownloadsModel::ACTION_UPLOAD, $new)) {
			throw new DocumentAccessException($new);
			return false;
		}
		
		return move_uploaded_file($filename, $this->downloadsRoot . $this->cwd . $new);
	}
}