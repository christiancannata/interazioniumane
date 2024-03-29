<?php
/**
 * SupplierTest
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
 * SupplierTest Class Doc Comment
 *
 * @category    Class
 * @description Supplier.
 * @package     FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 */
class SupplierTest extends TestCase
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
            "id": 12345,
            "code": "AE86",
            "name": "Mario Rossi S.R.L.",
            "type": "company",
            "first_name": "Mario",
            "last_name": "Rossi",
            "contact_person": "",
            "vat_number": "111222333",
            "tax_code": "111122233",
            "address_street": "Corso Magellano, 46",
            "address_postal_code": "20146",
            "address_city": "Milano",
            "address_province": "MI",
            "address_extra": "",
            "country": "Italia",
            "email": "mario.rossi@example.com",
            "certified_email": "mario.rossi@pec.example.com",
            "phone": "1234567890",
            "fax": "123456789",
            "notes": "",
            "created_at": "2021-15-08",
            "updated_at": "2021-15-08"
        }';

        $this->array = json_decode($json, true);

        $this->object = ObjectSerializer::deserialize($json, '\FattureInCloud\Model\Supplier');
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
     * Test "Supplier"
     */
    public function testSupplier()
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
     * Test attribute "code"
     */
    public function testPropertyCode()
    {
        TestCase::assertEquals($this->object['code'], $this->array['code']);
    }

    /**
     * Test attribute "name"
     */
    public function testPropertyName()
    {
        TestCase::assertEquals($this->object['name'], $this->array['name']);
    }

    /**
     * Test attribute "type"
     */
    public function testPropertyType()
    {
        TestCase::assertEquals($this->object['type'], $this->array['type']);
    }

    /**
     * Test attribute "first_name"
     */
    public function testPropertyFirstName()
    {
        TestCase::assertEquals($this->object['first_name'], $this->array['first_name']);
    }

    /**
     * Test attribute "last_name"
     */
    public function testPropertyLastName()
    {
        TestCase::assertEquals($this->object['last_name'], $this->array['last_name']);
    }

    /**
     * Test attribute "contact_person"
     */
    public function testPropertyContactPerson()
    {
        TestCase::assertEquals($this->object['contact_person'], $this->array['contact_person']);
    }

    /**
     * Test attribute "vat_number"
     */
    public function testPropertyVatNumber()
    {
        TestCase::assertEquals($this->object['vat_number'], $this->array['vat_number']);
    }

    /**
     * Test attribute "tax_code"
     */
    public function testPropertyTaxCode()
    {
        TestCase::assertEquals($this->object['tax_code'], $this->array['tax_code']);
    }

    /**
     * Test attribute "address_street"
     */
    public function testPropertyAddressStreet()
    {
        TestCase::assertEquals($this->object['address_street'], $this->array['address_street']);
    }

    /**
     * Test attribute "address_postal_code"
     */
    public function testPropertyAddressPostalCode()
    {
        TestCase::assertEquals($this->object['address_postal_code'], $this->array['address_postal_code']);
    }

    /**
     * Test attribute "address_city"
     */
    public function testPropertyAddressCity()
    {
        TestCase::assertEquals($this->object['address_city'], $this->array['address_city']);
    }

    /**
     * Test attribute "address_province"
     */
    public function testPropertyAddressProvince()
    {
        TestCase::assertEquals($this->object['address_province'], $this->array['address_province']);
    }

    /**
     * Test attribute "address_extra"
     */
    public function testPropertyAddressExtra()
    {
        TestCase::assertEquals($this->object['address_extra'], $this->array['address_extra']);
    }

    /**
     * Test attribute "country"
     */
    public function testPropertyCountry()
    {
        TestCase::assertEquals($this->object['country'], $this->array['country']);
    }

    /**
     * Test attribute "email"
     */
    public function testPropertyEmail()
    {
        TestCase::assertEquals($this->object['email'], $this->array['email']);
    }

    /**
     * Test attribute "certified_email"
     */
    public function testPropertyCertifiedEmail()
    {
        TestCase::assertEquals($this->object['certified_email'], $this->array['certified_email']);
    }

    /**
     * Test attribute "phone"
     */
    public function testPropertyPhone()
    {
        TestCase::assertEquals($this->object['phone'], $this->array['phone']);
    }

    /**
     * Test attribute "fax"
     */
    public function testPropertyFax()
    {
        TestCase::assertEquals($this->object['fax'], $this->array['fax']);
    }

    /**
     * Test attribute "notes"
     */
    public function testPropertyNotes()
    {
        TestCase::assertEquals($this->object['notes'], $this->array['notes']);
    }

    /**
     * Test attribute "created_at"
     */
    public function testPropertyCreatedAt()
    {
        TestCase::assertEquals($this->object['created_at'], $this->array['created_at']);
    }

    /**
     * Test attribute "updated_at"
     */
    public function testPropertyUpdatedAt()
    {
        TestCase::assertEquals($this->object['updated_at'], $this->array['updated_at']);
    }
}
