<?php
namespace DoctrineEntityGeneratorModule\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class DevelopmentNavigationFactory extends DefaultNavigationFactory {
	
	public function getName() {
		
		return 'development';
	}
}