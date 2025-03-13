<?php
/**
 * Appliance edit
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class appliance_edit
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'appliance_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "appliance_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'appliance_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'appliance_identifier';
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
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->user       = $openqrm->user();
		$this->controller = $openqrm;



		$wid = $this->response->html->request()->get('appliance_id');
		if($wid === '' && $this->response->html->request()->get('appliance_wizard_id') !== '') {
			$wid = $this->response->html->request()->get('appliance_wizard_id');
		}
		$this->apliance_id = $wid;
		$this->appliance  = new appliance();
		$this->appliance->get_instance_by_id($wid);
		$this->response->add('appliance_id', $wid);


		#$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$response = $this->edit();
		if(isset($response->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
			);
		}
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}

		// get plugins
		$plugins = $this->__plugins();

		$t = $this->response->html->template($this->tpldir.'/appliance-edit.tpl.php');
		$t->add(sprintf($this->lang['title'], $response->name), 'label');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->form);
		//$t->add($plugins);
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($plugins, 'plugins');
/*
		$t->add($this->lang['lang_ha'], 'lang_ha');
		$t->add($this->lang['lang_misc'], 'lang_misc');
		$t->add($this->lang['lang_mgmt'], 'lang_mgmt');
		$t->add($this->lang['lang_moni'], 'lang_moni');
		$t->add($this->lang['lang_dep'], 'lang_dep');
		$t->add($this->lang['lang_net'], 'lang_net');
		$t->add($this->lang['lang_enter'], 'lang_enter');
*/
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Edit
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit() {
		$response  = $this->get_response();
		$form      = $response->form;
		$id        = $this->apliance_id;
		if($id !== '') {
			$appliance = new appliance();
			$appliance->get_instance_by_id($id);
			if(!$form->get_errors() && $this->response->submit()) {
				// update appliance
				$appliance->update($id, $form->get_request());
				$response->msg = sprintf($this->lang['msg'], $appliance->name);
			} 
			else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
			}
			$response->name = $appliance->name;
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'edit');
		$id = $this->apliance_id;

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$appliance  = new appliance();
		$appliance->get_instance_by_id($id);

		$resource = new resource();
		$list     = $resource->get_list();

		$cpus[0] = array( 0, $this->lang['option_auto']);
		$cspe[0] = array( 0, $this->lang['option_auto']);
		$cmod[0] = array( 0, $this->lang['option_auto']);
		$nics[0] = array( 0, $this->lang['option_auto']);
		$memt[0] = array( 0, $this->lang['option_auto']);
		$swap[0] = array( 0, $this->lang['option_auto']);

		foreach ($list as $v) {
			$resource->get_instance_by_id($v['resource_id']);
			$c  = $resource->cpunumber;
			$cs = $resource->cpuspeed;
			$cm = $resource->cpumodel;
			$n  = $resource->nics;
			$m  = $resource->memtotal;
			$s  = $resource->swaptotal;
			
			if(isset($c) && $c  != '0')                { $cpus[$c]  = array($c, $c);   }
			if(isset($cs) && $cs != '0')               { $cspe[$cs] = array($cs, $cs); }
			if(isset($cm) && $cm != '0' && $cm !== '') { $cmod[$cm] = array($cm, $cm); }
			if(isset($n) && $n  != '0')                { $nics[$n]  = array($n, $n);   }
			if(isset($m) && $m  != '0')                { $memt[$m]  = array($m, $m);   }
			if(isset($s) && $s != '0')                 { $swap[$s]  = array($s, $s);   }
		}

		ksort($cpus, SORT_NUMERIC);
		ksort($cspe, SORT_NUMERIC);
		ksort($cmod, SORT_STRING);
		ksort($nics, SORT_NUMERIC);
		ksort($memt, SORT_NUMERIC);
		ksort($swap, SORT_NUMERIC);

		$d['cpus']['label']                        = $this->lang['form_cpus'];
		$d['cpus']['object']['type']               = 'htmlobject_select';
		$d['cpus']['object']['attrib']['index']    = array(0, 1);
		$d['cpus']['object']['attrib']['name']     = 'appliance_cpunumber';
		$d['cpus']['object']['attrib']['options']  = $cpus;
		$d['cpus']['object']['attrib']['selected'] = array($appliance->cpunumber);

		$d['cpuspeed']['label']                        = $this->lang['form_cpuspeed'];
		$d['cpuspeed']['object']['type']               = 'htmlobject_select';
		$d['cpuspeed']['object']['attrib']['index']    = array(0, 1);
		$d['cpuspeed']['object']['attrib']['name']     = 'appliance_cpuspeed';
		$d['cpuspeed']['object']['attrib']['options']  = $cspe;
		$d['cpuspeed']['object']['attrib']['selected'] = array($appliance->cpuspeed);

		$d['cpumodel']['label']                        = $this->lang['form_cpumodel'];
		$d['cpumodel']['object']['type']               = 'htmlobject_select';
		$d['cpumodel']['object']['attrib']['index']    = array(0, 1);
		$d['cpumodel']['object']['attrib']['name']     = 'appliance_cpumodel';
		$d['cpumodel']['object']['attrib']['options']  = $cmod;
		$d['cpumodel']['object']['attrib']['selected'] = array($appliance->cpumodel);

		$d['nics']['label']                        = $this->lang['form_nics'];
		$d['nics']['object']['type']               = 'htmlobject_select';
		$d['nics']['object']['attrib']['index']    = array(0, 1);
		$d['nics']['object']['attrib']['name']     = 'appliance_nics';
		$d['nics']['object']['attrib']['options']  = $nics;
		$d['nics']['object']['attrib']['selected'] = array($appliance->nics);

		$d['memory']['label']                        = $this->lang['form_memory'];
		$d['memory']['object']['type']               = 'htmlobject_select';
		$d['memory']['object']['attrib']['index']    = array(0, 1);
		$d['memory']['object']['attrib']['name']     = 'appliance_memtotal';
		$d['memory']['object']['attrib']['options']  = $memt;
		$d['memory']['object']['attrib']['selected'] = array($appliance->memtotal);

		$d['swap']['label']                        = $this->lang['form_swap'];
		$d['swap']['object']['type']               = 'htmlobject_select';
		$d['swap']['object']['attrib']['index']    = array(0, 1);
		$d['swap']['object']['attrib']['name']     = 'appliance_swaptotal';
		$d['swap']['object']['attrib']['options']  = $swap;
		$d['swap']['object']['attrib']['selected'] = array($appliance->swaptotal);

		$resource = new resource();
		$resource_hostname = '';
		if(isset($appliance->resources) && $appliance->resources !== '') {
			if ($appliance->resources >= 0) {
				$res = $resource->get_instance_by_id($appliance->resources);
				$resource_hostname = $res->hostname;
			} else {
				$resource_hostname = 'auto-select';
			}
		}

		$a = $response->html->a();
		$a->name  = '';
		#$a->label = '<img src="'.$this->controller->get('baseurl').'/img/resource.png">&#160;'.sprintf($this->lang['action_resource'], $resource_hostname);
		$a->label = $resource_hostname;
		$a->css ="manage";
		$a->title = sprintf($this->lang['action_resource'], $resource_hostname);
		$a->href  = $response->get_url($this->actions_name, 'step2');
		$d['resource']['label'] = $this->lang['form_resource'];
		$d['resource']['object'] = $a;

		$img = new image();
		$img = $img->get_instance_by_id($appliance->imageid);
		$a = $response->html->a();
		$a->name  = '';
		#$a->label = '<img src="'.$this->controller->get('baseurl').'/img/image.png">&#160;'.sprintf($this->lang['action_image'], $img->name) ;
		$a->label = $img->name;
		$a->css ="manage";
		$a->title = sprintf($this->lang['action_image'], $img->name) ;
		$a->href  = $response->get_url($this->actions_name, 'step3');
		$d['image']['label'] = $this->lang['form_image'];
		$d['image']['object'] = $a;

		$kern = new kernel();
		$kern = $kern->get_instance_by_id($appliance->kernelid);
		$a = $response->html->a();
		$a->name  = '';
		#$a->label = '<img src="'.$this->controller->get('baseurl').'/img/image.png">&#160;'.sprintf($this->lang['action_image'], $img->name) ;
		$a->label = $kern->name;
		$a->css ="manage";
		$a->title = sprintf($this->lang['action_kernel'], $kern->name) ;
		$a->href  = $response->get_url($this->actions_name, 'step4');
		$d['kernel']['label'] = $this->lang['form_kernel'];
		$d['kernel']['object'] = $a;

		$virtualization = new virtualization();
		if(isset($appliance->resources) && $appliance->resources !== '') {
			$resource->get_instance_by_id($appliance->resources);
		}
		$vl = array();
		$list  = $virtualization->get_list();
		foreach ($list as $v) {
			if(strpos($v['label'], ' VM') === false) {
				$vl[] = array($v['label'], $v['value']);
			} else {
				if($v['value'] === $resource->vtype) {
					$hide_virtualization = true;
					break;
				}
			}
		}
		asort($vl);

		if(isset($hide_virtualization)) {
			$virtualization->get_instance_by_id($resource->vtype);
			$div = $this->response->html->div();
			$div->name = '';
 			$div->add($virtualization->name);
			$d['virtualization']['label'] = $this->lang['form_virtualization'];
			$d['virtualization']['object'] = $div;
		} else {
			$d['virtualization']['label']                        = $this->lang['form_virtualization'];
			$d['virtualization']['object']['type']               = 'htmlobject_select';
			$d['virtualization']['object']['attrib']['index']    = array(1,0);
			$d['virtualization']['object']['attrib']['name']     = 'appliance_virtualization';
			$d['virtualization']['object']['attrib']['options']  = $vl;
			$d['virtualization']['object']['attrib']['selected'] = array($appliance->virtualization);
		}

		$d['capabilities']['label']                         = $this->lang['form_capabilities'];
		$d['capabilities']['object']['type']                = 'htmlobject_input';
		$d['capabilities']['object']['attrib']['type']      = 'text';
		$d['capabilities']['object']['attrib']['name']      = 'appliance_capabilities';
		$d['capabilities']['object']['attrib']['value']     = $appliance->capabilities;
		$d['capabilities']['object']['attrib']['maxlength'] = 1000;

		$d['comment']['label']                         = $this->lang['form_comment'];
		$d['comment']['validate']['regex']             = $this->openqrm->regex['comment'];
		$d['comment']['validate']['errormsg']          = sprintf($this->lang['error_comment'], $this->openqrm->regex['comment']);
		$d['comment']['object']['type']                = 'htmlobject_textarea';
		$d['comment']['object']['attrib']['name']      = 'appliance_comment';
		$d['comment']['object']['attrib']['value']     = $appliance->comment;
		$d['comment']['object']['attrib']['maxlength'] = 255;

		$form->add($d);
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Plugins
	 *
	 * @access protected
	 * @return array
	 */
	//--------------------------------------------
	function __plugins() {

/*
		$ha    = array();
		$net   = array();
		$mgmt  = array();
		$moni  = array();
		$misc  = array();
		$dep   = array();
		$enter = array();
*/

		$return = $this->response->html->div();
		
		$plugin = new plugin();
		$plugins = $plugin->available();
		foreach ($plugins as $k => $v) {
			$p = $plugin->get_config($v);
			$link = '';
			$hook = $this->openqrm->get('webdir')."/plugins/".$v."/openqrm-".$v."-appliance-edit-hook.php";
			if ($this->file->exists($hook)) {
				require_once($hook);
				$function = str_replace("-", "_", 'get_'.$v.'_appliance_edit');
				if(function_exists($function)) {
					$id = $this->apliance_id;
					$link = $function($id, $this->openqrm, $this->response);
					if(is_object($link)) {
						$link->label = ucfirst($v);
						$link->handler = 'onclick="wait();"';
						$link->css ="edit";
						$link->title = preg_replace('~(.*?)<a.*>(.*?)</a>(.*?)~i', '$1$2$3', $p['description']);
					}
				}
			}
			if(isset($link) && $link !== '') {
				$return->add($link);
			}
		}
		return $return;


/*
			if(isset($link) && $link !== '') {
				switch($p['type']) {
					case 'HA':
						$ha[] = $link;
					break;
					case 'monitoring':
						$moni[] = $link;
					break;
					case 'management':
						$mgmt[] = $link;
					break;
					case 'misc':
						$misc[] = $link;
					break;
					case 'network':
						$net[] = $link;
					break;
					case 'deployment':
						$dep[] = $link;
					break;
					case 'enterprise':
						$enter[] = $link;
					break;
				}
			}
		}

		if(count($ha) < 1) {
			$ha[] = $this->lang['no_plugin_available'];
		}
		if(count($net) < 1) {
			$net[] = $this->lang['no_plugin_available'];
		}
		if(count($mgmt) < 1) {
			$mgmt[] = $this->lang['no_plugin_available'];
		}
		if(count($moni) < 1) {
			$moni[] = $this->lang['no_plugin_available'];
		}
		if(count($misc) < 1) {
			$misc[] = $this->lang['no_plugin_available'];
		}
		if(count($dep) < 1) {
			$dep[] = $this->lang['no_plugin_available'];
		}
		if(count($enter) < 1) {
			$enter[] = $this->lang['no_plugin_available'];
		}


		return array(
				'plugin_ha' => $ha,
				'plugin_net' => $net,
				'plugin_mgmt' => $mgmt,
				'plugin_moni' => $moni,
				'plugin_dep' => $dep,
				'plugin_misc' => $misc,
				'plugin_enter' => $enter
			);
*/

	}

}
