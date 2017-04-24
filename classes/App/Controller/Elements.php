<?php
namespace App\Controller;

class Elements extends \App\Page {

	public function action_view(){
		$id = $this->request->param('id');

		$this->view->subview = 'elements/view';
		
		if (!$id)
		{
			$this->view->children = $this->pixie->orm->get('element')->order_by('order','asc')->where('parent_id',0)->find_all();
		}
		else
		{
			$element = $this->pixie->orm->get('element', $id);
			$this->view->element = $element;
			if (isset($element->inherit_id) && $element->inherit_id > 0)
			{
				$inherit = $this->pixie->orm->get('element')->where('id',$element->inherit_id)->find();
				$this->view->inherit = $inherit;
			}
			
			$this->view->children = $element->children->order_by('order','asc')->find_all();
			$this->view->ancestors = array_reverse($element->ancestors);
			$this->view->properties = $element->properties_with_templates;
			$this->view->templates = $element->templates->order_by('name','asc')->find_all();
			$this->view->siblings = $this->pixie->orm->get('element')
				->where('parent_id',$element->parent_id)
				->where('id','<>',$id)
				->find_all();
			$this->view->alltemplates = $this->pixie->orm->get('element')
				->where('is_template',1)
				->where('id','NOT IN',
				$this->pixie->db->expr('(SELECT template_id FROM elements_templates WHERE element_id = '.$element->id.')'))
				->order_by('name','asc')
				->find_all()->as_array();
			$this->view->isOwner = $element->isOwner();
		}

		$this->view->types = $this->pixie->orm->get('type')->find_all()->as_array();
		$this->view->categories = $this->pixie->orm->get('category')->order_by('id')->find_all()->as_array();
		$this->view->users = $this->pixie->orm->get('user')->order_by('username')->find_all()->as_array();
	}

	public function action_add(){
		if ($this->request->method == 'POST') {
			$id = $this->request->post('id');
			$name = $this->request->post('name');
			$type = $this->request->post('type');
			$category = $this->request->post('category');
			if (!empty($name) && !empty($type))
			{
				$element = $this->pixie->orm->get('element');
				if (!empty($id))
				{
					$parent = $this->pixie->orm->get('element')->where('id',$id)->find();
					if (isset($parent->id))
					{
						$element->parent_id = $parent->id;
					}
				}
				else
				{
					$element->parent_id = 0;	
				}
				$element->name = $name;
				$element->type_id = $type;
				$result = $this->pixie->db->query('select')->fields($this->pixie->db->expr("MAX(`order`) as `maxorder`"))->table('elements')->where('parent_id',$element->parent_id)->execute()->as_array();
				$element->order = $result[0]->maxorder+1;
				$element->category_id = $category;
				$element->user_id = $this->view->user->id;
				$element->save();
				if ($element->parent_id == 0)
				{
					$this->response->redirect('/elements/view/');
				}
				else
				{
					$this->response->redirect('/elements/view/'.$element->parent_id);
				}
			}
			$this->execute = false;
		}
	}

	public function action_edit(){
		if ($this->request->method == 'POST') {
			$id = $this->request->param('id');
			$name = $this->request->post('name');
			$type = $this->request->post('type');
			$category = $this->request->post('category');
			$inherit = $this->request->post('inherit');
			$user = $this->request->post('owner');
			$recursive = $this->request->post('recursive');

			$element = $this->pixie->orm->get('element',$id);
			$element->name = $name;
			$element->type_id = $type;
			$element->category_id = $category;
			$element->user_id = $user;

			if (!empty($recursive))
			{
				foreach ($element->descendants_withoutproperties_plain as $value) {
					$value->user_id = $user;
					$value->save();
				}
			}
			
			if ($inherit == 'None')
				$element->inherit_id = null;
			else
				$element->inherit_id = $inherit;
			
			$element->save();
			$this->custom_redirect($element);
		}		
	}

	public function action_delete(){
		$id = $this->request->param('id');
		$element = $this->pixie->orm->get('element')->where('id',$id)->find();
		$parent_id = $element->parent_id;
		$this->delete_element($element);
		if ($parent_id == 0)
		{
			$this->response->redirect('/elements/view/');
		}
		else
		{
			$this->response->redirect('/elements/view/'.$parent_id);
		}
		$this->execute = false;
	}

	public function action_order(){
		//$this->response->redirect('/');
		//file_put_contents('/tmp/test', 't');
		var_dump($this->request);exit;
	}

	public function action_orderup(){
		$id = $this->request->param('id');
		$element = $this->pixie->orm->get('element',$id);

		$element->order = $element->order-1;
		if ($element->order < 1)
			$element->order = 1;

		$element->save();
		$this->custom_redirect($element->parent);
	}

	public function action_orderdown(){
		$id = $this->request->param('id');
		$element = $this->pixie->orm->get('element',$id);
		$element->order = $element->order+1;
		$element->save();
		$this->custom_redirect($element->parent);
	}

	private function delete_element($element){
		//echo "deleting " . $element->name . " " . $element->id . "<br>\r\n";
		if (!$element->inherit->loaded())
		{
			foreach ($element->children->find_all() as $child) {
				$this->delete_element($child);
			}
		}
		foreach ($element->templates->find_all() as $template){
			$element->remove('templates',$template);
		}
		$element->save();
		$element->properties->delete_all();
		$element->delete();
	}
	public function action_link(){
		if ($this->request->method == 'POST') {
			$id = $this->request->param('id');
			$templateid = $this->request->post('template');
			if (!empty($id) && !empty($templateid))
			{
				$object = $this->pixie->orm->get('element')->where('id',$id)->find();
				$object->add('templates',$this->pixie->orm->get('element')->where('id',$templateid)->find());
				$object->save();
				$this->custom_redirect($object);
			}
		}
	}
	public function action_unlink(){
		if ($this->request->method == 'GET') {
			$templateid = $this->request->get('templateid');
			$elementid = $this->request->get('elementid');
			if (!empty($templateid) && !empty($elementid))
			{
				$object = $this->pixie->orm->get('element')->where('id',$elementid)->find();
				$object->remove('templates',$this->pixie->orm->get('element')->where('id',$templateid)->find());
				$object->save();
				$this->custom_redirect($object);
			}
		}
	}
}
