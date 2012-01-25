<?php
class DbVer_Resource_Dvinfo extends Zend_Application_Resource_ResourceAbstract
{

    const DEFAULT_REGISTRY_KEY = 'DvInfo';

    protected $_dvInfo;

    public function init() {
        return $this->getDvInfo();

    }

    public function setDatabaseProperties() {

        $options = $this->getOptions();

        $db['adapter'] = isset($options['adapter']) ? $options['adapter'] : '';
        $db['host'] = isset($options['params']['host']) ? $options['params']['host'] : '';
        $db['username'] = isset($options['params']['username']) ? $options['params']['username'] : '';
        $db['password'] = isset($options['params']['password']) ? $options['params']['password'] : '';
        $db['dbname'] = isset($options['params']['dbname']) ? $options['params']['dbname'] : '';
        $db['tablename'] = isset($options['params']['tablename']) ? $options['params']['tablename'] : '';
        $db['isDefaultTableAdapter'] = isset($options['params']['isDefaultTableAdapter']) ? $options['params']['isDefaultTableAdapter'] : '';
        $db['defaultBuild'] = isset($options['defaultBuild']) ? $options['defaultBuild'] : '';
        $db['buildPath'] = isset($options['buildPath']) ? $options['buildPath'] : '';
        $db['appPath'] = isset($options['appPath']) ? $options['appPath'] : '';


        $db['key'] = (isset($options['registry_key']) && !is_numeric($options['registry_key']))

                 ? $options['registry_key']

                 : self::DEFAULT_REGISTRY_KEY;
        return $db;
    }

    public function getDvInfo()
    {

        if (null === $this->_dvInfo) {

            $db = $this->setDatabaseProperties();
            $this->_dvInfo = new DbVer_DvInfo($db);

            Zend_Registry::set($db['key'], $this->_dvInfo);

        }
        return $this->_dvInfo;

    }

}