<?php

namespace GetPhoto\Test;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Sdk;
use GetPhoto\Notification;
use \PHPUnit_Framework_TestCase;

class NotificationTest extends PHPUnit_Framework_TestCase
{

	public function testFormatData()
	{
		$data = [
			'eventTypeId' => 'Customer.Registered',
			'customerId' => uniqid(),
			'photographerId' => 1,
			'jobId' => 1,
			'guestAccessId' => 1,
			'galleryId' => 1,
			'ipAddress' => '127.0.0.1',
			'isPotentialBuyer' => 1,
			'emptyString' => '',
			'null' => null,
			'zero' => 0,
			'zeroString' => '0',
			'archiveAt' => '2018-05-28 00:00:00',
			'couponExpirationDate' => null,
		];

		$result = (new Notification)->formatData($data);

		// These fields should not have been stripped.
		$this->assertArrayHasKey('emptyString', $result);
		$this->assertArrayHasKey('null', $result);

		$this->assertEquals('__emptyString__', $result['null']);
		$this->assertEquals('__emptyString__', $result['emptyString']);
		$this->assertEquals('1', $result['isPotentialBuyer']);

		// These fields should still be there and casted to string.
		$this->assertArrayHasKey('zeroString', $result);
		$this->assertArrayHasKey('zero', $result);
		$this->assertTrue(is_string($result['zero']));

		$this->assertEquals('__emptyString__', $result['couponExpirationDate']);

		// Notification::dateTimeFields should be formatted as follows.
		$this->assertEquals('20180528 000000', $result['archiveAt']);
	}

	public function testPush()
	{
		$this->markTestSkipped('Test broken. Plz fix me.');

		$item = (new Notification)->pushEvent([
			'Id' => sha1(time() . uniqid('event', true)),
			'EventTypeId' => 'User.Login',
			'DateTime' => gmdate('Ymd His'),
			'UserId' => rand(1, 50),
			'PhotographerId' => rand(1, 50),
		]);

		$configPath = realpath(implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			'..',
			'config',
			'notification.php',
		]));

		if (!$configPath) {
			$configPath = realpath(implode(DIRECTORY_SEPARATOR, [
				__DIR__,
				'..',
				'config',
				'notification.php.default',
			]));
		}

		$config = require $configPath;

		$dynamodb = DynamoDbClient::factory([
			'region' => $config['region'],
			'endpoint' => $config['endpoint'],
			'version'  => 'latest',
			'credentials' => [
				'key'    => $config['credential_key'],
				'secret' => $config['credential_secret'],
			]
		]);

		$response = $dynamodb->scan([
			'TableName' => 'EventQueue',
			'FilterExpression' => 'attribute_exists(Id)',
		]);

		$this->assertContains($item , $response['Items']);
	}

}
