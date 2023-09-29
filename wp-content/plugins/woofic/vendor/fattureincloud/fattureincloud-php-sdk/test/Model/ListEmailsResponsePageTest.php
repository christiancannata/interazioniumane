<?php
/**
 * ListEmailsResponsePageTest
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
 * The version of the OpenAPI document: 2.0.22
 * Contact: info@fattureincloud.it
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.2.1
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
 * ListEmailsResponsePageTest Class Doc Comment
 *
 * @category    Class
 * @description ListEmailsResponsePage
 * @package     FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */
class ListEmailsResponsePageTest extends TestCase
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
            "data": [
                {
                "id": 1,
                "status": "sent",
                "sent_date": "2022-07-17 13:53:12",
                "errors_count": 0,
                "error_log": "",
                "from_email": "test@mail.it",
                "from_name": "Test mail",
                "to_email": "mail@test.it",
                "to_name": "Mario",
                "subject": "Test",
                "content": "Test send email",
                "copy_to": "",
                "recipient_status": "unknown",
                "recipient_date": null,
                "kind": "Fatture",
                "attachments": []
                },
                {
                "id": 2,
                "status": "sent",
                "sent_date": "2022-07-18 13:53:12",
                "errors_count": 0,
                "error_log": "",
                "from_email": "test@mail.it",
                "from_name": "Test mail",
                "to_email": "mail@test.it",
                "to_name": "Maria",
                "subject": "Test",
                "content": "Test send email",
                "copy_to": "",
                "recipient_status": "unknown",
                "recipient_date": null,
                "kind": "Fatture",
                "attachments": []
                }
            ]
        }';

        $this->array = json_decode($json, true);

        $this->object = ObjectSerializer::deserialize($json, '\FattureInCloud\Model\ListEmailsResponsePage');
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
     * Test "ListEmailsResponsePage"
     */
    public function testListEmailsResponsePage()
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
}
