doctrine-entity-generator-module
================================

Doctrine entity generator module for Zend Framework 2.

#### Adding module
```sh
php composer.phar require bitweb/doctrine-entity-generator-module
# (When asked for a version, type `1.*`)
```
or add following to `composer.json`
```json
"require": {
  "bitweb/doctrine-entity-generator-module": "1.*",
}
```
Loading module in `APP_ROOT/config/application.config.php`:
```php
   'modules' => array(
    	'DoctrineModule',
    	'DoctrineORMModule',
      'Application',
    	'DoctrineEntityGeneratorModule'
    ),

```

This module requires adding initializer into `module.config.php` for ServiceManager:
```php
	'service_manager' => array(
        'initializers' => array (
        	function ($service, $sm) {
        		if ($service instanceof ObjectManagerAwareInterface) {
        			$service->setObjectManager($sm->get('doctrine.entitymanager.orm_default'));
        		}
        	}
        ),
    )
```
This is needed for the module to use project's Object manager.

Now, web interface can be accessed `http://example.com/yourproject/dev` for generating entities.

