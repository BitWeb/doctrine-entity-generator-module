<?php
namespace DoctrineEntityGeneratorModule\Service;

class EntityGeneratorService extends AbstractService {
	
	/**
	 * @var \Doctrine\ORM\Tools\EntityGenerator
	 */
	private $generator;
	
	/**
	 * @var \Doctrine\ORM\Mapping\Driver\DatabaseDriver
	 */
	private $driver;
	
	/**
	 * Should be in form "\<Modulename>\<EntityNamespace>"
	 * 
	 * @var string
	 */
	private $entityNamespace;
	
	/**
	 * @var string
	 */
	private $generatedEntityOutputDirectory;
	
	/**
	 * @var string
	 */
	private $moduleName = 'Application';
	
	/**
	 * Superclass which all of the generated entities will extend, e.g.,
	 * \BitWebExtension\Entity\AbstractEntity
	 * 
	 * @var string
	 */
	private $parentClassName;
	
	/**
	 * All class names retrieved by database driver
	 * 
	 * @var array
	 */
	private $allClassNames;
	
	/**
	 * Class names of entities that are generated
	 * 
	 * @var array
	 */
	private $classNames;
	
	/**
	 * If this option enabled,
	 * each of these classes should be placed into sub-namespace, 
	 * in case class names have common prefix, which is an existing separate class name,
	 * For example,
	 * Foo, FooBar, FooOther;
	 * generated classes:
	 * Foo, Foo\Bar, and Foo\Other
	 * 
	 * @var boolean
	 */
	private $isHierarchicalSeparation = false;
	
	/**
	 * @var array
	 */
	private $commonClassNamePrefixes = array();
	
	/**
	 * Needs to be initialized first
	 * 
	 * @var boolean
	 */
	private $isInitialized = false;
	
	/**
	 * @var array
	 */
	private $namespacedClassNames = array();
	
	private $options = array();

	public function getEntityList() {
		$this->initEntityGenerator();
		
		$result = array();
		
		$classNames = $this->driver->getAllClassNames();
		foreach($classNames as $className) {
			try {
				$metadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
				$this->driver->loadMetadataForClass($className, $metadata);
				$result[] = array(
					'entityName' => $metadata->name,
					'className' => $className,
					'path' => $this->generatedEntityOutputDirectory,
					'namespace' => $this->entityNamespace,
				);
			} catch(\Exception $e){
				//var_dump($e);
				echo 'could not parse '.$className.'<br>';
				echo '<div class="error">' . $e->getMessage() . '</div>';
				echo '<div>Possible error: Doctrine does not support primary keys as forgein keys: <a href="http://stackoverflow.com/questions/7045535/symfony2-doctrine2-mapping-from-existing-database-exception">stackOverflow</a><div>';
				continue;
			}
		}
		
		return $result;
	}
	
	protected function initEntityGenerator() {
		$this->driver = new \Doctrine\ORM\Mapping\Driver\DatabaseDriver($this->entityManager->getConnection()->getSchemaManager());
		$this->generator = new \Doctrine\ORM\Tools\EntityGenerator();
		$this->entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');
		$this->entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
	}
	
	/**
	 * @param array $options
	 * @return void
	 */
	public function init($options) {
		if ($this->isInitialized) {
			return;
		}
		$this->initEntityGenerator();
		$this->setOptions($options);
		
	//	$this->_helper->disableView();
// 		if(APPLICATION_ENV != 'development'){
// 			die('-');
// 		}

		$config = $this->getConfig()->toArray();
// 		var_dump($config);die();
		$this->generatedEntityOutputDirectory = $config['generatedEntitiesPath']['path'];
// 		echo $this->generatedEntityOutputDirectory . '<br>';
// 		echo __DIR__ . '/dev/GeneratedEntities';
		set_include_path(implode(PATH_SEPARATOR, array(
		realpath(__DIR__ . '/dev/GeneratedEntities'),
		get_include_path(),
		)));
		
		$this->isInitialized = true;
	}
	
	protected function initGenerator() {
		$this->generator->setGenerateAnnotations(true);
		$this->generator->setBackupExisting(false);
		$this->generator->setGenerateStubMethods(true);
		$this->generator->setRegenerateEntityIfExists(true);
		$this->generator->setUpdateEntityIfExists(false);
		if ($this->parentClassName != null) {
			$this->generator->setClassToExtend($this->parentClassName);
		}
		
		return $this->generator;
	}
	
	public function generate($parameters) {
		if (!$this->isInitialized) {
			throw new \RuntimeException(
				'Entity generator needs to be initialized first in order to generate'
			);
		}
		
		$moduleName = $this->moduleName;
		$parentClassName = $this->parentClassName;
		$this->classNames = $parameters->classNames;
		$classNames = $this->classNames;
	
		$outputList = array();
		$metadatas = array();
		
		foreach($classNames as $className){
			if(in_array($className, $this->driver->getAllClassNames())){
				
				$classNameWithNamespace = $this->getNamespacedClassName($className);
				$this->namespacedClassNames[$className] = $classNameWithNamespace;
				
				$realClassNameWithNamespace = $this->composeFullClassName($classNameWithNamespace);
				$realClassName = $this->getClassNameFromNamespacedClassName($classNameWithNamespace);
				$namespace = $this->getNamespaceFromNamespacedClassName($realClassNameWithNamespace, $realClassName);
				
				$metadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
				$this->driver->loadMetadataForClass($className, $metadata);
				$metadata = $this->fixMetadata($metadata);
		
				$outputList[] = array(
					'outputDirectory' => $this->generatedEntityOutputDirectory,
					'entityNamespace' => $this->entityNamespace,
					'entityName' => $className,
					'className' => $realClassName,
				);
				
				$metadata->name = $realClassNameWithNamespace;
				$metadata->namespace = $namespace;
				$metadatas[] = $metadata;
				
				echo $namespace . '\\' . $realClassName . '<br>';
			}
		
		}

		if (count($metadatas) > 0) {
			$this->initGenerator();
			if($this->parentClassName != null){
				if(!file_exists($this->generatedEntityOutputDirectory . '/' . $this->parentClassName . '.php')){
					$parentMetadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($this->parentClassName);
					$parentMetadata->name = $this->composeFullClassName($this->parentClassName);
					$this->generator->writeEntityClass($parentMetadata, $this->generatedEntityOutputDirectory);
					
				}
			}

			$this->generator->generate($metadatas, $this->generatedEntityOutputDirectory);
		}
		
		return $outputList;
	}
	
	/**
	 * @param array $options
	 * @throws Exception\InvalidArgumentException
	 */
	public function setOptions($options) {
		if (!is_array($options)) {
			throw new Exception\InvalidArgumentException(
				'The options argument must be an array'
			);
		}
		
		if (isset($options['moduleName'])) {
			$this->moduleName = $options['moduleName'];
		}
		
// 		if (isset($options['generatedEntityOutputDirectory'])) {
// 			$this->generatedEntityOutputDirectory = $options['generatedEntityOutputDirectory'];
// 		} else {
// 			throw new \InvalidArgumentException('Options value of "generatedEntityOutputDirectory" is required and can\'t be empty');
// 		}
		
		if (isset($options['parentClassName'])) {
			$this->parentClassName = $options['parentClassName'];
		}
		
		if (isset($options['isHierarchicalSeparation'])) {
			$this->isHierarchicalSeparation = (bool)$options['isHierarchicalSeparation'];
		}
		
		if (isset($options['entityNamespace'])) {
			$this->entityNamespace = $options['entityNamespace'];
		} else {
			throw new \InvalidArgumentException('Options value of "entityNamespace" is required and can\'t be empty');
		}
		
		$this->options = $options;
	}
	
	/**
	 * @return array
	 */
	public function getOptions() {
	
		return $this->options;
	}
	
	protected function getNamespacedClassName($namespace, $classNameWithNamespace = '') {
		$string = '';
		$maxSimilarity = 0;
		$subNamespace = '';
	
		foreach($this->classNames as $otherClassName) {
			// Ignore class if class is suffix
			$endsWith = strrpos($namespace, $otherClassName) == (strlen($namespace) - strlen($otherClassName));
			if ($namespace != $otherClassName &&
				$endsWith === false &&
				strstr($namespace, $otherClassName) !== false) {
				
				$similarity = $this->calculateSimilarity($namespace, $otherClassName);
				if ($similarity > $maxSimilarity) {
					$maxSimilarity = $similarity;
					$subNamespace = $otherClassName;
				}
			}
		}
	
		if ($subNamespace !== '') {
			$className = str_replace($subNamespace, '', $namespace);
			if ($classNameWithNamespace != '') {
				$className .= '\\' . $classNameWithNamespace;
			}
			
			return $this->getNamespacedClassName($subNamespace, $className);
		}
	
		$finalClassName = $namespace;
		if ($classNameWithNamespace != '') {
			$finalClassName .= '\\' . $classNameWithNamespace;
		}
	
		return $finalClassName;
	}
	
	protected function getClassNameFromNamespacedClassName($namespacedClassName) {
		$pos = strrpos($namespacedClassName, '\\');
		$className = $namespacedClassName;
		if ($pos !== false) {
			$className = substr($namespacedClassName, $pos+1);
		}
		
		return $className;
	}
	
	protected function getNamespaceFromNamespacedClassName($namespacedClassName, $className) {
		$pos = strrpos($namespacedClassName, $className);
		if ($pos !== false) {
			$className = substr($namespacedClassName, 0, $pos);
		}
		
		/** Remove trailing backslash if one exists */
		$className = $this->removeTrailingBackSlash($className);
		
		return $className;
	}
	
	protected function removeTrailingBackSlash($str) {
		$backSlashPos = strrpos($str, '\\');
		if ($backSlashPos !== false) {
			$str = substr($str, 0, $backSlashPos);
		}
		
		return $str;
	}
	
	protected function fixMetadata($metadata) {
		foreach ($metadata->associationMappings as &$associationMappingElement) {
			$className = $associationMappingElement['targetEntity'];
			$targetMetadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
			$this->driver->loadMetadataForClass($className, $targetMetadata);
			$associationMappingElement['targetEntity'] = $this->composeFullClassName($className);
				
			$className = $associationMappingElement['sourceEntity'];
			$sourceMetadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
			$this->driver->loadMetadataForClass($className, $sourceMetadata);
			$associationMappingElement['sourceEntity'] = $this->composeFullClassName($className);
		}
	
		return $metadata;
	}
	
	/**
	 * Composes full class name with module name and entity namespace.
	 * Should return classname in form
	 * \<Modulename>\<EntityNamespace>\<NamespacedClassName>
	 *
	 * @param string $className
	 * @return string
	 */
	protected function composeFullClassName($className) {
		$namespacedClassName = $className;
		if (isset($this->namespacedClassNames[$className])) {
			$namespacedClassName = $this->namespacedClassNames[$className];
		}
		
		return $this->moduleName . '\\'
			. $this->entityNamespace . '\\'
			. $namespacedClassName;
	}
	
	protected function calculateSimilarity($text1, $text2) {
	
		return similar_text($text1, $text2);
	}
	
	
}