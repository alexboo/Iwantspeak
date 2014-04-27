<?php
class Model_Table_Settings extends Zend_Db_Table_Abstract
{
	protected $_name = 'users_settings';
	
	public function get($user = null, $key = null, $default = null)
	{
		$setting = $this->fetchRow(array('user = ?' => $user, '`key` = ?' => $key));
		
		if ( isset($setting->key) ) {
			
			$setting = $setting->toArray();
			
			$value = @unserialize($setting['value']);
			
			if ( false !== $value )
				$setting['value'] = $value;
			
			return $setting['value'];
		}
		
		return $default;
	}
	
	public function set($user = null, $key = null, $value = null)
	{
		if ( null !== $user && null !== $key && null != $value ) {
			
			if ( !is_string($value) ) {
				
				$value = serialize($value);
				
			}
			
			$setting = $this -> get($user, $key);
			
			if ( !empty($setting) ) {
				return $this -> update(array('value' => $value), array('user = ?' => $user, '`key` = ?' => $key));
			}
			else {
				return $this -> insert(array('user' => $user, 'key' => $key, 'value' => $value));
			}
			
		}
		
		return false;
	}
}