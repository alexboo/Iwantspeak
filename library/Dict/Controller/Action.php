<?
class Dict_Controller_Action extends Zend_Controller_Action {

	protected $user;
	
	protected $dictionary;
	
	private $_dictionary_controllers = array('words', 'training');
	
	private $_login_controllers = array('words', 'training', 'dictionary');

	public function init() {

		$this -> user = new Model_User();

		Zend_Session::setOptions(array('cookie_lifetime' => 86400*90, 'gc_maxlifetime'  => 86400*90));
		
		Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('Zend_Auth'));
		
		if (($user = Zend_Auth::getInstance() -> getStorage() -> read())) {
			
			// Get user infos
			$this -> user -> findId($user -> id);
			
			Zend_Registry::set('user', $this -> user);
			
			if ( in_array($this->getRequest()->getControllerName(), $this->_dictionary_controllers) ) {
				
				$session = new Zend_Session_Namespace('dictionary');
				
				if ( !isset($session->id) ) {
					
					$this -> _redirect($this -> view -> url(array('controller' => 'dictionary', 'action' => 'change'), null, true));
					
				}
				else {
					
					$dictionaryTable = new Model_Dictionary();
				
					$this -> dictionary = $dictionaryTable->get($session->id);
					
					if ( $this -> dictionary['user'] != $this->user->id ) {
						
						$this -> dictionary = null;
						
						unset($session->id);
						
						$this -> _redirect($this -> view -> url(array('controller' => 'dictionary', 'action' => 'change'), null, true));
						
					}
				}
				
			}
		}
		
		$request = $this -> getRequest();

		/**
		 * Set user to VIEW if not request as AJAX
		 */
		if (! $request -> isXmlHttpRequest()) {
			$this -> view -> user = $this -> user;
		}

		if ($request -> getParam('language')) {
			$this -> view -> language = $request -> getParam('language');
		}
	}
	
	public function preDispatch() {
		
		if (  !$this -> user -> isAuthenticated() && in_array($this->getRequest()->getControllerName(), $this->_login_controllers) ) 
		{
        	return $this -> _forward('login', 'user', 'client');
		}		
	}
	
	public function postDispatch()
	{
		$flashMessenger = $this->_helper->getHelper('FlashMessenger');
		
		$messages = $flashMessenger->getMessages();
		
		if ( !empty($messages) ) {
			
			$this -> view -> flash_messages = $messages;
		}
	}

	/**
	 * Refresh current page
	 * @param string $default
	 */
	public function refr($default = '/') {

		$uri = $_SERVER['REQUEST_URI'];

		if ($uri && false === stripos($uri, 'user/logout')) {
			return $this -> getResponse() -> setRedirect(
				$uri);
		}

		if ($default == '/' && $this -> getRequest() -> getModuleName() == 'admin')
			$default = '/admin';

		$this -> getResponse() -> setRedirect($default);
	}

	/**
	 * Back to page
	 * @param string $default
	 */
	public function back($default = '/') {

		$uri = $_SERVER['HTTP_REFERER'];

		if ($uri && false === stripos($uri, 'user/logout')) {
			return $this -> getResponse() -> setRedirect(
				$uri);
		}

		if ($default == '/' && $this -> getRequest() -> getModuleName() == 'admin')
			$default = '/admin';

		$this -> getResponse() -> setRedirect($default);
	}

}