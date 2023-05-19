<?php

/**
 * @author      Chance Garcia
 * @copyright   (C)Copyright 2013 chancegarcia.com
 */

namespace Box\Model;

use Box\Exception\Exception;

class Model
{
    // @todo add curl history on info/error/errno properties for child classes to access for recording
    // @todo add last curl info/error/errno properties as well

    public function __construct($options = null)
    {
        if (null !== $options) {
            foreach ($options as $k => $v) {
                $method = 'set'.ucfirst($this->toClassVar($k));
                if (method_exists($this, $method)) {
                    $this->{$method}($v);
                }
            }
        }

        return $this;
    }

    public function toArray()
    {
        $aModel = get_object_vars($this);
        $aArray = [];

        foreach ($aModel as $k => $v) {
            $sKey = $this->toBoxVar($k);
            $aArray[$sKey] = $v;
        }

        return $aArray;
    }

    /**
     * used to throw exceptions that need to contain error information returned from Box.
     *
     * @param $data array containing error and error_description keys
     *
     * @throws \Box\Exception\Exception
     */
    public function error($data)
    {
        //$exception = \Box\Exception\Exception($data['error']);
        //$exception->setError($data['error']);
        //$exception->setErrorDescription($data['error_description']);
        throw new \Box\Exception\Exception($data['error'].' : '.$data['error_description']);
    }

    /**
     * @param string $class
     * @param string $classType
     *
     * @throws \Box\Exception\Exception
     *
     * @return bool returns true if validation passes. Throws exception if unable to validate or validation doesn't pass
     */
    public function validateClass($class, $classType)
    {
        if (!is_string($class)) {
            throw new Exception('Please specify a class string to validate', Exception::INVALID_INPUT);
        }

        if (!is_string($classType)) {
            throw new Exception('Unable to validate. Please specify a class type to validate', Exception::INVALID_CLASS_TYPE);
        }

        if (!class_exists($class)) {
            throw new Exception('Unable to find class', Exception::UNKNOWN_CLASS);
        }
        $oClass = new $class();

        if (!$oClass instanceof $classType) {
            throw new Exception('Invalid Connection Class', Exception::INVALID_CLASS_TYPE);
        }

        return true;
    }

    public function buildQuery($params, $numericPrefix = null)
    {
        return http_build_query($params, $numericPrefix, '&', PHP_QUERY_RFC3986);
    }

    public function toClassVar($str)
    {
        $aTokens = explode('_', $str);
        $sFirst = array_shift($aTokens);
        $aTokens = array_map('ucfirst', $aTokens);
        array_unshift($aTokens, $sFirst);

        return implode('', $aTokens);
    }

    public function toBoxVar($str)
    {
        $aTokens = preg_split('/(?<=\\w)(?=[A-Z])/', $str);
        $sFirst = array_shift($aTokens);
        $aTokens = array_map('lcfirst', $aTokens);
        array_unshift($aTokens, $sFirst);

        return implode('_', $aTokens);
    }

    /**
     * this will bomb out if any properties are private.
     *
     * @todo try using setter if found?
     *
     * @param $aData
     *
     * @return $this
     */
    public function mapBoxToClass($aData)
    {
        foreach ($aData as $k => $v) {
            $sClassProp = $this->toClassVar($k);
            $sSetterMethod = 'set'.ucfirst($sClassProp);
            if (method_exists($this, $sSetterMethod)) {
                $this->{$sSetterMethod}($v);
            } else {
                $this->{$sClassProp} = $v;
            }
        }

        return $this;
    }

    public function getNewClass($className = null, $classConstructorOptions = null)
    {
        if (null === $className) {
            throw new Exception('undefined class name', Exception::INVALID_INPUT);
        }

        $sMethod = 'get'.ucfirst($className).'Class';

        $sClass = $this->{$sMethod}();

        return new $sClass($classConstructorOptions);
    }
}
