<?php
namespace BitwebEntityGeneratorModule;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\EventManager\StaticEventManager;
use Zend\Loader\StandardAutoloader;

class Module implements AutoloaderProviderInterface {

	protected $view;

	protected $viewListener;

	public function init(ModuleManager $moduleManager) {
		$events = StaticEventManager::getInstance();
		$events->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, array ($this, 'onLoad'), 100);
		// 		$events->attach('bootstrap', 'bootstrap', array (
		// 			$this,
		// 			'initializeServiceLayer'
		// 		), 99);
	}

	public function getAutoloaderConfig() {
		return array (
			'Zend\Loader\StandardAutoloader' => array (
			    StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
			)
		);
	}

	// 	public function initializeServiceLayer($e) {
	// 		$app = $e->getParam('application');
	// 		$locator = $app->getLocator();
	// 	}

	public function getConfig() {
		//return include __DIR__ . '/config/module.config.php';
		$configs = array(
				include __DIR__ . '/../../config/module.config.php',
				include __DIR__ . '/../../config/routes.config.php',
		);
		$config = call_user_func_array('array_merge_recursive', $configs);
		//var_dump(array_merge_recursive($configs[0], $configs[1])); die();
		//var_dump($config); die();
		return $config;
	}


	public function onLoad(MvcEvent $event) {
		$this->initializeView($event);
	}

	public function initializeView(MvcEvent $event) {
		$viewModel = $event->getViewModel();
		$viewModel->setTemplate('layout/dev');

		$application = $event->getApplication();

		$basePath = $application->getRequest()->getBasePath();
		$locator = $application->getServiceManager();

		/* @var $renderer \Zend\View\Renderer\PhpRenderer */
		$renderer = $locator->get('Zend\View\Renderer\PhpRenderer');
		//$renderer->plugin('url')->setRouter($application->getRouter());
		$renderer->doctype()->setDoctype('HTML5');
		$renderer->plugin('basePath')->setBasePath($basePath);


		$configuration = $this->getConfig();

		$renderer->headScript()->appendFile($basePath . $configuration['development']['libs']['jquery']['js']);

		$renderer->headScript()->appendFile($basePath . $configuration['development']['libs']['fancybox']['js']);
		$renderer->headLink()->appendStylesheet($basePath . $configuration['development']['libs']['fancybox']['css']);

		$renderer->headScript()->appendFile('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js');
		$renderer->headScript()->appendFile('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js');
		$renderer->headLink()->appendStylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css');
		$renderer->headMeta()->setHttpEquiv('Content-Type', 'text/html; charset=utf-8');
	}
}
