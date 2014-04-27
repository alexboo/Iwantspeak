<?php
class Model_Table_Language extends Zend_Db_Table_Abstract
{
	protected $_name = 'languages';
	
	public function getList(array $params = array())
	{
		$select = $this -> select();
		
		$select -> order('title');
		
		return $this -> fetchAll($select);
	}
}