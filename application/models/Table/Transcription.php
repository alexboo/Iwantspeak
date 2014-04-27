<?php
class Model_Table_Transcription extends Zend_Db_Table_Abstract
{
	protected $_name = 'transcription';
	
	public function get($word = null)
	{
		$_transcription = $this->fetchRow(array('word = ?' => $word));
		
		if ( !empty($_transcription['id']) && empty($_transcription['text']) ) {
			
			$_transcription = $_transcription->toArray();
			
			$_transcription['path'] = APPLICATION_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'public', 'uploads', 'transcription', $_transcription['id']));
		
			$_transcription['url'] = '/' . implode('/', array('uploads', 'transcription', $_transcription['id']));
			
		}
		
		return $_transcription;
	}
	
	public function set($word = null, $text = null,  $file = null)
	{		
		if ( null !== $word && ( !empty($text) || is_readable($file)) ) {
			
			$_transcription = $this -> get($word);
			
			if ( empty($_transcription['id']) ) {
				
				$id = $this->insert(array('word' => $word, 'text' => $text, 'update' => new Zend_Db_Expr('NOW()')));
				
				if ( !empty($file) ) {
					
					copy($file, APPLICATION_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'public', 'uploads', 'speech', $id)));
					
				}
				
			}
			else $id = $_transcription['id'];
			
			return $id;
			
		}
		
		return null;
	}
}