<?php
class Model_Speech
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
		
		//<embed src=”http://www.yourwebsite.com/audiofolder/youraudio.mp3″ loop=”true” autoplay=”false” width=”145″ height=”60″></embed>
	}
	
	protected function getAdapter($source, $target)
	{		
		if ( !empty($this->_adapters[$source]) ) {
			
			return $this->createAdapter($this->_adapters[$source][0]);
		}
		
		return null;
	}
	
	public function speech($source, $target, $text)
	{
		$dictionaryAdapter = new Model_Speech_Dictionary();
		
		$_speech = $dictionaryAdapter->speech($source, $target, $text);
		
		if ( empty($_speech) ) {
			
			$this -> _adapter = $this->getAdapter($source, $target);
			
			if ( null === $this->_adapter )
				return null;
			
			$_speech = $this -> _adapter -> speech($source, $target, $text);
			
			if ( !empty($_speech) ) {
				
				$wordTable = new Model_Words();
				
				$word = $wordTable->get($text, $source);
				
				$speechTable = new Model_Table_Speech();
				
				if ( isset($_speech['path']) )
				
					$speechTable->set($word['id'], $_speech['path']);
				
			}
		}
		
		return $_speech;
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
			
			$adapter_name = 'Model_Speech_' . ucfirst($adapter); 
			
			if ( class_exists($adapter_name) && class_implements($adapter_name, 'Model_Speech_Interface') ) {
				
				$adapter = new $adapter_name;
				
			}
			else $adapter = null;
		}
		
		if ( null === $adapter ) 
			throw new Zend_Controller_Action_Exception('Not found speech adapter');
		
		return $adapter;
	}
}