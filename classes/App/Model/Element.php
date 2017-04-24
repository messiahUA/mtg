<?php

namespace App\Model;
 
class Element extends \PHPixie\ORM\Model
{
	private $override_parent = false;

	private $subtree_ids = array();

	protected $has_many = array(
		'properties',
		'children'=>array('model'=>'element','key'=>'parent_id'),
		'templates'=>array('model'=>'element','through'=>'elements_templates','key'=>'element_id','foreign_key'=>'template_id')
		);
	protected $belongs_to = array('user','category','type','parent'=>array('model'=>'element','key'=>'parent_id'),'inherit'=>array('model'=>'element','key'=>'inherit_id'));

	public function isOwner()
	{
		$user = $this->pixie->auth->user();
		foreach ($user->roles->find_all() as $role) {
			if ($role->name == 'admin')
				return true;
		}
		return ($user->id == $this->user_id);
	}
	private function ancestors_recursion()
	{
		//echo "<br>";
		$array = array();
		$parent = $this->parent;
		/*if ($this->inherit->loaded() && !$this->override_parent)
		{
			$this->parent_id = $this->inherit->id;
			$this->override_parent = true;
			$this->_parent_ = $this->inherit;
		}*/
		if ($this->override_parent)
			$parent = $this->_parent_;
		if (!$parent->loaded())
			return $array;
		//echo "parent of " . $this->name . " is " . $parent->name . "<br>";
		array_push($array,$parent);
		return array_merge($array,$parent->ancestors_recursion());
	}
	public function subtree($ids)
	{
		$this->subtree_ids = $ids;
		/*echo "subtree_ids =\r\n";
		var_dump($ids);
		echo "\r\n";*/
		return $this->subtree;
	}
	function get($property)
	{
		if ($property == 'descendants')
		{
			$descendants = array();
			//echo "in element " . $this->name . " " . $this->id . "\r\n";
			foreach ($this->children->order_by('order','asc')->find_all() as $key => $value)
			{
				//echo "Checking " . $value->name . " " . $value->id . "\r\n";
				if (isset($this->subtree_ids) && count($this->subtree_ids) > 0)
				{
					/*echo "\r\n";
					var_dump(!array_key_exists($value->id, $this->subtree_ids));
					var_dump(($value->type->name != 'Unit'));
					var_dump($value->type->name);
					echo "\r\n";
					echo "\r\n";
					var_dump($this->subtree_ids);
					echo "\r\n";*/
					if (!array_key_exists($value->id, $this->subtree_ids) && $value->type->name != 'Unit')
					{
						//echo "Skipping " . $value->name . "\r\n";
						continue;
					}
					//exit;
					if (array_key_exists($value->id, $this->subtree_ids) && count($this->subtree_ids[$value->id]) > 0)
					{
						$value->subtree_ids = $this->subtree_ids[$value->id];
					}
					//exit;
				}
				//echo "Adding child " . $value->name . " " . $value->id . "\r\n";
				if ($this->inherit->loaded() || $this->override_parent)
				{
					$value->parent_id = $this->id;
					$value->override_parent = true;
					$value->_parent_ = $this;
				}
				//echo "parent_id = " . $value->parent_id . "\r\n";
				//echo "override_parent = " . $value->override_parent . "\r\n";
				$descendants[] = array(
					'id'=>$value->id,
					'name'=>$value->name,
					'type'=>$value->type->name,
					'properties'=>$value->properties_as_array,
					'children'=>$value->descendants
				);
			}

			return $descendants;
		}
		if ($property == 'descendants_withoutproperties')
		{
			$descendants = array();
			foreach ($this->children->order_by('order','asc')->find_all() as $value) {
				if ($this->inherit->loaded() || $this->override_parent)
				{
					$value->parent_id = $this->id;
					$value->override_parent = true;
					$value->_parent_ = $this;
				}
				$descendants[] = array(
					'id'=>$value->id,
					'name'=>$value->name,
					'type'=>$value->type->name,
					'children'=>$value->descendants_withoutproperties
				);
			}

			return $descendants;
		}
		if ($property == 'descendants_withoutproperties_plain')
		{
			$descendants = array();
			foreach ($this->children->order_by('order','asc')->find_all() as $value) {
				/*if ($this->inherit->loaded() || $this->override_parent)
				{
					$value->parent_id = $this->id;
					$value->override_parent = true;
					$value->_parent_ = $this;
				}*/
				$descendants[] = $value;
				if (!$value->inherit->loaded())
					$descendants = array_merge($descendants,$value->descendants_withoutproperties_plain);
			}

			return $descendants;
		}
		if ($property == 'subtree'){
			return array(
				'id'=>$this->id,
				'name'=>$this->name,
				'type'=>$this->type->name,
				'properties'=>$this->properties_as_array,
				'children'=>$this->descendants
			);
		}
		if ($property == 'subtree_withoutproperties'){
			return array(
				'id'=>$this->id,
				'name'=>$this->name,
				'type'=>$this->type->name,
				'children'=>$this->descendants_withoutproperties
			);
		}
		if ($property == 'ancestors'){
			return $this->ancestors_recursion();
		}
		if ($property == 'siblings'){
			if ($this->parent_id == 0)
				$this->pixie->orm->get('element')->where('parent_id',0)->find_all();

			return $this->parent->children->find_all();
		}
		if ($property == 'properties_as_array'){
			$properties = array();
			$array = $this->properties_with_templates;

			$patterns = array();
			$replacements = array();

			$loop_count = 0;
			do
			{
				$loop_count++;
				$process = false;
				foreach ($array as $value)
				{
					$patterns[] = '/\$\{'.$value->name.'\}/';
					$replacements[] = $value->value;
				}
				for ($i=0; $i < count($array); $i++)
				{
					$property = $array[$i];
					if (preg_match('/\$\{.*\}/', $property->value) != 0)
					{
						$property->value = preg_replace($patterns, $replacements, $property->value);
						$process = true;
						$array[$i] = $property;
					}
				}
				if ($loop_count > 100)
					break;
			} while ($process);

			foreach ($array as $key => $value) {
				$properties[$value->name] = array('category'=>(isset($value->category->name)) ? $value->category->name : '','value'=>$value->value);
			}

			return $properties;
		}
		if ($property == 'properties_with_templates')
		{
			$properties = array();
			// For inherited templates of element
			$properties_helper = array();
			foreach ($this->properties->find_all()->as_array() as $value)
			{
				$add = true;
				foreach ($properties as $v) {
					if ($v->name == $value->name)
					{
						$add = false;
						break;
					}
				}
				if ($add)
					$properties[] = $value;
			}
/*echo "properties for ". $this->name .":<br>\r\n";
foreach ($properties as $value) {
	echo $value->name . ' = ' . $value->value . "<br>\r\n";
}*/

			// Checking all templates
			foreach ($this->templates->find_all()->as_array() as $template)
			{
				//echo "checking template ".$template->name."<br>\r\n";

				foreach($template->properties_with_templates as $value)
				{
					$add = true;
					foreach ($properties as $v) {
						if ($v->name == $value->name)
						{
							$add = false;
							break;
						}
					}
					if ($add)
					{
						if (isset($template->element_id))
							$properties_helper[$template->element_id][] = $value;
						else
							$properties[] = $value;
					}
				}
			}

/*echo "properties_helper ". $this->name .":<br>\r\n";
if (isset($properties_helper[115]))
{
foreach ($properties_helper[115] as $key => $value) {
	//echo $value->name . ' = ' . $value->value . "<br>\r\n";
}
}
if ($this->id == 164)
exit;*/
			if (count($properties_helper) > 0)
			{
				// First check for templates belonging to element itself
				if (isset($properties_helper[$this->id]))
				{
					foreach ($properties_helper[$this->id] as $value) {
						$add = true;
						foreach ($properties as $v) {
							if ($v->name == $value->name)
							{
								$add = false;
								break;
							}
						}
						if ($add)
							$properties[] = $value;
					}
					unset($properties_helper[$this->id]);
				}
				// Then add all templates belonging to inherited elements
				foreach ($properties_helper as $array)
				{
					if (!is_array($array))
						continue;
					foreach ($array as $value)
					{
						$add = true;
						foreach ($properties as $v)
						{
							if ($v->name == $value->name)
							{
								$add = false;
								break;
							}
						}
						if ($add)
							$properties[] = $value;
					}
				}
			}

			// Checking all ancestors
			$ancestors = $this->ancestors;
			foreach ($ancestors as $ancestor)
			{
				foreach ($ancestor->properties_with_templates as $key => $value)
				{
					$add = true;
					foreach ($properties as $v) {
						if ($v->name == $value->name)
						{
							$add = false;
							break;
						}
					}
					if ($add)
						$properties[] = $value;
				}
			}
			return $properties;
		}
		if (($property == 'children' || $property == 'properties' || $property == 'templates'))
		{
			if (isset($this->inherit_id) && $this->inherit_id > 0)
			{
				$inherit = $this->pixie->orm->get('element')->where('id',$this->inherit_id)->find();
				$p = $inherit->$property;
				$p->query->where('or',array('elements.id',$this->id));
				// Find out who is owner for inherited templates
				if ($property == 'templates')
				{
					$p->query->group_by('name');
					$p->query->fields('*','a0.element_id');
				}
				if ($property == 'properties')
				{
					$p->order_by($this->pixie->db->expr('FIELD (`element_id`,'.$this->id.','.$this->inherit_id.')'));
					//var_dump($p->query()->query());
					//exit;
				}
				return $p;
			}
			return null;
		}
	}
}