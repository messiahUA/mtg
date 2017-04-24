<?php
namespace App\Controller;

class Componentproperties extends \App\Page {
	public function action_add(){
		if ($this->request->method == 'POST') {
			$id = $this->request->post('id');
			$name = $this->request->post('name');
			$value = $this->request->post('value');
			$category = $this->request->post('category');

			$property = $this->pixie->orm->get('componentproperty');
			$property->name = $name;
			$property->value = $value;
			$property->component_id = $id;
			$property->category_id = $category;
			$property->save();

			$this->response->redirect('/components/view/'.$id);
			$this->execute = false;
		}
	}
	public function action_delete(){
		$id = $this->request->param('id');

		$property = $this->pixie->orm->get('componentproperty')->where('id',$id)->find();
		$component_id = $property->component_id;
		$property->delete();
		
		$this->response->redirect('/components/view/'.$component_id);
		$this->execute = false;
	}
	// Ajax
	public function action_edit(){
		if ($this->request->method == 'POST') {
			$id = $this->request->post('id');
			$name = $this->request->post('name');
			$value = $this->request->post('value');
			$category = $this->request->post('category');

			$property = $this->pixie->orm->get('componentproperty')->where('id',$id)->find();
			$property->name = $name;
			$property->value = $value;
			$property->category_id = $category;
			$property->save();

			//$this->response->redirect('/components/view/'.$property->component_id);
			$this->execute = false;
		}
	}
}