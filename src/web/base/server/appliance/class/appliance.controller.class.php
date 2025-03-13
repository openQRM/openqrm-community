<?php
/**
 * Server Controller
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class appliance_controller
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
var $lang = array(
	'select' => array (
		'tab' => 'Server',
		'label' => 'Server',
		'action_remove' => 'remove',
		'action_start' => 'start',
		'action_stop' => 'stop',
		'action_edit' => 'edit',
		'action_release' => 'release',
		'action_add' => 'Add a new Server',
		'action_continue' => 'Continue setup',
		'table_state' => 'State',
		'table_id' => 'Id',
		'table_name' => 'Name',
		'table_type' => 'Type',
		'table_kernel' => 'Kernel',
		'table_image' => 'Image',
		'table_resource' => 'Resource',
		'table_deployment' => 'Deployment',
		'appliance_create_in_progress' => 'Server create in progress by user %s',
		'resource_release' => 'release resource',
		'lang_filter' => 'Filter by Resource',
		'lang_type_filter' => 'Filter by Resource Type',
		'lang_filter_title' => 'Filter by Resource ID, Name or Mac. Use ? as single and * as multi wildcard.',
		'please_wait' => 'Loading. Please wait ..',
	),
	'step1' => array (
		'label' => 'Add Server (1/4)',
		'title' => 'Add a new Server',
		'msg' => 'Added Server %s',
		'form_name' => 'Name',
		'form_comment' => 'Comment',
		'lang_name_generate' => 'generate name',
		'error_name' => 'Name must be %s',
		'error_comment' => 'Comment must be %s',
		'appliance_create_in_progress_event' => 'Server %s create in progress (step %s) by user %s',
		'info' => 'Adding a server is devided in up to four steps:<br><br>(1) Give a name and a description to your new server.<br>(2) Choose a Resource - either a Physical Machine or a Virtual.<br>(3) Add an Image - the &quot;disk&quot; for a Virtual Machine.<br>(4) Add the Kernel - needed to boot a Virtual Machine with pxe.',
		'please_wait' => 'Loading. Please wait ..',
		'error_exists' => 'Server %s is already in use.',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'step2' => array (
		'label' => 'Add Server (2/4)',
		'title' => 'Select a resource for Server %s',
		'msg' => 'Added resource %s to Server %s',
		'or' => 'or',
		'action_add' => 'new resource',
		'appliance_create_in_progress_event' => 'Server %s create in progress (step %s) by user %s',
		'info' => 'Select a resource for your new server from the list below. If there is no suitable resource you can add a new one by selecting NEW RESOURCE. When done adding you will be redirected to this page to continue the setup. Depending on the type of your selected resource the following two steps of the setup might be skipped.',
		'form_resource' => 'Resource',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'step3' => array (
		'label' => 'Add Server  (3/4)',
		'title' => 'Select an image for Server %s',
		'msg' => 'Added image %s to Server %s',
		'or' => 'or',
		'action_add' => 'new image',
		'info' => 'The image represents the &quot;disc&quot; of you servers resource. Please choose one from the list below. If there is no suitable image you can add a new one by selecting NEW IMAGE. When done adding you will be redirected to this page to continue the setup. The image can be configured right after selection by checking the checkbox below.',
		'form_image' => 'Image',
		'form_image_edit' => 'Edit Image details after selection',
		'appliance_create_in_progress_event' => 'Server %s create in progress (step %s) by user %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'step4' => array (
		'label' => 'Add Server  (4/4)',
		'title' => 'Select a kernel for Server %s',
		'msg' => 'Added kernel %s to Server %s',
		'or' => 'or',
		'action_add' => 'new kernel',
		'form_kernel' => 'Kernel',
		'info' => 'A kernel is needed for VMs of type networkboot and must fit the choosen image. Please select one from the list below. If there is no suitable kernel you can add a new one by selecting NEW KERNEL and reading the instructions carefully.',
		'appliance_create_in_progress_event' => 'Server %s create in progress (step %s) by user %s',
		'appliance_created' => 'Server %s created by user %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'remove' => array (
		'label' => 'Remove Server',
		'msg' => 'Removed Server %s',
		'msg_still_active' => 'Not removing Server %s!<br>It is still active.',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'start' => array (
		'label' => 'Start Server',
		'msg' => 'Started Server %s',
		'msg_no_resource' => 'Could not find any available resource for Server %s',
		'msg_always_active' => 'An Server with the openQRM Server as resource is always active!',
		'msg_already_active' => 'Not starting already aktive Server %s',
		'msg_reource_not_idle' => 'Resource %s is not in idle state. Not starting Server %s',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'stop' => array (
		'label' => 'Stop Server',
		'msg' => 'Stopped Server %s',
		'msg_always_active' => 'An Server with the openQRM Server as resource is always active!',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'release' => array (
		'label' => 'Release Server resource',
		'msg' => 'Released Server %s resource',
		'msg_openqrm' => 'The openQRM Server resource cannot be released!',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),
	'edit' => array (
		'label' => 'Edit Server',
		'title' => 'Edit Server %s',
		'msg' => 'Updated Server %s',
		'option_auto' => 'auto',
		'lang_ha' => 'Ha',
		'lang_misc' => 'Misc',
		'lang_mgmt' => 'Management',
		'lang_moni' => 'Monitoring',
		'lang_net' => 'Network',
		'lang_enter' => 'Enterprise',
		'lang_dep' => 'Deployment',
		'form_comment' => 'Comment',
		'form_cpus' => 'Cpus',
		'form_cpuspeed' => 'Cpuspeed',
		'form_cpumodel' => 'Cpumodel',
		'form_capabilities' => 'Capabilities',
		'form_virtualization' => 'Virtualization',
		'form_resource' => 'Resource',
		'form_image' => 'Image',
		'form_kernel' => 'Kernel',
		'form_nics' => 'Nics',
		'form_memory' => 'Memory',
		'form_swap' => 'Swap',
		'action_resource' => 'change resource %s',
		'action_image' => 'change image %s',
		'action_kernel' => 'change kernel %s',
		'error_comment' => 'Comment must be %s only',
		'no_plugin_available' => 'No action available',
		'please_wait' => 'Loading. Please wait ..',
		'canceled' => 'Operation canceled. Please wait ..',
	),

);

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
		$this->tpldir   = $this->rootdir.'/server/appliance/tpl';
		$this->response = $response;
		$this->file     = $this->openqrm->file();
		$this->lang     = $this->user->translate($this->lang, $this->rootdir."/server/appliance/lang", 'appliance.ini');
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
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = "select";
		}

		if($this->action !== 'select') {
			if($this->response->html->request()->get('appliance_wizard_id') !== '') {
				$this->response->add('appliance_wizard_id', $this->response->html->request()->get('appliance_wizard_id'));
			}
			else if($this->response->html->request()->get('appliance_id') !== '') {
				$this->response->add('appliance_id', $this->response->html->request()->get('appliance_id'));
			}
		}
		$this->response->add('resource_filter', $this->response->html->request()->get('resource_filter'));

		// handle table params
		#$vars = $this->response->html->request()->get('appliance');
		#if($vars !== '') {
		#	if(!isset($vars['action'])) {
		#		foreach($vars as $k => $v) {
		#			$this->response->add('appliance['.$k.']', $v);
		#		}
		#	} else {
		#		foreach($vars as $k => $v) {
		#			unset($this->response->params['appliance['.$k.']']);
		#		}
		#	}
		#}

		$content = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$content[] = $this->select(true);
			break;
			case 'step1':
				$content[] = $this->select(false);
				$content[] = $this->step1(true);
			break;
			case 'step2':
				$content[] = $this->select(false);
				$content[] = $this->step2(true);
			break;
			case 'step3':
				$content[] = $this->select(false);
				$content[] = $this->step3(true);
			break;
			case 'step4':
				$content[] = $this->select(false);
				$content[] = $this->step4(true);
			break;
			case 'remove':
				$content[] = $this->select(false);
				$content[] = $this->remove(true);
			break;
			case 'start':
				$content[] = $this->select(false);
				$content[] = $this->start(true);
			break;
			case 'stop':
				$content[] = $this->select(false);
				$content[] = $this->stop(true);
			break;
			case 'release':
				$content[] = $this->release(true);
			break;
			case 'edit':
				$content[] = $this->select(false);
				$content[] = $this->edit(true);
			break;
			case 'redirect':
				$this->__redirect(true);
			break;
			// select
			case 'load_select':
				$tmp           = $this->select(false);
				$tmp['value']  = $this->__loader('select');
				$tmp['active'] = true;
				$content[]     = $tmp;
			break;
			// step2 resource add
			case 'load_radd':
				$content[]     = $this->select(false);
				$tmp           = $this->step2(false);
				$tmp['value']  = $this->__loader('radd');
				$tmp['active'] = true;
				$content[]     = $tmp;
			break;
			// step3 image add
			case 'load_iadd':
				$content[]     = $this->select(false);
				$tmp           = $this->step3(false);
				$tmp['value']  = $this->__loader('iadd');
				$tmp['active'] = true;
				$content[]     = $tmp;
			break;
			// step3 image edit
			case 'load_iedit':
				$content[]     = $this->select(false);
				$tmp           = $this->step3(false);
				$tmp['value']  = $this->__loader('iedit');
				$tmp['active'] = true;
				$content[]     = $tmp;
			break;
			// step4 kernel edit
			case 'load_kadd':
				$content[]     = $this->select(false);
				$tmp           = $this->step4(false);
				$tmp['value']  = $this->__loader('kadd');
				$tmp['active'] = true;
				$content[]     = $tmp;
			break;
			// server edit
			case 'load_edit':
				$content[]     = $this->select(false);
				$tmp           = $this->edit(false);
				$tmp['value']  = $this->__loader('edit');
				$tmp['active'] = true;
				$content[]     = $tmp;
			break;
		}

		$tab = $this->response->html->tabmenu($this->prefix_tab);
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);

		return $tab;
	}

	//--------------------------------------------
	/**
	 * API
	 *
	 * @access public
	 */
	//--------------------------------------------
	function api() {
		require_once($this->rootdir.'/server/appliance/class/appliance.api.class.php');
		$controller = new appliance_api($this);
		$controller->action();
	}


	//--------------------------------------------
	/**
	 * CLI
	 *
	 * @access public
	 */
	//--------------------------------------------
	function cli() {
		require_once($this->rootdir.'/server/appliance/class/appliance.cli.class.php');
		$controller = new appliance_cli($this);
		$controller->action();
	}
	
	//--------------------------------------------
	/**
	 * Select Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function select( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.select.class.php');
			$controller = new appliance_select($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['select'];
			$data = $controller->action();
		}
		$content['label']   = $this->lang['select']['tab'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add Server step 1 (Name)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step1( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step1.class.php');
			$controller                  = new appliance_step1($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step1'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step1']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step1' );
		$content['onclick'] = false;
		if($this->action === 'step1'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Add Server step 2 (Resource)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step2( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step2.class.php');
			$controller                  = new appliance_step2($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step2'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step2']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step2' );
		$content['onclick'] = false;
		if($this->action === 'step2'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add Server step 3 (Image)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step3( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step3.class.php');
			$controller                  = new appliance_step3($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step3'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step3']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step3' );
		$content['onclick'] = false;
		if($this->action === 'step3'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add Server step 4 (Kernel)
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function step4( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.step4.class.php');
			$controller                  = new appliance_step4($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['step4'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['step4']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'step4' );
		$content['onclick'] = false;
		if($this->action === 'step4'){
			$content['active']  = true;
		}
		return $content;
	}
	
	//--------------------------------------------
	/**
	 * Edit Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function edit( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.edit.class.php');
			$controller                  = new appliance_edit($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['edit'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = $this->lang['edit']['label'];
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'edit' );
		$content['onclick'] = false;
		if($this->action === 'edit'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Remove Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function remove( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.remove.class.php');
			$controller                  = new appliance_remove($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['remove'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Remove';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'remove' );
		$content['onclick'] = false;
		if($this->action === 'remove'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Start Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function start( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.start.class.php');
			$controller                  = new appliance_start($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['start'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Start';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'start' );
		$content['onclick'] = false;
		if($this->action === 'start'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Stop Server
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function stop( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.stop.class.php');
			$controller                  = new appliance_stop($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['stop'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Stop';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'stop' );
		$content['onclick'] = false;
		if($this->action === 'stop'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Release Server resource
	 *
	 * @access public
	 * @param bool $hidden
	 * @return array
	 */
	//--------------------------------------------
	function release( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.release.class.php');
			$controller                  = new appliance_release($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = $this->lang['release'];
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
		$content['label']   = 'Release';
		$content['hidden']  = true;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'release' );
		$content['onclick'] = false;
		if($this->action === 'release'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Redirect
	 *
	 * @access public
	 * @param bool $hidden
	 * @return null
	 */
	//--------------------------------------------
	function __redirect( $hidden = true ) {
		$data = '';
		if( $hidden === true ) {
			require_once($this->rootdir.'/server/appliance/class/appliance.redirect.class.php');
			$controller                  = new appliance_redirect($this->openqrm, $this->response);
			$controller->actions_name    = $this->actions_name;
			$controller->tpldir          = $this->tpldir;
			$controller->message_param   = $this->message_param;
			$controller->identifier_name = $this->identifier_name;
			$controller->lang            = '';
			$controller->rootdir         = $this->rootdir;
			$controller->prefix_tab      = $this->prefix_tab;
			$data = $controller->action();
		}
	}

	//--------------------------------------------
	/**
	 * Load Plugin as new tab
	 *
	 * @access public
	 * @param enum $mode [radd|iadd|iedit|edit|select]
	 * @return object
	 */
	//--------------------------------------------
	function __loader($mode) {

		$modes = array();
		$modes['radd']   = array('folder' => 'server');
		$modes['iadd']   = array('folder' => 'server');
		$modes['iedit']  = array('folder' => 'server');
		$modes['kadd']   = array('folder' => 'server');
		$modes['edit']   = array('folder' => 'plugins');
		$modes['select'] = array('folder' => 'plugins');

		$plugin = $this->response->html->request()->get('aplugin');
		$name   = $plugin;
		$class  = $plugin;
		if($this->response->html->request()->get('acontroller') !== '') {
			$class = $this->response->html->request()->get('acontroller');
			$name  = $class;
		}
		$class  = str_replace('-', '_', $class).'_controller';

		// handle new response object
		$response = $this->response->response();
		$response->id = 'aload';
		unset($response->params['appliance[sort]']);
		unset($response->params['appliance[order]']);
		unset($response->params['appliance[limit]']);
		unset($response->params['appliance[offset]']);
		unset($response->params['appliance_filter']);
		$response->add('aplugin', $plugin);
		$response->add('acontroller', $name);
		$response->add($this->actions_name, 'load_'.$mode);

		$path    = $this->openqrm->get('webdir').'/'.$modes[$mode]['folder'].'/'.$plugin.'/class/'.$name.'.controller.class.php';
		$role = $this->openqrm->role($response);
		$data = $role->get_plugin($class, $path);
		$data->pluginroot = '/'.$modes[$mode]['folder'].'/'.$plugin;
		return $data;
	}

}
