<?php
namespace App;

class Page extends \PHPixie\Controller {
	protected $auth;
	protected $view;
	
	public function before() {
		$this->view = $this->pixie->view('main');
		$controller = $this->request->param('controller');
		$buffer = $this->pixie->session->get($controller.'_buffer');
		if ($buffer)
		{
			foreach ($buffer as $id => $action) {
				if (!$this->pixie->orm->get('element')->where('id',$id)->find()->loaded())
					unset($buffer[$id]);
			}
			$this->view->buffer = $buffer;
		}
		$this->view->controller = $controller;
		$this->view->id = $this->request->param('id');
		$this->view->logged = $this->logged_in();
		$this->view->user = $this->pixie->auth->user();
		$this->view->error = $this->pixie->session->get('error');
		if (in_array($this->request->param('controller'), array('elements','components')))
			$this->check_role('mm');
		elseif (in_array($this->request->param('controller'), array('components')))
			$this->check_role('admin');
	}
	
	public function after() {
		$this->response->body = $this->view->render();
		$this->pixie->session->remove('error');
	}

	protected function logged_in(){
        if($this->pixie->auth->user() == null)
            return false;
        return true;
    }

    protected function check_role($role){
        if (!$this->logged_in() || $role && !$this->pixie->auth->has_role($role)){
          	$this->view->subview = 'nopermission';
          	$this->execute = false;
          	$this->after();
            return false;
        }
        return true;
    }

	public function custom_redirect($element){
		if ($element != null && $element->loaded())
		{
			if ($element->is_template == 0)
				$this->response->redirect('/elements/view/'.$element->id);
			else
				$this->response->redirect('/templates/view/'.$element->id);
		}
		else
			$this->response->redirect('/'.$this->request->param('controller').'/view');
		
		$this->execute = false;
	}

	public function set_error($text){
		$this->pixie->session->set('error',$text);
	}
}