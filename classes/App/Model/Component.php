<?php

namespace App\Model;
 
class Component extends \PHPixie\ORM\Model {
	protected $has_many = array(
		'files',
		'componentproperties',
		'dependson'=>array('model'=>'component','through'=>'components_dependency','key'=>'component_id','foreign_key'=>'dependent_id')
	);
	protected $belongs_to = array('user');

	public function isOwner()
	{
		$user = $this->pixie->auth->user();
		foreach ($user->roles->find_all() as $role) {
			if ($role->name == 'admin')
				return true;
		}
		return ($user->id == $this->user_id);
	}
	function get($property)
	{
		if ($property == 'properties_as_array'){
			$properties = array();
			$array = $this->componentproperties->find_all();

			/*$patterns = array();
			$replacements = array();

			foreach ($array as $value)
			{
				$patterns[] = '/\$\{'.$value->name.'\}/';
				$replacements[] = $value->value;
			}*/

			foreach ($array as $key => $value) {
				$properties[$value->name] = array('category'=>(isset($value->category->name)) ? $value->category->name : '','value'=>$value->value);
			}

			return $properties;
		}
	}
}