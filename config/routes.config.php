<?php
return array (
	'development' => array (
		'type' => 'Zend\Mvc\Router\Http\Segment', 
		'options' => array (
			'route' => '/entity-generator[/:controller[/:action]]', 
			'defaults' => array (
				'__NAMESPACE__' => 'DoctrineEntityGeneratorModule\Controller', 
				'controller' => 'DoctrineEntityGeneratorModule\Controller\Entity', 
				'action' => 'index', 
			)
		)
	)
);