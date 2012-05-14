<?php
/*
	this script deals with all user data and authentication of the user
*/

class user {
	public $data = [ //initalized with defaults (for guest user)
		'_id' => 'guest',
		'info'=> [
			'fName'=> '',
			'lName'=> '',
			'team'=> 0
		],
		'permissions' => [
			'input'
		],
		'prefs'=> [
			'fade'=> true,
			'verbose'=> true
		],
		'account'=> [
			'pword'=> '',
			'email'=> ''
		],
		'opt'=> [ //optional info
			'zip'=> '',
			'browser'=> '',
			'gender'=> ''
		]
	];

	public function processUser($inputUser){//puts user object in class
		global $vars;//temporary until i switch to a request class

		//if nothing is passed to func then assume guest, if incorrect then send error
		
		if(empty($inputUser)) return; 

		if (empty($inputUser['user']['_id']) == true) {
			error('scoutid was not receved','','logout();');//cannot run logout() w/out scoutid
		}
		if (empty($inputUser['user']['token']) == true) {
			logout('token was not receved');
		}

		//check user & assign user object
		$this->$data = $db->user->findOne(
			[
				'_id' => $inputUser['user']['_id']
			]
		);

		if ($this->$data['token'] !== $inputUser['user']['token']) {//validate token
			logout('token is incorrect, you have been logged out for security reasons');
		}
		if ($this->$data['stats']['ip'] !== $vars['ip']) {//validate ip address
			logout('ip is incorrect, you have been logged out for security reasons');
		}
		if (count($this->$data['permissions']) == 0) {//if user has no permissions
			error('your account is banned', 'banned account');
		}
		
	}

	public function logout($error_message = ''){//must be function to let it be called from other areas in script
		// delete token & ip for active user
		$this->$data['stats']['ip'] = '';
		$this->$data['stats']['token'] = '';
		$this->updateUser();
		
		//TODO: check for logout error?
		
		if($error_message == ''){//if no error message is specified then assume no error
			send_reg(['message' => 'logout successful']);
		} else {
			error($error_message,'','logout();');
		}
	}
	protected function updateUser(){//updates the mongo representation of the user
		global $db;

		if($this->$data['_id'] != 'guest'){//there is no real guest object in mongo
			$db->user->update(//just replace the entire user doc
				[
					'_id:' => $this->$data['_id']
				],
				[
					$this->$data
				]
			);
		}
	}
	public function validateUserInfo(){//for signup and user info changes
		$array = str_split($this->data['info']['fName']);
		foreach($array as $char) {
			if(!preg_match('/[a-z]/i', $char)) error('first name is invalid. this must contain only english letters');
		}

		$array = str_split($this->data['info']['lName']);
		foreach($array as $char) {
			if(!preg_match('/[a-z]/i', $char)) error('last name is invalid. this must contain only english letters');
		}

		if(!is_int($this->data['info']['team']) || $this->data['info']['team'] > 9999 || $this->data['info']['team'] < 1){
			error('team number is invalid');
		}
	}
	protected function error($errorText){//not finished
		send_error($errorText);
	}
}


?>