<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initAutoload() {

		// Add autoloader empty namespace
		$autoLoader = new Zend_Loader_Autoloader_Resource(array(

			'basePath' => APPLICATION_PATH , 'namespace' => '' , 'resourceTypes' => array(
			'form' => array(
				'path' => 'forms/' , 'namespace' => 'Form_'
			) ,
			'model' => array(
				'path' => 'models/' , 'namespace' => 'Model_'
			))
		));
		
		// Return it, so that it can be stored by the bootstrap
		return $autoLoader;
	}
	
	protected function _initLayout()
	{
		Zend_Layout::startMvc();
		
		Zend_Layout::getMvcInstance() -> setLayout('base');
	}
	
	protected function _initPagination() {

		// Default pagination tpl
		Zend_View_Helper_PaginationControl::setDefaultViewPartial(
			'pagination.phtml'
			);
	}
}

