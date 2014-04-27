<?php
class Model_User extends Zend_Db_Table_Abstract
{
	protected $_name = 'users';
	 
	protected $_authenticated, $id;

	public function __construct(Zend_Db_Table_Row_Abstract $row = null) {

		if (null !== $row) {
			foreach($row as $k => $v) {
				$this -> $k = $v;
			}
		}

		// Set default
		$this -> _authenticated = false;
	}
	
	public function login($email, $password) {

		$auth = Zend_Auth::getInstance();

		$adapter = new Zend_Auth_Adapter_DbTable(
			$this -> getTable() -> getAdapter(), 'users', 'email', 'password', "MD5(?)");

		$adapter -> setIdentityColumn('email');
			
		$adapter -> setIdentity($email) -> setCredential($password);

		if (($result = $auth -> authenticate($adapter))) {

			if ($result -> isValid()) {

				$auth -> getStorage() -> write($adapter -> getResultRowObject(
					null, 'password'
				));

				return true;
			} else
				return $result -> getMessages();
		}
	}
	
	/**
	 * create new user
	 * @param array $values
	 */
	public function create(array $values) {

		if (($user = $this -> findEmail($values['email']))) {
			return $user;
		}
		else {

			$info = $this -> getTable() -> info();

			foreach($values as $k => $v) {

				if (! in_array($k, $info['cols']) ) {
					unset($values[$k]);
				}

				if ($k == 'password') $values[$k] = md5($v);
			}

			$values['create'] = new Zend_Db_Expr('NOW()');
			
			if (! empty($values)) {

				if (($id = $this -> getTable() -> insert($values))) {
					return $id;
				}
			}
		}
	}
	
	public function set(array $values = array(), $user = null)
	{
		if ( null !== $user ) {
			
			$info = $this -> getTable() -> info();

			if (! empty($values)) {
			
				foreach($values as $k => $v) {
	
					if (! in_array($k, $info['cols']) ) {
						unset($values[$k]);
					}
	
					if ($k == 'password') $values[$k] = md5($v);
				}

				if (($id = $this -> getTable() -> update($values, array('id = ?' => $user)))) {
					return $id;
				}
			}
		}	
	}
	
	public function getTable(){

		if (null === $this -> _table) {
			$this -> _table = new Model_Table_User();
		}
		return $this -> _table;
	}

	public function setTable(Model_Table_User $table) {
		return $this -> _table = $table;
	}

	public function isAuthenticated() {
		return $this -> _authenticated === true;
	}

	public function findId($id)
	{
		if (($row = $this -> getTable() -> findId($id))) {

			$this -> _authenticated = true;

			foreach($row -> toArray() as $k => $v) {
				$this -> $k = $v;
			}

			return $this;
		}
	}

	public function findEmail($email)
	{
		if ($row = $this -> getTable() -> findEmail($email)) {
			foreach($row -> toArray() as $k => $v) {
				$this -> $k = $v;
			}
			return $this;
		}

		return false;
	}
	
	public function getId()
	{
		if (! $this -> isAuthenticated()) {
			return Zend_Session::getId();
		}
		return $this -> id;
	}
	
	public function __get($param)
	{
		if ( isset($this -> $param) )
			return $this -> $param;
		else 
			return null;
	}
	
	public function isTemporary()
	{
		return !empty($this->session);
	}
}