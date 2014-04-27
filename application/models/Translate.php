<?php
class Model_Translate
{
	protected $_default_adapter = 'google';
	
	protected $_adapter = null;
	
	protected $_adapters = array(
			'ru' => array('google', 'abbyy', 'sozdikkz'),
			'en' => array('abbyy', 'google'),
			'de' => array('abbyy', 'google'),
			'kk' => array('sozdikkz'),
		);
		
	protected $_bridges = array(
		'kk' => 'ru'
	);
	
	public function __construct($adapter = null)
	{
		/*
		if ( null !== $adapter ) {
			$this -> _adapter = $this -> createAdapter($adapter);
		}
		else $this -> _adapter = $this -> createAdapter($this -> _default_adapter);
		*/
	}
	
	public function translate($source, $target, $text)
	{
		$original_source = $source;
		
		$dictionaryAdapter = new Model_Translate_Dictionary();
		
		$translates = $dictionaryAdapter->translate($source, $target, $text);
		
		/*
		if ( !empty($translates) ) {
			var_dump($translates); exit;
		}
		*/
		
		if ( empty($translates) ) {
		
			if ( (!empty($this->_bridges[$source]) && $this->_bridges[$source] != $target) ) {

                                $bridge_text = $dictionaryAdapter->translate($source, $this->_bridges[$source], $text);

                                if ( empty($bridge_text) ) {

                                    $this -> _adapter = $this->getAdapter($source, $this->_bridges[$source]);

                                    if ( null === $this->_adapter )
                                            return null;

                                    $bridge_text = $this -> _adapter -> translate($source, $this->_bridges[$source], $text);
                                }
				
				$source = $this->_bridges[$source];
				
			}
			
			if ( (!empty($this->_bridges[$target]) && $this->_bridges[$target] != $source) ) {

                                $bridge_text = $dictionaryAdapter->translate($source, $this->_bridges[$target], $text);

                                if ( empty($bridge_text) ) {

                                    $this -> _adapter = $this->getAdapter($source, $this->_bridges[$target]);

                                    if ( null === $this->_adapter )
                                            return null;

                                    $bridge_text = $this -> _adapter -> translate($source, $this->_bridges[$target], $text);
                                }
				
				$source = $this->_bridges[$target];
				
			}
			
			$this -> _adapter = $this->getAdapter($source, $target);
			
			if ( null === $this->_adapter )
				return null;
			
			if ( !empty($bridge_text) ) {
				
				$translates = array();
				
				foreach ( $bridge_text as $word ) {
					
					if ( !empty($word) ) {
						$_translates = $this -> _adapter -> translate($source, $target, trim($word));
						
						if ( !empty($_translates) )
							$translates += $_translates;
					}
					
					sleep(1);
					
				}
				
			}
			else
				$translates = $this -> _adapter -> translate($source, $target, $text);
				
			$wordsTable = new Model_Words();
	 		
			if ( !empty($translates) ) {
				
				$_translates = array();
				
				foreach ( $translates as $translate ) {
					
					$_translates[] = array('language' => $target, 'translate' => $translate);
					
				}
				
				$wordsTable->set($text, $original_source, $_translates);
			}
				
		}
		
		return $translates;
	}
	
	public function setAdapter($adapter = null)
	{
		if ( null !== $adapter ) {
			$this -> _adapter = $this -> createAdapter($adapter);
		}
	}
	
	protected function getAdapter($source, $target)
	{		
		if ( !empty($this->_adapters[$source]) && !empty($this->_adapters[$target]) ) {
			
			$sources = $this->_adapters[$source];
			
			$targets = $this->_adapters[$target];
			
			$adapters = array();
			
			foreach ( $sources as $_adapter ) {
				
				if ( in_array($_adapter, $targets) ) {
					return $this->createAdapter($_adapter);
				}
			}
		}
		
		return null;
	}
	
	protected function createAdapter($adapter = null)
	{
		if ( null !== $adapter ) {
			
			$adapter_name = 'Model_Translate_' . ucfirst($adapter); 
			
			if ( class_exists($adapter_name) && class_implements($adapter_name, 'Model_Translate_Interface') ) {
				
				$adapter = new $adapter_name;
				
			}
			else $adapter = null;
		}
		
		if ( null === $adapter ) 
			throw new Zend_Controller_Action_Exception('Not found translate adapter');
		
		return $adapter;
	}
}