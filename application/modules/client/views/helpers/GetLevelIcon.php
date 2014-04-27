<?php
class Zend_View_Helper_GetLevelIcon extends Zend_View_Helper_Abstract {

	public function GetLevelIcon($balls = 0) {

		$baseUrl = new Zend_View_Helper_BaseUrl();
		
		if ( $balls < 3 ) {
			return '<img class="level-ico" alt="Слово находится на 1 - ом уровне изучения" src="' . $baseUrl->baseUrl('images/level-1.png') . '"/>';
		}
		
		if ( $balls < 6 ) {
			return  '<img class="level-ico" alt="Слово находится на 2 - ом уровне изучения" src="' . $baseUrl->baseUrl('images/level-2.png') . '"/>';
		}
		
		if ( $balls < 12 ) {
			return  '<img class="level-ico" alt="Слово находится на 3 - ем уровне изучения" src="' . $baseUrl->baseUrl('images/level-3.png') . '"/>';
		}
		
		return  '<img class="level-ico" alt="Вы хорошо знаете это слово" src="' . $baseUrl->baseUrl('images/level-full.png') . '"/>';

	}

}