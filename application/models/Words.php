<?php
class Model_Words extends Zend_Db_Table_Abstract
{
	protected $_name = 'words';
	
	private $_max_balls = 12;
	
	protected $_orderField = 'word', $_orderTypes = 'ASC';

	protected $_limitLists = 10, $_offsetLists = 0;
	
	protected $_levels = array(1 => '`balls` < 3', 2 => '`balls` >= 3 AND `balls` < 6', 3 => '`balls` >= 6 AND `balls` < 12', 'full' => '`balls` = 12');
	
	public function get($word = null, $language = null, array $params = array()) 
	{
		$select = $this->select()->setIntegrityCheck(false);
		
		$select -> from(array('words'), array('*'));
		
		if ( isset($params['dictionary']) ) {
			
			$select -> joinInner(array('uw' => 'user_words'), 'uw.word = words.id', array('balls'));
			$select -> where('uw.dictionary = ?', $params['dictionary']);
			
		}
		
		if ( is_numeric($word) ) {
			$select -> where('id = ?', $word);
		}
		else {
			$select -> where('language = ?', $language);
			$select -> where('word = ?', new Zend_Db_Expr('LOWER(' . $this->getAdapter()->quote($word) . ')'));
		}
		
		return $this->fetchRow($select);
	}
	
	public function set($word = null, $language = null, array $translates = array())
	{
		if ( null !== $word && null !== $language ) {
			
			$word = trim($word);
			
			$_word = $this->get($word, $language);
			
			if ( empty($_word['id']) ) {
				
				$id = $this -> insert(array('word' => new Zend_Db_Expr('LOWER(' . $this->getAdapter()->quote($word) . ')'), 'language' => $language, 'update' => new Zend_Db_Expr('NOW()')));
				
			}
			else 
				$id = $_word['id'];
			
			if ( !empty($translates) ) {
				
				foreach ( $translates as $translate ) {
					
					if ( !empty($translate['language']) && !empty($translate['translate']) ) {
						
						$this->setTranslate($translate['translate'], $id, $translate['language']);
						
					}
					
				}
				
			}
			
			return $id;
		}
	}
	
	public function getTranslates(array $params = array(), $order = null, $limit = null) {
		
		$translateTable = new Zend_Db_Table('translate');
		
		$select = $translateTable -> select() -> setIntegrityCheck(false);
		
		$select -> from(array('translate'), array('*'));
		
		if ( isset($params['dictionary']) ) {
			$select -> joinInner(array('ut' => 'user_translate'), 'ut.translate = translate.id', array(''));
			$select -> where('ut.dictionary = ?', $params['dictionary']);
		}
		
		if ( isset($params['word']) ) {
			$select -> where('translate.word = ?', $params['word']);
		}
		
		if ( isset($params['language']) ) {
			$select -> where('translate.language = ?', $params['language']);
		}
		
		if ( !empty($order) ) {
			
			$select -> order($order);
			
		}
		
		if ( !empty($limit) )  {
			
			$select -> limit($limit);
			
		}
		
		$select -> group('translate.id');
		
		return $translateTable->fetchAll($select);
	}
	
	public function getTranslate($translate = null, $word = null, $language = null, array $params = array())
	{
		$translateTable = new Zend_Db_Table('translate');
		
		if ( is_numeric($translate) )
			return $translateTable->fetchRow(array('id = ?' => $translate));
		
		return $translateTable->fetchRow(array('translate = ?' => $translate, 'word = ?' => $word, 'language = ?' => $language));
	}
	
	public function setTranslate($translate = null, $word = null, $language = null)
	{
		if ( null !== $translate && null !== $word && null !== $language ) {
			
			$translate = trim($translate);
			
			$translateTable = new Zend_Db_Table('translate');
			
			$_translate = $this->getTranslate($translate, $word, $language);
			
			if ( empty($_translate['id']) ) {
				
				$id = $translateTable->insert(array('translate' => new Zend_Db_Expr('LOWER(' . $this->getAdapter()->quote($translate) . ')'), 'word' => $word, 'language' => $language));
				
			}
			else 
				$id = $_translate['id'];
			
			return $id;
		}
	}
	
	public function addWordForUser($dictionary = null, $word = null, $user = null, array $translates = array())
	{
		$userwordTable = new Zend_Db_Table('user_words');
		
		$_word = $userwordTable->fetchRow(array('word = ?' => $word, 'user = ?' => $user));
		
		if ( empty($_word['user']) ) {
			
			$userwordTable->insert(array('word' => $word, 'user' => $user, 'dictionary' => $dictionary, 'date' => new Zend_Db_Expr('NOW()')));
			
		}
		
		if ( !empty($translates) ) {
			
			foreach ( $translates as $translate ) {
				
				$this->addTranslateForUser($dictionary, $translate, $user);
			}
		}
	}
	
	public function addTranslateForUser($dictionary = null, $translate = null, $user = null)
	{
		$usertranslateTable = new Zend_Db_Table('user_translate');
		
		$_translate = $usertranslateTable->fetchRow(array('translate = ?' => $translate, 'user = ?' => $user));
		
		if ( empty($_translate['user']) ) {
			
			$usertranslateTable->insert(array('translate' => $translate, 'user' => $user, 'dictionary' => $dictionary));
			
		}
	}
	
	public function getWords(array $params = array())
	{
		$select = $this -> select() -> setIntegrityCheck(false);
		
		$select -> from(array('w' => 'words'), array(new Zend_Db_Expr('SQL_CALC_FOUND_ROWS w.*')));
		
		$select -> joinInner(array('uw' => 'user_words'), 'w.id = uw.word', array('balls', 'selected'));
		
		if ( !empty($params['dictionary']) )
			$select->where('uw.dictionary = ?', $params['dictionary']);
			
		if ( !empty($params['word']) )
			$select->where('w.word LIKE ?', $params['word']);
		
		if ( !empty($params['level']) ) {
			if ( !empty($this->_levels[$params['level']]) ) {
				$select->where($this->_levels[$params['level']]);
			}
			
			if ( $params['level'] == 'selected' ) {
				$select->where('uw.selected = ?', 'yes');
			}
		}
		
		$select->order($this->_orderField . ' ' . $this->_orderTypes);
		
		$select->limit($this->_limitLists, $this->_offsetLists);
		
		$words = $this->fetchAll($select);
		
		$this -> find_count = $this -> _db -> fetchOne("SELECT FOUND_ROWS()");
		
		return $words;
	}
	
	public function getFoundCount()
	{
		return (int) $this -> find_count;
	}
	
	public function getRandWord($dictionary = null)
	{
		$settingsTable = new Model_Table_Settings();
		
		$user = Zend_Registry::get('user');
		
		$typies_words = $settingsTable->get($user->id, 'training-words-type', array(1, 2, 3, 'full'));
		
		$count_words = $this->getUserWordsCount($dictionary);
		
		$limit = ($count_words < 20) ? $count_words : 20;
		
		$queries = array();
		
		$balls = $this->_levels;
		
		foreach ( $typies_words as $type ) {
			
			if ( !empty($balls[$type]) ) {
				
				$count = $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `dictionary` = :dict AND ' . $balls[$type], array('dict' => $dictionary));
				
				$limit = ceil($limit / 2);
				
				$queries[] = "(SELECT `word`, `balls` FROM `user_words` WHERE " . $balls[$type] . " AND `dictionary` = " . $this->_db->quote($dictionary) . " LIMIT " . $limit . " OFFSET " . rand(0, ($count - 1)) . ")";
				
			}
		}
		
		if ( in_array('selected', $typies_words) ) {
			
			$count = $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `dictionary` = :dict AND `selected` = \'yes\'', array('dict' => $dictionary));
			
			if ( $count > 0 )
				$queries[] = "(SELECT `word`, `balls` FROM `user_words` WHERE `selected` = 'yes' AND `dictionary` = " . $this->_db->quote($dictionary) . " LIMIT 10 OFFSET " . rand(0, ($count - 1)) . ")";
			
		} 
		
		$word = $this->_db->fetchRow(implode($queries, ' UNION ') . ' ORDER BY RAND() LIMIT 1');
		
		if ( !empty($word) ) {
			return $this->get($word['word'], null, array('dictionary' => $dictionary));
		}
		
		return null;
	}
	
	public function getUserStatistic($user = null)
	{
		$count['all'] = $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `user` = :user', array('user' => $user));
		
		$count[1] = $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `user` = :user AND `balls` < 3', array('user' => $user));
		
		$count[2] = $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `user` = :user AND `balls` >= 3 AND `balls` < 6', array('user' => $user));
		
		$count[3] = $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `user` = :user AND `balls` >= 6 AND `balls` < 12', array('user' => $user));
		
		$count['full'] = $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `user` = :user AND `balls` = 12', array('user' => $user));
		
		return $count;
	}
	
	public function addTranslateBall($dictionary = null, $translate = null, $ball = 1) {
		
		if ( is_numeric($ball) ) {
			
			$this->_db->query("UPDATE `user_translate` SET `balls` = IF(`balls` < 12, `balls` + $ball, 12) WHERE `dictionary` = :dict AND `translate` = :trans", 
				array('dict' => $dictionary, 'trans' => $translate));
				
			$translate = $this -> getTranslate($translate);
			
			$this->updateWordBalls($dictionary, $translate['word']);
				
			return true;
		}
		
		return false;
	}
	
	public function updateWordBalls($dictionary = null, $word = null) {
		
		$this->_db->query("UPDATE `user_words` SET `balls` = 
			(
				SELECT AVG(`ut`.`balls`) 
				FROM `user_translate` `ut`
				INNER JOIN `translate` `t` ON `t`.`id` = `ut`.`translate`
				WHERE `ut`.`dictionary` = :dict AND `t`.`word` = :word
			) 
		WHERE `dictionary` = :dict AND `word` = :word", 
				array('dict' => $dictionary, 'word' => $word));
		
	}
	
	public function deleteWordForUser($dictionary = null, $word = null)
	{
		$userwordTable = new Zend_Db_Table('user_words');
		
		$translates = $this -> getTranslates(array('dictionary' => $dictionary, 'word' => $word));
		
		foreach ( $translates as $translate ) {
			
			$this -> deleteTranslateForUser($dictionary, $translate['id']);
			
		}
		
		return $userwordTable->delete(array('word = ?' => $word, 'dictionary = ?' => $dictionary));
	}
	
	public function deleteTranslateForUser($dictionary = null, $translate = null)
	{
		$usertranslateTable = new Zend_Db_Table('user_translate');
		
		return $usertranslateTable->delete(array('translate = ?' => $translate, 'dictionary = ?' => $dictionary));
	}
	
	public function selectWord($dictionary = null, $word = null)
	{
		$userwordTable = new Zend_Db_Table('user_words');
		
		return $userwordTable->update(array('selected' => new Zend_Db_Expr("IF(`selected` = 'yes', 'no', 'yes')")), 
			array('dictionary = ?' => $dictionary, 'word = ?' => $word));
	}
	
	public function getUserWordsCount($dictionary = null)
	{
		if ( null != $dictionary ) {
			
			$userwordTable = new Zend_Db_Table('user_words');
		
			return (int) $this->_db->fetchOne('SELECT COUNT(*) FROM user_words WHERE `dictionary` = :dict', array('dict' => $dictionary));
			
		}
		
		return 0;
	}
	
	public function getPopularWords($user = null, $from = null, $to = null, $limit = 100)
	{
		if ( null !== $from && null !== $to && null !== $user ) {
			
			$select = $this->select()->setIntegrityCheck(false);
			
			$select -> from(array('uw' => 'user_words'), array('id' => 'word', 'cnt' => new Zend_Db_Expr('COUNT(`uw`.`word`)')));
			
			$select -> joinInner(array('w' => 'words'), 'w.id = uw.word', array('language'));
			
			$select -> group('id');
			
			$select -> order('cnt DESC');
			
			$select -> where('user != ?', $user);
			
			$select -> where('language = ?', $from);
			
			$select->limit($limit);
			
			$words = $this->fetchAll($select)->toArray();
			
			if ( !empty($words) ) {
			
				foreach ( $words as & $word ) {
					
					$select = $this->select()->setIntegrityCheck(false);
				
					$select -> from(array('ut' => 'user_translate'), array('translate', 'cnt' => new Zend_Db_Expr('COUNT(`ut`.`translate`)')));
					
					$select -> joinInner(array('t' => 'translate'), 't.id = ut.translate', array('word', 'language'));
					
					$select -> group('translate');
					
					$select -> order('cnt DESC');
					
					$select -> where('word = ?', $word['id']);
					
					$select -> where('language = ?', $to);
					
					$select->limit(3);
					
					$_translates = $this->fetchAll($select);
					
					foreach ( $_translates as $translate ) {
						$word['translates'][] = $translate['translate'];
					}
					
				}
				
				return $words;
			
			}
			
		}
		
		return null;
	}
    
	/**
	 * set order field
	 * @param string $field
	 */
	public function setOrderField($field)
	{
		/*
		$info = $this->info();
		
		if (in_array($field, $info['cols'])) 
		{
		*/
			$this -> _orderField = $field;
			/*
		}
		*/
	}

	/**
	 * set order type
	 * @param ASC|DESC $types
	 */
	public function setOrderTypes($types)
	{
		if (in_array($types, array('ASC', 'DESC'))) {
			$this -> _orderTypes = $types;
		}
	}
	
    /**
     * get order field
     */
	public function getOrderField()
	{
		return $this -> _orderField;
	}

	/**
	 * get order type
	 */
	public function getOrderTypes()
	{
		return $this -> _orderTypes;
	}

	/**
	 * set select limit
	 * @param $offset
	 * @param $limit
	 */
	public function setLimitPage($offset, $limit=10)
	{
		$this -> _limitLists = $limit; $this -> _offsetLists = $offset;
	}
}