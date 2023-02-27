<?php
/**
 * @package     Box
 * @subpackage  Box_Event
 * @author      Floris de Leeuw
 * @copyright   (C)Copyright 2014 
 */

namespace Box\Model\Event;


interface EventInterface
{
    public function __construct($options = null);
    public function getEventId();

}
