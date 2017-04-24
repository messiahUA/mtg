<?php
namespace App\Model;

class User extends \PHPixie\ORM\Model{
    protected $has_many = array('roles'=>array('through'=>'users_roles'));
}