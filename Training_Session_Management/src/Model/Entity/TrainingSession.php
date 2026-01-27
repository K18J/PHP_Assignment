<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class TrainingSession extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    protected $_virtual = ['duration', 'is_full'];

    protected function _getDuration()
    {
        if (empty($this->start_date) || empty($this->end_date)) {
            return null;
        }

        $seconds = $this->end_date->getTimestamp() - $this->start_date->getTimestamp();
        return $seconds > 0 ? round($seconds / 3600, 2) : null;
    }

    protected function _getIsFull()
    {
        if (!isset($this->max_participants)) {
            return false;
        }

        if (isset($this->registrations) && is_iterable($this->registrations)) {
            return count($this->registrations) >= (int)$this->max_participants;
        }

        return false;
    }
}

