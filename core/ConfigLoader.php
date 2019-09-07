<?php
namespace Core;

class ConfigLoader
{
    private $routing;

    private $setting;

    private $service;

    public function __construct($env="")
    {
        $this->routingPath = CONFIG_DIR .DS. 'routing.php';
        $this->servicePath = CONFIG_DIR .DS. 'service.php';

        if ($env === 'development') {
            $this->settingPath = CONFIG_DIR .DS. 'development/setting.php';
        } else {
            $this->settingPath = CONFIG_DIR .DS. 'setting.php';
        }

        $this->load();
    }

    public function load()
    {
        try {

            $this->routing = require_once($this->routingPath);
            $this->service = require_once($this->servicePath);
            $this->setting = require_once($this->settingPath);

        } catch (\Exception $e) {

            echo $e->getMessage();
        }
    }

    public function get($config)
    {
        if ($config === 'routing') {
            return $this->routing;
        } else if ($config === 'service') {
            return $this->service;
        } else {
            return $this->setting[$config];
        }
    }

    public function set($name, $key, $value): bool
    {
        if (isset($this->setting[$name][$key])) {
            $this->setting[$name][$key] = $value;
            return true;
        }

        return false;
    }

}
