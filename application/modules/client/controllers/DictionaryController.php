<?php
class DictionaryController extends Dict_Controller_Action
{
	public function changeAction()
	{
		$session = new Zend_Session_Namespace('dictionary');
		
		if ( $id = $this->_getParam('id', false) ) {
			
			$session -> id = $id;
			
			$this -> _redirect($this -> view -> url(array('controller' => 'words', 'action' => 'index'), null, true));
		}
		else {
			
			$dictionaryTable = new Model_Dictionary();
			
			$dictionaries = $dictionaryTable -> getList(array('user' => $this -> user -> id));
			
			if ( $dictionaries -> valid() ) {
					
				if ( count($dictionaries) == 1 ) {
					
					$session -> id = $dictionaries[0]['id'];
					
					$this -> _redirect($this -> view -> url(array('controller' => 'words', 'action' => 'index', 'id' => $dictionaries[0]['id']), null, true));
				}
				
				$this -> view -> dictionaries = $dictionaries;
			}
		}
	}
	
	public function createAction()
	{
		$dictionaryTable = new Model_Dictionary();
		
		$languageTable = new Model_Table_Language();
		
		$form = new Form_Dictionary_Edit(array('languages' => $languageTable -> getList()));
		
		if ( $this -> getRequest() -> isPost() ) {
			
			if ( $form -> isValid($_POST) ) {
				
				$values = $form -> getValues();
				
				$values['user'] = $this -> user -> id;
				
				$dictionaryTable -> set($values);
				
				$this -> _redirect($this -> view -> url(array('controller' => 'dictionary', 'action' => 'change'), null, true));
				
			}
		}
		
		$this -> view -> form = $form;
	}
}