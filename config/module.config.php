<?php
use Zend\ServiceManager\ServiceManager;

use Zend\Mvc\Controller\ControllerManager;

return array (
	'generatedEntitiesPath' => array ( //Change this path to generate entities to correct directory
		'path' => realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../data/DoctrineEntityGeneratorModule/GeneratedEntities/',
		'namespace' => 'Entity'
	),
	'router' => array (
		'routes' => include 'routes.config.php',
	),
	'controllers' => array (
		'invokables' => array (
			'DoctrineEntityGeneratorModule\Controller\Index' => 'DoctrineEntityGeneratorModule\Controller\IndexController', 
		), 
		'factories' => array (
			'DoctrineEntityGeneratorModule\Controller\Entity' => function (ControllerManager $cm) {
				$sm = $cm->getServiceLocator();
				$controller = new DoctrineEntityGeneratorModule\Controller\EntityController();
				$controller->setEntityGeneratorService($sm->get('DoctrineEntityGeneratorModule\Service\EntityGenerator'));
				
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
			'DoctrineEntityGeneratorModule\Service\EntityGenerator' => function(ServiceManager $sm) {
				$service = new DoctrineEntityGeneratorModule\Service\EntityGeneratorService();
			
				return $service;
			},
			'entityGeneratorNavigation' => 'DoctrineEntityGeneratorModule\Navigation\EntityGeneratorNavigationFactory'
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