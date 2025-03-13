<?php
/**
 * Collectd Api
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
*/

class collectd_api
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->controller = $controller;
		$this->user       = $this->controller->user;
		$this->html       = $this->controller->response->html;
		$this->response   = $this->html->response();
		$this->file       = $this->controller->file;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$action = $this->response->html->request()->get($this->controller->actions_name);
		switch( $action ) {
			case 'image':
				$this->image();
			break;
			case 'download':
				$this->download();
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Get Image
	 *
	 * @param array $_REQUEST[values]
	 * @param string $_REQUEST[interval]
	 * @param string $_REQUEST[path]
	 * @param int $_REQUEST[width] optional
	 * @param int $_REQUEST[height] optional
	 * @access public
	 */
	//--------------------------------------------
	function image( $raw = false ) {
		$values   = $this->response->html->request()->get('values');
		$interval = $this->response->html->request()->get('interval');
		$path     = $this->response->html->request()->get('path');
		$width    = 800;
		if($this->response->html->request()->get('width') !== '') {
			$width = $this->response->html->request()->get('width');
		}
		$height = 200;
		if($this->response->html->request()->get('height') !== '') {
			$height = $this->response->html->request()->get('height');
		}
		$bcolors  = array('#ffe8e8','#e8e8ff','#e2ffe2');
		$lcolors  = array('#ff7777','#7777ff','#55ff55');

		$i   = 0;
		$str = '';
		foreach($values as $value) {
			if($value !== '') {
				$str.= 'DEF:'.$value.'_avg='.$path.':'.$value.':AVERAGE ';
				$str.= 'DEF:'.$value.'_max='.$path.':'.$value.':MAX ';
				$str.= 'AREA:'.$value.'_max'.$bcolors[$i].' ';
				$str.= 'LINE2:'.$value.'_avg'.$lcolors[$i].':'.$value.' ';
				$str.= 'GPRINT:'.$value.'_avg:AVERAGE:%5.1lf%sAvg ';
				$i++;
			}
		}

		$names = explode('/', dirname($path));
		$head = $names[count($names)-1];
		$host = $names[count($names)-2];
		$name  = str_replace('.rrd', '',basename($path));
		$title = $host.' '.$head.' '. str_replace($head, '', $name).' '.$interval.' '.date('d. F Y H:i:s', time());

		$cmd  = 'rrdtool graph - ';
		$cmd .= '-t "'.$title.'" ';
		$cmd .= '--imgformat PNG ';
		$cmd .= '--width '.$width.' ';
		$cmd .= '--height '.$height.' ';
		$cmd .= '--start now-'.$interval.' ';
		$cmd .= '--end now ';
		$cmd .= '--interlaced ';
		$cmd .= $str;

		$return = shell_exec($cmd);
		if($raw === false) {
			$size   = strlen($return);
			$mime   = 'image/png';
			header("Pragma: public");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate");
			header("Content-type: $mime");
			header("Content-Length: ".$size);
			header("Content-disposition: inline; filename=test");
			header("Accept-Ranges: ".$size);
			flush();
			echo $return;
			exit(0);
		}
		else if ($raw === true) {
			return $return;
		}
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @param string $_REQUEST[appliance_id]
	 * @param string $_REQUEST[interval]
	 * @access public
	 */
	//--------------------------------------------
	function download() {

		$_REQUEST[$this->controller->actions_name] = 'statistics';
		$role  = $this->controller->openqrm->role($this->response);
		$allow = $role->check_permission($this->controller, true);
		if($allow === true) {
			$file = tempnam('/dummydir', 'xx');
			$dir = dirname($file).'/'.time().'/';
			$error = $this->file->mkdir($dir);
			if($error === '') {
				$this->controller->action = 'statistics';
				$tab = $this->controller->statistics(true);
				$images = $tab['value']->get_elements('images');

				$x = preg_match_all('~<img src="([^"].*?)"~is', $images, $matches);
				if(isset($matches[1][0])) {
	
					foreach($matches[1] as $match) {
						$values = array();
						$tmp    = explode('?', $match);
						$params = explode('&amp;', $tmp[1]);
						foreach($params as $param) {
							$tmp = explode('=', $param);
							if($tmp[0] === 'path') {
								$path = $tmp[1];
							}
							else if($tmp[0] === 'interval') {
								$interval = $tmp[1];
							}
							else if($tmp[0] === urlencode('values[]')) {
								$values[] = $tmp[1];
							}
							else if($tmp[0] === 'width') {
								$width = $tmp[1];
							}
							else if($tmp[0] === 'height') {
								$height = $tmp[1];
							}
						}
						$_REQUEST['path']     = $path;
						$_REQUEST['interval'] = $interval;
						$_REQUEST['values']   = $values;
						$_REQUEST['width']    = $width;
						$_REQUEST['height']   = $height;

						$names = explode('/', dirname($path));
						$head = $names[count($names)-1];
						$name = str_replace('.rrd', '',basename($path));

						$filename = $head.'-'.$name.'-'.$interval.'.png';
						$this->file->mkfile($dir.$filename, $this->image(true), 'w+', true);
						$images = str_replace($match, $filename, $images);
					}
				}
				$tab['value']->add('', 'form');
				$tab['value']->add('', 'interval');
				$tab['value']->add('', 'submit');
				$tab['value']->add('', 'download');
				$tab['value']->add($images, 'images');
				$data  = '<!DOCTYPE html><html><head><title>collectd</title>';
				$data .= '<style type="text/css">'.$this->file->get_contents($_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/collectd/css/collectd.css').'</style>';
				$data .= '</head><body>';
				$data .= $tab['value']->get_string();
				$data .= '</body></html>';
				$this->file->mkfile($dir.'index.html', $data,'w+', true);

				shell_exec('cd '.$dir.' && tar -czvf '.$file.' *');

				$size = filesize($file);
				header("Pragma: public");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: must-revalidate");
				header("Content-type: application/x-tar");
				header("Content-Length: ".$size);
				header("Content-disposition: inline; filename=collectd_".time().".tar");
				header("Accept-Ranges: ".$size);
				flush();
				readfile($file);
				$this->file->remove($file);
				$this->file->remove($dir, true);
				exit(0);
			}
		}
	}

}
