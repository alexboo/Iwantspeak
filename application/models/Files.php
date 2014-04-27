<?php
class Model_Files
{
	const FILE_HTML = 'html';
	
	const FILE_MEDIA = 'media';
	
	private static $_proxies = array();
	
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
	
	public function loadHtml($source, $proxy = false, $try = 0)
	{
		$filename = $this -> getFileName($source, 'html');
		
		if ( !$this -> getFromCache($filename['path']) ) {
			
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $source);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($c, CURLOPT_REFERER, "http://mywords.kz");
			
			$cookie_file = APPLICATION_PATH . '/../public/uploads/files/cookie';
			curl_setopt($c, CURLOPT_COOKIEJAR, $cookie_file);
			curl_setopt($c, CURLOPT_COOKIEJAR, $cookie_file);
			
			if ( $proxy ) {
				$proxy = $this->getProxy();
				
				if ( !empty($proxy) ) {
					curl_setopt ($c, CURLOPT_CONNECTTIMEOUT, 3); 
					curl_setopt ($c, CURLOPT_TIMEOUT, 5); 
					curl_setopt ($c, CURLOPT_PROXY, $proxy); 
					curl_setopt($c, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
				}
				
			}
			
			$data = curl_exec($c);
			curl_close($c);
			
			if ( empty($data) && $try <= 5 ) {
				
				$try ++;
				
				$this->loadHtml($source, $proxy, $try);
				
			}
			else
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
			
			if ( $proxy ) {
				$proxy = $this->getProxy();
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 3); 
				curl_setopt ($ch, CURLOPT_TIMEOUT, 5); 
				curl_setopt ($ch, CURLOPT_PROXY, $proxy); 
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			}
			
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
		if ( empty(self::$_proxies) || (self::$_proxies['time'] + 600) < time() )
		{
			$file = file_get_contents('http://www.digitalcybersoft.com/ProxyList/fresh-proxy-list.shtml');
			
			if ( preg_match_all('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}:[0-9]{1,5}/', $file, $matches) )
			{
				$proxies = $matches[0];
			}
			else $proxies = array();
			
			$mc = curl_multi_init (); 
			
			for ($thread_no = 0; $thread_no<count ($proxies); $thread_no++) 
			{
				$c[$thread_no] = curl_init (); 
				curl_setopt ($c[$thread_no], CURLOPT_URL, "http://google.com"); 
				curl_setopt ($c[$thread_no], CURLOPT_HEADER, 0); 
				curl_setopt ($c[$thread_no], CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt ($c[$thread_no], CURLOPT_CONNECTTIMEOUT, 5); 
				curl_setopt ($c[$thread_no], CURLOPT_TIMEOUT, 10); 
				curl_setopt ($c[$thread_no], CURLOPT_PROXY, trim ($proxies[$thread_no])); 
				curl_setopt ($c[$thread_no], CURLOPT_PROXYTYPE, 0);
				curl_multi_add_handle ($mc, $c [$thread_no]); 
			} 
			 
			do { 
				while (($execrun = curl_multi_exec ($mc, $running)) == CURLM_CALL_MULTI_PERFORM); 
				
				if ($execrun != CURLM_OK) break; 
				
				while ($done = curl_multi_info_read ($mc)) 
				{ 
					$info = curl_getinfo ($done ['handle']); 
					
					if ($info ['http_code'] == 302) { 
						$ready[] = trim($proxies[array_search ($done['handle'], $c)])."\r\n"; 
					} 
					
					curl_multi_remove_handle ($mc, $done ['handle']); 
				} 
			} while ($running); 
			
			curl_multi_close($mc);
			
			if ( !empty($ready) ) {
				
				self::$_proxies['time'] = time();
				self::$_proxies['proxies'] = $ready;
				
			}
		}
		
		if ( !empty(self::$_proxies['proxies']) )
		{
			return self::$_proxies['proxies'][rand(0, (count(self::$_proxies['proxies']) - 1))];
		}
		
		return null;
	}
}