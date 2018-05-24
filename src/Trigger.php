<?php

namespace GetPhoto;

use GetPhoto\Base\ServiceBase;
use GetPhoto\Helpers\Constant;
use GetPhoto\Helpers\Method;
use GetPhoto\Helpers\Variable;
use GuzzleHttp\Client;

class Trigger extends ServiceBase
{

    protected $requiredFields = [
        'Id',
        'Description',
        'Type',
        'Action',
    ];

    protected $data;

    public function __construct($id, $description, $type, $action)
    {
        $this->data = [
            'id' => $id,
            'description' => $description,
            'type' => $type,
            'action' => $action,
        ];

		$this->setUpConfig();
    }

    public function setPriority($priority)
	{
		$this->data['priority'] = $priority;
	}

    public function setTriggersToProcessOnCreation($triggers)
	{
		$this->data['triggersToProcessOnCreation'] = $triggers;
	}

    public function addActionParameter($parameter)
    {
        if (!isset($this->data['actionParameters'])) {
            $this->data['actionParameters'] = [];
        }

        $this->data['actionParameters'][] = $parameter;
    }

    public function addSubscriptionKey($value)
    {
        $this->data['subscriptionKey'] = $value;
    }

    public function addDelay($value)
    {
        $this->data['delay'] = $value;
    }

    public function addWhen($value)
    {
        $this->data['when'] = $value;
    }

    public function addParameter($data)
    {
        if (!isset($this->data['parameters'])) {
            $this->data['parameters'] = [];
        }

        $this->data['parameters'][] = $data;
    }

    public function save()
    {
        if (isset($this->data['actionParameters'])) {
            $this->data['actionParameters'] = implode('**', $this->data['actionParameters']);
        }

        $url = $this->config['notification_service_url'] . $this->config['method_add_trigger'];

        try {
            $client = new Client();
            $response = $client->post($url, ['form_params' => $this->data]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException("Error connecting to API: code " . $response->getStatusCode() . ".");
            }
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new \RuntimeException("Error connecting to API $url: " . $e->getMessage());
        }
    }

    public function createConstant($name, $value)
    {
        return new Constant($name, $value);
    }

    public function createVariable($name, $value = null)
    {
        return new Variable($name, $value ?: $name);
    }

    public function createMethod($method, $parameters)
    {
        return new Method($method, $parameters);
    }

}
