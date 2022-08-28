<?php

namespace SimpleConfig;

class Config
{
    const CURRENT_FOLDER = ['..', '.'];

    private static $instance;

    /**
     * configs
     */
    private $configs = [];

    /**
     * runtime config cache
     */
    private $cache = [];

    /**
     * root folder
     */
    private $rootPath = null;

    private function __construct()
    {
    }

    /**
     * 
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function setConfigsFolder($rootFolder)
    {
        $instance = self::getInstance();

        $instance->loadConfig($rootFolder);

        return $instance;
    }

    /**
     * load config root path
     * 
     * @param string $rootFolder
     * @return void
     */
    protected function loadConfig($rootFolder)
    {
        // 設定 config 根目錄
        if ($this->rootPath == null) {
            $this->rootPath = $rootFolder;
        }

        $configFiles = scandir($rootFolder);
        foreach ($configFiles as $key => $value) {
            $path = realpath($rootFolder . DIRECTORY_SEPARATOR . $value);

            $this->parseDirectory($path, $value);
            $this->parseFile($path);
        }

        unset($configFiles, $file);
    }

    /**
     * parse folder nest
     * 
     * @param string $path
     * @param string $current 
     * @return bool
     */
    protected function parseDirectory($path, $current)
    {
        if (!is_dir($path)) {

            return false;
        }

        if (in_array($current, self::CURRENT_FOLDER)) {

            return false;
        }

        $this->loadConfig($path);

        return true;
    }

    /**
     * parse file content
     * 
     * @param string $file
     * @return void
     */
    protected function parseFile($file)
    {
        if (is_dir($file)) {
            return false;
        }

        $parser = Parser::getParser($file);

        $folder = [];
        // config file in nest folder
        if (dirname($file) !== $this->rootPath) {
            $folder = array_merge(
                $folder,
                explode('/', str_replace($this->rootPath . DIRECTORY_SEPARATOR, '', dirname($file)))
            );
        }

        $folder = implode('.', $folder);

        $this->configs[$parser->fileName()] = $parser->parse();

        if ($folder) {
            $this->configs[$folder . '.' . $parser->fileName()] = $parser->parse();
        }

        return;
    }

    /**
     * get config
     * 
     * @param string $config
     * @return mixed
     */
    public function get($config)
    {
        // access runtime cache first
        if (isset($this->cache[$config])) {
            return $this->cache[$config];
        }

        $nest = explode('.', $config);
        $fileKey = $this->multilayerFolder($nest);

        // get key in file
        foreach (explode('.', $fileKey) as $key => $value) {
            unset($nest[$key]);
        }
        //  check config file exist
        if (!$this->fileExist($fileKey)) {

            return null;
        }

        $configValue = $this->configs[$fileKey];

        foreach ($nest as $value) {
            // check find key in config file
            if (!isset($configValue[$value])) {

                return null;
            }

            $configValue = $configValue[$value];
        }

        $this->cache($config, $configValue);

        return $configValue;
    }

    protected function fileExist($fileName)
    {
        if (!isset($this->configs[$fileName])) {
            return false;
        }

        return true;
    }

    public function multilayerFolder($nest)
    {
        $folderFlag = false;
        $configKey = [];
        foreach ($nest as $value) {
            // find folder nest key
            if (array_key_exists($multiLayerKey = implode('.', $nest), $this->configs)) {
                $folderFlag = true;

                break;
            }

            array_unshift($configKey, array_pop($nest));
        }

        return $folderFlag
            ? $multiLayerKey
            : array_shift($configKey);
    }

    public function set($key, $value)
    {
        $nest = explode('.', $key);
        $fileKey = $this->multilayerFolder($nest);

        foreach (explode('.', $fileKey) as $key => $item) {
            unset($nest[$key]);
        }

        $config = &$this->configs[$fileKey] ?: [];
        $cache = &$this->cache[$fileKey] ?: [];

        foreach ($nest as $layerKey) {
            $config = &$config[$layerKey] ?: [];
            $cache = &$cache[$layerKey] ?:[];
        }

        $config = $value;
        $cache = $value;

        return $config;
    }

    private function cache($key, $data)
    {
        $this->cache[$key] = $data;
    }
}
