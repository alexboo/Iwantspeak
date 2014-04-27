<?php
class Model_Dictionary extends Zend_Db_Table_Abstract
{
	protected $_name = 'dictionaries';
	
	public function get($id, array $params = array())
	{
		$select = $this -> select() -> setIntegrityCheck(false);
		
		$select -> from(array('d' => 'dictionaries'), array('*'));
		
		$select -> joinInner(array('from' => 'languages'), 'd.from = from.key', array('ftitle' => 'from.title', 'fkey' => 'from.key'));
		
		$select -> joinInner(array('to' => 'languages'), 'd.to = to.key', array('ttitle' => 'to.title', 'tkey' => 'to.key'));
		
		if ( isset($params['user']) && null !== $params['user'] ) {
			$select -> where('user = ?', $params['user']);
		}
		
		$select -> where('`d`.`id` = ?', $id);
		
		return $this -> fetchRow($select);
	}
	
	public function getList(array $params = array())
	{
		$select = $this -> select() -> setIntegrityCheck(false);
		
		$select -> from(array('d' => 'dictionaries'), array('*'));
		
		$select -> joinInner(array('from' => 'languages'), 'd.from = from.key', array('ftitle' => 'from.title'));
		
		$select -> joinInner(array('to' => 'languages'), 'd.to = to.key', array('ttitle' => 'to.title'));
		
		if ( isset($params['user']) && null !== $params['user'] ) {
			$select -> where('user = ?', $params['user']);
		}
		
		return $this -> fetchAll($select);
	}
	
	public function set(array $values = array(), $id = null)
	{
		if ( $id === null && isset($values['user']) && isset($values['from']) && isset($values['to']) ) {
			
			$dictionary = $this -> fetchRow(array('`user` = ?' => $values['user'], '`from` = ?' => $values['from'], '`to` = ?' => $values['to']));
			
			if ( $dictionary ) {
				return $dictionary -> id;
			}
			
		}
		
		$info = $this -> info();
		
		foreach($values as $k => $v) {

			if (! in_array($k, $info['cols']) ) {
				unset($values[$k]);
			}
		}
		
		if ( null !== $id ) {
			$this -> update($values, array('id = ?' => $id));
			
			return $id;
		}
		else {
			return $this -> insert($values);
		}
	}
}