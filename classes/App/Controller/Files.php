<?php
namespace App\Controller;

class Files extends \App\Page {
	public function action_add() {
		$id = $this->request->post('id');
		$name = $this->request->post('name');
		$name_template = $this->request->post('name_template');
		$path = $this->request->post('path');

		$file = $this->pixie->orm->get('file');
		$file->name = $name;
		$file->name_template = $name_template;
		$file->path = $path;
		$file->content = '';

		$component = $this->pixie->orm->get('component')->where('id',$id)->find();
		$file->component_id = $component->id;

		$file->save();
		
		$this->response->redirect('/components/view/'.$id);
		$this->execute = false;
	}
	public function action_edit() {
		$id = $this->request->param('id');
		$name = $this->request->post('name');
		$name_template = $this->request->post('name_template');
		$path = $this->request->post('path');
		$content = $this->request->post('content',null,false);
		//$content = $_POST['content'];

		$file = $this->pixie->orm->get('file')->where('id',$id)->find();
		$file->name = $name;
		$file->name_template = $name_template;
		$file->path = $path;
		$file->content = $content;
		$file->save();

		/*$query = $this->pixie->db->query('update')->table('files')
		->data(array('content' => $content))
		->where('id',$id)
		->execute();*/
		//var_dump($query->query());

		$this->response->redirect('/components/view/'.$file->component->id);
		$this->execute = false;
	}
	public function action_delete() {
		$id = $this->request->param('id');
		
		$file = $this->pixie->orm->get('file')->where('id',$id)->find();
		$component = $file->component;
		
		$file->delete();
		
		$this->response->redirect('/components/view/'.$component->id);
		$this->execute = false;
	}
}