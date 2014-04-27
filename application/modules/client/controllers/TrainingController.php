<?php
class TrainingController extends Dict_Controller_Action
{
	public function indexAction()
	{
		$session = new Zend_Session_Namespace('dictionary');
		
		$dictionary = $session->id;
		
		$wordsTable = new Model_Words();
		
		$this -> view -> count_words = $wordsTable->getUserWordsCount($dictionary);
		
		if ( $this -> view -> count_words > 0 ) {
		
			$word = $wordsTable->getRandWord($dictionary);
			
			if  ( isset($word['id']) ) {
				
				Zend_Registry::set('training_word', $word);
				
				$settingsTable = new Model_Table_Settings();
				
				$method = $settingsTable -> get($this->user->id, 'training-method', 'all');
				
				if ( $method == 'all' ) {
					
					if ( $word['balls'] < 3 )
						$method = 1;
					
					else if ( $word['balls'] < 6 )
						$method = 2;
						
					else
						$method = 3;
				}
				
				if ( isset($word) ) {
					
					if ( $method == 1 )
						return $this->_forward('first', 'training');
					else if ( $method == 2 )
						return $this->_forward('second', 'training');
					else
						return $this->_forward('third', 'training');
					
				}
			}
		}
	}
	
	public function firstAction()
	{
		$session = new Zend_Session_Namespace('training');
		
		$dictionary = $this->dictionary['id'];
		
		$wordsTable = new Model_Words();
		
		if ( $answer = $this->_getParam('answer', false) ) {
			
			if ( !isset($session->word) ) {
				
				$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
			
			$word = $session->word;
			
			unset($session->word);
			
			if ( $this->getRequest()-> isXmlHttpRequest() ) {
				
				$translate = $wordsTable->getTranslate($answer);
				
				if ( $translate['word'] == $word ) {
					$wordsTable->addTranslateBall($dictionary, $translate['id'], 1);
				}
				
				return $this -> _helper -> json($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
			else {
				
				$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
		}
		else if ( $next = $this->_getParam('next', false) ) {
			
			$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
			
		}
		else {
			
			$word = Zend_Registry::get('training_word');
			
			$transcriptionTable = new Model_Table_Transcription();
			
			$speechTable = new Model_Table_Speech();
			
			$session->word = $word['id'];
			
			$this -> view -> word = $word;
			$this -> view -> transcription = $transcriptionTable->get($word['id']);
			$this -> view -> speech = $speechTable->get($word['id']);

			$count = $wordsTable -> getAdapter() -> fetchOne("SELECT COUNT(DISTINCT `word`) FROM `translate` WHERE `word` != :word AND `language` = :lang", array('word' => $word['id'], 'lang' => $this->dictionary['to']));

			$start = rand(0, $count - 4);

			$this -> view -> translates = $wordsTable -> getAdapter() -> fetchAll("
			(
				SELECT `t`.`id`, `t`.`translate`, 'false' AS `true`, `w`.`word`, `w`.`id` AS `word_id`
				FROM `words` `w`
				INNER JOIN `translate` `t` ON `w`.`id` = `t`.`word`
				WHERE `t`.`language` = :lang AND `t`.`word` != :word
				GROUP BY `w`.`id`
				LIMIT $start, 4
			)
			UNION 
			(
				SELECT `t`.`id`, `t`.`translate`, 'true' AS `true`, '' AS `word`, `t`.`word` AS `word_id` FROM `translate` `t`
				INNER JOIN `user_translate` `ut` ON `ut`.`translate` = `t`.`id`
				WHERE `ut`.`dictionary` = :dict AND `t`.`word` = :word
				ORDER BY `ut`.`balls` ASC
				LIMIT 1
			)
			ORDER BY RAND()
			", array('dict' => $dictionary, 'word' => $word['id'], 'lang' => $this->dictionary['to']));
			
		}
	}
	
	public function secondAction()
	{
		$session = new Zend_Session_Namespace('training');
			
		$dictionary = $this->dictionary['id'];
			
		$wordsTable = new Model_Words();
		
		if ( $answer = $this->_getParam('answer', false) ) {
			
			if ( !isset($session->word) ) {
				
				$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
			
			$word = $session->word;
			
			unset($session->word);
			
			if ( $this->getRequest()-> isXmlHttpRequest() ) {
				
				if ( $word != $answer ) {
				    $wordsTable->addTranslateBall($dictionary, $session->translate, -1);
				}
				else  {
					
				    if ( !empty($session->translate) )
					$wordsTable->addTranslateBall($dictionary, $session->translate, 1);
				}

				return $this -> _helper -> json($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
			else {
				
				$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
		}
		else if ( $next = $this->_getParam('next', false) ) {
			
			$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
			
		}
		else {
			
			$word = Zend_Registry::get('training_word');
			
			$speechTable = new Model_Table_Speech();
			
			$session->word = $word['id'];
			
			$speech = $speechTable->get($word['id']);
			
			if ( rand(1, 4) == 3 && !empty($speech)) {
				
				$this -> view -> speech = $speech;
				
			}
			else {
				
				$this -> view -> translate = $wordsTable -> getAdapter() -> fetchRow("SELECT `t`.`id`, `t`.`translate` FROM `translate` `t`
					INNER JOIN `user_translate` `ut` ON `ut`.`translate` = `t`.`id`
					WHERE `ut`.`dictionary` = :dict AND `t`.`word` = :word
					ORDER BY `balls` ASC
					LIMIT 1", array('word' => $word['id'], 'dict' => $dictionary));
				
				$session->translate = $this -> view -> translate['id'];
				
			}

			$count = $wordsTable -> getAdapter() -> fetchOne("SELECT COUNT(*) FROM `words` `w` WHERE `w`.`language` = :lang AND `w`.`id` != :word", array('word' => $word['id'], 'lang' => $this->dictionary['to']));

			$start = rand(0, $count - 4);

			$this -> view -> words = $wordsTable -> getAdapter() -> fetchAll("
			(
				SELECT `w`.`id`, `w`.`word`, 'false' AS `true`
				FROM `words` `w`
				WHERE `w`.`language` = :lang AND `w`.`id` != :word
				LIMIT $start, 4
			)
			UNION 
			(
				SELECT `w`.`id`, `w`.`word`, 'true' AS `true`
				FROM `words` `w`
				INNER JOIN `user_words` `uw` ON `uw`.`word` = `w`.`id`
				WHERE `uw`.`dictionary` = :dict AND `w`.`id` = :word
				ORDER BY RAND()
				LIMIT 1
			)
			ORDER BY RAND()
			", array('dict' => $dictionary, 'word' => $word['id'], 'lang' => $this->dictionary['from']));
			
		}
	}
	
	public function thirdAction()
	{
		$session = new Zend_Session_Namespace('training');
			
		$dictionary = $this->dictionary['id'];
			
		$wordsTable = new Model_Words();
		
		if ( $answer = $this->_getParam('answer', false) ) {
			
			if ( !isset($session->word) ) {
				
				$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
			
			$word = $wordsTable->get($session->word);
			
			unset($session->word);
			
			if ( $this->getRequest()-> isXmlHttpRequest() ) {
				
				if ( mb_strtolower($word['word'], 'UTF-8') != mb_strtolower($answer, 'UTF-8') ) {
					
					$wordsTable->addTranslateBall($dictionary, $session->translate, -1);
					
					return $this -> _helper -> json(false);
					
				}
				else  {
					
					if ( !empty($session->translate) )
						$wordsTable->addTranslateBall($dictionary, $session->translate, 1);
					
					return $this -> _helper -> json(true);	
				}
				
			}
			else {
				
				$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
				
			}
		}
		else if ( $next = $this->_getParam('next', false) ) {
			
			$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
			
		}
		else {
			
			$word = Zend_Registry::get('training_word');
			
			$speechTable = new Model_Table_Speech();
			
			$session->word = $word['id'];

			$this->view->word = $word['word'];
			
			$speech = $speechTable->get($word['id']);
			
			if ( rand(1, 4) == 3 && !empty($speech) ) {
				
				$this -> view -> speech = $speech;
				
			}
			else {
				
				$this -> view -> translate = $wordsTable -> getAdapter() -> fetchRow("SELECT `t`.`id`, `t`.`translate` FROM `translate` `t`
					INNER JOIN `user_translate` `ut` ON `ut`.`translate` = `t`.`id`
					WHERE `ut`.`dictionary` = :dict AND `t`.`word` = :word
					ORDER BY `balls` ASC
					LIMIT 1", array('word' => $word['id'], 'dict' => $dictionary));
				
				$session->translate = $this -> view -> translate['id'];
				
			}

			$word_length = mb_strlen($word['word']) - 1;
			
			$help = '';
			
			if ( $word_length > 5 ) {
				
				$_characters[] = rand(0, ceil($word_length / 2));
				
				$_characters[] = rand(ceil($word_length / 2), $word_length);
			}
			else {
				
				$_characters[] = rand(0, $word_length);
			}
			
			for ($i = 0; $i <= $word_length; $i ++) {
			    $characters[$i]['char'] = mb_substr($word['word'], $i, 1);
			    if ( in_array($i, $_characters) )
				$characters[$i]['hide'] = false;
			    else
				$characters[$i]['hide'] = true;
			}
			
			
			$this->view->characters = $characters;
		}
	}
	
	public function settingsAction()
	{
		$settingsTable = new Model_Table_Settings();
		
		if ( isset($_POST['words']) || isset($_POST['method']) ) {
			
			if ( !empty($_POST['words']) ) {
				
				$settingsTable->set($this->user->id, 'training-words-type', $_POST['words']);
				
			}
			
			if ( !empty($_POST['method']) ) {
				
				$settingsTable->set($this->user->id, 'training-method', $_POST['method']);
				
			}
			
			$this -> _redirect($this -> view -> url(array('controller' => 'training', 'action' => 'index'), null, true));
			
		}
		
		$this->view->training_method = $settingsTable -> get($this->user->id, 'training-method', 'all');
		
		$this->view->training_words_type = $settingsTable -> get($this->user->id, 'training-words-type', array(1, 2, 3, 'full'));
	}
}