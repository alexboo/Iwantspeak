<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

define('LOGS_PATH', realpath(dirname(__FILE__) . '/../logs'));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$options = new Zend_Console_Getopt(
  array(
    'help|h' => 'Print help',
  	'debug|d' => 'Debug output'
  )
);

$options -> setOptions(
	array('ignoreCase' => true,)
);

try {
	$options -> parse();

	if ($options -> getOption('h')) {
		die($options -> getUsageMessage());
	}

	$debug = !! $options -> getOption('d');
} catch (Zend_Console_Getopt_Exception $e) {
    die($options -> getUsageMessage());
}

$application -> bootstrap(array('autoload', 'db'));

set_time_limit(0);

$translates = array(
	//array('en' => array('ru', 6)),
	//array('ru' => array('en', 8)),
	array('ru' => array('kk', 9)),
	array('kk' => array('ru', 10)),
	array('kk' => array('en', 11)),
        array('en' => array('kk', 7)),
);

$files = scandir(APPLICATION_PATH . '/../public/uploads/files/html');

foreach ( $files as $file ) {
	
	if ( !in_array($file, array('.', '..')) ) {
		//echo APPLICATION_PATH . '/../public/uploads/files/html/' . $file;
		unlink(APPLICATION_PATH . '/../public/uploads/files/html/' . $file);
	}
	
}

/*
$fileModel = new Model_Files();

while ( true ) {
	
	$fileModel->load('http://getmyip.ru/?' . rand(1, 1000), Model_Files::FILE_HTML, true);
	
}
*/


// Run
$frequencyTable = new Zend_Db_Table('frequency_list');

$wordsTable = new Model_Words();

$translateModel = new Model_Translate();
			
$transcriptionModel = new Model_Transcription();
			
$speechModel = new Model_Speech();

foreach ( $translates as $array ) {
	
	foreach ( $array as $from => $to ) {
		
		$select = $frequencyTable->select()->setIntegrityCheck(false);
		
		$select->from(array('fl' => 'frequency_list'), array('*'));
		
		$select->joinLeft(array('w' => 'words'), 'w.word = fl.word AND w.language = fl.language', array(''));
		
		$select->joinLeft(array('uw' => 'user_words'), 'uw.word = w.id', array('id_word' => 'word'));
		
		$select->where('`fl`.`language` = ?', $from);
		
		$select->having('id_word IS NULL');
		
		$select->group('fl.word');
		
		$select->order('fl.id');
		
		$words = $frequencyTable -> fetchAll($select);
		
		foreach ( $words as $word ) {
			
			$_translates = $translateModel -> translate($from, $to[0], $word['word']);
					
			$_t = $transcriptionModel -> transcription($from, $to[0], $word['word']);
					
			$_s = $speechModel -> speech($from, $to[0], $word['word']);
			
			$_word = $wordsTable->get($word['word'], $from);
			
			if ( !empty($_translates) ) {
				
				$_translates = $frequencyTable -> fetchAll(array('language = ?' => $to[0], 'word IN(?)' => $_translates)) -> toArray();
				
			}
			
			//var_dump($_translates); exit;
			
			if ( !empty($_translates) ) {
				
				foreach ( $_translates as $translate ) {
					
					$_trans = $wordsTable->getTranslate($translate['word'], $_word['id'], $to[0]);
					
					$trans[] = $_trans['id'];
					
				}
				
				if ( !empty($trans) ) {
					
					$wordsTable -> addWordForUser($to[1], $_word['id'], 13, $trans);
						
					echo "Added new word '{$word['word']}'\n";
				}
				else {
					echo "Error! words isn't frequency\n";
				}
				
				ob_flush();
				sleep(rand(1,5));
				
			}
		}
	}
}