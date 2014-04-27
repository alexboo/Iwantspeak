<?php
class Model_Files
{
	const FILE_HTML = 'html';
	
	const FILE_MEDIA = 'media';
	
	private $_proxies = array();
	
	protected $_cache_time = 86400;
	
	public function load($source = null, $type = self::FILE_HTML, $proxy = false)
	{
		$_loader = 'load' . ucfirst($type);
		
		if ( method_exists($this, $_loader) )
		{
			return $this -> $_loader($source, $proxy);
		}
		
		return null;
	}
	
	public function loadHtml($source, $proxy = false)
	{
		$filename = $this -> getFileName($source, 'html');
		
		if ( !$this -> getFromCache($filename['path']) ) {
			
			
			if ( $proxy ) {
				
				$source = str_replace('http://', '', str_replace('https://', '', $source));
				
				$source = explode('/', $source);
				
				$host = $source[0];
				
				unset($source[0]);
				
				if ( !empty($source) )
					$url = implode($source, '/');
				else 
					$url = '/';
				
				$proxy = $this->getProxy();
				$proxy_ip = $proxy['ip'];
				$proxy_port = $proxy['port'];
				//$host = "market.yandex.ru";
				//$url = "model.xml?modelid=3882502&hid=91491&int_lnk=p-good";
				//$url = str_replace(" ", "%20", $url);
				$sock = fsockopen($proxy_ip, $proxy_port, $errno, $errstr, 10);
				
				$data = '';
				
				if ($sock){
					
					fwrite($sock, "GET /$url HTTP/1.0\r\n" .
					"Host: $host\r\n" .
					"\r\n");
					
					while (!feof($sock)) 
						$data.=fread($sock, 128);
						
					fclose($sock);
				}
			}
			else {
				$c = curl_init();
				curl_setopt($c, CURLOPT_URL, $source);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);				
				$data = curl_exec($c);
				curl_close($c);
			}
			
			$this -> saveFile($filename['path'], $data);
			
		}
		
		return $filename;
	}
	
	public function loadMedia($source, $proxy = false)
	{
		$filename = $this -> getFileName($source, 'media');
		
		if ( !$this -> getFromCache($filename['path']) ) {
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $source);
			$fp = fopen($filename['path'], 'w+');
			curl_setopt($ch, CURLOPT_FILE, $fp);
						
			curl_exec ($ch);
			curl_close ($ch);
			fclose($fp);
		}
		
		return $filename;
	}
	
	private function getFromCache($file = null)
	{		
		if ( is_readable($file) && (filectime($file) > time() - $this -> _cache_time) )
		{
			return $file;
		}
		
		return false;
	}
	
	private function getFileName($file = null, $folder = null)
	{
		$hash = md5($file);
		
		return array(
			'path' => APPLICATION_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'public', 'uploads', 'files', $folder, $hash)),
			'url' => '/' . implode('/', array('uploads', 'files', $folder, $hash))
		);
	}
	
	private function saveFile($filename, $data)
	{
		$fp = fopen($filename, 'w+');
		fwrite($fp, $data);
		fclose($fp);
	}
	
	private function getProxy()
	{		
		
		if ( empty($this->_proxies) ) {
			$proxies = file(APPLICATION_PATH . '/../public/proxy.txt');
			$this->_proxies = $proxies;
		}
		else {
			$proxies = $this->_proxies;
		}
		
		$mc = curl_multi_init (); 
		
		for ($thread_no = 0; $thread_no<count ($proxies); $thread_no++) 
		{
			$c [$thread_no] = curl_init (); 
			curl_setopt ($c[$thread_no], CURLOPT_URL, "http://google.com"); 
			curl_setopt ($c[$thread_no], CURLOPT_HEADER, 0); 
			curl_setopt ($c[$thread_no], CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt ($c[$thread_no], CURLOPT_CONNECTTIMEOUT, 5); 
			curl_setopt ($c[$thread_no], CURLOPT_TIMEOUT, 10); 
			curl_setopt ($c[$thread_no], CURLOPT_PROXY, trim ($proxies [$thread_no])); 
			curl_setopt ($c[$thread_no], CURLOPT_PROXYTYPE, 0);
			curl_multi_add_handle ($mc, $c [$thread_no]); 
		} 
		 
		do { 
			while (($execrun = curl_multi_exec ($mc, $running)) == CURLM_CALL_MULTI_PERFORM); 
			
			if ($execrun != CURLM_OK) break; 
			
			while ($done = curl_multi_info_read ($mc)) 
			{ 
				$info = curl_getinfo ($done ['handle']); 
				
				if ($info ['http_code'] == 200) { 
					$ready[] = trim($proxies[array_search ($done['handle'], $c)])."\r\n"; 
				} 
				
				curl_multi_remove_handle ($mc, $done ['handle']); 
			} 
		} while ($running); 
		
		curl_multi_close($mc);
		
		$proxy = $ready[rand(0, (count($ready) - 1))];
		
		$proxy = explode(':', $proxy);
		
		var_dump($proxy);
		
		return array('ip' => $proxy[0], 'port' => $proxy[1]);
	}
}