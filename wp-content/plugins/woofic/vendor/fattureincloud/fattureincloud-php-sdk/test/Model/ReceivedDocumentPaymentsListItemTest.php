<?php
/**
 * ReceivedDocumentPaymentsListItemTest
 *
 * PHP version 7.3
 *
 * @category Class
 * @package  FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */

/**
 * Fatture in Cloud API v2 - API Reference
 *
 * Connect your software with Fatture in Cloud, the invoicing platform chosen by more than 400.000 businesses in Italy.   The Fatture in Cloud API is based on REST, and makes possible to interact with the user related data prior authorization via OAuth2 protocol.
 *
 * The version of the OpenAPI document: 2.0.10
 * Contact: info@fattureincloud.it
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 5.4.0
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
 * ReceivedDocumentPaymentsListItemTest Class Doc Comment
 *
 * @category    Class
 * @description ReceivedDocumentPaymentsListItem
 * @package     FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */
class ReceivedDocumentPaymentsListItemTest extends TestCase
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
            "amount": 592,
            "due_date": "2021-08-15",
            "paid_date": "2021-08-15",
            "id": 777,
            "payment_terms": {
              "days": 0,
              "type": "standard"
            },
            "status": "paid",
            "payment_account": {
              "id": 222,
              "name": "Contanti",
              "virtual": false
            }
          }';

        $this->array = json_decode($json, true);

        $this->object = ObjectSerializer::deserialize($json, '\FattureInCloud\Model\ReceivedDocumentPaymentsListItem');
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
     * Test "ReceivedDocumentPaymentsList"
     */
    public function testReceivedDocumentPaymentsList()
    {
        foreach ($this->array as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object);
        }
    }

    /**
     * Test attribute "id"
     */
    public function testPropertyId()
    {
        TestCase::assertEquals($this->object['id'], $this->array['id']);
    }

    /**
     * Test attribute "amount"
     */
    public function testPropertyAmount()
    {
        TestCase::assertEquals($this->object['amount'], $this->array['amount']);
    }

    /**
     * Test attribute "due_date"
     */
    public function testPropertyDueDate()
    {
        $date = new \DateTime($this->array['due_date']);
        TestCase::assertEquals($this->object['due_date'], $date);
    }

    /**
     * Test attribute "paid_date"
     */
    public function testPropertyPaidDate()
    {
        ;
        $date = new \DateTime($this->array['paid_date']);
        TestCase::assertEquals($this->object['paid_date'], $date);
    }

    /**
     * Test attribute "payment_terms"
     */
    public function testPropertyPaymentTerms()
    {
        foreach ($this->array['payment_terms'] as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object['payment_terms']);
        }
    }

    /**
     * Test attribute "status"
     */
    public function testPropertyStatus()
    {
        TestCase::assertEquals($this->object['status'], $this->array['status']);
    }

    /**
     * Test attribute "payment_account"
     */
    public function testPropertyPaymentAccount()
    {
        foreach ($this->array['payment_account'] as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object['payment_account']);
        }
    }
}
