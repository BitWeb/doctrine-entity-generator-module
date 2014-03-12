<?php
return array (
	'development' => array (
		'type' => 'Zend\Mvc\Router\Http\Segment', 
		'options' => array (
			'route' => '/dev[/:controller[/:action]]', 
			'defaults' => array (
				'__NAMESPACE__' => 'DoctrineEntityGeneratorModule\Controller', 
				'controller' => 'DoctrineEntityGeneratorModule\Controller\Entity', 
				'action' => 'index', 
			)
		), 
// 		'may_terminate' => true, 
// 		'child_routes' => array (
// 			'query' => array (
// 				'type' => 'Zend\Mvc\Router\Http\Query', 
// 				'may_terminate' => true, 
// 				'child_routes' => array(
					
// 				)
// 			), 
// 			'anchor' => array (
// 				'type' => 'Zend\Mvc\Router\Http\Segment', 
// 				'options' => array (
// 					'route' => '[#:anchor]', 
// 					'defaults' => array (
// 						'anchor' => null
// 					)
// 				)
// 			)
// 		)
	)
	,
	
	/*'development-index' => array (
			'type' => 'Zend\Mvc\Router\Http\Segment',
			'options' => array (
					'route' => '/dev[/:controller[/:action]]',
					'defaults' => array (
							'controller' => 'dev-index',
							'action' => 'index'
					)
			)
	),
	
	'development-index-lit' => array (
			'type' => 'Zend\Mvc\Router\Http\Regex',
			'options' => array (
					'regex' => '(/dev|/dev/index|/dev/index/index)',
					'defaults' => array (
							'controller' => 'dev-index',
							'action' => 'index'
					),
					'spec' => '/dev',
			)
	),*/
	
	/*
	 * 'development-index' => array (
			'type' => 'Zend\Mvc\Router\Http\Segment',
			'options' => array (
					'route' => '[/:controller[/:action]]',
					'defaults' => array (
							'controller' => 'dev-index',
							'action' => 'index'
					)
			)
	),
	 */
	/*
	'development-index' => array (
			'type' => 'Zend\Mvc\Router\Http\Part',
			'options' => array (
					'route' => '/dev',
					'defaults' => array (
							'controller' => 'development-index',
							'action' => 'index'
					),
					'child_routes'  => array(
						'development-index-test' => array(
							'type'    => 'Zend\Mvc\Router\Http\Literal',
							'options' => array(
								'route'    => '/dev/test',
								'defaults' => array(
									'action' => 'test',
								),
							),
						),
					),
			)
		)
	)*/
);