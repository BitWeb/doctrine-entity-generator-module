<?php
namespace DoctrineEntityGeneratorModule\Service;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Log\LoggerInterface;
use Zend\Config\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractService implements ObjectManagerAwareInterface, ServiceLocatorAwareInterface  {
	
	/**
	 * Enity Manager
	 *
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $entityManager;
	
	/**
	 * @var \Zend\ServiceManager\ServiceManager
	 */
	protected $locator;
	
	/**
	 * @var \Zend\Config\Config
	 */
	protected $config;
	
	public function setObjectManager(ObjectManager $objectManager) {
		$this->entityManager = $objectManager;
	}
	
	public function getObjectManager() {
		return $this->entityManager;
	}
	
	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->locator = $serviceLocator;
	}
	
	/**
	 * @return ServiceLocatorInterface
	*/
	public function getServiceLocator() {
		
		return $this->locator;
	}
	
}
