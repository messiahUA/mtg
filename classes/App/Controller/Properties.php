<?php
namespace App\Controller;

class Properties extends \App\Page {
	public function action_add(){
		if ($this->request->method == 'POST') {
			$id = $this->request->post('id');
			$name = $this->request->post('name');
			$element = $this->pixie->orm->get('element')->where('id',$id)->find();
			if (!empty($name))
			{
				$value = $this->request->post('value');
				$category = $this->request->post('category');
				$property = $this->pixie->orm->get('property');
				$property->name = $name;
				$property->value = $value;
				$property->element_id = $id;
				$property->category_id = $category;

				if ($element->loaded())
					$property->save();
			}
			else
			{
				$this->set_error('Property name is empty!');
			}
			$this->custom_redirect($element);
		}
	}
	public function action_delete(){
		$id = $this->request->param('id');
		$property = $this->pixie->orm->get('property')->where('id',$id)->find();
		$element = $this->pixie->orm->get('element')->where('id',$property->element_id)->find();
		$property->delete();
		$this->custom_redirect($element);
	}
	public function action_view(){
		$id = $this->request->param('id');
		$property = $this->pixie->orm->get('property')->where('id',$id)->find();
		$this->view->subview = 'properties/view';
		$this->view->property = $property;
		$this->view->isOwner = $property->element->isOwner();
		$this->view->categories = $this->pixie->orm->get('category')->order_by('id')->find_all()->as_array();
	}
	public function action_edit(){
		if ($this->request->method == 'POST') {
			$id = $this->request->param('id');
			$name = $this->request->post('name');
			$value = $this->request->post('value');
			$category = $this->request->post('category');
			$property = $this->pixie->orm->get('property')->where('id',$id)->find();
			$property->name = $name;
			$property->value = $value;
			$property->category_id = $category;
			$property->save();
			//$element = $property->element;
			//$this->custom_redirect($element);
			//$this->redirect_back();
		}
		$this->execute = false;
	}
}