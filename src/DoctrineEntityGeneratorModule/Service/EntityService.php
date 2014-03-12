<?php
namespace DoctrineEntityGeneratorModule\Service;

use DoctrineEntityGeneratorModule\Service\AbstractService;

class EntityService extends AbstractService {
	
	/**
	 * EntityManager
	 * @var \Doctrine\ORM\EntityManager
	
	private $em;
	private $driver; //Database driver
	private $cmf; //Class metadata factory
	private $generatedEntityOutputDirectory; //Dir where to output new entity classes
	private $entityNamespace; //Entity folder
	
	 */
	
	protected $config;
	
	public function setConfiguration($configuration) {
		$this->config = $configuration;
	}
	
	public function init() {
		$this->_helper->disableView();
		if(APPLICATION_ENV != 'development'){
			die('-');
		}
	
		set_include_path(implode(PATH_SEPARATOR, array(
				realpath(__DIR__ . '/dev/GeneratedEntities'),
				get_include_path(),
		)));
	
		
		//$this->config = $this->locator->get('Config');
		//$this->em = $this->_helper->getEntityManager();
	}
	
	private function initEntityGenerator() {
		$this->entityNamespace = $this->config['generatedEntitiesPath']['namespace'];
		$this->generatedEntityOutputDirectory = $this->config['generatedEntitiesPath']['path'];
		
		/*$this->entityManager->getConfiguration()->newDefaultAnnotationDriver('data/dev/GeneratedEntities');
		$config = new \Doctrine\ORM\Configuration();
		$this->entityManager->getConfiguration()->setMetadataDriverImpl($config->newDefaultAnnotationDriver('data/dev/GeneratedEntities'));*/  
		$this->driver = new \Doctrine\ORM\Mapping\Driver\DatabaseDriver($this->entityManager->getConnection()->getSchemaManager());
		$this->entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');
		$this->entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
		//$this->entityManager->getConnection()->getDatabasePlatform()->
	}

	
	public function getEntityList() {
		$this->initEntityGenerator();
		$classNames = $this->driver->getAllClassNames();

		$result = array();
		foreach ($classNames as $className) {
			try{
			$metadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
			$this->driver->loadMetadataForClass($className, $metadata);
			//$metadata = $this->fixMetadata($metadata);
			
			
			$result[] = array(
				'entityName' => $metadata->name,
				'className' => $className,
				'path' => $this->generatedEntityOutputDirectory,
				'namespace' => $this->entityNamespace,
			);
			}catch(\Exception $e){
				//var_dump($e);
				echo 'could not parse '.$className.'<br>';
				echo '<div class="error">' . $e->getMessage() . '</div>';
				echo '<div>Possible error: Doctrine does not support primary keys as forgein keys: <a href="http://stackoverflow.com/questions/7045535/symfony2-doctrine2-mapping-from-existing-database-exception">stackOverflow</a><div>';
				continue;
			}
		}
		
		return $result;
	}
	
	public function fixName($name, $nameTo = null) {
		if ($nameTo != null) {
			$name = $nameTo;
		}
		if($name{strlen($name)-1} == 's') {
			$name = substr($name, 0, strlen($name)-1);
		}
		
		return $name;
	}
	
	public function fixMetadata($metadata, $moduleName = 'Application') {

		$metadata->name = $this->fixName($metadata->name); //, $metadata->table['name']
		
		foreach ($metadata->associationMappings as &$associationMappingElement) {
			$className = $associationMappingElement['targetEntity'];
			$targetMetadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
			$this->driver->loadMetadataForClass($className, $targetMetadata);
			$associationMappingElement['targetEntity'] = $moduleName . '\Entity\\' . $this->fixName($targetMetadata->name);
			
			$className = $associationMappingElement['sourceEntity'];
			$sourceMetadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
			$this->driver->loadMetadataForClass($className, $sourceMetadata);
			$associationMappingElement['sourceEntity'] = $moduleName . '\Entity\\' . $this->fixName($sourceMetadata->name);			
		}
		
		//$metadata->name = 'dfg';
		//$metadata->name = $this->entityNamespace . '\\' . $metadata->name;
		
		return $metadata;
	}
	
	public function generateEntities($parameters) {
		$classNames = $parameters->generate;
		$parentClassName = $parameters->parentClassName;
		$moduleName = ($parameters->moduleName != null)?$parameters->moduleName:'Application';
		$this->initEntityGenerator();
		$outputList = array();
		$metadatas = array();
		
		foreach($classNames as $className){

			if(in_array($className, $this->driver->getAllClassNames())){
				
				$metadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($className);
				$this->driver->loadMetadataForClass($className, $metadata);
				//$metadata =  $this->cmf->getMetadataFor($className);
				$metadata = $this->fixMetadata($metadata, $moduleName);

				//var_dump($metadata);die();
				$outputList[] = array(
					'outputDirectory' => $this->generatedEntityOutputDirectory,
					'entityNamespace' => $this->entityNamespace,
					'entityName' => $className,
					'className' => $className,
				);
				
				$metadata->name = $this->entityNamespace . '\\' . $className;
				$metadatas[] = $metadata;
			}
		
		}
		if (count($metadatas) > 0) {
		
			$generator = new \Doctrine\ORM\Tools\EntityGenerator();
			$generator->setGenerateAnnotations(true);
			$generator->setBackupExisting(false);
			$generator->setGenerateStubMethods(true);
			$generator->setRegenerateEntityIfExists(true);
			$generator->setUpdateEntityIfExists(false);
			if($parentClassName != null){
				if(!file_exists($this->generatedEntityOutputDirectory . '/' . $parentClassName . '.php')){
					$parentMetadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo($parentClassName);
					$parentMetadata->name = $this->entityNamespace . '\\' . $className;
					$generator->writeEntityClass($parentMetadata, $this->generatedEntityOutputDirectory);
				}
				
				/* @TODO REMOVE THIS ! */
				//$generator->setClassToExtend('\BitwebExtension\Entity\AbstractEntity');
			}
			$generator->generate($metadatas,  $this->generatedEntityOutputDirectory);
			
		}
		
		return $outputList;
	}

}