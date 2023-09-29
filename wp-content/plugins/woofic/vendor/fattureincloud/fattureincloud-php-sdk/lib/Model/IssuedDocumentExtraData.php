<?php
/**
 * IssuedDocumentExtraData
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
 * The version of the OpenAPI document: 2.0.29
 * Contact: info@fattureincloud.it
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.6.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace FattureInCloud\Model;

use ArrayAccess;
use FattureInCloud\ObjectSerializer;

/**
 * IssuedDocumentExtraData Class Doc Comment
 *
 * @category Class
 * @description Issued document extra data [TS fields follow the technical specifications provided by \&quot;Sistema Tessera Sanitaria\&quot;]
 * @package  FattureInCloud
 * @author   Fatture In Cloud API team
 * @link     https://fattureincloud.it
 * @implements \ArrayAccess<string, mixed>
 */
class IssuedDocumentExtraData implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'IssuedDocument_extra_data';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'show_sofort_button' => 'bool',
        'multifatture_sent' => 'int',
        'ts_communication' => 'bool',
        'ts_flag_tipo_spesa' => 'float',
        'ts_pagamento_tracciato' => 'bool',
        'ts_tipo_spesa' => 'string',
        'ts_opposizione' => 'bool',
        'ts_status' => 'int',
        'ts_file_id' => 'string',
        'ts_sent_date' => '\DateTime',
        'ts_full_amount' => 'bool',
        'imported_by' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'show_sofort_button' => null,
        'multifatture_sent' => null,
        'ts_communication' => null,
        'ts_flag_tipo_spesa' => null,
        'ts_pagamento_tracciato' => null,
        'ts_tipo_spesa' => null,
        'ts_opposizione' => null,
        'ts_status' => null,
        'ts_file_id' => null,
        'ts_sent_date' => 'date',
        'ts_full_amount' => null,
        'imported_by' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static $openAPINullables = [
        'show_sofort_button' => true,
            'multifatture_sent' => true,
            'ts_communication' => true,
            'ts_flag_tipo_spesa' => true,
            'ts_pagamento_tracciato' => true,
            'ts_tipo_spesa' => true,
            'ts_opposizione' => true,
            'ts_status' => true,
            'ts_file_id' => true,
            'ts_sent_date' => true,
            'ts_full_amount' => true,
            'imported_by' => true
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull($openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'show_sofort_button' => 'show_sofort_button',
        'multifatture_sent' => 'multifatture_sent',
        'ts_communication' => 'ts_communication',
        'ts_flag_tipo_spesa' => 'ts_flag_tipo_spesa',
        'ts_pagamento_tracciato' => 'ts_pagamento_tracciato',
        'ts_tipo_spesa' => 'ts_tipo_spesa',
        'ts_opposizione' => 'ts_opposizione',
        'ts_status' => 'ts_status',
        'ts_file_id' => 'ts_file_id',
        'ts_sent_date' => 'ts_sent_date',
        'ts_full_amount' => 'ts_full_amount',
        'imported_by' => 'imported_by'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'show_sofort_button' => 'setShowSofortButton',
        'multifatture_sent' => 'setMultifattureSent',
        'ts_communication' => 'setTsCommunication',
        'ts_flag_tipo_spesa' => 'setTsFlagTipoSpesa',
        'ts_pagamento_tracciato' => 'setTsPagamentoTracciato',
        'ts_tipo_spesa' => 'setTsTipoSpesa',
        'ts_opposizione' => 'setTsOpposizione',
        'ts_status' => 'setTsStatus',
        'ts_file_id' => 'setTsFileId',
        'ts_sent_date' => 'setTsSentDate',
        'ts_full_amount' => 'setTsFullAmount',
        'imported_by' => 'setImportedBy'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'show_sofort_button' => 'getShowSofortButton',
        'multifatture_sent' => 'getMultifattureSent',
        'ts_communication' => 'getTsCommunication',
        'ts_flag_tipo_spesa' => 'getTsFlagTipoSpesa',
        'ts_pagamento_tracciato' => 'getTsPagamentoTracciato',
        'ts_tipo_spesa' => 'getTsTipoSpesa',
        'ts_opposizione' => 'getTsOpposizione',
        'ts_status' => 'getTsStatus',
        'ts_file_id' => 'getTsFileId',
        'ts_sent_date' => 'getTsSentDate',
        'ts_full_amount' => 'getTsFullAmount',
        'imported_by' => 'getImportedBy'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct($data = null)
    {
        $this->setIfExists('show_sofort_button', $data ?? [], null);
        $this->setIfExists('multifatture_sent', $data ?? [], null);
        $this->setIfExists('ts_communication', $data ?? [], null);
        $this->setIfExists('ts_flag_tipo_spesa', $data ?? [], null);
        $this->setIfExists('ts_pagamento_tracciato', $data ?? [], null);
        $this->setIfExists('ts_tipo_spesa', $data ?? [], null);
        $this->setIfExists('ts_opposizione', $data ?? [], null);
        $this->setIfExists('ts_status', $data ?? [], null);
        $this->setIfExists('ts_file_id', $data ?? [], null);
        $this->setIfExists('ts_sent_date', $data ?? [], null);
        $this->setIfExists('ts_full_amount', $data ?? [], null);
        $this->setIfExists('imported_by', $data ?? [], null);
    }

    /**
      * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
      * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
      * $this->openAPINullablesSetToNull array
      *
      * @param string $variableName
      * @param array  $fields
      * @param mixed  $defaultValue
      */
    private function setIfExists(string $variableName, $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets show_sofort_button
     *
     * @return bool|null
     */
    public function getShowSofortButton()
    {
        return $this->container['show_sofort_button'];
    }

    /**
     * Sets show_sofort_button
     *
     * @param bool|null $show_sofort_button show_sofort_button
     *
     * @return self
     */
    public function setShowSofortButton($show_sofort_button)
    {
        if (is_null($show_sofort_button)) {
            array_push($this->openAPINullablesSetToNull, 'show_sofort_button');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('show_sofort_button', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['show_sofort_button'] = $show_sofort_button;

        return $this;
    }

    /**
     * Gets multifatture_sent
     *
     * @return int|null
     */
    public function getMultifattureSent()
    {
        return $this->container['multifatture_sent'];
    }

    /**
     * Sets multifatture_sent
     *
     * @param int|null $multifatture_sent multifatture_sent
     *
     * @return self
     */
    public function setMultifattureSent($multifatture_sent)
    {
        if (is_null($multifatture_sent)) {
            array_push($this->openAPINullablesSetToNull, 'multifatture_sent');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('multifatture_sent', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['multifatture_sent'] = $multifatture_sent;

        return $this;
    }

    /**
     * Gets ts_communication
     *
     * @return bool|null
     */
    public function getTsCommunication()
    {
        return $this->container['ts_communication'];
    }

    /**
     * Sets ts_communication
     *
     * @param bool|null $ts_communication Send issued document to \"Sistema Tessera Sanitaria\"
     *
     * @return self
     */
    public function setTsCommunication($ts_communication)
    {
        if (is_null($ts_communication)) {
            array_push($this->openAPINullablesSetToNull, 'ts_communication');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_communication', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_communication'] = $ts_communication;

        return $this;
    }

    /**
     * Gets ts_flag_tipo_spesa
     *
     * @return float|null
     */
    public function getTsFlagTipoSpesa()
    {
        return $this->container['ts_flag_tipo_spesa'];
    }

    /**
     * Sets ts_flag_tipo_spesa
     *
     * @param float|null $ts_flag_tipo_spesa Issued document ts \"tipo spesa\" [TK, FC, FV, SV,SP, AD, AS, ECG, SR]
     *
     * @return self
     */
    public function setTsFlagTipoSpesa($ts_flag_tipo_spesa)
    {
        if (is_null($ts_flag_tipo_spesa)) {
            array_push($this->openAPINullablesSetToNull, 'ts_flag_tipo_spesa');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_flag_tipo_spesa', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_flag_tipo_spesa'] = $ts_flag_tipo_spesa;

        return $this;
    }

    /**
     * Gets ts_pagamento_tracciato
     *
     * @return bool|null
     */
    public function getTsPagamentoTracciato()
    {
        return $this->container['ts_pagamento_tracciato'];
    }

    /**
     * Sets ts_pagamento_tracciato
     *
     * @param bool|null $ts_pagamento_tracciato Issued document ts traced payment
     *
     * @return self
     */
    public function setTsPagamentoTracciato($ts_pagamento_tracciato)
    {
        if (is_null($ts_pagamento_tracciato)) {
            array_push($this->openAPINullablesSetToNull, 'ts_pagamento_tracciato');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_pagamento_tracciato', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_pagamento_tracciato'] = $ts_pagamento_tracciato;

        return $this;
    }

    /**
     * Gets ts_tipo_spesa
     *
     * @return string|null
     */
    public function getTsTipoSpesa()
    {
        return $this->container['ts_tipo_spesa'];
    }

    /**
     * Sets ts_tipo_spesa
     *
     * @param string|null $ts_tipo_spesa Can be [ 'TK', 'FC', 'FV', 'SV', 'SP', 'AD', 'AS', 'SR', 'CT', 'PI', 'IC', 'AA' ]. Refer to the technical specifications to learn more.
     *
     * @return self
     */
    public function setTsTipoSpesa($ts_tipo_spesa)
    {
        if (is_null($ts_tipo_spesa)) {
            array_push($this->openAPINullablesSetToNull, 'ts_tipo_spesa');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_tipo_spesa', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_tipo_spesa'] = $ts_tipo_spesa;

        return $this;
    }

    /**
     * Gets ts_opposizione
     *
     * @return bool|null
     */
    public function getTsOpposizione()
    {
        return $this->container['ts_opposizione'];
    }

    /**
     * Sets ts_opposizione
     *
     * @param bool|null $ts_opposizione Issued document ts \"opposizione\"
     *
     * @return self
     */
    public function setTsOpposizione($ts_opposizione)
    {
        if (is_null($ts_opposizione)) {
            array_push($this->openAPINullablesSetToNull, 'ts_opposizione');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_opposizione', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_opposizione'] = $ts_opposizione;

        return $this;
    }

    /**
     * Gets ts_status
     *
     * @return int|null
     */
    public function getTsStatus()
    {
        return $this->container['ts_status'];
    }

    /**
     * Sets ts_status
     *
     * @param int|null $ts_status Issued document ts status
     *
     * @return self
     */
    public function setTsStatus($ts_status)
    {
        if (is_null($ts_status)) {
            array_push($this->openAPINullablesSetToNull, 'ts_status');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_status', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_status'] = $ts_status;

        return $this;
    }

    /**
     * Gets ts_file_id
     *
     * @return string|null
     */
    public function getTsFileId()
    {
        return $this->container['ts_file_id'];
    }

    /**
     * Sets ts_file_id
     *
     * @param string|null $ts_file_id Issued document ts file id
     *
     * @return self
     */
    public function setTsFileId($ts_file_id)
    {
        if (is_null($ts_file_id)) {
            array_push($this->openAPINullablesSetToNull, 'ts_file_id');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_file_id', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_file_id'] = $ts_file_id;

        return $this;
    }

    /**
     * Gets ts_sent_date
     *
     * @return \DateTime|null
     */
    public function getTsSentDate()
    {
        return $this->container['ts_sent_date'];
    }

    /**
     * Sets ts_sent_date
     *
     * @param \DateTime|null $ts_sent_date Issued document ts sent date
     *
     * @return self
     */
    public function setTsSentDate($ts_sent_date)
    {
        if (is_null($ts_sent_date)) {
            array_push($this->openAPINullablesSetToNull, 'ts_sent_date');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_sent_date', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_sent_date'] = $ts_sent_date;

        return $this;
    }

    /**
     * Gets ts_full_amount
     *
     * @return bool|null
     */
    public function getTsFullAmount()
    {
        return $this->container['ts_full_amount'];
    }

    /**
     * Sets ts_full_amount
     *
     * @param bool|null $ts_full_amount Issued document ts total amount
     *
     * @return self
     */
    public function setTsFullAmount($ts_full_amount)
    {
        if (is_null($ts_full_amount)) {
            array_push($this->openAPINullablesSetToNull, 'ts_full_amount');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ts_full_amount', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ts_full_amount'] = $ts_full_amount;

        return $this;
    }

    /**
     * Gets imported_by
     *
     * @return string|null
     */
    public function getImportedBy()
    {
        return $this->container['imported_by'];
    }

    /**
     * Sets imported_by
     *
     * @param string|null $imported_by Issued document imported by software
     *
     * @return self
     */
    public function setImportedBy($imported_by)
    {
        if (is_null($imported_by)) {
            array_push($this->openAPINullablesSetToNull, 'imported_by');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('imported_by', $nullablesSetToNull, true);
            if ($index !== false) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['imported_by'] = $imported_by;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link     https://fattureincloud.it
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


