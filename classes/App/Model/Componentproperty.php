<?php

namespace App\Model;
 
class Componentproperty extends \PHPixie\ORM\Model {
	protected $belongs_to = array('component','category');
}