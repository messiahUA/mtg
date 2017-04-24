<?php

namespace App\Model;
 
class Property extends \PHPixie\ORM\Model {
	protected $belongs_to = array('element','category');
}