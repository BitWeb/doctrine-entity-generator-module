<?php
use Zend\ServiceManager\ServiceManager;

use Zend\Mvc\Controller\ControllerManager;

$entityGeneratorConfiguration = array (
	'generatedEntitiesPath' => array ( //Change this path to generate entities to correct directory
		'path' => realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../data/BitwebEntityGeneratorModule/GeneratedEntities/', 
		'namespace' => 'Entity'
	)
);

$roleManagementConfiguration = array (
	'generatedConfigurationRelativePath' => realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../data/Development/ACL/',	
);

return array (
	'generatedEntitiesPath' => array ( //Change this path to generate entities to correct directory
			'path' => realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../data/BitwebEntityGeneratorModule/GeneratedEntities/',
			'namespace' => 'Entity'
	),
	'router' => array (
		'routes' => include 'routes.config.php',
	),
	'controllers' => array (
		'invokables' => array (
			'BitwebEntityGeneratorModule\Controller\Index' => 'BitwebEntityGeneratorModule\Controller\IndexController', 
		), 
		'factories' => array (
			'BitwebEntityGeneratorModule\Controller\Entity' => function (ControllerManager $cm) {
				$sm = $cm->getServiceLocator();
				$controller = new BitwebEntityGeneratorModule\Controller\EntityController();
				$controller->setEntityService($sm->get('BitwebEntityGeneratorModule\Service\Entity'));
				$controller->setEntityGeneratorService($sm->get('BitwebEntityGeneratorModule\Service\EntityGenerator'));
				
				return $controller;
			},
			'BitwebEntityGeneratorModule\Controller\Role' => function (ControllerManager $cm) {
				$sm = $cm->getServiceLocator();
				$controller = new BitwebEntityGeneratorModule\Controller\RoleController();
				$controller->setRoleService($sm->get('BitwebEntityGeneratorModule\Service\Role'));
			
				return $controller;
			},
			'BitwebEntityGeneratorModule\Controller\RoleGroups' => function (ControllerManager $cm) {
				$sm = $cm->getServiceLocator();
				$controller = new BitwebEntityGeneratorModule\Controller\RoleGroupsController();
				$controller->setRoleGroupsService($sm->get('BitwebEntityGeneratorModule\Service\RoleGroups'));
					
				return $controller;
			}
		)
	), 
	'view_manager' => array (
		'display_not_found_reason' => true, 
		'display_exceptions' => true, 
		'doctype' => 'HTML5', 
		'not_found_template' => 'error/404', 
		'exception_template' => 'error/index', 
		'template_map' => array (
			'layout/dev' => __DIR__ . '/../view/layout/layout.phtml', 
			'index/index' => __DIR__ . '/../view/index/index.phtml', 
			'error/dev/404' => __DIR__ . '/../view/error/404.phtml', 
			'error/dev/index' => __DIR__ . '/../view/error/index.phtml'
		), 
		'template_path_stack' => array (
			__DIR__ . '/../view'
		), 
	), 
	'service_manager' => array (
		'invokables' => array (
			
		),
		'factories' => array (
			'BitwebEntityGeneratorModule\Service\Entity' => function(ServiceManager $sm) use ($entityGeneratorConfiguration) {
				$service = new BitwebEntityGeneratorModule\Service\EntityService();
				$service->setConfiguration($entityGeneratorConfiguration);
				
				return $service;
			},
			'BitwebEntityGeneratorModule\Service\Role' => function(ServiceManager $sm) use ($roleManagementConfiguration) {
				$service = new BitwebEntityGeneratorModule\Service\RoleService();
				$service->setConfiguration($roleManagementConfiguration);
				
				return $service;
			},
			'BitwebEntityGeneratorModule\Service\RoleGroups' => function(ServiceManager $sm) use ($roleManagementConfiguration) {
				$service = new BitwebEntityGeneratorModule\Service\RoleGroupsService();
			
				return $service;
			},
			'BitwebEntityGeneratorModule\Service\EntityGenerator' => function(ServiceManager $sm) use ($entityGeneratorConfiguration) {
				$service = new BitwebEntityGeneratorModule\Service\EntityGeneratorService();
			
				return $service;
			},
			'developmentNavigation' => 'BitwebEntityGeneratorModule\Navigation\DevelopmentNavigationFactory'
		)
	), 
	'navigation' => array (
		'development' => include 'navigation.config.php',
	),
	'development' => array (
		'libs' => array ( //Override those values in the project if your libs are somewhere else
			'jquery' => array (
				'js' => '/js/jquery-min.js',
			),
			'fancybox' => array (
				'js' => '/lib/fancybox/jquery.fancybox-1.3.4.js',
				'css' => '/lib/fancybox/jquery.fancybox-1.3.4.css',
			)
		),
	)
);