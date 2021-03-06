<?php

namespace BFW;

use \Exception;

/**
 * Class to load all files from a directory in config dir.
 */
class Config
{
    /**
     * @const ERR_JSON_PARSE Exception code if the parse of a json file fail.
     */
    const ERR_JSON_PARSE = 1102001;
    
    /**
     * @const ERR_GETVALUE_FILE_NOT_INDICATED Exception code if the file to use
     * is not indicated into the method getValue().
     * (only if there are many config files)
     */
    const ERR_GETVALUE_FILE_NOT_INDICATED = 1102002;
    
    /**
     * @const ERR_GETVALUE_FILE_NOT_FOUND Exception code if the file to use is
     * not found.
     */
    const ERR_FILE_NOT_FOUND = 1102003;
    
    /**
     * @const ERR_GETVALUE_KEY_NOT_FOUND Exception code if the asked config key
     * not exist.
     */
    const ERR_KEY_NOT_FOUND = 1102004;
    
    /**
     * @const ERR_KEY_NOT_ADDED Exception code if the key can not be added to
     * the config.
     */
    const ERR_KEY_NOT_ADDED = 1102005;
    
    /**
     * @var string $configDirName Directory's name in config dir
     */
    protected $configDirName = '';

    /**
     * @var string $configDir Complete path of the readed directory
     */
    protected $configDir = '';

    /**
     * @var string[] $configFiles List of files to read
     */
    protected $configFiles = [];

    /**
     * @var array $config List of config value found
     */
    protected $config = [];

    /**
     * Constructor
     * Define properties configDirName and configDir
     * 
     * @param string $configDirName Directory's name in config dir
     */
    public function __construct(string $configDirName)
    {
        $this->configDirName = $configDirName;
        $this->configDir     = CONFIG_DIR.$this->configDirName;
    }
    
    /**
     * Getter accessor to the property configDirName
     * 
     * @return string
     */
    public function getConfigDirName(): string
    {
        return $this->configDirName;
    }

    /**
     * Getter accessor to the property configDir
     * 
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * Getter accessor to the property configFiles
     * 
     * @return string[]
     */
    public function getConfigFiles(): array
    {
        return $this->configFiles;
    }
    
    /**
     * Getter accessor to $config property
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * Getter accessor to have all values contain into a config file
     * 
     * @param string $filename The filename config to read
     * 
     * @return mixed
     */
    public function getConfigByFilename(string $filename)
    {
        if (!isset($this->config[$filename])) {
            throw new Exception(
                'The file '.$filename.' has not been found',
                $this::ERR_FILE_NOT_FOUND
            );
        }
        
        return $this->config[$filename];
    }
    
    /**
     * Search and load all config files which has been found
     * 
     * @return void
     */
    public function loadFiles()
    {
        $this->searchAllConfigFiles($this->configDir);
        
        foreach ($this->configFiles as $fileKey => $filePath) {
            $this->loadConfigFile($fileKey, $filePath);
        }
    }

    /**
     * Search all config files in a directory
     * Search also in sub-directory (2nd parameter)
     * 
     * @param string $dirPath The directory path where is run the search
     * @param string $pathIntoFirstDir (default '') Used when this method
     *  reads a subdirectory. It's the path of the readed directory during
     *  the first call to this method.
     * 
     * @return void
     */
    protected function searchAllConfigFiles(
        string $dirPath,
        string $pathIntoFirstDir = ''
    ) {
        if (!file_exists($dirPath)) {
            return;
        }

        //Remove some value in list of file
        $listFiles = array_diff(
            scandir($dirPath),
            ['.', '..', 'manifest.json']
        );

        foreach ($listFiles as $file) {
            $fileKey  = $pathIntoFirstDir.$file;
            $readPath = $dirPath.'/'.$file;
            
            if (is_link($readPath)) {
                $readPath = realpath($readPath);
            }

            if (is_file($readPath)) {
                $this->configFiles[$fileKey] = $readPath;
            } elseif (is_dir($readPath)) {
                $this->searchAllConfigFiles(
                    $readPath,
                    $pathIntoFirstDir.$file.'/'
                );
            }
        }
    }

    /**
     * Load a config file.
     * Find the file's extension and call the method to parse the file
     * 
     * @param string $fileKey The file's key. Most of the time, the path to
     *  the file from the $this->configDir value
     * @param string $filePath The path to the file
     * 
     * @return void
     */
    protected function loadConfigFile(string $fileKey, string $filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($fileExtension === 'json') {
            $this->loadJsonConfigFile($fileKey, $filePath);
            return;
        }

        if ($fileExtension === 'php') {
            $this->loadPhpConfigFile($fileKey, $filePath);
            return;
        }

        //@TODO : YAML
    }

    /**
     * Load a json config file
     * 
     * @param string $fileKey The file's key. Most of the time, the path to
     *  the file from the $this->configDir value
     * @param string $filePath The path to the file
     * 
     * @return void
     * 
     * @throws \Exception If there is an error from the json parser
     */
    protected function loadJsonConfigFile(string $fileKey, string $filePath)
    {
        $json   = file_get_contents($filePath);
        $config = json_decode($json);

        if ($config === null) {
            throw new Exception(
                json_last_error_msg(),
                $this::ERR_JSON_PARSE
            );
        }

        $this->config[$fileKey] = $config;
    }

    /**
     * Load a php config file
     * 
     * @param string $fileKey The file's key. Most of the time, the path to
     *  the file from the $this->configDir value
     * @param string $filePath The path to the file
     * 
     * @return void
     */
    protected function loadPhpConfigFile(string $fileKey, string $filePath)
    {
        $this->config[$fileKey] = require($filePath);
    }

    /**
     * Return a config value for a key
     * 
     * @param string $key The asked key for the value
     * @param string $file (default null) If many file is loaded, the file name
     *  where is the key. Is the file is into a sub-directory, the
     *  sub-directory should be present.
     * 
     * @return mixed
     * 
     * @throws \Exception If file parameter is null and there are many files. Or
     *  if the file not exist. Or if the key not exist.
     */
    public function getValue(string $key, string $file = null)
    {
        $nbConfigFile = count($this->config);

        if ($file === null && $nbConfigFile > 1) {
            throw new Exception(
                'There are many config files. Please indicate the file to'
                .' obtain the config '.$key,
                $this::ERR_GETVALUE_FILE_NOT_INDICATED
            );
        }

        if ($nbConfigFile === 1) {
            $file = key($this->config);
        }

        if (!isset($this->config[$file])) {
            throw new Exception(
                'The file '.$file.' has not been found for config '.$key,
                $this::ERR_FILE_NOT_FOUND
            );
        }

        $config = (array) $this->config[$file];
        if (!array_key_exists($key, $config)) {
            throw new Exception(
                'The config key '.$key.' has not been found',
                $this::ERR_KEY_NOT_FOUND
            );
        }

        return $config[$key];
    }
    
    /**
     * Setter to modify the all config value for a specific filename.
     * 
     * @param string $filename The filename config to modify
     * @param array $config The new config value
     * 
     * @return $this
     * 
     * @throws \Exception If the new value if not an array or an object.
     */
    public function setConfigForFilename(string $filename, array $config): self
    {
        if (!isset($this->configFiles[$filename])) {
            $this->configFiles[$filename] = $filename;
        }
        
        $this->config[$filename] = $config;
        
        return $this;
    }
    
    /**
     * Setter to modify a config key into the config of a filename
     * 
     * @param string $filename The filename config to modify
     * @param string $configKey The name of the key to modify
     * @param mixed $configValue The new value for the config key
     * 
     * @return $this
     * 
     * @throws \Exception If the key has not been found
     */
    public function setConfigKeyForFilename(
        string $filename,
        string $configKey,
        $configValue
    ): self {
        if (!isset($this->config[$filename])) {
            $this->config[$filename] = [];
        }
        
        if (!isset($this->configFiles[$filename])) {
            $this->configFiles[$filename] = $filename;
        }
        
        if (is_array($this->config[$filename])) {
            $this->config[$filename][$configKey] = $configValue;
        } else {
            throw new Exception(
                'The config key '.$configKey.' can not be added.',
                $this::ERR_KEY_NOT_ADDED
            );
        }
        
        return $this;
    }
}
