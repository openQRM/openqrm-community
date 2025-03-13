<?php
/**
 * Docblock
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */


class docblock
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $classfile
	 */
	//--------------------------------------------
	function __construct($classfile) {
		$this->path    = realpath($classfile);
		$content       = file_get_contents($this->path);
		$content       = str_replace("\r\n", "\n", $content);
		$this->content = $content;
		ini_set('pcre.backtrack_limit', 10000000);
	}

	//--------------------------------------------
	/**
	 * Get content
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function get() {
		$ar               = array();
		$ar['filename']   = basename($this->path);
		$ar['classname']  = $this->__regex('~[\\n| |\\t]+class[ ]+([^ |\\n]*)~i', false);
		$ar['extends']    = $this->__regex('~extends ([^\\n{ ]*)~i', false);
		$ar['implements'] = $this->__regex('~implements ([^\\n{]*)~i', false);
		$ar['docblock']   = $this->__docblock('class[ ]+'.$ar['classname'], false);
		$ar['attribs']    = $this->__attribs();
		$ar['methods']    = $this->__regex('~[\\n| |\\t]+function[ ]+([^\(| ]*).*~i');

		if(is_array($ar['methods'])) {
			foreach($ar['methods'] as $key => $value) {
				$params = $this->__regex('~function[ ]+'.$key.'[ ]*\((.+)\)~i', false);
				$ar['methods'][$key]['params']   = trim(htmlentities($params));
				$ar['methods'][$key]['docblock'] = $this->__docblock('function[ ]+'.$key.'[ ]*\(');
			}
		}

		return $ar;
	}

	//--------------------------------------------
	/**
	 * Docblock
	 *
	 * @access private
	 * @param integer $offset
	 * @return array
	 */
	//--------------------------------------------
	function __docblock( $offset ) {
		$res = $this->__regex('~/\*\*\\n(.+)\*/.+'.$offset.'~s', false);
		$res = preg_replace('~.+/\*\*\\n~is', '', $res);
		$res = str_replace(array("\t","* ","*"), '', $res);
		$res = htmlentities($res);
		$res = str_replace('&lt;code&gt;', '<code>', $res);
		$res = str_replace('&lt;/code&gt;', '</code>', $res);
		$res = explode("\n", $res);
		unset($res[count($res)-1]);
		return $res;
	}

	//--------------------------------------------
	/**
	 * Get attribs
	 *
	 * @access private
	 * @return string|array
	 */
	//--------------------------------------------
	function __attribs() {
		$regex = '~[\\n| |\\t]+(var|private|protected|public|static)[ ]+\$([^ |=|;]*).*[\\n]~i';
		preg_match_all($regex, $this->content, $matches);
		if(isset($matches[2][0])) {
			$m = array();
			foreach($matches[2] as $key => $value) {
				$default = $this->__regex('~'.$matches[1][$key].'.+\$'.$value.'.*=([^;]*).*\\n~im', false);
				$default = trim(htmlentities($default));
				switch($default) {
					case "''": $default = 'empty';  break;
					case ""  : $default = 'null';	break;
				}
				if(strpos($default, 'array') === false) {
					$default = str_replace(array("\t",'"',"'","&quot;"), '', $default);
				}
				$m[$value]['access']   = ($matches[1][$key] === 'var')? 'public' : $matches[1][$key];
				$m[$value]['default']  = $default;
				$m[$value]['docblock'] = $this->__docblock($matches[1][$key].'[ ]+\$'.$value.'[ |=|;]+');
			}
			return $m;
		} else {
			return '';
		}
	}

	//--------------------------------------------
	/**
	 * Regex
	 *
	 * @access private
	 * @param string $regex
	 * @param bool $all
	 * @param bool $key
	 * @return string|array
	 */
	//--------------------------------------------
	function __regex($regex, $all = true, $key = true) {
		if($all === true) {
			preg_match_all($regex, $this->content, $matches);
			if(isset($matches[1][0])) {
				if(count($matches[1]) < 2) {
					return array($matches[1][0] => '');
				} else {
					if($key === true) {
						$m = array();
						foreach($matches[1] as $value) {
							$m[$value] = '';
						}
						return $m;
					} else {
						return $matches[1];
					}
				}
			} else {
				return '';
			}
		} else {
			preg_match($regex, $this->content, $matches);
			if(isset($matches[1])) {
				return $matches[1];
			} else {
				return '';
			}
		}
	}

}
