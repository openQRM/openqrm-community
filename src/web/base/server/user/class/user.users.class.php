<?php
/**
 * User Users
 *
    openQRM Enterprise developed by OPENQRM AUSTRALIA PTY LTD.

    All source code and content (c) Copyright 2021, OPENQRM AUSTRALIA PTY LTD unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with OPENQRM AUSTRALIA PTY LTD.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2021, OPENQRM AUSTRALIA PTY LTD <info@openqrm-enterprise.com>
 */

class user_users
{
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'admin_id';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'admin_action';
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* name of action buttons
* @access public
* @var string
*/
var $lang = array();
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $user
	 * @param object $file
	 * @param object $response
	 */
	//--------------------------------------------
	function __construct( $openqrm, $response ) {
		$this->user     = $openqrm->get('user');
		$this->response = $response;
                $this->openqrm = $openqrm;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action( $action = null ) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = 'select';
		}

		switch( $this->action ) {
			// SELECT
			case '':
			case 'select':
				$response      = $this->select();
				$data['label'] = $this->lang['label'];
				$data['table'] = $response->table;
				$href          = $response->html->a();
				$href->href    = $response->html->thisfile.$response->get_string($this->actions_name, 'insert', '?', true );
				$href->label   = $this->lang['action_new'];
				$href->css     = 'add';
				$href->handler = 'onclick="wait();"';
				$data['new']   = $href;
				$data['canceled'] = $this->lang['canceled'];
				$data['wait'] = $this->lang['please_wait'];
				$data['prefix_tab'] = $this->prefix_tab;
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user_select.tpl.php');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// INSERT
			case 'insert':
				$response = $this->insert();
				if(isset($response->error)) {
					if(!isset($_REQUEST['nomsg'])) {
						$_REQUEST[$this->message_param] = $response->error;
					}
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['canceled'] = $this->lang['canceled'];
				$data['wait'] = $this->lang['please_wait'];
				$data['prefix_tab'] = $this->prefix_tab;
				$data['label'] = $this->lang['action_new'];
				$data['name'] = '';
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user_insert.tpl.php');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// edit
			case 'edit':
				$response = $this->edit();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['canceled'] = $this->lang['canceled'];
				$data['wait'] = $this->lang['please_wait'];
				$data['prefix_tab'] = $this->prefix_tab;
				$data['label'] = sprintf($this->lang['label_edit'], $response->html->request()->get('user[user_name]'));
				$data['name'] = '';
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user_insert.tpl.php');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// DELETE
			case $this->lang['action_delete']:
			case 'delete':
				$response = $this->delete();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['canceled'] = $this->lang['canceled'];
				$data['wait'] = $this->lang['please_wait'];
				$data['prefix_tab'] = $this->prefix_tab;
				$vars = array('thisfile' => $response->html->thisfile);
				$t = $response->html->template($this->tpldir.'/user_delete.tpl.php');
				$t->add($vars);
				$t->add($this->lang['label_delete'], 'label');
				$t->add($response->form);
				$t->add($data);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->get_response('select');

		$h['name']['title']    = $this->lang['name'];
		$h['name']['sortable'] = true;
		$h['role']['title']    = $this->lang['role'];
		$h['role']['sortable'] = true;
		$h['func']['title']    = '&#160;';
		$h['func']['sortable'] = false;

		$result = $this->user->get_users();
		$b = array();
		foreach($result as $k => $v) {
			if($k !== 0) {
				$tmp = array();
				foreach($v as $value) {
					if($value['label'] === 'user_name') {
						$tmp['name'] = $value['value'];
						$params  = '?user[user_name]='.$value['value'];
						$params .= $response->get_string($this->actions_name, 'edit', '&', true );
						$a = $response->html->a();
						$a->href = $response->html->thisfile.$params;
						$a->label = $this->lang['action_edit'];
						$a->title = $this->lang['action_edit'];
						$a->css   = 'edit';
						$a->handler = 'onclick="wait();"';
						$tmp['func'] =  $a->get_string();
					}
					if($value['label'] === 'role_name') {
						$tmp['role'] = $value['value'];
					}
				}
				$b[] = $tmp;
			}
		}

		$table                      = $response->html->tablebuilder( 'ut', $response->params );
		$table->sort                = 'name';
		$table->css                 = 'htmlobject_table';
		$table->border              = 0;
		$table->id                  = 'Tabelle';
		$table->head                = $h;
		$table->body                = $b;
		$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form           = true;
		$table->sort_link           = false;
		$table->autosort            = true;
		$table->identifier          = 'name';
		$table->identifier_name     = $this->identifier_name;
		$table->identifier_disabled = array( 'openqrm' );
		$table->actions             = array($this->lang['action_delete']);
		$table->actions_name        = $this->actions_name;
		$table->max                 = count( $b );

		$response->table = $table;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function insert() {
		$response = $this->get_response('insert');
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$user = $form->get_request('user');
			$ini  = $user;
			unset($ini['pass2']);	
			if(
				(isset($user['user_password']) && !isset($user['pass2'])) ||
				(isset($user['user_password']) && $user['user_password'] !== $user['pass2'])
			) {
				$form->set_error('user[pass2]', sprintf($this->lang['error_no_match'], $this->lang['password_repeat'], $this->lang['password']));
			}
			if(!$form->get_errors()) {
				$check = new user ($user['user_name']);
				// Check user exists
				if(!$check->check_user_exists()) {
					global $USER_INFO_TABLE;
					$ini['user_id'] = (int)str_replace(".", "", str_pad(microtime(true), 15, "0"));
					$check->add($ini);
					$pass = new user($ini['user_name']);
					$pass->change_htpasswd('insert');
				} else {
					$form->set_error('user[user_name]', sprintf($this->lang['error_user_exists'], $user['user_name']));
					$error = sprintf($this->lang['error_user_exists'], $user['user_name']);
				}
				if(!isset($error) || $error === '') {
					$response->msg = sprintf($this->lang['msg_saved'], $user['user_name']);
				} else {
					$response->error = $error;
				}
 			} else {
				$response->error = join('<br>', $form->get_errors());
			}
		}		
		if($form->get_errors()) {
			$response->error = join('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * edit
	 *
	 * @access public
	 * @param enum $mode [edit|account]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function edit( $mode = 'edit' ) {
		$response = $this->get_response( $mode );
		$form     = $response->form;
		if($mode === 'account') {
			$username = $this->user->name;
		}
		else if ($mode === 'edit') {
			$username = $response->html->request()->get('user[user_name]');
		}
		if(!$form->get_errors() && $this->response->submit()) {
			$user = $form->get_request('user');
			$ini  = $user;
			unset($ini['pass2']);
			if(
				$mode === 'account' ||
				($mode === 'edit' && $username === 'openqrm')				
			) {
				unset($ini['user_role']);
				unset($ini['user_state']);
			}
			if(
				(isset($user['user_password']) && !isset($user['pass2'])) ||
				(isset($user['user_password']) && $user['user_password'] !== $user['pass2'])
			) {
				$form->set_error('user[pass2]', sprintf($this->lang['error_no_match'], $this->lang['password_repeat'], $this->lang['password']));
			}
			if(!$form->get_errors()) {
				$error = '';
				$update = new user($username);
				$update->get_instance_by_name($username);
				foreach($ini as $k => $v) {
					$arg = str_replace('user_', '', $k);
					$update->$arg = $v;
				}
				$update->query_update();
				if($error === '') {
					$response->msg = sprintf($this->lang['msg_saved'], $username);
				} else {
					$response->error = $error;
				}
			} else {
				$response->error = join('<br>', $form->get_errors());
			}
		}
		if($form->get_errors()) {
			$response->error = join('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Account
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function account() {
		$response = $this->edit('account');
		if(isset($response->error)) {
			$_REQUEST[$this->message_param] = $response->error;
		}
		if(isset($response->msg)) {
			$response->redirect(
				$response->get_url($this->actions_name, 'account', $this->message_param, $response->msg)
			);
		}
		$data['canceled'] = $this->lang['canceled'];
		$data['wait'] = $this->lang['please_wait'];
		$data['prefix_tab'] = $this->prefix_tab;
		$data['label'] = $this->lang['label_account'];
		$data['cancel'] = '';
		$data['role']  = '';
		$data['name']   = $response->form->get_elements('user')->value;
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
			));
		$t = $response->html->template($this->tpldir.'/user_insert.tpl.php');
		$t->add($response->form);
		$t->add($vars);
		$t->group_elements(array('param_' => 'form'));
		return $t;

	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function delete() {
		$response = $this->get_response('delete');
		$folders  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $folders !== '' ) {
			$i = 0;
			foreach($folders as $folder) {
				$d['param_f'.$i]['label']                       = $folder;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'[]';
				$d['param_f'.$i]['object']['attrib']['value']   = $folder;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors  = array();
				$message = array();
				foreach($folders as $key => $user) {
					// protect user openqrm
					if($user !== 'openqrm') {
						$del = new user($user);
						$del->set_user();
						$error = $del->query_delete();
						if(is_array($error) && count($error) > 1 ) {
							$errors[] = $error;
						} else {
							$form->remove($this->identifier_name.'['.$key.']');
							$message[] = sprintf($this->lang['msg_deleted'], $user);
						}
					}
					else if($user === 'openqrm') {
						$form->remove($this->identifier_name.'['.$key.']');
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
			if($form->get_errors()) {
				$response->error = join('<br>', $form->get_errors());
			}
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
	 * @param enum $mode [select|insert|edit|account|delete]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response($mode) {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, $mode);

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		if( $mode !== 'select' && $mode !== 'delete') {	
			if( $mode === 'edit' ) {
				$user = $this->response->html->request()->get( 'user[user_name]' );
				$ini  = new user($user);
				$d['user']['static']                    = true;
				$d['user']['object']['type']            = 'htmlobject_input';
				$d['user']['object']['attrib']['name']  = 'user[user_name]';
				$d['user']['object']['attrib']['type']  = 'hidden';
				$d['user']['object']['attrib']['value'] = $ini->name;
				$pass_required = false;
			}
			if( $mode === 'account' ) {
				// get logged in user
				$ini = $this->user;
				$d['user']['static']                    = true;
				$d['user']['object']['type']            = 'htmlobject_input';
				$d['user']['object']['attrib']['name']  = 'user[user_name]';
				$d['user']['object']['attrib']['type']  = 'hidden';
				$d['user']['object']['attrib']['value'] = $ini->name;
				$pass_required = false;
			}
			if( $mode === 'insert' ) {
				$ini  = array();
				$ini['lang']  = 'en';
				$d['user']['label']                     = $this->lang['name'];
				$d['user']['required']                  = true;
				$d['user']['validate']['regex']         = '/^[a-z0-9_-]+$/i';
				$d['user']['validate']['errormsg']      = $this->lang['error_name'];
				$d['user']['object']['type']            = 'htmlobject_input';
				$d['user']['object']['attrib']['name']  = 'user[user_name]';
				$d['user']['object']['attrib']['type']  = 'text';
				$d['user']['object']['attrib']['value'] = '';
				$pass_required = true;
			}

			$lang = array();
			$files = $this->openqrm->file()->get_files($this->openqrm->get('basedir').'/web/base/lang/', '', '*.htmlobjects.ini');
			foreach($files as $v) {
				$tmp = explode('.', $v['name']);
				$lang[] = array($tmp[0]);
			}

			$d['lang']['label']                        = $this->lang['lang'];
			$d['lang']['object']['type']               = 'htmlobject_select';
			$d['lang']['object']['attrib']['name']     = 'user[user_lang]';
			$d['lang']['object']['attrib']['index']    = array(0,0);
			$d['lang']['object']['attrib']['options']  = $lang;
			if(isset($ini->lang)) {
				$d['lang']['object']['attrib']['selected'] = array( $ini->lang );
			}

			$d['forename']['label']                     = $this->lang['forename'];
			$d['forename']['object']['type']            = 'htmlobject_input';
			$d['forename']['object']['attrib']['type']  = 'text';
			$d['forename']['object']['attrib']['name']  = 'user[user_first_name]';
			if(isset($ini->first_name)) {
				$d['forename']['object']['attrib']['value'] = $ini->first_name;
			}

			$d['lastname']['label']                     = $this->lang['lastname'];
			$d['lastname']['object']['type']            = 'htmlobject_input';
			$d['lastname']['object']['attrib']['type']  = 'text';
			$d['lastname']['object']['attrib']['name']  = 'user[user_last_name]';
			if(isset($ini->last_name)) {
				$d['lastname']['object']['attrib']['value'] = $ini->last_name;
			}

			$gender = array();
			$gender[] = array( '', '' );
			$gender[] = array( 'm', $this->lang['male'] );
			$gender[] = array( 'f', $this->lang['female'] );

			$d['gender']['label']                        = $this->lang['gender'];
			$d['gender']['object']['type']               = 'htmlobject_select';
			$d['gender']['object']['attrib']['name']     = 'user[user_gender]';
			$d['gender']['object']['attrib']['index']    = array(0,1);
			$d['gender']['object']['attrib']['options']  = $gender;
			if(isset($ini->gender)) {
				$d['gender']['object']['attrib']['selected'] = array( $ini->gender );
			}

			$d['department']['label']                     = $this->lang['department'];
			$d['department']['object']['type']            = 'htmlobject_input';
			$d['department']['object']['attrib']['type']  = 'text';
			$d['department']['object']['attrib']['name']  = 'user[user_department]';
			if(isset($ini->department)) {
				$d['department']['object']['attrib']['value'] = $ini->department;
			}

			$d['office']['label']                     = $this->lang['office'];
			$d['office']['object']['type']            = 'htmlobject_input';
			$d['office']['object']['attrib']['type']  = 'text';
			$d['office']['object']['attrib']['name']  = 'user[user_office]';
			if(isset($ini->office)) {
				$d['office']['object']['attrib']['value'] = $ini->office;
			}

			$d['state'] = [];
			if(
				$mode === 'insert' ||
				($mode === 'edit' && $ini->name !== 'openqrm')
			) {
				$state[] = array('activated', $this->lang['activated']);
				$state[] = array('deactivated', $this->lang['deactivated']);
				$d['state']['label']                     = $this->lang['state'];
				$d['state']['object']['type']            = 'htmlobject_select';
				$d['state']['object']['attrib']['index']  = array(0,1);
				$d['state']['object']['attrib']['options'] = $state;
				$d['state']['object']['attrib']['name']  = 'user[user_state]';
				if(isset($ini->state)) {
					$d['state']['object']['attrib']['selected'] = array($ini->state);
				}
			}

			$d['description']['label']                     = $this->lang['description'];
			$d['description']['object']['type']            = 'htmlobject_textarea';
			$d['description']['object']['attrib']['name']  = 'user[user_description]';
			if(isset($ini->description)) {
				$d['description']['object']['attrib']['value'] = $ini->description;
			}

			$d['capabilities']['label']                     = $this->lang['capabilities'];
			$d['capabilities']['object']['type']            = 'htmlobject_textarea';
			$d['capabilities']['object']['attrib']['name']  = 'user[user_capabilities]';
			if(isset($ini->capabilities)) {
				$d['capabilities']['object']['attrib']['value'] = $ini->capabilities;
			}

			$d['role'] = [];
			if(
				$mode === 'insert' ||
				($mode === 'edit' && $ini->name !== 'openqrm')
			) {
				$roles = $this->user->get_role_list();
				if(is_array($roles)) {
					$d['role']['label']                        = $this->lang['role'];
					$d['role']['required']                     = true;
					$d['role']['object']['type']               = 'htmlobject_select';
					$d['role']['object']['attrib']['name']     = 'user[user_role]';
					$d['role']['object']['attrib']['css']      = 'users2groups';
					$d['role']['object']['attrib']['index']    = array('value', 'label');
					$d['role']['object']['attrib']['options']  = $roles;
					$d['role']['object']['attrib']['id']       = 'role_select';
					if($mode === 'edit') {
						if(isset($ini->role)) {
							$d['role']['object']['attrib']['selected'] = array($ini->role);
						}
					}
				}
			}                        
                        // if ldap is enabled do not allow access the the openQRM user administration
			if (file_exists($this->openqrm->get('basedir').'/plugins/ldap/web/.running')) {
				$d['pass1'] = '';
				$d['pass2'] = '';
			} else {
				$d['pass1']['label']                     = $this->lang['password'];
				$d['pass1']['required']                  = $pass_required;
				$d['pass1']['object']['type']            = 'htmlobject_input';
				$d['pass1']['object']['attrib']['name']  = 'user[user_password]';
				$d['pass1']['object']['attrib']['type']  = 'password';
				$d['pass1']['object']['attrib']['value'] = '';

				$d['pass2']['label']                     = $this->lang['password_repeat'];
				$d['pass2']['required']                  = $pass_required;
				$d['pass2']['object']['type']            = 'htmlobject_input';
				$d['pass2']['object']['attrib']['name']  = 'user[pass2]';
				$d['pass2']['object']['attrib']['type']  = 'password';
				$d['pass2']['object']['attrib']['value'] = '';
			}
         
			$form->add($d);
		}
		$response->form = $form;
		$response->form->display_errors = false;
		return $response;
	}

}
