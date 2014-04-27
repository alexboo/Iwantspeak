<?php
class Model_Transcription_Abbyy implements Model_Transcription_Interface
{
	public function transcription($source, $target, $text)
	{
		$filesModel = new Model_Files();
		
		$url = 'http://lingvopro.abbyyonline.com/ru/Search/' . $source . '-' . $target . '/' . $text;
		
		$file = $filesModel -> load($url, Model_Files::FILE_HTML);
		
		$src = $this -> getTranscriptionFromFile($file['path']);
		
		return $filesModel -> load('http://lingvopro.abbyyonline.com/' . $src[0], Model_Files::FILE_MEDIA);
	}
	
	private function getTranscriptionFromFile($file = null)
	{
		if ( is_readable($file) ) {
			$data = file_get_contents($file);
			
			if ( preg_match_all('/<img class="transcription"[^>]{1,}src="([^"]{1,})">/i', $data, $translates) ) 
			{
				return array_map('strip_tags', $translates[1]);
			}
			
			return null;
		}
	}
}