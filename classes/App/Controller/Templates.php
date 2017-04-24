<?php
namespace App\Controller;

class Templates extends \App\Page {
	public function action_view(){
		$id = $this->request->param('id');

		$this->view->subview = 'templates/view';
		
		if (!$id)
		{
			$this->view->templates = $this->pixie->orm->get('element')
			->order_by('name','asc')
			->where('is_template',1)
			->find_all();
		}
		else
		{
			$template = $this->pixie->orm->get('element')->where('id', $id)->find();
			$this->view->template = $template;
			$this->view->templates = $template->templates->find_all();
			//$this->view->properties = $template->properties->find_all();
			$this->view->properties = $template->properties_with_templates;
			$this->view->alltemplates = $this->pixie->orm->get('element')
			->where('is_template',1)
			->where('id','<>',$template->id)
			->where('and',array('id','NOT IN',
				$this->pixie->db->expr('(SELECT template_id FROM elements_templates WHERE element_id = '.$template->id.')')))
			->find_all()
			->as_array();
		}
		$this->view->categories = $this->pixie->orm->get('category')->order_by('id')->find_all()->as_array();
	}
	public function action_edit(){
		if ($this->request->method == 'POST') {
			$id = $this->request->param('id');
			$name = $this->request->post('name');

			$template = $this->pixie->orm->get('element')->where('id',$id)->find();
			$template->name = $name;

			$template->save();

			$this->response->redirect('/templates/view/'.$id);
			$this->execute = false;
		}
	}
	public function action_add(){
		if ($this->request->method == 'POST') {
			$name = $this->request->post('name');
			if (!empty($name))
			{
				$template = $this->pixie->orm->get('element');
				$template->name = $name;
				$template->is_template = 1;
				$template->user_id = $this->view->user->id;
				$template->save();
			}
			$this->response->redirect('/templates/view/');
			$this->execute = false;
		}
	}
	public function action_link(){
		if ($this->request->method == 'POST') {
			$id = $this->request->param('id');
			$templateid = $this->request->post('template');
			if (!empty($id) && !empty($templateid))
			{
				$object = $this->pixie->orm->get('element')->where('id',$id)->find();
				//echo "adding " . $this->pixie->orm->get('element')->where('id',$templateid)->find()->name . ' to ' . $template->name;
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
	public function action_delete(){
		$templateid = $this->request->param('id');
		if (!empty($templateid))
		{
			$template = $this->pixie->orm->get('element')->where('id',$templateid)->find();
			if ($template->loaded())
			{
				foreach ($template->templates->find_all() as $t){
					$template->remove('templates',$t);
				}
				$template->save();
				$template->delete();
			}
		}
		$this->response->redirect('/templates/view/');
		$this->execute = false;
	}
}