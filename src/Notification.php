<?php

namespace GetPhoto;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use GetPhoto\Base\ServiceBase;
use \RuntimeException;

class Notification extends ServiceBase
{

	/**
	 * Definition of required fields per event
	 *
	 * @var array
	 */
	private $requiredFields = [
		'Customer.*' => [
			'customerId',
			'photographerId',
		],
		'Gallery.*' => [
			'galleryId',
			'photographerId',
		],
		'Job.*' => [
			'jobId',
			'photographerId',
		],
		'Order.*' => [
			'orderId',
			'photographerId',
		],
		'Order.Paid' => [
			'paymentAmount',
		],
		'Communication.*' => [
			'communicationProfileId',
			'customerNewsletterId',
		],
	];

	private $dateTimeFields = [
		'dateTime',
		'couponExpirationDate',
		'archiveAt',
		'archivedAt',
		'activatedAt',
		'orderedAt',
		'paidAt',
	];

	public function pushEvent($data, $params = [])
	{
		$this->setUpConfig($params);

		$event = $this->formatData($data);

		$this->checkRequired($event);

		$tableName = 'EventQueue';

		if(!empty($params['TableName'])) {
			$tableName = $params['TableName'];
		}

		$this->getDynamoDB()->putItem([
			'TableName' => $tableName,
			'Item' => $this->prepareData($event),
		]);

		return $event;
	}

	protected function prepareData($event)
	{
		$data = [];
		foreach ($event as $k => $v) {
			$data[ucfirst($k)] = $v;
		}

		return (new Marshaler)->marshalItem($data);
	}

	public function getDynamoDB()
	{
		return new DynamoDbClient([
			'endpoint' => $this->config['endpoint'],
			'region'   => $this->config['region'],
			'credentials' => [
				'key'    => $this->config['credential_key'],
				'secret' => $this->config['credential_secret'],
			],
			'version' => 'latest',
			'client.backoff.retries' => $this->config['client_backoff_retries'],
		]);
	}

	public function formatData($input)
	{
		$data = [];
		foreach ($input as $key => $value) {
			if (in_array($key, $this->dateTimeFields) && !empty($value)) {
				$data[$key] = date('Ymd His', strtotime($value));
				continue;
			}

			$data[$key] = (string)$value;

			if ($value === null || $value === '') {
				$data[$key] = '__emptyString__';
			}
		}

		$data['id'] = !empty($data['id']) ? $data['id'] : $this->generateId('NL');
		$data['dateTime'] = (!empty($data['dateTime']) && $data['dateTime'] !== '__emptyString__') ? $data['dateTime'] : gmdate('Ymd His');

		$data['active'] = 1;
		$data['deleted'] = 0;

		return $data;
	}

	private function checkRequired($event) {
		if (empty($event['eventTypeId'])) {
			throw new RuntimeException('EventTypeId not defined!');
		}

		list($eventClass, $eventType) = explode('.', $event['eventTypeId']);

		$tests = [
			'*',
			$eventClass . '.*',
			'*.' . $eventType,
			$eventClass . '.' . $eventType,
		];

		foreach ($tests as $key) {
			if (!empty($this->requiredFields[$key])) {
				foreach ($this->requiredFields[$key] as $requiredField) {
					if (empty($event[$requiredField]) || ($event[$requiredField] === '__emptyString__')) {
						$missingFields[] = $requiredField;
					}
				}
			}
		}

		if (!empty($missingFields)) {
			throw new RuntimeException('The following required fields are empty: ' . implode(', ', $missingFields));
		}
	}

}
