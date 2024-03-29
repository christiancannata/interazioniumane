<?php
/**
 * GetReceiptsMonthlyTotalsResponseTest
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
 * GetReceiptsMonthlyTotalsResponseTest Class Doc Comment
 *
 * @category    Class
 * @description Monthly totals.
 * @package     FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */
class GetReceiptsMonthlyTotalsResponseTest extends TestCase
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
                  "net": 15000,
                  "gross": 18000,
                  "count": 10
                },
                {
                  "net": 18000,
                  "gross": 22000,
                  "count": 20
                },
                {
                  "net": 20000,
                  "gross": 24400,
                  "count": 30
                },
                {
                  "net": 19000,
                  "gross": 22000,
                  "count": 20
                },
                {
                  "net": 17000,
                  "gross": 20000,
                  "count": 10
                },
                {
                  "net": 18000,
                  "gross": 24000,
                  "count": 21
                },
                {
                  "net": 22000,
                  "gross": 25000,
                  "count": 30
                },
                {
                  "net": 17000,
                  "gross": 21000,
                  "count": 21
                },
                {
                  "net": 0,
                  "gross": 0,
                  "count": 10
                },
                {
                  "net": 0,
                  "gross": 0,
                  "count": 20
                },
                {
                  "net": 0,
                  "gross": 0,
                  "count": 30
                },
                {
                  "net": 0,
                  "gross": 0,
                  "count": 21
                }
              ]
        }';

        $this->array = json_decode($json, true);

        $this->object = ObjectSerializer::deserialize($json, '\FattureInCloud\Model\GetReceiptsMonthlyTotalsResponse');
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
     * Test "GetReceiptsMonthlyTotalsResponse"
     */
    public function testGetReceiptsMonthlyTotalsResponse()
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
