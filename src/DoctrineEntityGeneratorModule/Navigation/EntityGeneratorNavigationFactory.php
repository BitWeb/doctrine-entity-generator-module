<?php
namespace DoctrineEntityGeneratorModule\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class EntityGeneratorNavigationFactory extends DefaultNavigationFactory {
	
	public function getName() {
		
		return 'entityGeneratorNavigation';
	}
}