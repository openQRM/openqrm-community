<?php
/**
 * collectd statistics
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class collectd_statistics
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'collectd_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "collectd_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'collectd_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'collectd_identifier';
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();
/**
* path to image
* @access public
* @var string
*/
var $image_path = 'api.php?action=plugin&plugin=collectd&collectd_action=image';
/**
* image_width
* @access public
* @var integer
*/
var $image_width = 243;
/**
* image_height
* @access public
* @var integer
*/
var $image_height = 800;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->openqrm  = $openqrm;
		$this->user     = $this->openqrm->user();
		$this->rootdir  = $this->openqrm->get('webdir');
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->tpldir   = $this->rootdir.'/plugins/collectd/tpl';
		if($this->response->html->request()->get('appliance_id') !== '') {
			$appliance = $this->openqrm->appliance();
			$appliance->get_instance_by_id($this->response->html->request()->get('appliance_id'));
			$this->appliance = $appliance;
			if(isset($appliance->resources) && $appliance->resources !== '') {
				$resource = new resource();
				$resource->get_instance_by_id($appliance->resources);
				$this->resource = $resource;
			}
		}
		$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
		$this->lib = '/usr/share/openqrm/plugins/collectd/data';

		$intervals[0] = array('all');
		$intervals[1] = array('1hour');
		$intervals[2] = array('1day');
		$intervals[3] = array('1week');
		$intervals[4] = array('1month');
		$this->intervals = $intervals;
		$this->response->add('interval', $this->response->html->request()->get('interval'));
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$response = $this->statistics();
		$t = $this->response->html->template($this->tpldir.'/collectd-statistics.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->images, 'images');
		$t->add($response->links, 'links');
		$t->add($response->download, 'download');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		if(isset($this->resource)) {
			$t->add(sprintf($this->lang['label'], $this->resource->hostname), 'label');
		} else {
			$t->add($this->lang['no_data'], 'label');
		}
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Statistics
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function statistics() {
		$response = $this->get_response();
		$images = '';
		$links  = '';
		if(isset($this->resource)) {
			$folder = $this->lib.'/'.$this->appliance->name;
			// if resource is the openqrm server
			if ($this->resource->id == 0) {
				$folder = $this->lib.'/openqrm';
			}
			if($this->file->exists($folder)) {
				$folders =$this->file->get_folders($folder);
				$i       = 0;
				$max     = count($folders) -1;
				foreach($folders as $dir) {
					if($i === $max && $max > 2) {
						$links .= '<a href="#'.$dir['name'].'" class="last">'.ucfirst($dir['name']).'</a>';
					} else {
						$links  .= '<a href="#'.$dir['name'].'">'.ucfirst($dir['name']).'</a>';
					}
					$images .= '<h3><a id="'.$dir['name'].'">'.ucfirst($dir['name']).'</a><a href="#top" class="toplink">&and;</a></h3><div class="image_box">';
					$files = $this->file->get_files($dir['path']);
					foreach($files as $file) {
						$path = $file['path'];
						$cmd  = 'rrdtool info '.$path;
						$cmd .= "|grep 'ds\['";
						$cmd .= "|sed 's/^ds\[//'";
						$cmd .= "|sed 's/\].*//'";
						$cmd .= "|sort";
						$cmd .= "|uniq";
						$ret  = shell_exec($cmd);
						$src  = $this->image_path;
						$src .= '&amp;path='.$path;
						$src .= '&amp;width='.$this->image_width;
						$src .= '&amp;height='.$this->image_height;
						if(isset($ret)) {
							$values = explode("\n", $ret);
							foreach($values as $value) {
								if($value !== '') {
									$src .= '&amp;'.urlencode('values[]').'='.$value;
								}
							}
						}
						if($this->response->html->request()->get('interval') === '') {
							$intervals = $this->intervals[1];
						} 
						else if ($this->response->html->request()->get('interval') === 'all') {
							for($i=1;$i<$c=count($this->intervals);$i++) {
								$intervals[] = $this->intervals[$i][0];
							}
						} else {
							$intervals = array($this->response->html->request()->get('interval'));
						}
						$images .= '<h4>'.str_replace('.rrd', '', $file['name']).'</h4>';
						foreach ($intervals as $interval) {
							$alt = str_replace('.rrd', '', basename($path)).'_'.$interval;
							$images .= '<img src="'.$src.'&amp;interval='.$interval.'&amp;uniqe='.time().'" alt="'.$alt.'">';
						}
					}
					$images .= '</div>';
					$i++;
				}
			}
		}

		$a = $response->html->a();
		$a->label  = $this->lang['action_download'];
		$a->id     = 'downloadlink';
		$a->css    = 'badge';
		$a->href   = $this->image_path;
		$a->href  .= '&interval='.$this->response->html->request()->get('interval');
		$a->href  .= '&appliance_id='.$this->response->html->request()->get('appliance_id');
		$a->href  .= '&'.$this->actions_name.'=download';
		$response->download  = $a;
		$response->links  = $links;
		$response->images = $images;
		return $response;
	}



	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'statistics');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$submit->css = 'interval_submit';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$d['interval']['label']                       = $this->lang['form_interval'];
		$d['interval']['object']['type']              = 'htmlobject_select';
		$d['interval']['object']['attrib']['id']      = 'interval';
		$d['interval']['object']['attrib']['handler'] = 'onchange="wait();this.form.submit();"';
		$d['interval']['object']['attrib']['name']    = 'interval';
		$d['interval']['object']['attrib']['index']   = array(0,0);
		$d['interval']['object']['attrib']['options'] = $this->intervals;
		if($this->response->html->request()->get('interval') === '') {
			$d['interval']['object']['attrib']['selected'] = array($this->intervals[1][0]);
		} 


		$form->add($d);
		$response->form = $form;
		return $response;
	}


}
