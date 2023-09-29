<?php
/**
 * CreateWebhooksSubscriptionResponseTest
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */

/**
 * Fatture in Cloud API v2 - API Reference
 *
 * Connect your software with Fatture in Cloud, the invoicing platform chosen by more than 500.000 businesses in Italy.   The Fatture in Cloud API is based on REST, and makes possible to interact with the user related data prior authorization via OAuth2 protocol.
 *
 * The version of the OpenAPI document: 2.0.27
 * Contact: info@fattureincloud.it
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.5.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Please update the test case below to test the model.
 */

namespace FattureInCloud\Test\Model;

use FattureInCloud\ObjectSerializer;
use PHPUnit\Framework\TestCase;

/**
 * CreateWebhooksSubscriptionResponseTest Class Doc Comment
 *
 * @category    Class
 * @description CreateWebhooksSubscriptionResponse
 * @package     FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */
class CreateWebhooksSubscriptionResponseTest extends TestCase
{
    public $array = [];
    public $object;

    /**
     * Setup before running any test case
     */
    public static function setUpBeforeClass(): void
    {
    }

    /**
     * Setup before running each test case
     */
    public function setUp(): void
    {
        $json = '{
            "data": {
                "id": "SUB123",
                "sink": "https://endpoint.test",
                "verified": true,
                "types": ["it.fattureincloud.webhooks.cashbook.create"]
            },
            "warnings": ["error"]
        }';

        $this->array = json_decode($json, true);
        $this->object = ObjectSerializer::deserialize($json, '\FattureInCloud\Model\CreateWebhooksSubscriptionResponse');
    }

    /**
     * Clean up after running each test case
     */
    public function tearDown(): void
    {
    }

    /**
     * Clean up after running all test cases
     */
    public static function tearDownAfterClass(): void
    {
    }

    /**
     * Test "CreateWebhooksSubscriptionResponse"
     */
    public function testCreateWebhooksSubscriptionResponse()
    {
        foreach ($this->array as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object);
        }
    }

    /**
     * Test attribute "data"
     */
    public function testPropertyData()
    {
        foreach ($this->array['data'] as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object['data']);
        }
    }

    /**
     * Test attribute "warnings"
     */
    public function testPropertyWarnings()
    {
        foreach ($this->array['warnings'] as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object['warnings']);
        }
    }
}
