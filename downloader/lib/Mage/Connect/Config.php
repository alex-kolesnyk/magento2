<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Connect_Config
implements Iterator
{
    protected $_configFile;
    protected $_configLoaded;
    const HEADER = "::ConnectConfig::v::1.0::";
    const DEFAULT_DOWNLOADER_PATH = "downloader";
    const DEFAULT_CACHE_PATH = ".cache";

    protected $defaultProperties = array();
    protected $properties = array();

    protected function initProperties()
    {
        $this->defaultProperties = array (
           'php_ini' => array(
                'type' => 'file',
                'value' => '',
                'prompt' => 'location of php.ini',
                'doc' => "It's a location of PHP.ini to use blah",
                'possible' => '/path/php.ini',
            ),
           'protocol' => array(
                'type' => 'set',
                'value' => 'http',
                'prompt' => 'preffered protocol',
                'doc' => 'preffered protocol',
                'rules' => array('http', 'ftp')
            ),
           'preferred_state' => array(
                'type' => 'set',
                'value' => 'stable',
                'prompt' => 'preferred package state',
                'doc' => 'preferred package state',
                'rules' => array('beta','alpha','stable','devel')
            ),
           'use_custom_permissions_mode'  => array (
                'type' => 'bool',
                'value' => false,
                'prompt' => 'Use custom permissions for directory and file creation',
                'doc' => 'Use custom permissions for directory and file creation',
                'possible' => 'true, false',
            ),
           'global_dir_mode' => array (
                'type' => 'octal',
                'value' => 0777,
                'prompt' => 'directory creation mode',
                'doc' => 'directory creation mode',
                'possible' => '0777, 0666 etc.',
            ),
           'global_file_mode' => array (
                'type' => 'octal',
                'value' => 0666,
                'prompt' => 'file creation mode',
                'doc' => 'file creation mode',
                'possible' => '0777, 0666 etc.',
            ),
            'downloader_path' => array(
                'type' => 'dir',
                'value' => 'downloader',
                'prompt' => 'relative path, location of magento downloader',
                'doc' => "relative path, location of magento downloader",
                'possible' => 'path',
            ),
            'magento_root' => array(
                'type' => 'dir',
                'value' => '',
                'prompt' => 'location of magento root dir',
                'doc' => "Location of magento",
                'possible' => '/path',
            ),
            'root_channel_uri' => array(
                'type' => 'string',
                'value' => 'connect20.magentocommerce.com/community',
                'prompt' => '',
                'doc' => "",
                'possible' => '',
            ),
            'root_channel' => array(
                'type' => 'string',
                'value' => 'community',
                'prompt' => '',
                'doc' => "",
                'possible' => '',
            ),
            'remote_config' => array(
                'type' => 'string',
                'value' => '',
                'prompt' => '',
                'doc' => "",
                'possible' => 'ftp://name:password@host.com:port/path/to/folder/',
            ),
            'sync_pear' => array(
                'type' => 'boolean',
                'value' => true,
                'prompt' => '',
                'doc' => "",
                'possible' => '',
            )
        );
        $this->properties = $this->defaultProperties;
    }

    public function getDownloaderPath()
    {
        return $this->magento_root . DIRECTORY_SEPARATOR . $this->downloader_path;
    }

    public function getPackagesCacheDir()
    {
        return $this->getDownloaderPath() . DIRECTORY_SEPARATOR . self::DEFAULT_CACHE_PATH;
    }

    public function getChannelCacheDir($channel)
    {
        $channel = trim( $channel, "\\/");
        return $this->getPackagesCacheDir(). DIRECTORY_SEPARATOR . $channel;
    }

    public function __construct($configFile = "connect.cfg")
    {
        $this->initProperties();
        $this->_configFile = $configFile;
        $this->load();
    }

    public function getFilename()
    {
        return $this->_configFile;
    }

    public function load()
    {
        $this->_configLoaded=false;
        /**
         * Trick: open in append mode to read,
         * place pointer to begin
         * create if not exists
         */
        if(is_file($this->_configFile)){
            $f = fopen($this->_configFile, "r");
            fseek($f, 0, SEEK_SET);
            $size = filesize($this->_configFile);
            if(!$size) {
                $this->store();
                return;
            }

            $headerLen = strlen(self::HEADER);
            $contents = fread($f, $headerLen);

            if(self::HEADER != $contents) {
                $this->store();
                return;
            }

            $size -= $headerLen;
            $contents = fread($f, $size);

            $data = @unserialize($contents);
            if($data === unserialize(false)) {
                $this->store();
                return;
            }
            foreach($data as $k=>$v) {
                $this->$k = $v;
            }
            fclose($f);
        }
        $this->_configLoaded=true;
    }

    public function store()
    {
        if($this->_configLoaded||strlen($this->remote_config)>0){
            // @TODO: use ftp to save config
            $data = serialize($this->toArray());
            if(strlen($this->remote_config)>0){
                $confFile=$this->downloader_path.DIRECTORY_SEPARATOR."connect.cfg";
                $ftpObj = new Mage_Connect_Ftp();
                $ftpObj->connect($this->remote_config);
                $tempFile = tempnam(sys_get_temp_dir(),'config');
                $f = @fopen($tempFile, "w+");
                @fwrite($f, self::HEADER);
                @fwrite($f, $data);
                @fclose($f);
                $ret=$ftpObj->upload($confFile, $tempFile);
                $ftpObj->close();
            }elseif(is_file($this->_configFile)&&is_writable($this->_configFile)||is_writable(getcwd())) {
                $f = @fopen($this->_configFile, "w+");
                @fwrite($f, self::HEADER);
                @fwrite($f, $data);
                @fclose($f);
            }
        }
    }

    public function validate($key, $val)
    {
        $rules = $this->extractField($key, 'rules');
        if(null === $rules) {
            return true;
        } elseif( is_array($rules) ) {
            return in_array($val, $rules);
        }
        return false;
    }

    public function possible($key)
    {
        $data = $this->getKey($key);
        if(! $data) {
            return null;
        }
        if('set' == $data['type']) {
            return implode("|", $data['rules']);
        }
        if(!empty($data['possible'])) {
            return $data['possible'];
        }
        return "<".$data['type'].">";
    }

    public function type($key)
    {
        return $this->extractField($key, 'type');
    }

    public function doc($key)
    {
        return $this->extractField($key, 'doc');
    }

    public function extractField($key, $field)
    {
        if(!isset($this->properties[$key][$field])) {
            return null;
        }
        return $this->properties[$key][$field];
    }

    public function hasKey($fld)
    {
        return isset($this->properties[$fld]);
    }

    public function getKey($fld)
    {
        if($this->hasKey($fld)) {
            return $this->properties[$fld];
        }
        return null;
    }

    public function rewind() {
        reset($this->properties);
    }

    public function valid() {
        return current($this->properties) !== false;
    }

    public function key() {
        return key($this->properties);
    }

    public function current() {
        return current($this->properties);
    }

    public function next() {
        next($this->properties);
    }

    public function __get($var)
    {
        if (isset($this->properties[$var]['value'])) {
            return $this->properties[$var]['value'];
        }
        return null;
    }

    public function __set($var, $value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (isset($this->properties[$var])) {
            if ($value === null) {
                $value = '';
            }
            if($this->properties[$var]['value'] !== $value) {
                $this->properties[$var]['value'] = $value;
                $this->store();
            }
        }
    }

    public function toArray($withRules = false)
    {
        $out = array();
        foreach($this as $k=>$v) {
            $out[$k] = $withRules ? $v : $v['value'];
        }
        return $out;
    }

    /**
    * Return default config value by key
    *
    * @param string $key
    * @return mixed
    */
    public function getDefaultValue($key)
    {
        if (isset($this->defaultProperties[$key]['value'])) {
            return $this->defaultProperties[$key]['value'];
        }
        return false;
    }
}
