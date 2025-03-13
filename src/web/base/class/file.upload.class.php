<?php
/**
 * @package file
 */

/**
 * Fileupload
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
class file_upload
{
	
	/**
	 *  translation for message strings
	 * @access public
	 * @var array
	 */
	var $lang = array(
		'400' => 'Bad Request: Uploadfile not set',
		'401' => 'Unauthorized: Directory is not writeable',
		'403' => 'Forbidden: ',
		'404' => 'Not Found: Directory not exists',
		'406' => 'Not Acceptable: File is not uploded',
		'500' => 'Server Error: ',
		'Server' => array(
			'1' => 'File exceeds upload_max_filesize',
			'2' => 'File exceeds MAX_FILE_SIZE',
			'3' => 'File was only partially uploaded',
			'4' => 'No file was uploaded',
			'6' => 'Missing a temporary folder',
			'7' => 'Failed to write file to disk',
			'8' => 'File upload stopped by extension',
		),
	);
	
	//---------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param $object file
	 * @access public
	 */
	//---------------------------------------
	function __construct($obj)
	{
		$this->file = $obj;
	}
	
	//---------------------------------------
	/**
	 * Upload
	 *
	 * @access public
	 * @param $key string
	 * @param $dir string
	 * @param $name string
	 * @param $replace bool allow to replace files if exists
	 * @return array
	 */
	//---------------------------------------
	function upload($key, $dir, $name = '', $replace = false)
	{
		if (isset($_FILES[$key])) {
			$userfile = $_FILES[$key];
			if ($userfile['error'] === 0) {
				if (is_uploaded_file($userfile["tmp_name"])) {
					($name == '') ? $name = $userfile['name'] : null;
					if (is_dir($dir)) {
						$file = $dir . "/" . $name;
						$error = $this->file->check_filename($file, $replace);
						if ($error === '') {
							if (@move_uploaded_file($userfile['tmp_name'], $file) !== false) {
								$this->file->chmod($file);
								return '';
							} else return $this->print_error('500', $this->lang['Server'][7]);
						} else return $this->print_error('403', $error);
					} else return $this->print_error('404');
				} else return $this->print_error('406');
			} else return $this->print_error('500', $this->lang['Server'][$userfile['error']]);
		} else return $this->print_error('400');
	}
	
	//---------------------------------------
	/**
	 * @access protected
	 * @param $key int
	 * @param $error string
	 * @return array
	 */
	//---------------------------------------
	function print_error($key, $error = '')
	{
		$arr = array(
			'status' => $key,
			'msg' => $this->lang[$key] . $error,
		);
		return $arr;
	}
	
}
