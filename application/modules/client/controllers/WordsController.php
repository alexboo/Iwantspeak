<?php
class WordsController extends Dict_Controller_Action
{
	public function indexAction()
	{		
		$dictionaryTable = new Model_Dictionary();
		
		$dictionary = $this->dictionary;
		
		if ( isset($dictionary['id']) ) {
		
			$this -> view -> dictionary = $dictionary;
		
			$wordsTable = new Model_Words();
			
			$statistics = $wordsTable->getUserStatistic($this->user->id);
			
			if ( $statistics['all'] == $statistics['full']  ) {
				
				$flash_messages = $this->view->flash_mesages;
				
				if ( $statistics['all'] > 0)
					$flash_messages[] = 'Вы выучили все слова в вашем словаре. Вы можете добавить 100 новых слов автоматически в свой словарь перейдя по <a href="' . $this->view->url(array('controller' => 'words', 'action' => 'get'), null, true) . '">ссылке</a>';
				else 
					$flash_messages[] = 'В вашем словаре нет слов. Вы можете добавить 100 новых слов автоматически в свой словарь перейдя по <a href="' . $this->view->url(array('controller' => 'words', 'action' => 'get'), null, true) . '">ссылке</a>';
				
				$this->view->flash_messages = $flash_messages;
				
			}
			
			$inpage = 99;
			
			$wordsTable -> setLimitPage(((max(1, (integer) $this -> _getParam('page')) - 1) * $inpage), $inpage);
			
			if ( $order = $this->_getParam('order', false) ) {
				
				$order = explode('.', $order);
				
				if ( isset($order[0]) && in_array($order[0], array('date', 'name', 'balls')) ) {
					$wordsTable->setOrderField($order[0]);
				}
					
				if ( isset($order[1]) ) {
					if ( $order[1] == 'down' )
						$wordsTable->setOrderTypes('DESC');
					else 
						$wordsTable->setOrderTypes('ASC');
				}
				
			}
			
			$order = $wordsTable -> getOrderField();
			$types = $wordsTable -> getOrderTypes();
			
			$this->view->order = $order . '.' . ($types == 'DESC' ? 'down' : 'up');
			
			$where = array('dictionary' => $dictionary['id']);
			
			if ( $level = $this->_getParam('level', false) ) {
				$where['level'] = $level;
				$this -> view -> level = $level;
			}
			
			if ( $word = $this->_getParam('word', false) ) {
				$where['word'] = $word;
				$this -> view -> word = $word;
			}
			
			$this->view->words = $wordsTable->getWords($where)->toArray();
	
			$this->view->words_count = $wordsTable->getFoundCount();
	
			$this -> view -> paginator = new Zend_Paginator(
				new Zend_Paginator_Adapter_Null($wordsTable->getFoundCount())
			);
	
			$this -> view -> paginator -> setCurrentPageNumber($this -> _getParam('page'))
				-> setItemCountPerPage($inpage);
			
		}
		else throw new Zend_Controller_Action_Exception('This page dont exist', 404);
	}
	
	public function addAction()
	{
		$dictionaryTable = new Model_Dictionary();
		
		$dictionary = $this->dictionary;
		
		if ( isset($dictionary['id']) ) {
		
			$form = new Form_Word_Edit();
		
			$wordsTable = new Model_Words();
			
			if ( $this->getRequest()->isPost() && $form->isValid($_POST) ) {
				
				$word = $wordsTable->get($_POST['word'], $dictionary['from']);
				
				if ( empty($word['id']) ) {
					
					$wordsTable->set($_POST['word'], $dictionary['from']);
					
					$word = $wordsTable->get($_POST['word'], $dictionary['from']);
				}
				
				$_translates = explode("\n", trim($_POST['translates']));
				
				foreach ( $_translates as $translate ) {
					
					$translate = trim($translate);
					
					if ( !empty($translate) ) {
						
						$_translate['id'] = $wordsTable->setTranslate($translate, $word['id'], $dictionary['to']);
						
						$translates[] = $_translate['id'];
						
					}
				}
				
				$wordsTable->addWordForUser($dictionary['id'], $word['id'], $this->user->id, $translates);
				
				if ( isset($_POST['save']) )
					 $this -> _redirect($this -> view -> url(array('controller' => 'words', 'action' => 'add'), null, true));
				else 
					$this -> _redirect($this -> view -> url(array('controller' => 'words', 'action' => 'index'), null, true));
				
			}
			else {
				$this->view->errors = $form->getErrors();
			}
			
			if ( $word = $this->_getParam('word', false) ) {
				
				if ( is_numeric($word) )
					$this -> view -> word = $wordsTable->get($word);
				else {
					$this -> view -> word = $wordsTable->get($word, $dictionary['from']);
					$word = $this -> view -> word['id'];
				}
				
				$this -> view -> translates = $wordsTable->getTranslates(array('dictionary' => $dictionary['id'], 'word' => $word));
			}
			
			$this -> view -> dictionary = $dictionary;
			
			$this -> view -> form = $form;
			
		}
		else throw new Zend_Controller_Action_Exception('This page dont exist', 404);
	}
	
	public function wordAction()
	{
		$word = $this->_getParam('word');
		
		$dictionaryTable = new Model_Dictionary();
		
		$dictionary = $this->dictionary['id'];
		
		$this -> view -> dictionary = $this->dictionary;
		
		if ( null !== $word && !empty($this -> view -> dictionary['id']) ) {
			
			$wordsTable = new Model_Words();
			
			$speechTable = new Model_Table_Speech();
			
			$transcriptionTable = new Model_Table_Transcription();			
			
			$this -> view -> word = $wordsTable->get($word, null, array('dictionary' => $dictionary));
			
			$this -> view -> translates = $wordsTable->getTranslates(array('dictionary' => $dictionary, 'word' => $word));
			
			$this -> view -> speech = $speechTable->get($word);
			
			$this -> view -> transcription = $transcriptionTable->get($word);
			
			if ( $this->_request->isXmlHttpRequest() )
				return $this -> _helper -> json($this->view->render('words/word.phtml'));
		}
		else throw new Zend_Controller_Action_Exception('This page dont exist', 404);
	}
	
	public function deleteAction()
	{
		$type = $this->_getParam('type', false);
		
		$id = $this->_getParam('id', false);
		
		if ( $type && $id ) {
			
			$wordsTable = new Model_Words();
			
			if ( $type == 'translate' ) {
				
				$translate = $wordsTable->getTranslate($id);
				
				$translates = $wordsTable->getTranslates(array('dictionary' => $this->dictionary['id'], 'word' => $translate['word']))->toArray();
				
				if ( count($translates) > 1 ) {
					
					$wordsTable->deleteTranslateForUser($this->dictionary['id'], $id);
					
					if ( !$this->_request->isXmlHttpRequest() )
						$this -> _redirect($this -> view -> url(array('controller' => 'words', 'action' => 'word', 'word' => $translate['word']), null, true));
					else 
						return $this -> _helper -> json(true);
						
				}
				else {
					
					if ( !$this->_request->isXmlHttpRequest() ) {
						
						$flashMessenger = $this->_helper->getHelper('FlashMessenger');
						
						$flashMessenger->addMessage('Нельзя удалить единственный перевод слова');
						
						$this -> _redirect($this -> view -> url(array('controller' => 'words', 'action' => 'word', 'word' => $translate['word']), null, true));
					}
					else 
						return $this -> _helper -> json(array('error' => "Нельзя удалить единственный перевод слова"));
					
				}
			}
			
			if ( $type == 'word' ) {
				
				$wordsTable->deleteWordForUser($this->dictionary['id'], $id);
				
				$this -> _redirect($this -> view -> url(array('controller' => 'words', 'action' => 'index'), null, true));
			}
			
		}
	}
	
	public function selectAction()
	{
		if ( $this->getRequest()-> isXmlHttpRequest() ) {
			
			$id = $this->_getParam('id', false);
			
			$wordsTable = new Model_Words();
			
			if ( $id ) {
				
				return $this -> _helper -> json($wordsTable->selectWord($this->dictionary['id'], $id));
				
			}
			
		}
		else throw new Zend_Controller_Action_Exception('This page dont exist', 404);
	}
	
	public function getAction()
	{
		$wordsTable = new Model_Words();
		
		$statistics = $wordsTable->getUserStatistic($this->user->id);
		
		$flashMessenger = $this->_helper->getHelper('FlashMessenger');
		
		if ( ($statistics['all'] - $statistics['full']) <= 200  ) {
			
			$limit = $this->_getParam('limit', 100);
			
			if ( $limit > 100 )
				$limit = 100;
			
			$words = $wordsTable->getPopularWords($this->user->id, $this->dictionary['from'], $this->dictionary['to'], $limit);
			
			if ( count($words) >= $limit ) {
				
				foreach ( $words as $word ) {
					
					$wordsTable->addWordForUser($this->dictionary['id'], $word['id'], $this->user->id, $word['translates'], $limit);
					
				}
				
				$flashMessenger->addMessage('В ваш словарик было добавленно ' . $limit . ' слов');
			}
			else {
				
				$flashMessenger->addMessage('Мы не смогли найти слова которые вы еще не знаете');
				
			}
		}
		else {
			$flashMessenger->addMessage('У вас слишко большое количество не выученных слов. Что бы вам было проще учить слова, их нужно учить небольшыми порциями');
		}
		
		$this -> back('/');
	}
}