<?php
/**
 * EmailScheduleTest
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
 * ## Request informations In every request description you will be able to find some additional informations about context, permissions and supported functionality:  | Parameter | Description | |-----------|-------------| | 👥 Context | Indicate the subject of the request. Can be `company`, `user` or `accountant`.  | | 🔒 Required scope | If present, indicates the required scope to fulfill the request. | | 🔍 Filtering | If present, indicates which fields support the filtering feature. | | ↕️ Sorting | If present, indicates which fields support the sorting feature. | | 📄 Paginated results | If present, indicate that the results are paginated. | | 🎩 Customized responses supported | If present, indicate that you can use `field` or `fieldset` to customize the response body. |  For example the request `GET /entities/{entityRole}` have tis informations: \\ 👥 Company context \\ 🔒 Required scope: `entity.clients:r` or `entity.suppliers:r` (depending on `entityRole`) \\ 🔍 Filtering: `id`, `name` \\ ↕️ Sorting: `id`, `name` \\ 📄 Paginated results \\ 🎩 Customized responses supported  Keep in mind that if you are making **company realted requests**, you will need to specify the company id in the requests: ``` GET /c/{company_id}/issued_documents ```
 *
 * The version of the OpenAPI document: 2.0.6
 * Contact: info@fattureincloud.it
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 5.3.0
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
 * EmailScheduleTest Class Doc Comment
 *
 * @category    Class
 * @description EmailSchedule
 * @package     FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */
class EmailScheduleTest extends TestCase
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
            "sender_email": "mariorossi@fattureincloud.it",
            "sender_id": 5,
            "recipient_email": "mary.red@example.com",
            "subject": "Nostra pro forma nr. 1",
            "body": "Gentile Mario Rossi,<br>per vedere la nostra pro forma di  o per scaricarne una copia in versione PDF prema sul bottone sottostante.<br><br>{{allegati}}<br><br>Cordiali saluti,<br><b>Mario Rossi</b>",
            "attach_pdf": true,
            "include": {
                "document": false,
                "delivery_note": false,
                "attachment": false,
                "accompanying_invoice": false
            },
            "send_copy": false
        }';

        $this->array = json_decode($json, true);

        $this->object = ObjectSerializer::deserialize($json, '\FattureInCloud\Model\EmailSchedule');
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
     * Test "EmailSchedule"
     */
    public function testEmailSchedule()
    {
        foreach ($this->array as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object);
        }
    }

    /**
     * Test attribute "sender_id"
     */
    public function testPropertySenderId()
    {
        TestCase::assertEquals($this->object['sender_id'], $this->array['sender_id']);
    }

    /**
     * Test attribute "sender_email"
     */
    public function testPropertySenderEmail()
    {
        TestCase::assertEquals($this->object['sender_email'], $this->array['sender_email']);
    }

    /**
     * Test attribute "recipient_email"
     */
    public function testPropertyRecipientEmail()
    {
        TestCase::assertEquals($this->object['recipient_email'], $this->array['recipient_email']);
    }

    /**
     * Test attribute "subject"
     */
    public function testPropertySubject()
    {
        TestCase::assertEquals($this->object['subject'], $this->array['subject']);
    }

    /**
     * Test attribute "body"
     */
    public function testPropertyBody()
    {
        TestCase::assertEquals($this->object['body'], $this->array['body']);
    }

    /**
     * Test attribute "include"
     */
    public function testPropertyInclude()
    {
        foreach ($this->array['include'] as $key => $value) {
            Testcase::assertArrayHasKey($key, $this->object['include']);
        }
    }

    /**
     * Test attribute "attach_pdf"
     */
    public function testPropertyAttachPdf()
    {
        TestCase::assertEquals($this->object['attach_pdf'], $this->array['attach_pdf']);
    }

    /**
     * Test attribute "send_copy"
     */
    public function testPropertySendCopy()
    {
        TestCase::assertEquals($this->object['send_copy'], $this->array['send_copy']);
    }
}
