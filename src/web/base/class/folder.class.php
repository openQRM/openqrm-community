<?php
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

// -----------------------------------------------------------------------------------------------------------------------
//
//
//
//
//
// -----------------------------------------------------------------------------------------------------------------------
class Folder
{
	var $files = array();
	var $folders = array();
	var $arExcludedFiles = array('.', '..');
	
	//-------------------------------------------------------------------
	//
	//
	//-------------------------------------------------------------------
	function getFolderContent($path, $excludes = '')
	{
		
		if ($excludes != '') {
			$arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
		} else {
			$arExcludedFiles = $this->arExcludedFiles;
		}
		
		$handle = opendir("$path/.");
		while (false != ($file = readdir($handle))) {
			if (in_array($file, $arExcludedFiles) == FALSE) {
				if (is_file("$path/$file") == TRUE) {
					$myFile = new File("$path/$file");
					$this->files[] = $myFile;
				}
			}
		}
	}
	
	//-------------------------------------------------------------------
	//
	//
	//-------------------------------------------------------------------
	function getFolders($path, $excludes = '')
	{
		
		if ($excludes != '') {
			$arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
		} else {
			$arExcludedFiles = $this->arExcludedFiles;
		}
		$handle = opendir("$path/.");
		while (false != ($file = readdir($handle))) {
			if (in_array($file, $arExcludedFiles) == FALSE) {
				if (is_dir("$path/$file") == TRUE) {
					$this->folders[] = $file;
				}
			}
		}
		sort($this->folders);
	}
}
