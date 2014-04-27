<?php
class Model_Speech_Easy implements Model_Speech_Interface
{
	public function speech($source, $target, $text)
	{
		$filesModel = new Model_Files();
		
		$url = 'http://www.english-easy.info/talker/?word=' . $text;
		
		$file = $filesModel -> load($url, Model_Files::FILE_HTML);
		
		$src = $this -> getSpeechFromFile($file['path']);
		
		return $filesModel -> load('http://www.english-easy.info/talker/' . $src[0], Model_Files::FILE_MEDIA);
	}
	
	private function getSpeechFromFile($file = null)
	{
		if ( is_readable($file) ) {
			$data = file_get_contents($file);
			
			if ( preg_match_all('/href="([^"]{1,}.mp3)"/i', $data, $translates) ) 
			{
				return $translates[1];
			}
			
			return null;
		}
	}
}