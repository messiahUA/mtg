<?php
namespace App\Controller;

class Components extends \App\Page {
	public function action_view(){
		$id = $this->request->param('id');
		if ($id)
		{
			$this->view->subview = 'components/view';
			$component = $this->pixie->orm->get('component',$id);
			$this->view->component = $component;
			$this->view->files = $component->files->order_by('path','desc')->order_by('name','asc')->find_all();
			$this->view->properties = $component->componentproperties->order_by('name','asc')->find_all();
			$this->view->isOwner = $component->isOwner();
		}
		else
		{
			$this->view->subview = 'components/index';
			$this->view->components = $this->pixie->orm->get('component')->order_by('order')->find_all();
		}
		$this->view->categories = $this->pixie->orm->get('category')->order_by('id')->find_all()->as_array();
		$this->view->allcomponents = $this->pixie->orm->get('component')->find_all()->as_array();
		$this->view->users = $this->pixie->orm->get('user')->order_by('username')->find_all()->as_array();
	}

	public function action_add(){
		if ($this->request->method == 'POST') {
			$name = $this->request->post('name');

			if (!empty($name))
			{
				$component = $this->pixie->orm->get('component');

				$component->name = $name;
				$result = $this->pixie->db->query('select')->fields($this->pixie->db->expr("MAX(`order`) as `maxorder`"))->table('components')->execute()->as_array();
				$component->order = $result[0]->maxorder+1;
				$component->user = $this->pixie->auth->user();

				$component->save();
			}
		}
		$this->response->redirect('/components/view/');
		$this->execute = false;
	}

	public function action_edit(){
		$id = $this->request->param('id');
		$name = $this->request->post('name');
		$owner = $this->request->post('owner');
		$add_dependson = $this->request->post('add_dependson');
		$del_dependson = $this->request->post('del_dependson');
		//$code = $this->request->post('code',false);
		$code = $_POST['code'];

		$component = $this->pixie->orm->get('component')->where('id',$id)->find();

		if ($component->loaded())
		{
			$component->name = $name;
			$component->code = $code;
			$component->user_id = $owner;

			if (is_array($add_dependson) && count($add_dependson) > 0)
			{
				foreach ($add_dependson as $componentid) {
					$component->add('dependson',$this->pixie->orm->get('component')->where('id',$componentid)->find());
				}
			}

			if (is_array($del_dependson) && count($del_dependson) > 0)
			{
				foreach ($del_dependson as $componentid) {
					$component->remove('dependson',$this->pixie->orm->get('component')->where('id',$componentid)->find());
				}
			}

			$component->save();
		}

		$this->response->redirect('/components/view/'.$id);
		$this->execute = false;
	}
	public function action_delete(){
		$id = $this->request->param('id');
		
		$component = $this->pixie->orm->get('component')->where('id',$id)->find();

		if ($component->loaded())
		{
			$component->files->delete_all();
			$component->componentproperties->delete_all();
			$component->delete();
		}

		$this->response->redirect('/components/view/');
		$this->execute = false;
	}

	public function action_orderup(){
		$id = $this->request->param('id');
		$component = $this->pixie->orm->get('component',$id);
		$component->order = $component->order-1;
		if ($component->order < 1)
			$component->order = 1;
		$component->save();
		$this->view->subview = 'back';
	}

	public function action_orderdown(){
		$id = $this->request->param('id');
		$component = $this->pixie->orm->get('component',$id);
		$component->order = $component->order+1;
		$component->save();
		$this->view->subview = 'back';
	}
}