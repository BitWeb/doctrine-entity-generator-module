<?php
namespace DoctrineEntityGeneratorModule\Controller;

use Zend\View\Model\ViewModel;
use DoctrineEntityGeneratorModule\Service\EntityService;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineEntityGeneratorModule\Service\EntityGeneratorService;

class EntityController extends AbstractActionController {
	
	/**
	 * @var \DoctrineEntityGeneratorModule\Service\EntityGeneratorService
	 */
	protected $entityGeneratorService;
	
	public function setEntityGeneratorService(EntityGeneratorService $entityGeneratorService) {
		$this->entityGeneratorService = $entityGeneratorService;
	}
	
	public function indexAction() {
		$view = new ViewModel();
 		$classNames = $this->entityGeneratorService->getEntityList();
		$view->setVariable('classNames', $classNames);
	
		return $view;
	}
	
	public function generateAction(){
		$view = new ViewModel();
		$view->setTemplate('entity/generate');
		if($this->request->isPost()){			
			$options = array(
				'moduleName' => 'Application',
				'entityNamespace' => 'Entity',
				'parentClass' => 'Test'
			);
			$this->entityGeneratorService->init($options);
			$outputList = $this->entityGeneratorService->generate($this->request->getPost());
		}
		
		return $view;
	}

}