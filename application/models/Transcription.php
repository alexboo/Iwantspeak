<?php
class Model_Transcription
{
	protected $_default_adapter = 'google';
	
	protected $_adapter = null;
	
	protected $_adapters = array(
			'ru' => array('google', 'abbyy'),
			'en' => array('google', 'abbyy'),
			'de' => array('google', 'abbyy'),
			'kk' => array(),
		);
	
	public function __construct($adapter = null)
	{
		if ( null !== $adapter ) {
			$this -> _adapter = $this -> createAdapter($adapter);
		}
		else $this -> _adapter = $this -> createAdapter($this -> _default_adapter);
	}
	
	protected function getAdapter($source, $target)
	{		
		if ( !empty($this->_adapters[$source]) ) {
			
			return $this->createAdapter($this->_adapters[$source][0]);
		}
		
		return null;
	}
	
	public function transcription($source, $target, $text)
	{
		$dictionaryAdapter = new Model_Transcription_Dictionary();
		
		$_transcription = $dictionaryAdapter->transcription($source, $target, $text);
		
		if ( empty($_transcription) ) {
			
			$this -> _adapter = $this->getAdapter($source, $target);
			
			if ( null === $this->_adapter )
				return null;
			
			$_transcription = $this -> _adapter -> transcription($source, $target, $text);
			
			if ( !empty($_transcription) ) {
				
				$wordTable = new Model_Words();
				
				$word = $wordTable->get($text, $source);
				
				$transcriptionTable = new Model_Table_Transcription();
				
				if ( is_array($_transcription) && isset($_transcription['path']) ) {
					$transcriptionTable->set($word['id'], null, $_transcription['path']);
				}	
				else { 
					$transcriptionTable->set($word['id'], $_transcription, null);
				}
				
			}
		}
		
		return $_transcription;
	}
	
	public function setAdapter($adapter = null)
	{
		if ( null !== $adapter ) {
			$this -> _adapter = $this -> createAdapter($adapter);
		}
	}
	
	protected function createAdapter($adapter = null)
	{
		if ( null !== $adapter ) {
			
			$adapter_name = 'Model_Transcription_' . ucfirst($adapter); 
			
			if ( class_exists($adapter_name) && class_implements($adapter_name, 'Model_Transcription_Interface') ) {
				
				$adapter = new $adapter_name;
				
			}
			else $adapter = null;
		}
		
		if ( null === $adapter ) 
			throw new Zend_Controller_Action_Exception('Not found transcription adapter');
		
		return $adapter;
	}
}