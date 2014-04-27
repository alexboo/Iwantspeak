<?php
class Model_Speech_Dictionary implements Model_Speech_Interface
{
	public function speech($source, $target, $text)
	{
		$wordTable = new Model_Words();
		
		$speechTable = new Model_Table_Speech();
		
		$word = $wordTable->get($text, $source);
		
		if ( !empty($word['id']) )
			return $speechTable->get($word['id']);
			
		return null;
	}
}