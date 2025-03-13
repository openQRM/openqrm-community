<?php
/**
 * @package htmlobjects
 */
/*
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

//----------------------------------------------------------------------------------------
/**
 * Regex
 *
 * @package htmlobjects
 * @author Alexander Kuballa akuballa@openqrm-enterprise.com
 * @copyright Copyright (c) 2009, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */
//----------------------------------------------------------------------------------------
class regex
{
	//--------------------------------
	/**
	 * pereg_match()
	 *
	 * @param string $pattern
	 * @param string $match
	 * @return array|null
	 */
	//--------------------------------
	static public function match($pattern, $match)
	{
		@preg_match($pattern, $match, $matches);
		if (debug::active()) {
			$error = error_get_last();
			if (strstr($error['message'], 'preg_match')) {
				$msg = str_replace('preg_match() [<a href=\'function.preg-match\'>function.preg-match</a>]:', '', $error['message']);
				debug::add($msg . ' in ' . $pattern, 'ERROR');
			}
		}
		if ($matches) {
			return $matches;
		} else {
			return null;
		}
	}
	//--------------------------------
	/**
	 * pereg_replace()
	 *
	 * @param string $pattern
	 * @param string $replace
	 * @param string $string
	 * @return array|null
	 */
	//--------------------------------
	static public function replace($pattern, $replace, $string)
	{
		$error = '';
		$str = @preg_replace($pattern, $replace, $string) | $error;
		echo $error;
		if (debug::active()) {
			$error = error_get_last();
			if (strstr($error['message'], 'preg_replace')) {
				$msg = str_replace('preg_replace() [<a href=\'function.preg-replace\'>function.preg-replace</a>]:', '', $error['message']);
				debug::add($msg . ' in ' . $pattern, 'ERROR');
			}
		}
		return $str;
	}
	
}
