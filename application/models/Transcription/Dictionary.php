<?php
class Model_Transcription_Dictionary implements Model_Transcription_Interface
{
	public function transcription($source, $target, $text)
	{
		if ( !empty($text) ) {
			$wordTable = new Model_Words();
			
			$transcriptionTable = new Model_Table_Transcription();
			
			$word = $wordTable->get($text, $source);
			
			if ( !empty($word['id']) ) {
				$_trans = $transcriptionTable -> get($word['id']);
				
				if ( !empty($_trans['text']) )
					return $_trans['text'];
				
				if ( !empty($_trans['path']) )
					return $_trans;
			}
			
			return null;
		}
	}
}