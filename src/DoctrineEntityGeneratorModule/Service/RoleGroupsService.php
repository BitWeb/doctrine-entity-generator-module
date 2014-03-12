<?php
namespace DoctrineEntityGeneratorModule\Service;

use BitwebAcl\Entity\BW\ResourceGroup;

use Zend\Stdlib\Parameters;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Tools\SchemaTool;

use BitwebAcl\Entity\BW;
use DoctrineEntityGeneratorModule\Service\AbstractService;

class RoleGroupsService extends AbstractService {
	
	public function getRoles() {
		$roleRepository = $this->entityManager->getRepository(BW\Role::getClass());
		
		return new ArrayCollection($roleRepository->findAll());
	}
	
	public function getResourceGroups() {
		$resourceGroupRepository = $this->entityManager->getRepository(BW\ResourceGroup::getClass());
	
		return new ArrayCollection($resourceGroupRepository->findAll());
	}
	
	public function getResourceGroupsByIdentificators($identificators) {
		$resourceGroups = new ArrayCollection();
		$resourceGroupRepository = $this->entityManager->getRepository(BW\ResourceGroup::getClass());
		//This is because then we don't need a repository class
		foreach ((array)$identificators as $identificator) {
			$resourceGroup = $resourceGroupRepository->find($identificator);
			if ($resourceGroup != null) {
				$resourceGroups->add($resourceGroup);
			}
		}
	
		return $resourceGroups;
	}
	
	public function getResources() {
		$resourceRepository = $this->entityManager->getRepository(BW\Resource::getClass());
	
		return new ArrayCollection($resourceRepository->findAll());
	}
	
	public function getResourcesByIdentificators($identificators) {
		$resources = new ArrayCollection();
		$resourceRepository = $this->entityManager->getRepository(BW\Resource::getClass());
		//This is because then we don't need a repository class
		foreach ((array)$identificators as $identificator) {
			$resource = $resourceRepository->find($identificator);
			if ($resource != null) {
				$resources->add($resource);
			}
		}
		
		return $resources;
	}
	
	public function getRole($identificator) {
		$roleRepository = $this->entityManager->getRepository(BW\Role::getClass());
		$role = $roleRepository->find($identificator);
	
		return $role;
	}
	
	public function getResource($identificator) {
		$resourceRepository = $this->entityManager->getRepository(BW\Resource::getClass());
		$resource = $resourceRepository->find($identificator);
		
		return $resource;
	}
	
	public function removeResourceFromResourceGroup(BW\Resource $resource, BW\ResourceGroup $resourceGroup) {
		$resourceGroup = $resourceGroup->removeResource($resource);
		
		$this->entityManager->persist($resourceGroup);
		
		return $resourceGroup;
	}
	
	public function getResourceGroup($identificator) {
		$resourceGroupRepository = $this->entityManager->getRepository(BW\ResourceGroup::getClass());
		
		return $resourceGroupRepository->find($identificator);
	}
	
	public function areTablesExisting() {

		return $this->entityManager->getConnection()->getSchemaManager()->tablesExist($this->getTableNames());
	}
	
	public function markAllResourcesObsolete() {
		$resources = $this->getResources();
		foreach ($resources as $resource) { /* @var $resource \BitwebAcl\Entity\BW\Resource */
			$resource->markObsolete();
			$this->entityManager->persist($resource);
		}
	}
	
	public function getResourceByControllerAliasAndClassAndAction($controllerAlias, $controllerClass, $action) {
		$resourceRepository = $this->entityManager->getRepository(BW\Resource::getClass());
		$resource = $resourceRepository->findOneBy(array(
			'alias' => $controllerAlias,
			'controller' => $controllerClass,
			'action' => $action		
		));
		
		return $resource;
	}
	
	public function saveRole(BW\Role $role = null, Parameters $parameters) {
		if ($role == null) {
			$role = new BW\Role($parameters->identificator);
		}
		
		if ($parameters->clearResourceGroups) {
			$role->clearResourceGroups();
		}
		foreach ((array)$parameters->resourceGroups as $resourceGroup) {
			$role->addResourceGroup($resourceGroup);
		}
	
		$this->entityManager->persist($role);
	
		return $role;
	}
	
	public function saveResource(BW\Resource $resource = null, Parameters $parameters) {
		if ($resource == null) {
			$resource = new BW\Resource($parameters->alias, $parameters->controller, $parameters->action);
		}
		$resource->markActive();
		
		$this->entityManager->persist($resource);
		
		return $resource;
	}
	
	public function saveResourceGroup(BW\ResourceGroup $resourceGroup = null, Parameters $parameters) {
		if ($resourceGroup == null) {
			$resourceGroup = new ResourceGroup($parameters->identificator);
		}
		foreach ($parameters->resources as $resource) {
			$resourceGroup->addResource($resource);
		}

		$this->entityManager->persist($resourceGroup);
		
		return $resourceGroup;
	}
	
	public function generateTables() {
		$entityManager = $this->entityManager;
		$schemaTool = new SchemaTool($entityManager);
		
		$entityMetadata = $this->getEntityMetadata();
		
		$schemaTool->createSchema($entityMetadata);
	}
	
	protected function getEntityMetadata() {
		$entityManager = $this->entityManager;
		
		$entityClasses = array (
				BW\Resource::getClass() => true,
				BW\ResourceGroup::getClass() => true,
				BW\Role::getClass() => true,
		);
		$entityClasses = array_map(function($value) use ($entityManager) {
			var_dump($value);
			return $entityManager->getClassMetadata($value);
		}, array_keys($entityClasses));
		
		return $entityClasses;
	}
	
	protected function getTableNames() {
		$metadata = $this->getEntityMetadata();
		$tableNames = array();
		foreach ($metadata as $oneEntityMetadata) { /* @var $oneEntityMetadata \Doctrine\ORM\Mapping\ClassMetadata */
			$tableNames[] = $oneEntityMetadata->getTableName();
		}
		
		return $tableNames;
	}
}