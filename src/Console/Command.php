<?php

namespace Sokil\Mongo\Migrator\Console;

use Sokil\Mongo\Migrator\Config;
use Sokil\Mongo\Migrator\Console\Exception\ConfigurationNotFound;
use Sokil\Mongo\Migrator\Manager;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputOption;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    private $config;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->addOption(
            '--config',
            '-c',
            InputOption::VALUE_OPTIONAL,
            'The config file'
        );

    }

    /**
     *
     * @var \Sokil\Mongo\Migrator\Manager
     */
    private $manager;
    
    const CONFIG_FILENAME = 'mongo-migrator';
    
    /**
     *
     * @return \Sokil\Mongo\Migrator\Config
     */
    protected function getConfig($cfgFile = null)
    {
        if (!$this->config) {
            $this->config = new Config($this->readConfig($cfgFile));
        }
        
        return $this->config;
    }
    
    private function readConfig($cfgFile = null)
    {

        $file = self::CONFIG_FILENAME;
        if (!empty($cfgFile)) {
            $file = $cfgFile;
        }
        $filename = $this->getProjectRoot() . '/' . $file;

        $yamlFilename = $filename . '.yaml';
        if (file_exists($yamlFilename)) {
            return Yaml::parse(file_get_contents($yamlFilename));
        }

        $phpFilename = $filename . '.php';
        if (file_exists($phpFilename)) {
            return require($phpFilename);
        }
        
        throw new ConfigurationNotFound('Config not found');
    }

    /**
     * Check if  migration config present in project
     *
     * @return bool
     */
    public function isProjectInitialised($cfgFile = null)
    {
        try {
            $config = $this->getConfig($cfgFile);
            return (bool) $config;
        } catch (ConfigurationNotFound $e) {
            return false;
        }
    }

    /**
     * @return bool
     * @deprecated due to misspell in method name
     */
    public function isProjectInitialisd($cfgFile = null)
    {
        return $this->isProjectInitialised($cfgFile);
    }

    /**
     * Project root
     *
     * @return string
     */
    public function getProjectRoot()
    {
        $baseDir = getenv('BASE_DIR');
        if (empty($baseDir)) {
            return getcwd();
        }
        return $baseDir;
    }
    
    /**
     *
     * @return \Sokil\Mongo\Migrator\Manager
     */
    public function getManager($cfgFile = null)
    {
        if (!$this->manager) {
            $this->manager = new Manager($this->getConfig($cfgFile), $this->getProjectRoot());
        }
        
        return $this->manager;
    }
}
