<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Subscription extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
