<?php

namespace GetPhoto\Base;

class ServiceBase
{

    protected $config;

	/**
	 * @param $params
	 * @return bool
	 */
    public function setUpConfig($params = null)
    {
    	if (!empty($params['config'])) {
    		$this->config = $params['config'];
    		return true;
		}

        $configPath = realpath(implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..',
            '..',
            'config',
            'notification.php',
        ]));

        if ($configPath === false) {
            return false;
        }

        $this->config = require $configPath;
        return true;
    }

	/**
	 * @param string $prefix
	 * @return string
	 */
    protected function generateId($prefix = 'fotograf')
    {
        return sha1(time() . uniqid($prefix, true));
    }

}
