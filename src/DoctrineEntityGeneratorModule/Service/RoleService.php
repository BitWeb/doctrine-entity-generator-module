<?php
namespace DoctrineEntityGeneratorModule\Service;

use BitwebExtension\Acl\Acl;
use DoctrineEntityGeneratorModule\Service\AbstractService;

class RoleService extends AbstractService {
	
	const ACL_CONFIG_FILE_NAME = 'acl.config.php';
	const ACL_TYPE_GENERATED = 'generated';
	const ACL_TYPE_PROJECT = 'project';
	const ACL_TYPE_NONE = 'none';
	
	protected $configuration;
	protected $usedConfigurationType;
	
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
	}
	
	public function getUsedConfigurationType() {
		if ($this->usedConfigurationType === null) {
			$this->getAclConfig();
		}
		
		return $this->usedConfigurationType;
	}
	
	public function getAclConfig() {
		if (file_exists($this->getGeneratedAclConfigFilePath())) {
			$this->usedConfigurationType = self::ACL_TYPE_GENERATED;

			return include $this->getGeneratedAclConfigFilePath();
		}
		$config = $this->getConfig();
		
		if (isset($config['acl'])) {
			$this->usedConfigurationType = self::ACL_TYPE_PROJECT;

			return $config['acl']->toArray();
		}
		$this->usedConfigurationType = self::ACL_TYPE_NONE;
		
		return array();
	}
	
	public function getRoles() {
		$aclConfig = $this->getAclConfig();
		$acl = new Acl($aclConfig);
		
		return $acl->getRoles();
	}
	
	public function getControllersData() {
		$controllerNames = $this->locator->get('ControllerLoader')->getCanonicalNames();
		
		$controllers = array();
		
		foreach ($controllerNames as $controllerName) {
			$controllers[] = array (
				'instance' => $this->locator->get('ControllerLoader')->get($controllerName),
				'alias' => $controllerName,
			);
		}
		usort($controllers, function($controllerA, $controllerB) {
				
			return strnatcmp($controllerA['alias'], $controllerB['alias']);
		});
		
		$aclConfig = $this->getAclConfig();
		$acl = new Acl($aclConfig);
		/*if (!isset($config['acl']['type']) || $config['acl']['type'] == 'config') {
			$acl = new Acl($config['acl']->toArray());
		}*/
		
		$controllersData = array(
			'controllers' => array(),
			'roles' => $acl->getRoles(),		
		);
		
		foreach ($controllers as $controllerEntry) { //Entry is data in the ControllerLoader
			$controller = $controllerEntry['instance'];
			$controllerData = array(
				'class' => get_class($controller),
				'alias' => $controllerEntry['alias'],
				'missing' => false,
			);
			$actionsData = array();
			
			$controllerClass = get_class($controller);
			$roles = $acl->getRoles();

			$allowedList = $aclConfig['acl']['resources']['allow'];
			$denyedList =  $aclConfig['acl']['resources']['deny'];

			$resource = $this->getResourceNameFromControllerClass($controllerClass);
			$module = $this->getModuleNameFromControllerClass($controllerClass);
			$controllerName = $this->getParsedControllerName($this->getControllerNameFromControllerClass($controllerClass));
			$controllerData['module'] = $module;

			$controllerData['actions'] = array();
			foreach ($allowedList[$module] as $listResource => $listPrivileges) {
				$listResourceName = $this->getParsedControllerName($listResource);
				if ($listResourceName == $controllerName) {
					$controllerData['resourceName'] = $listResource;
					$controllerData['actions']['allowed'] = $listPrivileges;
					foreach ((array)$listPrivileges as $action => $privileges) {
						$actionsData[] = $action;
					}
				}
			}
			foreach ($denyedList[$module] as $listResource => $listPrivileges) {
				$listResourceName = $this->getParsedControllerName($listResource);
				if ($listResourceName == $controllerName) {
					$controllerData['resourceName'] = $listResource;
					$controllerData['actions']['denyed'] = $listPrivileges;
					foreach ((array)$listPrivileges as $action => $privileges) {
						$actionsData[] = $action;
					}
				}
			}
			$controllersData['controllers'][$controllerEntry['alias']] = array (
				'controller' => $controllerData,
				'actions' => array_unique($actionsData),
			);
		}
		
		return $controllersData;
	}
	
	public function getActionsFromController($controllerClass) {
		$reflection = new \ReflectionClass($controllerClass);
		$methods = $reflection->getMethods();
		$actions = array();
		foreach ($methods as $method) {
			$match = null;
			if (preg_match('/(.*)Action$/', $method->getName(), $match) && $method->class === $controllerClass) { //
				$action = preg_replace('/Action$/', '', preg_replace('/[A-Z]{1}/e', '\'-\' . strtolower($0)' , $match));
				$actions[] = $action[1];
			}
		}
		
		return $actions;
	}
	
	public function addController($module, $controllerClass) {
		$aclConfig = $this->getAclConfig();
		
		$controllerResourceName = $this->getSimpleControllerNameFromControllerClass($controllerClass);
		$aclConfig['acl']['resources']['allow'][$module][$controllerResourceName] = array (
			'all' => array(),		
		);
		
		return $this->save($aclConfig);
	}
	
	public function addAction($module, $controller, $action, $roles = array()) {
		$aclConfig = $this->getAclConfig();
		
		$aclConfig['acl']['resources']['allow'][$module][$controller][$action] = $roles;
		
		return $this->save($aclConfig);
	}
	
	public function removeAction($module, $controller, $action) {
		$aclConfig = $this->getAclConfig();
		
		unset($aclConfig['acl']['resources']['allow'][$module][$controller][$action]);
		
		return $this->save($aclConfig);
	}
	
	public function addRole($module, $controller, $action, $list, $role) {
		$aclConfig = $this->getAclConfig();
		
		$privileges = (array)$aclConfig['acl']['resources'][$list][$module][$controller][$action];
		array_push($privileges, $role);
		sort($privileges);
		$aclConfig['acl']['resources'][$list][$module][$controller][$action] = array_unique($privileges);
		
		return $this->save($aclConfig);
	}
	
	public function removeRole($module, $controller, $action, $list, $role) {
		$aclConfig = $this->getAclConfig();
		
		$index = array_search($role, $aclConfig['acl']['resources'][$list][$module][$controller][$action]);
		if ($index !== false) {
			unset($aclConfig['acl']['resources'][$list][$module][$controller][$action][$index]);
		}
		
		return $this->save($aclConfig);
	}
	
	protected function save($aclConfig) {
		$config = $this->getConfig();
		$classReplacements = array();
		if (isset($config['acl']['roleMapping'])) {
			$useStatements = array();
			$useStatements = '';
			foreach ($config['acl']['roleMapping']->toArray() as $constantClass => $constantValue) {
				$constantClassSplit = explode('\\', strrev($constantClass), 2);
				$constantClassSplit = array_map(strrev, $constantClassSplit);
				$constantClass = substr($constantClass, 0, strpos($constantClass, '::'));
				$useStatements[$constantClass] = true;
				$classReplacements[' => ' . $constantClassSplit[0]] = ' => \'' . $constantValue . '\''; 
			}
		}
		$useStatements = implode('', array_map(function($constantClass) {
			
			return sprintf('use %1$s;%2$s', $constantClass, PHP_EOL);
		}, array_keys($useStatements)));
		
		if (!file_exists($this->configuration['generatedConfigurationRelativePath'])) {
			mkdir($this->configuration['generatedConfigurationRelativePath']);
			chmod($this->configuration['generatedConfigurationRelativePath']);
		}
		
		$template = '<?php' . PHP_EOL . 
						'%2$s' . PHP_EOL .
						'return %1$s; ' . PHP_EOL .	
					'?>';
		
		/*$aclConfig = array (
			'acl' => $aclConfig		
		);*/
		$content = sprintf($template, var_export($aclConfig, true), $useStatements);
		$content = str_replace(array_values($classReplacements), array_keys($classReplacements), $content);
		$content = preg_replace('/(^  )|([\t]*  )/m', "\t", $content);
		$content = preg_replace('/[\d] => /m', '', $content);
		
		return file_put_contents($this->configuration['generatedConfigurationRelativePath'] . 'acl.config.php', $content);
	}
	
	protected function getResourceNameFromControllerClass($controllerClass) {
		$controllerClass = $this->getSimpleControllerNameFromControllerClass($controllerClass);
		
		$controllerClassSplit = explode('\\', $controllerClass, 3);
		$controllerClassSplit[2] = $this->getParsedControllerName($controllerClassSplit[2]);
		
		return implode('\\', $controllerClassSplit);
	}
	
	protected function getParsedControllerName($string) {
		
		return str_replace(' ', '', ucwords(str_replace(array ('\\', '-'), ' ', $string)));
	}
	
	protected function getSimpleControllerNameFromControllerClass($controllerClass) {
		
		return $this->getControllerNameFromControllerClass(substr($controllerClass, 0, strripos($controllerClass, 'Controller')));
	}
	
	protected function getModuleNameFromControllerClass($controllerClass) {
		
		return substr($controllerClass, 0, stripos($controllerClass, '\\'));
	}
	
	protected function getControllerNameFromControllerClass($controllerClass) {
		$withoutModuleName = substr($controllerClass, strpos($controllerClass, 'Controller') + strlen('Controller') + 1);
		$withoutControllerSuffix = (strstr($withoutModuleName, 'Controller')) ? substr($withoutModuleName, 0, stripos($withoutModuleName, 'Controller')) : $withoutModuleName;
		
		return $withoutControllerSuffix;
	}

	protected function getGeneratedAclConfigFilePath() {
	
		return $this->configuration['generatedConfigurationRelativePath'] . self::ACL_CONFIG_FILE_NAME;
	}
}