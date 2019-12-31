<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager;

class Config implements ConfigInterface
{
    protected $config = array();

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    public function getAllowOverride()
    {
        return (isset($this->config['allow_override'])) ? $this->config['allow_override'] : null;
    }

    public function getFactories()
    {
        return (isset($this->config['factories'])) ? $this->config['factories'] : array();
    }

    public function getAbstractFactories()
    {
        return (isset($this->config['abstract_factories'])) ? $this->config['abstract_factories'] : array();
    }

    public function getInvokables()
    {
        return (isset($this->config['invokables'])) ? $this->config['invokables'] : array();
    }

    public function getServices()
    {
        return (isset($this->config['services'])) ? $this->config['services'] : array();
    }

    public function getAliases()
    {
        return (isset($this->config['aliases'])) ? $this->config['aliases'] : array();
    }

    public function getInitializers()
    {
        return (isset($this->config['initializers'])) ? $this->config['initializers'] : array();
    }

    public function getShared()
    {
        return (isset($this->config['shared'])) ? $this->config['shared'] : array();
    }

    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $allowOverride = $this->getAllowOverride();
        isset($allowOverride) ? $serviceManager->setAllowOverride($allowOverride) : null;

        foreach ($this->getFactories() as $name => $factory) {
            $serviceManager->setFactory($name, $factory);
        }

        foreach ($this->getAbstractFactories() as $factory) {
            $serviceManager->addAbstractFactory($factory);
        }

        foreach ($this->getInvokables() as $name => $invokable) {
            $serviceManager->setInvokableClass($name, $invokable);
        }

        foreach ($this->getServices() as $name => $service) {
            $serviceManager->setService($name, $service);
        }

        foreach ($this->getAliases() as $alias => $nameOrAlias) {
            $serviceManager->setAlias($alias, $nameOrAlias);
        }

        foreach ($this->getInitializers() as $initializer) {
            $serviceManager->addInitializer($initializer);
        }

        foreach ($this->getShared() as $name => $isShared) {
            $serviceManager->setShared($name, $isShared);
        }
    }

}
