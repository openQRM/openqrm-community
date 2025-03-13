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

class http
{
/**
* regex pattern for httprequest (crosssitescripting)
* @access protected
* @var array
*/
var $request_filter;


	//---------------------------------------------------------------
	/**
	* returns http request as cleaned string
	* string is empty when request not set
	* @access public
	* @param  $arg string
	* @return string | array [empty if request not set]
	*/
	//---------------------------------------------------------------
	function get_request($arg)
	{
		if(debug::active()) {
			debug::add($arg);
		}
		$Req = '$_REQUEST'.$this->string_to_index($arg);

		if(eval("return isset($Req);") && eval("return $Req;") != '') {
			if(is_array(eval("return $Req;"))) {
				return $this->get_request_array(eval("return $Req;"));
			} else {
				return $this->filter_request(eval("return $Req;"));
			}
		} else {
			return '';
		}

	}

	//-------------------------------------------------
	/**
	 * get values from http request as array
	 * @acess protected
	 * @return array
	 */
	//-------------------------------------------------
	function get_request_array($arg) {

		debug::add($arg);

		$arReturn = array();
		if(is_array($arg)) {
			foreach($arg as $key => $value) {
				if(is_array($value)) {
					$arReturn[$key] = $this->get_request_array($value);
				}
				if(is_string($value)) {
					$arReturn[$key] = $this->filter_request($value);
				}
			}
		}
		if(is_string($arg)) {
			$arReturn[$key] = $this->filter_request($value);
		}
		return $arReturn;
	}

	//---------------------------------------------------------------
	/**
	* set filter for request handling (XSS)
	*
	* <code>
	* $http = new http();
	* $http->set_request_filter(array(
	*    array ( 'pattern' => '~\r\n~', 'replace' => '\n'),
	*  );
	* </code>
	*
	* @access public
	* @param  array $arg
	*/
	//---------------------------------------------------------------
	function set_request_filter($arg = array()) {
		if(isset($arg) && count($arg) > 0) {
			$this->request_filter = array();
			foreach($arg as $key => $value) {
				if(isset($value['pattern'])) {
					if(!isset($value['replace'])) {
						debug::add('could not find array key ["replace"]', 'NOTICE');
						$value['replace'] = '';
					}
					$this->request_filter[] = array('pattern' => $value['pattern'], 'replace' => $value['replace']);
				} else {
					debug::add('could not find array key ["pattern"]', 'ERROR');
				}
			}
		} else {
			debug::add('nothing to do', 'NOTICE');
		}
	}

	//---------------------------------------------------------------
	/**
	* add filter for request handling
	* @access public
	* @param  array $arg
	*/
	//---------------------------------------------------------------
	function add_request_filter( $arg ) {

		$tmp = $this->request_filter;
		$this->set_request_filter($arg);
		if($tmp) {
			$this->request_filter = array_merge($tmp, $this->request_filter);
		}

	}


	//---------------------------------------------------------------
	/**
	* performes preg_replace
	* @access protected
	* @param string $arg
	* @return string
	*/
	//---------------------------------------------------------------
	function filter_request($arg) {
		if(is_string($arg)) {
			$str = '';
			$arg = stripslashes($arg);
			if(is_array($this->request_filter)) {
				foreach ($this->request_filter as $reg) {
					$arg = regex::replace($reg['pattern'], $reg['replace'], $arg);
				}
				$str = $arg;
			} else {
				debug::add('no filter set - use set_request_filter()', 'NOTICE');
				$str = $arg;
			}
			debug::add($arg.' return '.$str);
			return $str;
		} else {
			debug::add($arg.' is not type string', 'ERROR');
		}
	}

	//---------------------------------------------------------------
	/**
	* returns http request [POST/GET] as string
	* @access public
	* @param $firstchar string
	* @param $excludes array
	* @return string [empty if request empty]
	*/
	//---------------------------------------------------------------
	function get_request_string($firstchar = '?', $excludes = array()) {
		$type = array('$_POST','$_GET');
		$_strReturn = '';
		foreach($type as $request) {
			foreach(eval("return $request;") as $name => $foo) {
				if(in_array($name, $excludes) == false) {
					$value = http_request($name);
					if(is_array($value)) {
						foreach($value as $key => $val) {
							$_strReturn .= '&'.$name.'['.$key.']='.$val;
						}
					} else {
						$_strReturn .= '&'.$name.'='.$value;
					}
				}
			}
		}
		if($_strReturn != '') $_strReturn = preg_replace('/^&/', $firstchar, $_strReturn);
		return $_strReturn;
	}

	//-------------------------------------------------
	/**
	 * transform string to array index string
	 * @acess public
	 * @param array $name
	 * @return string
	 */
	//-------------------------------------------------
	function string_to_index($arg) {

		$strReturn = '';

		// replace unindexed array
		$arg = $this->unindex_array($arg);
		$regex = '~(\[.*\])~';

		preg_match($regex, $arg, $matches);
		if($matches) {
			$str = '['.preg_replace('~\[.*\]~', '', $arg).']'.$matches[0];
		}
		else  {
			$str = '['.$arg.']';
		}
		// add quots to make it array
		$str = str_replace('[', '["', $str);
		$str = str_replace(']', '"]', $str);

		return $str;

	}

	//-------------------------------------------------
	/**
	 * remove unindexed array
	 * @acess protected
	 * @param array $name
	 * @return string
	 */
	//-------------------------------------------------
	function unindex_array($name) {
		return preg_replace('~\[]$~', '', $name);
	}

	//---------------------------------------------------------------
	/**
	* header redirect
	* tries php header redirect,
	* on fail js redirect,
	* on fail meta redirect
	*
	* @access public
	* @param  $url string
	*/
	//---------------------------------------------------------------
	function redirect($url){
		if (!headers_sent()){
			header('Location: '.$url); exit;
		} else {
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
			echo '</noscript>'; exit;
		}
	}

}
