<?php

return array(
    'default' => array(
        'model' => 'user',
 
        //Login providers
        'login' => array(
            'password' => array(
                'login_field' => 'username',
                'password_field' => 'password',
                'hash_method' => false
            )
        ),
 
        //Role driver configuration
        'roles' => array(
            'driver' => 'relation',
            'type' => 'has_many',
 
            //Field in the roles table
            //that holds the models name
            'name_field' => 'name',
            'relation' => 'roles'
        )
    )
);