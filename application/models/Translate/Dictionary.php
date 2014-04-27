<?php
class Model_Translate_Dictionary implements Model_Translate_Interface
{
	public function translate($source, $target, $text)
	{
		$wordsTable = new Model_Words();
		
		$word = $wordsTable->get($text, $source);
		
		if ( !empty($word['id']) ) {
			
			$_translates = $wordsTable->getTranslates(array('word' => $word['id'], 'language' => $target));
			
			$translates = array();
			
			foreach ( $_translates as $translate ) {
				$translates[] = $translate['translate'];
			}
			
			return $translates;
		}
		
		return null;
	}
}