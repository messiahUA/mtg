<?php
namespace App\Controller;

class Users extends \App\Page{
    public function action_view(){

        /*$user = $this->pixie->orm->get('user')->where('id',1)->find();
        $password='123';
        $hash = $this->pixie->auth->provider('password')->hash_password($password);
        $user->password=$hash;
        $user->save();*/

        $this->view->users = $this->pixie->orm->get('user')->order_by('username')->find_all();
        $this->view->subview = 'users/view';
    }
 
    public function action_login() {
        if($this->request->method == 'POST'){
            $login = $this->request->post('username');
            $password = $this->request->post('password');
            $logged = $this->pixie->auth->provider('password')->login($login, $password);
        }
        $this->view->subview = 'back';
    }
 
    public function action_logout() {
        if ($this->logged_in())
            $this->pixie->auth->logout();
        $this->view->subview = 'back';
    }
}