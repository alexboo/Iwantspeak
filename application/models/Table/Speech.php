<?php
class Model_Table_Speech extends Zend_Db_Table_Abstract
{
	protected $_name = 'speech';
	
	public function get($word = null)
	{
		$_speech = $this->fetchRow(array('word = ?' => $word));
		
		if ( !empty($_speech['id'])) {
			
			$_speech = $_speech->toArray();
			
			$_speech['path'] = APPLICATION_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'public', 'uploads', 'speech', $_speech['id']));
		
			$_speech['url'] = '/' . implode('/', array('uploads', 'speech', $_speech['id']));
			
		}
		
		return $_speech;
	}
	
	public function set($word = null, $file = null)
	{
		if ( null !== $word && is_readable($file) ) {
			
			$_speech = $this -> get($word);
			
			if ( empty($_speech['id']) ) {
				
				$id = $this->insert(array('word' => $word, 'update' => new Zend_Db_Expr('NOW()')));
				
				copy($file, APPLICATION_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'public', 'uploads', 'speech', $id)));
				
			}
			else $id = $_speech['id'];
			
			return $id;
			
		}
		
		return null;
	}
}