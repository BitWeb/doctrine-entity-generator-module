<?php
namespace DoctrineEntityGeneratorModule\Controller;

use Zend\Stdlib\Parameters;

use Zend\View\Model\ViewModel;

use DoctrineEntityGeneratorModule\Service\RoleGroupsService;

use Doctrine\ORM\Tools\SchemaTool;

use Zend\Mvc\Controller\AbstractActionController;

class RoleGroupsController extends AbstractActionController {
	
	protected $roleGroupsService;
	
	public function setRoleGroupsService(RoleGroupsService $roleGroupsService) {
		$this->roleGroupsService = $roleGroupsService;
	}
	
	public function indexAction() {
		if (!$this->roleGroupsService->areTablesExisting()) {
			
			return $this->redirect()->toRoute('development/query', array (
				'controller' => 'role-groups',
				'action' => 'generate-database'	
			));
		}
		
		$view = new ViewModel();
		$view->roles = $this->roleGroupsService->getRoles();
		$view->resourceGroups = $this->roleGroupsService->getResourceGroups();
		$view->resources = $this->roleGroupsService->getResources();
		
		return $view;
	}
	
	public function generateDatabaseAction() {
		$areTablesExisting = $this->roleGroupsService->areTablesExisting();
		if ($this->params()->fromQuery('generate') && !$areTablesExisting) {
			$this->roleGroupsService->generateTables();
		}
		
		$areTablesExisting = $this->roleGroupsService->areTablesExisting();
		$view = new ViewModel();
		$view->areTablesExisting = $areTablesExisting;
		
		return $view;
	}
	
	public function editAction() {
		
	}
	
	public function addResourceGroupToRoleAction() {
		$view = new ViewModel();
		
		$role = $this->roleGroupsService->getRole($this->params()->fromQuery('role'));
		
		if ($this->request->isPost()) {
			$resourceGroups = $this->roleGroupsService->getResourceGroupsByIdentificators($this->params()->fromPost('resourceGroups'));
			$parameters = new Parameters(array(
				'resourceGroups' => $resourceGroups->toArray(),
				'clearResourceGroups' => true,	
			));
			
			$role = $this->roleGroupsService->saveRole($role, $parameters);
			
			return $this->backToMain('tabRoles');
		}
		
		$view->role = $role;
		$view->resourceGroups = $this->roleGroupsService->getResourceGroups();
		
		return $view;
	}
	
	public function addResourceGroupAction() {
		$view = new ViewModel();
		
		if ($this->request->isPost()) {
			$identificator = $this->params()->fromPost('groupIdentificator');
			$resourceGroup = $this->roleGroupsService->getResourceGroup($identificator);
			if ($resourceGroup == null) {
				$parameters = new Parameters(array(
					'identificator' => $identificator,		
				));
				$this->roleGroupsService->saveResourceGroup($resourceGroup, $parameters);
				
				return $this->backToMain('tabResourceGroups');
			}
			$view->groupIdentificator = $identificator;
			$view->alreadyExisting = true;
		}
		
		
		return $view;
	}
	
	public function addRoleAction() {
		$view = new ViewModel();
	
		if ($this->request->isPost()) {
			$identificator = $this->params()->fromPost('roleIdentificator');
			$role = $this->roleGroupsService->getRole($identificator);
			if ($role == null) {
				$parameters = new Parameters(array(
					'identificator' => $identificator,
				));
				$this->roleGroupsService->saveRole($role, $parameters);
	
				return $this->backToMain('tabRoles');
			}
			$view->roleIdentificator = $identificator;
			$view->alreadyExisting = true;
		}
	
	
		return $view;
	}
	
	public function addResourcesToGroupAction() {
		$view = new ViewModel();
		
		$groupIdenfiticator = $this->params()->fromPost('groupIdentificator'); 
		$resources = $this->roleGroupsService->getResourcesByIdentificators(array_keys($this->params()->fromPost('resources')));
		
		if ($groupIdenfiticator != null) {
			$group = $this->roleGroupsService->getResourceGroup($groupIdenfiticator);

			$parameters = new Parameters(array(
				'resources' => $resources,
				'identificator' => $groupIdenfiticator,		
			));
			$this->roleGroupsService->saveResourceGroup($group, $parameters);
			
			return $this->backToMain('tabResourceGroups');
		}

		$view->groups = $this->roleGroupsService->getResourceGroups();
		$view->resources = $resources;
		
		return $view;
	}
	
	public function updateResourcesAction() {
		/* @var $roleService \Development\Service\RoleService */
		$roleService = $this->serviceLocator->get('Development\Service\Role');
		
		$this->roleGroupsService->markAllResourcesObsolete();
		$controllers = $roleService->getControllersData();
		foreach ($controllers['controllers'] as $controller) {
			$actions = $roleService->getActionsFromController($controller['controller']['class']);
			foreach ($actions as $action) {
				$resource = $this->roleGroupsService->getResourceByControllerAliasAndClassAndAction($controller['controller']['alias'], $controller['controller']['class'], $action);
				$parameters = new Parameters(array(
					'alias' => $controller['controller']['alias'],
					'controller' => $controller['controller']['class'],
					'action' => $action,	
				));
				$this->roleGroupsService->saveResource($resource, $parameters);
			}	
		}

		return $this->backToMain('tabResources');
	}
	
	public function removeResourceFromGroupAction() {
		$resource =  $this->roleGroupsService->getResource($this->params()->fromQuery('resource'));
		$resourceGroup =  $this->roleGroupsService->getResourceGroup($this->params()->fromQuery('resourceGroup'));
		
		if ($this->request->isPost()) {
			$this->roleGroupsService->removeResourceFromResourceGroup($resource, $resourceGroup);
			
			return $this->backToMain('tabResourceGroups');
		}
		
		$view = new ViewModel();
		
		$view->resource = $resource;
		$view->resourceGroup = $resourceGroup;
		
		return $view;
	}
	
	protected function backToMain($anchor = null) {
		
		//TODO Make this close Fancybox if we're in iframe
		return $this->redirect()->toRoute('development/anchor', array_filter(array(
			'controller' => 'role-groups',
			'anchor' => $anchor,
		)));
	}
}