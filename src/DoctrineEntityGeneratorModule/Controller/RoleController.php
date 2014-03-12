<?php
namespace DoctrineEntityGeneratorModule\Controller;

use DoctrineEntityGeneratorModule\Service\RoleService;

use Zend\View\Model\ViewModel;

use Zend\Mvc\Controller\AbstractActionController;

class RoleController extends AbstractActionController {
	
	protected $roleService;
	
	public function setRoleService(RoleService $roleService) {
		$this->roleService = $roleService;
	}
	
	public function indexAction() {
		$view = new ViewModel();
		
		//var_dump($this->serviceLocator->getCanonicalNames());
		
		//$view->controllers = $this->serviceLocator->get('ControllerLoader')->getRegisteredServices();
		$aclData = $this->roleService->getControllersData();
		
		$configuration = $this->serviceLocator->get('Configuration');
		
		$view->isRoleMappingDefined = isset($configuration['acl']['roleMapping']);
		$view->controllersData = $aclData['controllers'];
		$view->roles = $aclData['roles'];
		$view->usedAclConfiguration = $this->roleService->getUsedConfigurationType();
		
		return $view;
	}
	
	public function addActionAction() {
		$view = new ViewModel();
		$view->setTemplate('development/role/index/add-action');
		$this->layout()->setTemplate('layout/iframe-layout');
		
		$moduleName = $this->params()->fromQuery('moduleName');
		$controllerClass = $this->params()->fromQuery('controllerClass');
		$controllerResourceName = $this->params()->fromQuery('controllerResourceName');
		$actionNames = $this->params()->fromPost('actionNames');
		
		$actions = $this->roleService->getActionsFromController($controllerClass);
		$view->actions = $actions;

		if ($moduleName != null && $controllerResourceName != null && $actionNames != null) {
			$isSaved = true;
			foreach ($actionNames as $actionName) {
				$isSaved &= $this->roleService->addAction($moduleName, $controllerResourceName, $actionName);
			}
			if ($isSaved !== false) {
				$view->setTemplate('development/partial/popup-iframe-refresh');
				$view->hash = $controllerClass;
				$view->selfUrl = '/dev/role';
			}
		}
		
		return $view;
	}
	
	public function removeActionAction() {
		$view = new ViewModel();
		$view->setTemplate('development/role/index/remove-action');
		$this->layout()->setTemplate('layout/iframe-layout');
		
		$moduleName = $this->params()->fromQuery('moduleName');
		$controllerClass = $this->params()->fromQuery('controllerClass');
		$controllerResourceName = $this->params()->fromQuery('controllerResourceName');
		$actionName = $this->params()->fromQuery('actionName');
		$confirm = $this->params()->fromPost('confirm');

		if ($confirm && $moduleName != null && $controllerResourceName != null) {
			$isSaved = $this->roleService->removeAction($moduleName, $controllerResourceName, $actionName);
			if ($isSaved !== false) {
				$view->setTemplate('development/partial/popup-iframe-refresh');
				$view->hash = $controllerClass;
				$view->selfUrl = '/dev/role';
			}
		}
		
		return $view;
	}
	
	public function addRoleAction() {
		$view = new ViewModel();
		$view->setTemplate('development/role/index/add-role');
		$this->layout()->setTemplate('layout/iframe-layout');
	
		$moduleName = $this->params()->fromQuery('moduleName');
		$controllerClass = $this->params()->fromQuery('controllerClass');
		$controllerResourceName = $this->params()->fromQuery('controllerResourceName');
		$actionName = $this->params()->fromQuery('actionName');
		$list = $this->params()->fromQuery('list');
		$roles = $this->params()->fromPost('roles');
	
		if ($moduleName != null && $controllerResourceName != null && $actionName != null && $list != null && $roles != null) {
			$isSaved = true;
			foreach ((array)$roles as $role) {
				$isSaved &= $this->roleService->addRole($moduleName, $controllerResourceName, $actionName, $list, $role);
			}
			if ($isSaved !== false) {
				$view->setTemplate('development/partial/popup-iframe-refresh');
				$view->hash = $controllerClass;
				$view->selfUrl = '/dev/role';
			}
		}
		
		$view->roles = $this->roleService->getRoles();
	
		return $view;
	}
	
	public function removeRoleAction() {
		$view = new ViewModel();
		$view->setTemplate('development/role/index/remove-role');
		$this->layout()->setTemplate('layout/iframe-layout');
	
		$moduleName = $this->params()->fromQuery('moduleName');
		$controllerClass = $this->params()->fromQuery('controllerClass');
		$controllerResourceName = $this->params()->fromQuery('controllerResourceName');
		$actionName = $this->params()->fromQuery('actionName');
		$role = $this->params()->fromQuery('role');
		$list = $this->params()->fromQuery('list');
		$confirm = $this->params()->fromPost('confirm');
	
		if ($confirm && $moduleName != null && $controllerResourceName != null && $role != null) {
			$isSaved = $this->roleService->removeRole($moduleName, $controllerResourceName, $actionName, $list, $role);
			if ($isSaved !== false) {
				$view->setTemplate('development/partial/popup-iframe-refresh');
				$view->hash = $controllerClass;
				$view->selfUrl = '/dev/role';
			}
		}
	
		return $view;
	}
	
	public function addControllerAction() {
		
		$moduleName = $this->params()->fromQuery('moduleName');
		$controllerClass = $this->params()->fromQuery('controllerClass');
		$controllerResourceName = $this->params()->fromQuery('controllerResourceName');
		
		if ($moduleName != null && $controllerClass != null) {
			$this->roleService->addController($moduleName, $controllerClass);
		}

		return $this->redirect()->toRoute('development', array(
			'action' => 'index',		
		), array(), true);
	}
}