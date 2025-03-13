<?php
/**
 * Plugins Configuration
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class aa_plugins_configure
{
/**
* plugin key
* @access public
* @var string
*/
var $plugin_key = 'aa_plugins';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'aa_plugins_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'aa_plugins_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "aa_plugins_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'aa_plugins_tab';
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
	 * @param htmlobject_response $response
	 * @param file $file
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->openqrm  = $openqrm;
		$this->user		= $openqrm->user();
		$this->file     = $this->openqrm->file();
		$this->plugin   = $this->response->html->request()->get($this->identifier_name);
		$this->response->add($this->identifier_name, $this->plugin);
		require_once($this->openqrm->get('classdir').'/openqrm_server.class.php');
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
		$form = $this->configure();
		if(isset($form->msg)) {
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'select', $this->message_param, $form->msg)
			);
		}
		$t = $this->response->html->template($this->tpldir.'/aa_plugins-configure.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($form);
		$t->add(sprintf($this->lang['label'], $this->plugin), 'label');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Configure
	 *
	 * @access public
	 * @return htmlobject_tablebulider
	 */
	//--------------------------------------------
	function configure() {

		$response = $this->get_response();
		$form     = $response->form;

		$server = new openqrm_server();
		$tmp  = $this->openqrm->get('basedir').'/var/lock/plugin_manager.'.$this->plugin.'.conf.tmp';
		$file = $this->openqrm->get('basedir').'/var/lock/plugin_manager.'.$this->plugin.'.conf';

		$command  = $this->openqrm->get('basedir').'/bin/openqrm boot-service view';
		$command .= ' --openqrm-ui-user '.$this->user->name;
		$command .= ' --openqrm-cmd-mode raw';
		$command .= ' -n '.$this->plugin;
		$command .= ' -a default';
		$command .= ' > '.$tmp;
		$command .= ' && mv '.$tmp.' '.$file;

		if($this->file->exists($file)) {
			$this->file->remove($file);
		}
		$server->send_command($command, null, true);
		while (!$this->file->exists($file))
		{
			usleep(10000); // sleep 10ms to unload the CPU
			clearstatcache();
		}
		sleep(2);
		$d = $this->__parse_conf($file,str_replace('storage', '', $this->plugin));
		// clean up
		$this->file->remove($file);
		$i = 0;
		if(!isset($d)) {
			$div = $this->response->html->div();
			$div->name = 'no data';
			$div->add($this->lang['no_data']);
			$d['param_placeholder']['object'] = $div;
		}
		$form->add($d);
		if(!$form->get_errors() && $response->submit()) {
			$request = $form->get_request(null, true);
			foreach($request as $k => $v) {
				if($v === '') { $v = 'NoValue'; }
				$command  = $this->openqrm->get('basedir').'/bin/openqrm boot-service configure';
				$command .= ' --openqrm-ui-user '.$this->user->name;
				$command .= ' --openqrm-cmd-mode background';
				$command .= ' -a default';
				$command .= ' -v '.$v;
				$command .= ' -k '.$k;
				$command .= ' -n '.$this->plugin;
				$server->send_command($command, null, true);
			}
			$form->msg = sprintf($this->lang['msg'], $this->plugin);
		}
		return $form;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'configure');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$response->form = $form;

		return $response;
	}

	//--------------------------------------------
	/**
	 * Parse an openqrm config file
	 *
	 * @access public
	 * @param string $path
	 * @param string $replace
	 * @param string $plugin
	 * @return array
	 */
	//--------------------------------------------
	function __parse_conf ( $path, $plugin = null ) {
		if(file_exists($path)) {

			$blacklist[] = 'OPENQRM_PLUGIN_VERSION';
			$blacklist[] = 'OPENQRM_PLUGIN_BUILD_REQUIREMENTS';
			$blacklist[] = 'OPENQRM_PLUGIN_DESCRIPTION';
			$blacklist[] = 'OPENQRM_PLUGIN_TYPE';
			$blacklist[] = 'OPENQRM_PLUGIN_DEPENDENCIES';
			$blacklist[] = 'OPENQRM_PLUGIN_PLUGIN_DEPENDENCIES';
			$blacklist[] = 'OPENQRM_PLUGIN_STATE_DIRS';
			$blacklist[] = 'OPENQRM_PLUGIN_STATE_FILES';

			$ini = $this->file->get_contents( $path );
			$ini = explode("\n", $ini);
			if ( count( $ini ) == 0 ) { return null; }
			$d = null;
			$i = 0;
			foreach( $ini as $line ){
				$line = trim( $line );
				// only get lines starting with OPENQRM_ or $plugin_
				if(strpos($line, 'OPENQRM_') === 0 || (isset($plugin) && stripos($line, str_replace('-', '_', $plugin)) === 0)) { 
					// Key-value pair
					list( $key, $value ) = explode( '=', $line, 2 );
					$key   = trim( $key );
					$value = trim( $value );
					$value = str_replace("\"", "", $value );
					if(!in_array($key, $blacklist)) {
						$d['param_f'.$i]['label']                       = $key;
						$d['param_f'.$i]['validate']['regex']           = '/^[^ ]+$/i';
						$d['param_f'.$i]['validate']['errormsg']        = $this->lang['error_value'];
						$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
						$d['param_f'.$i]['object']['attrib']['type']    = 'text';
						$d['param_f'.$i]['object']['attrib']['name']    = $key;
						$d['param_f'.$i]['object']['attrib']['value']   = $value;
						if(isset($ini[$i-1]) && strpos($ini[$i-1], '#') === 0) {
							$d['param_f'.$i]['object']['attrib']['title'] = trim(substr($ini[$i-1],1));
						}
					}
				}
				$i++;
			}
			return $d;
		}
	}

}
