# Dotrine2
This is the Doctrine 2 integrarion with Zend Framework 1.11.x.

# Changes
To change or update this repository code base you must use the development branch. To have your merges  and trigger a pull request when you done.

# Usage & Implementation
Checkout the following (submodules) into /library/ folder within your project.

	cd /var/www/[APPLICATION_FOLDER]
	git submodule add git@github.com:clocklimited/Doctrine.git library/Doctrine
	git submodule add git@github.com:clocklimited/Bisna.git library/Bisna
	git submodule add git@github.com:clocklimited/Symfony.git library/Symfony
	
# Configuration

Create the following folders inside your /library/ folder.
	cd /var/www/[APPLICATION_FOLDER]/library
	mkdir -p Bauer/Entity/Proxy

Create a scrips/ folder and then move the doctrine.php to /scripts/ folder.
	cd /var/www/[APPLICATION_FOLDER]
	mkdir scripts
	mv library/Doctrine/doctrine.php scripts
	
Add the following to your production settings in the /application/configs/application.ini config file:

	; ------------------------------------------------------------------------------
	; Autoload Namespaces
	; ------------------------------------------------------------------------------
	autoloaderNamespaces[] = "Bauer"
	autoloaderNamespaces[] = "Bisna"
	autoloaderNamespaces[] = "Symfony"
	autoloaderNamespaces[] = "Doctrine"

	;; added for Doctrine2 Integration
	pluginPaths.Bisna\Application\Resource\ = "Bisna/Application/Resource"

	; ------------------------------------------------------------------------------
	; Doctrine Cache Configuration
	; ------------------------------------------------------------------------------

	; Points to default cache instance to be used. Optional is only one cache is defined
	resources.doctrine.cache.defaultCacheInstance = default

	; Cache Instance configuration for "default" cache
	resources.doctrine.cache.instances.default.adapterClass = "Doctrine\Common\Cache\ArrayCache"
	resources.doctrine.cache.instances.default.namespace    = "Application_"

	; ------------------------------------------------------------------------------
	; Doctrine DBAL Configuration
	; ------------------------------------------------------------------------------

	; Points to default connection to be used. Optional if only one connection is defined
	resources.doctrine.dbal.defaultConnection = default

	; Database configuration
	;resources.doctrine.dbal.connections.default.parameters.wrapperClass = ""
	resources.doctrine.dbal.connections.default.parameters.driver   = "pdo_mysql"
	resources.doctrine.dbal.connections.default.parameters.dbname   = "[DATABASE_NAME]"
	resources.doctrine.dbal.connections.default.parameters.host = "localhost"
	resources.doctrine.dbal.connections.default.parameters.port = 3306
	resources.doctrine.dbal.connections.default.parameters.user = "[DATABASE_USERNAME]"
	resources.doctrine.dbal.connections.default.parameters.password = "[DATABASE_PASSWORD]"

	; ------------------------------------------------------------------------------
	; Doctrine ORM Configuration
	; ------------------------------------------------------------------------------

	; Points to default EntityManager to be used. Optional if only one EntityManager is defined
	resources.doctrine.orm.defaultEntityManager = default

	; EntityManager configuration for "default" manager
	resources.doctrine.orm.entityManagers.default.connection     = default
	resources.doctrine.orm.entityManagers.default.proxy.autoGenerateClasses = false
	resources.doctrine.orm.entityManagers.default.proxy.namespace           = "Bauer\Entity\Proxy"
	resources.doctrine.orm.entityManagers.default.proxy.dir                 = APPLICATION_PATH "/../library/Bauer/Entity/Proxy"
	resources.doctrine.orm.entityManagers.default.metadataDrivers.annotationRegistry.annotationFiles[]     = APPLICATION_PATH "/../library/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php"
	resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.adapterClass          = "Doctrine\ORM\Mapping\Driver\AnnotationDriver"
	resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.mappingNamespace      = "Bauer\Entity"
	resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.mappingDirs[]         = APPLICATION_PATH "/../library/ZC/Entity"
	resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.annotationReaderClass = "Doctrine\Common\Annotations\AnnotationReader"
	resources.doctrine.orm.entityManagers.default.metadataDrivers.drivers.0.annotationReaderCache = default

Find the lines on the above configuration, and set your database details:

 * dbname   = "[DATABASE_NAME]"
 * host = "localhost"
 * user = "[DATABASE_USERNAME]"
 * password = "[DATABASE_PASSWORD]"

