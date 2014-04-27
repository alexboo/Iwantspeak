<?php
class UserController extends Dict_Controller_Action
{
	public function indexAction()
	{
		
	}
	
	public function loginAction()
	{

		if (Zend_Auth::getInstance() -> hasIdentity() && !empty($this->user->name)) {
			$this -> getResponse() -> setRedirect('/words');
		}

		$form = new Form_User_Login();

		if ($this -> getRequest() -> isPost()) {

			$result = false;

			if ($form -> isValid($_POST)) {

				if (true === ($result = $this -> user -> login($form -> getValue('email'),
					$form -> getValue('password')))) {
					$result = true;
				} else {
					$form -> addErrors($result);
					$result = false;
				}
				
				if ( $result ) {
					$uri = $_SERVER['REQUEST_URI'];

					if ( $uri != '/' )
						$this -> refr();
					else 
						$this -> getResponse() -> setRedirect('/words');
				}
			}
		}

		$this -> view -> form = $form;
	}
    
	public function registrationAction() {

		if (Zend_Auth::getInstance() -> hasIdentity() && !empty($this->user->name)) {
			return $this -> back();
		}
		
		$form = new Form_User_Registration();
		
		if ($this -> getRequest() -> isPost()) {

			if ($form -> isValid($_POST)) {

				$user = new Model_User();
				
				$values = $form -> getValues();
				
				if ( Zend_Auth::getInstance() -> hasIdentity() ) {
					
					$values['session'] = new Zend_Db_Expr('NULL');
					
					$user->set($values, $this->user->getId());

					$this -> getResponse() -> setRedirect('/words');
				}
				else {
					// Create values
	
					// Zend_Debug::dump($values); exit;
					
					if (($id = $user -> create($values))) {
						if ( $this -> user -> login($form -> getValue('email'),
						$form -> getValue('password')) )
						{
							$this -> getResponse() -> setRedirect('/words');
						}
					}
				}
			}
		}
		
		$this -> view -> form = $form;
	}
	
	public function logoutAction()
	{
		if (Zend_Auth::getInstance() -> hasIdentity()) {
			Zend_Auth::getInstance() -> clearIdentity();
		}
		
		$this -> getResponse() -> setRedirect('/');
	}
	
	public function statisticAction()
	{
		if (Zend_Auth::getInstance() -> hasIdentity()) {
			
			$wordsTable = new Model_Words();
		
			$this -> view -> statistic = $wordsTable->getUserStatistic($this->user->id);
		
		}
		
	}
	
	public function tempAction()
	{
		$user = new Model_User();
		
		$values = array('email' => Zend_Session::getId(), 'session' => Zend_Session::getId(), 'create' => new Zend_Db_Expr('NOW()'));
		
		if (($id = $user -> create($values))) {
			
			$auth = Zend_Auth::getInstance();
	
			$adapter = new Zend_Auth_Adapter_DbTable(
				$user -> getTable() -> getAdapter(), 'users', 'session', 'password');
	
			$adapter -> setIdentityColumn('session');
				
			$adapter -> setIdentity(Zend_Session::getId()) -> setCredential('');
	
			if (($result = $auth -> authenticate($adapter))) {
	
				if ($result -> isValid()) {
	
					$auth -> getStorage() -> write($adapter -> getResultRowObject(
						null, 'password'
					));
	
					$this -> getResponse() -> setRedirect('/words');
					
				}
			}
		}
	}
}