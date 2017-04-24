<?php
namespace App\Controller;

class Buffer extends \App\Page {

	private $controller = '';

	public function action_copy(){
		$id = $this->request->param('id');
		$this->controller = $this->request->get('controller');
		$this->buffer('add','copy',$id);
		$model = 'element';
		if ($this->controller == 'components')
		{
			$model = 'component';
		}
		//$element = $this->pixie->orm->get($model)->where('id',$id)->find();
		$this->view->subview = 'back';
		//$this->response->redirect('/'.$this->controller.'/view/'.(isset($element->parent_id) ? $element->parent_id : ''));
		//$this->execute = false;
	}

	public function action_cut(){
		$id = $this->request->param('id');
		$this->controller = $this->request->get('controller');
		//$element = $this->pixie->orm->get('element')->where('id',$id)->find();
		if ($this->controller != 'components')
			$this->buffer('add','cut',$id);
		$this->view->subview = 'back';
		//$this->response->redirect('/'.$this->controller.'/view/'.(isset($element->parent_id) ? $element->parent_id : ''));
		//$this->execute = false;
	}

	public function action_clear(){
		$id = $this->request->param('id');
		$this->controller = $this->request->get('controller');
		//$element = $this->pixie->orm->get('element')->where('id',$id)->find();
		if ($id)
			$this->buffer('delete','',$id);
		else
			$this->pixie->session->remove($this->controller.'_buffer');
		//$this->response->redirect('/'.$this->controller.'/view/'.(isset($element->parent_id) ? $element->parent_id : ''));
		//$this->execute = false;
		$this->view->subview = 'back';
	}

	private function buffer($action,$param,$value){
		$session = $this->pixie->session;
		$buffer = $session->get($this->controller.'_buffer');
		if (!$buffer)
			$buffer = array();
		if (!isset($buffer[$value]))
			$buffer[$value] = array();
		if ($action == 'add')
		{
			if (array_search($value, $buffer) === false)
				$buffer[$value] = $param;
		}
		elseif ($action == 'delete')
		{
			unset($buffer[$value]);
		}
		if (count($buffer) > 0)
			$session->set($this->controller.'_buffer',$buffer);
		else
			$session->remove($this->controller.'_buffer',$buffer);
	}

	public function action_paste(){
		$id = $this->request->get('id');
		
		$this->controller = $this->request->get('controller');

		$model = 'element';
		if ($this->controller == 'components')
			$model = 'component';

		if (!empty($id))
			$object = $this->pixie->orm->get($model)->where('id',$id)->find();
		
		$buffer = $this->pixie->session->get($this->controller.'_buffer');
		foreach ($buffer as $objectid => $action)
		{
			if ($action == 'copy')
			{
				$o = $this->pixie->orm->get($model)->where('id',$objectid)->find();
				$o->name = $o->name . ' Copy';
				if ($model == 'element')
				{
					$this->copy_element($o,$object);
				}
				if ($model == 'component')
				{
					$this->copy_component($o);
				}
			}
			elseif ($action == 'cut')
			{
				if ($model == 'element')
				{
					$o = $this->pixie->orm->get($model)->where('id',$objectid)->find();
					$o->parent_id = $id;
					$o->save();
				}
				$this->pixie->session->remove($this->controller.'_buffer');
			}
		}
		$this->view->subview = 'back';
		//$this->response->redirect('/'.$this->controller.'/view/'.(isset($object->id) ? $object->id : ''));
		//$this->execute = false;
	}

	private function copy_component($component){
		$newobject = $this->pixie->orm->get('component');
		$newobject->name = $component->name;
		$newobject->order = $component->order+1;
		$newobject->code = $component->code;
		$newobject->user = $this->pixie->auth->user();

		$newobject->save();

		foreach ($component->componentproperties->find_all() as $property) {
			$newproperty = $this->pixie->orm->get('componentproperty');
			$newproperty->name = $property->name;
			$newproperty->value = $property->value;
			$newproperty->category_id = $property->category_id;
			$newproperty->component_id = $newobject->id;
			$newproperty->save();
		}

		foreach ($component->files->find_all() as $file) {
			$newfile = $this->pixie->orm->get('file');
			$newfile->name = $file->name;
			$newfile->name_template = $file->name_template;
			$newfile->path = $file->path;
			$newfile->content = $file->content;
			$newfile->component_id = $newobject->id;
			$newfile->save();
		}
	}

	private function copy_element($old,$new = null)
	{
		static $ids_mapping = array();
		$newobject = $this->pixie->orm->get('element');
		$newobject->name = $old->name;
		$newobject->type_id = $old->type_id;
		$newobject->category_id = $old->category_id;
		$newobject->is_template = $old->is_template;
		$newobject->user_id = $this->view->user->id;

		if ($old->inherit_id != null)
			$newobject->inherit_id = $ids_mapping[$old->inherit_id];
		else
			$newobject->inherit_id = $old->inherit_id;

		if ($new)
			$newobject->parent_id = $new->id;
		else
			$newobject->parent_id = $old->parent_id;
		$newobject->order = $old->order;
		//$newobject->id = rand(1000,9999);
		//echo "newobject->id = ". $newobject->id . " newobject->parent_id = ". $newobject->parent_id ." newobject->inherit_id = ". $newobject->inherit_id ." old->parent_id = " . $old->parent_id." old->id = " . $old->id." old->inherit_id = " . $old->inherit_id." old->name = " . $old->name . "<br>";
		$newobject->save();
		$ids_mapping[$old->id] = $newobject->id;
		if (isset($old->properties))
		{
			//echo "copying properties of a old<br>";
			//*
			foreach ($old->properties->find_all() as $property)
			{
				$newproperty = $this->pixie->orm->get('property');
				$newproperty->name = $property->name;
				$newproperty->value = $property->value;
				$newproperty->category_id = $property->category_id;
				$newproperty->element_id = $newobject->id;
				$newproperty->save();
			}//*/
		}
		if (isset($old->templates))
		{
			//echo "copying templates of a old<br>";
			//*
			foreach ($old->templates->find_all() as $template)
			{
				$newobject->add('templates',$template);
			}
			$newobject->save();
			//*/
		}
		//var_dump($old->inherit_id);
		//var_dump(($old->inherit_id != null));
		if (isset($old->children) && $old->inherit_id == null)
		{
			//echo "copying children of a old<br>";
			//var_dump($old->children->query()->query());exit;
			foreach($old->children->order_by('order')->find_all() as $c)
			{
				if ($c->parent_id == $newobject->inherit_id) continue;
				//echo "copying from " . $c->name . " to " . $newobject->name ."<br>";
				$this->copy_element($c,$newobject);
			}
		}
	}
}