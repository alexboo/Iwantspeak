<?

class Model_Table_User extends Zend_Db_Table_Abstract
{

	protected $_name = 'users';

	public function findId($id)
	{
		if ($row = $this -> fetchRow(array('id = ?' => $id))) {
			if (0 != count($row)) {
				return $row;
			}
		}
	}
	
	public function findEmail($username)
	{
		if ($row = $this -> fetchRow(array('email = ?' => $username))) {
			if (0 != count($row)) {
				return $row;
			}
			
			return false;
		}
	}

}

