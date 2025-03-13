<?php
/**
 * Filehandler
 *
 * openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.
 *
 * All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.
 *
 * This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
 * The latest version of this license can be found here: src/doc/LICENSE.txt
 *
 * By using this software, you acknowledge having read this license and agree to be bound thereby.
 *
 * http://openqrm-enterprise.com
 *
 * Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class file_handler
{
	/**
	 *  date as formated string
	 * @access public
	 * @var string
	 */
	var $date_format = "Y/m/d - H:i";
	/**
	 *  file permissions
	 * @access public
	 * @var string
	 */
	var $permissions_file = 0666;
	/**
	 *  dir permissions
	 * @access public
	 * @var string
	 */
	var $permissions_dir = 0777;
	/**
	 *  define allowed chars for filname
	 * @access public
	 * @var string
	 */
	var $regex_filename = '[a-zA-Z0-9~._-]';
	/**
	 *  translation for message strings
	 * @access public
	 * @var array
	 */
	var $lang = array(
		'remove_error' => 'failed to delete %s',
		'copy_error' => 'failed to copy %s to %s',
		'filename_error' => 'filename must be %s',
		'saved_error' => 'failed to save %s',
		'exists_error' => '%s already exists',
		'file' => 'File',
		'folder' => 'Folder',
		'permission_denied' => 'Permission denied',
		'not_found' => 'File not found',
	);
	
	/**
	 *  files not to be shown
	 * @access private
	 * @var array
	 */
	var $arExcludedFiles = array('.', '..');
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct()
	{
		// solve basename problem
		setlocale(LC_ALL, 'en_US.UTF8');
	}
	
	//-------------------------------------------------------	
	/**
	 * return filinfos as array
	 *
	 * @param $path string
	 * @return array|null
	 */
	//-------------------------------------------------------
	function get_fileinfo($path)
	{
		if (file_exists($path)) {
			$ar = array();
			$pi = pathinfo($path);
			
			$ar['path'] = $path;
			$ar['name'] = $pi["basename"];
			$ar['dir'] = $pi["dirname"];
			$ar['filesize'] = filesize($path);
			$ar['date'] = date($this->date_format, filemtime($path));
			if (isset($pi["extension"])) {
				$ar['extension'] = strtolower($pi["extension"]);
			} else {
				$ar['extension'] = '';
			}
			$ar['read'] = is_readable($path);
			$ar['write'] = is_writable($path);
			
			return $ar;
		}
	}
	
	//-------------------------------------------------------	
	/**
	 * return folderinfo as array
	 *
	 * @param $path string
	 * @return array|null
	 */
	//-------------------------------------------------------
	function get_folderinfo($path)
	{
		if (file_exists($path)) {
			$ar["path"] = $path;
			$ar["name"] = basename($path);
			$ar["date"] = date($this->date_format, filemtime($path));
			$ar["permissions"] = $this->get_permissions_octal($path);
			$ar["read"] = is_executable($path);
			$ar["write"] = is_writeable($path);
			return $ar;
		}
	}
	
	//-------------------------------------------------------
	/**
	 * read file list from directory $path and return an array of file infos
	 *
	 * Usage:
	 * <code>
	 *    require_once($this->rootdir.'/class/file.handler.class.php');
	 *    $file = new file_handler();
	 * </code>
	 *
	 * @param    $path string
	 * @param    $excludes array files not to be returned
	 * @param    $pattern string    matching pattern according to the rules used by the libc glob() function
	 * @param    $subpattern    second filter for $pattern (pattern = '*.gif', subpattern = 'name_*' finds "name_*.gif" not "foobar_*.gif")
	 * @return    $ar    array    @see: $this->get_fileinfo()
	 */
	//-------------------------------------------------------
	function get_files($path, $excludes = '', $pattern = '*', $subpattern = null)
	{
		$ar = array();
		if (file_exists($path)) {
			if ($excludes != '') {
				$this->arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
			}
			$glob = glob("$path/$pattern");
			if (isset($subpattern) && $subpattern !== '') {
				$glob = $this->__subpattern($glob, glob("$path/$subpattern", GLOB_BRACE));
			}
			foreach ($glob as $file) {
				if (in_array($file, $this->arExcludedFiles) === false) {
					if (is_file("$file") === true) {
						$ar[] = $this->get_fileinfo("$file");
					}
				}
			}
		}
		return $ar;
	}
	
	//-------------------------------------------------------	
	/**
	 * check if file is dir
	 *
	 * @param array $path
	 * @return bool
	 */
	//-------------------------------------------------------
	function is_dir($path)
	{
		return is_dir($path);
	}
	
	//-------------------------------------------------------	
	/**
	 * check if file is writable
	 *
	 * @param array $path
	 * @return bool
	 */
	//-------------------------------------------------------
	function is_writeable($path)
	{
		return is_writable($path);
	}
	
	//-------------------------------------------------------	
	/**
	 * check if file exists
	 *
	 * @param array $path
	 * @return bool
	 */
	//-------------------------------------------------------
	function exists($path)
	{
		return file_exists($path);
	}
	
	//-------------------------------------------------------	
	/**
	 * synchronize pattern and subpattern
	 *
	 * @param array $orig original glob
	 * @param array $sub sub glob
	 * @return array
	 */
	//-------------------------------------------------------
	function __subpattern($orig, $sub)
	{
		$return = array();
		foreach ($sub as $value) {
			if (in_array($value, $orig)) {
				$return[] = $value;
			}
		}
		return $return;
	}
	
	//-------------------------------------------------------	
	/**
	 * read directory and return an array of folderinfos
	 *
	 * @param string $path
	 * @param array $excludes files not to be returned
	 * @return array
	 */
	//-------------------------------------------------------
	function get_folders($path, $excludes = '', $pattern = '*', $subpattern = null)
	{
		$ar = array();
		if (file_exists($path)) {
			if ($excludes != '') {
				$this->arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
			}
			$glob = glob("$path/$pattern", GLOB_ONLYDIR);
			if (isset($subpattern) && $subpattern !== '') {
				$glob = $this->__subpattern($glob, glob("$path/$subpattern", GLOB_ONLYDIR | GLOB_BRACE));
			}
			foreach ($glob as $file) {
				if (in_array($file, $this->arExcludedFiles) === false) {
					$ar[] = $this->get_folderinfo("$file");
				}
			}
			/*
			$handle = opendir ("$path/.");
			while (false !== ($file = readdir ($handle))) {
				if (in_array($file, $this->arExcludedFiles) === false){
					if (is_dir("$path/$file") === true) {
						$ar[] = $this->get_folderinfo("$path/$file");
					}		
				}	
			}
			*/
		}
		return $ar;
	}
	
	//-------------------------------------------------------
	/**
	 * check valid filename
	 *
	 * @param $path string
	 * @param $replace bool
	 * @return string on error
	 */
	//-------------------------------------------------------	 
	function check_filename($path, $replace = false)
	{
		$str = '';
		$name = basename($path);
		preg_match('/^' . $this->regex_filename . '{' . strlen($name) . '}$/u', $name, $matches);
		if (!isset($matches[0])) {
			$str = sprintf($this->lang['filename_error'], $this->regex_filename);
		}
		if ($replace === false) {
			if (file_exists($path) && is_file($path)) {
				$str = sprintf($this->lang['exists_error'], $this->lang['file'] . ' ' . $name);
			}
			if (file_exists($path) && is_dir($path)) {
				$str = sprintf($this->lang['exists_error'], $this->lang['folder'] . ' ' . $name);
			}
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * check permissions
	 *
	 * @param $path string
	 * @return string on error
	 */
	//-------------------------------------------------------	 
	function check_permissions($path)
	{
		$str = '';
		$name = basename($path);
		if (!is_readable($path)) {
			$str = $this->lang['permission_denied'];
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * set file permissions
	 *
	 * @param $path string
	 */
	//-------------------------------------------------------
	function chmod($path)
	{
		if (is_file($path)) @chmod($path, $this->permissions_file);
		if (is_dir($path)) @chmod($path, $this->permissions_dir);
	}
	
	//-------------------------------------------------------
	/**
	 * copy a file ($path) to $target
	 *
	 * @param $path string
	 * @param $target string
	 * @return string on error
	 */
	//-------------------------------------------------------
	function copy($path, $target)
	{
		$str = '';
		if ($path !== $target) {
			$str = $this->check_filename($target);
			if ($str === '') {
				if (is_dir($path)) {
					$str = $this->mkdir($target);
					if ($str === '') {
						$handle = opendir("$path/.");
						while (false !== ($file = readdir($handle))) {
							if ($file !== '.' && $file !== '..') {
								if (is_dir($path . '/' . $file)) {
									$this->copy($path . '/' . $file, $target . '/' . $file);
								} else {
									if (!@copy($path . '/' . $file, $target . '/' . $file)) {
										$str .= sprintf($this->lang['copy_error'], $this->lang['file'] . ' ' . basename($path));
									} else {
										$this->chmod($target);
									}
								}
							}
						}
					}
				}
				if (is_file($path)) {
					if (!@copy($path, $target)) {
						$str .= sprintf($this->lang['copy_error'], $this->lang['file'] . ' ' . $path, $target);
					} else {
						$this->chmod($target);
					}
				}
			}
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * move a file ($path) to $target
	 *
	 * alias for rename
	 *
	 * @param $path string
	 * @param $target string
	 * @return string
	 */
	//-------------------------------------------------------	 
	function move($path, $target)
	{
		$str = $this->rename($path, $target);
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * rename a file or folder ($path) to $target
	 *
	 * @param $path string
	 * @param $target string
	 * @return string on error
	 * @todo find out why second realpath returns empty
	 */
	//-------------------------------------------------------
	function rename($path, $target)
	{
		$path = realpath($path);
		#$target = realpath($target);
		$str = '';
		if ($path !== $target) {
			$str = $this->check_filename($target);
			if ($str === '') {
				if (@rename($path, $target) === false) {
					$str .= sprintf($this->lang['copy_error'], $this->lang['file'] . ' ' . basename($path));
				} else {
					$this->chmod($target);
				}
			}
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * delete a file ($path)
	 *
	 * @param $path string
	 * @param $recursive bool
	 * @return string on error
	 */
	//-------------------------------------------------------
	function remove($path, $recursive = false)
	{
		$ar = array();
		if (is_file($path)) {
			if (@unlink($path) === false) $ar[] = sprintf($this->lang['remove_error'], $this->lang['file'] . ' ' . basename($path));
		}
		if (is_dir($path)) {
			if ($recursive === false) {
				if (@rmdir($path) === false) $ar[] = sprintf($this->lang['remove_error'], $this->lang['folder'] . ' ' . basename($path));
			}
			if ($recursive === true) {
				$scan = glob(rtrim($path, '/') . '/*');
				foreach ($scan as $file) {
					$error = $this->remove($file, $recursive);
					if ($error !== '') {
						$ar[] = $error;
					}
				}
				if (@rmdir($path) === false) $ar[] = sprintf($this->lang['remove_error'], $this->lang['folder'] . ' ' . basename($path));
			}
		}
		$str = join('<br>', $ar);
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * make a directory ($path)
	 *
	 * @param $path string
	 * @return string on error
	 */
	//-------------------------------------------------------
	function mkdir($path)
	{
		$str = '';
		$str = $this->check_filename($path);
		if ($str === '') {
			if (@mkdir($path) === false) {
				$str = sprintf($this->lang['saved_error'], $this->lang['folder'] . ' ' . basename($path));
			} else {
				$this->chmod($path);
			}
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * make a file
	 *
	 * @param $path string
	 * @param $data string
	 * @param $mode for more details refere to php fopen
	 * @param $replace bool
	 * @return string on error
	 */
	//-------------------------------------------------------
	function mkfile($path, $data, $mode = 'w+', $replace = false)
	{
		$str = '';
		$str = $this->check_filename($path, $replace);
		if ($str === '') {
			$fp = @fopen($path, $mode);
			if ($fp) {
				if (fwrite($fp, $data) === false) {
					$str = sprintf($this->lang['saved_error'], $this->lang['file'] . ' ' . basename($path));
				} else {
					$this->chmod($path);
				}
				fclose($fp);
			} else {
				$str = sprintf($this->lang['saved_error'], $this->lang['file'] . ' ' . basename($path)) . ': ' . $this->lang['permission_denied'];
			}
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * reads an ini file ($path) and returns an array
	 *
	 * @param $path string
	 * @param $multidimensional bool
	 * @return array | null
	 */
	//-------------------------------------------------------
	function get_ini($path, $multidimensional = true)
	{
		if (file_exists($path)) {
			return parse_ini_file($path, $multidimensional);
		}
	}
	
	//-------------------------------------------------------
	/**
	 * creates an ini file ($path) from an array
	 *
	 * " will be saved as &quot;
	 *
	 * @param $path string
	 * @param $data array
	 * @param $extension string fileextension
	 * @return string
	 * @todo ereg
	 */
	//-------------------------------------------------------
	function make_ini($path, $data, $extension = '.ini')
	{
		$str = '';
		if ($extension) {
			preg_match('/' . $extension . '$/i', $path, $matches);
			if (count($matches) === 0) {
				$path = $path . $extension;
			}
		}
		if (is_array($data)) {
			$fp = @fopen($path, 'w+');
			if ($fp) {
				foreach ($data as $key => $value) {
					if (!is_array($value)) {
						fwrite($fp, trim($key) . ' = "' . trim(str_replace('"', '&quot;', $value)) . "\"\n");
					} else {
						fwrite($fp, '[' . trim($key) . ']' . "\n");
						foreach ($value as $subkey => $subvalue) {
							fwrite($fp, trim($subkey) . ' = "' . trim(str_replace('"', '&quot;', $subvalue)) . "\"\n");
						}
					}
				}
				fclose($fp);
				$this->chmod($path);
			} else {
				$str = sprintf($this->lang['saved_error'], $this->lang['file'] . ' ' . basename($path));
			}
		} else {
			$str = 'Error: make_ini data must be of type array!';
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * returns octal filepermissions
	 *
	 * @param $path string
	 * @return string
	 */
	//-------------------------------------------------------	
	function get_permissions_octal($path)
	{
		$info = substr(sprintf('%o', fileperms($path)), -4);
		return $info;
	}
	
	//-------------------------------------------------------
	/**
	 * returns file content as string
	 *
	 * @param $path string
	 * @return string
	 */
	//-------------------------------------------------------	
	function get_contents($path)
	{
		$str = '';
		$error = $this->check_permissions($path);
		if ($error === '') {
			$str = file_get_contents($path);
		} else {
			$str = $error;
		}
		return $str;
	}
	
}
