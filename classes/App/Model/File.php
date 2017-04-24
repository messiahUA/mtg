<?php

namespace App\Model;
 
class File extends \PHPixie\ORM\Model {
	protected $belongs_to = array('component');
}