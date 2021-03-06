<?php

namespace BFW\Core\AppSystems;

use \Exception;

class Memcached extends AbstractSystem
{
    /**
     * @var \BFW\Memcached $memcached The class used to connect to
     * memcache(d) server.
     * The class name should be declared into config file.
     */
    protected $memcached;
    
    /**
     * Load and initialize le memcached object
     */
    public function __construct()
    {
        $this->loadMemcached();
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return \BFW\Memcached
     */
    public function __invoke()
    {
        return $this->memcached;
    }
    
    /**
     * Getter accessor to property memcached
     * 
     * @return \BFW\Memcached
     */
    public function getMemcached()
    {
        return $this->memcached;
    }
    
    /**
     * Connect to memcache(d) server with the class declared in config file
     * 
     * @return void
     * 
     * @throws \Exception If memcached is enabled but no class is define. Or if
     *  The class declared into the config is not found.
     */
    protected function loadMemcached()
    {
        $memcachedConfig = \BFW\Application::getInstance()
            ->getConfig()
            ->getValue('memcached', 'memcached.php')
        ;

        if ($memcachedConfig['enabled'] === false) {
            return;
        }

        try {
            $this->memcached = new \BFW\Memcached;
            $this->memcached->connectToServers();
        } catch (Exception $e) {
            $this->memcached = null;
            
            trigger_error(
                'Memcached connexion error'
                .' #'.$e->getCode().' : '.$e->getMessage(),
                E_USER_WARNING
            );
        }
    }
}
