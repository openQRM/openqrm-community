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

class debug
{
	static protected $debug = false;
	static protected $panic = false;
	static protected $_infos = array();
	//--------------------------------
	/**
	 * Start Debugger
	 *
	 * @access public
	 */
	//--------------------------------
	public static function start($panic = false)
	{
		if ($panic === true) {
			self::$panic = true;
		}
		self::$debug = true;
	}
	//--------------------------------
	/**
	 * Stop Debugger
	 *
	 * @access public
	 */
	//--------------------------------
	public static function stop()
	{
		self::$debug = false;
	}
	//--------------------------------
	/**
	 * Stop Debugger
	 *
	 * @access public
	 */
	//--------------------------------
	public static function active()
	{
		return self::$debug;
	}
	
	//--------------------------------
	/**
	 * Add string to debugger info array
	 *
	 * @access public
	 * @param string $msg
	 * @param string $state
	 */
	//--------------------------------
	public static function add($msg, $state = 'INFO')
	{
		if (self::active()) {
			#if((self::$panic === true) || (self::$panic === false && $state != strtolower('INFO'))) {
			$debug = debug_backtrace();
			self::$_infos[] = $state . ' ' . $debug[1]['class'] . '->' . $debug[1]['function'] . '() ' . $msg;
			#for($i = 2; $i < count($debug); $i++) {
			#	self::$_infos[] = '---- line '.$debug[$i]['line'].' : '.$debug[$i]['class'].'->'.$debug[$i]['function'].'()';
			#}
			#}
		}
	}
	//--------------------------------
	/**
	 * Print Debuger Info
	 *
	 * @access public
	 */
	//--------------------------------
	public static function flush()
	{
		if (self::$debug === true) {
			print "Debugger Info\n";
			print "<pre>\n";
			foreach (self::$_infos as $msg) {
				print $msg . "\n";
			}
			print '</pre>';
			// unset array
			self::$_infos = array();
		}
	}
	
	
}
