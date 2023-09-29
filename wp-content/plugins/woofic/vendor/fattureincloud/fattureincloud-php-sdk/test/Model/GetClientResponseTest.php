<?php
/**
 * GetClientResponseTest
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
 * GetClientResponseTest Class Doc Comment
 *
 * @category    Class
 * @description GetClientResponse
 * @package     FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */
class GetClientResponseTest extends TestCase
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
                "id": 16451,
                "code": "AE86",
                "name": "Avv. Maria Rossi",
                "type": "person",
                "first_name": "Maria",
                "last_name": "Rossi",
                "contact_person": "",
                "vat_number": "IT12345640962",
                "tax_code": "BLTGNI5ABCDA794E",
                "address_street": "Via Roma, 1",
                "address_postal_code": "20900",
                "address_city": "Milano",
                "address_province": "MI",
                "address_extra": "",
                "country": "Italia",
                "email": "maria.rossi@example.com",
                "certified_email": "maria.rossi@pec.example.com",
                "phone": "1234567890",
                "fax": "",
                "notes": "",
                "created_at": "2021-04-29 08:53:07",
                "updated_at": "2021-04-29 08:53:07",
                "default_payment_terms": 1,
                "default_payment_terms_type": "standard",
                "bank_name": "Indesa",
                "bank_iban": "IT40P123456781000000123456",
                "bank_swift_code": "AK86PCT",
                "shipping_address": "Corso Magellano 4",
                "e_invoice": true,
                "ei_code": "111111",
                "default_vat": {
                  "id": 54321,
                  "value": 45,
                  "description": "",
                  "is_disabled": false
                },
                "default_payment_method": {
                  "id": 386092,
                  "name": "Credit card"
                }
            }
        }';

        $this->array = json_decode($json, true);

        $this->object = ObjectSerializer::deserialize($json, '\FattureInCloud\Model\GetClientResponse');
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
     * Test "GetClientResponse"
     */
    public function testGetClientResponse()
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
