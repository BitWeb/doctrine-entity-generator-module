<?php
namespace BitwebEntityGeneratorModule\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

	public function indexAction() {
		
		return $this->redirect()->toRoute('development', array(
			'controller' => 'entity',
			'action' => 'index',
		));
	}
	
	public function testAction() {
		echo 'index controller, test action';
		
		return new ViewModel();
	}
	
}