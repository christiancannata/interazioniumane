<?php

/**
 * @package     Box
 * @subpackage  Box_Event
 * @author      Floris de Leeuw
 */

namespace Box\Model\Event;

use Box\Exception\Exception;
use Box\Model\Model;
use Box\Model\Event\EventInterface;

class Event extends Model implements EventInterface {

    const URI = "https://api.box.com/2.0/events";

    protected $type;
    protected $event_id;
    protected $event_type;
    protected $created_by;
    protected $session_id;
    protected $source;

    public function __construct($options = null) {
        parent::__construct($options);

        return $this;
    }

    public function getEventId() {
        return $this->event_id;
    }

    public function setEventId($event_id = null) {
        $this->event_id = $event_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy = null) {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    public function setType($type = null) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventType() {
        return $this->event_type;
    }

    public function setEventType($event_type = null) {
        $this->event_type = $event_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessionId() {
        return $this->session_id;
    }

    public function setSessionId($session_id = null) {
        $this->session_id = $session_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource() {
        return $this->source;
    }

    public function setSource($source = null) {
        if ($source['type'] === 'folder') {
            $this->source = new \Box\Model\Folder\Folder($source);
        } elseif ($source['type'] === 'file') {
            $this->source = new \Box\Model\File\File($source);
        }
        return $this;
    }

}
