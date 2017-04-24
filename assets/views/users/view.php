<ul class="offset4">
<?php foreach ($users as $user) { ?>
<li><?php echo $user->username ?> <?php foreach ($user->roles->find_all() as $role) { echo $role->name.' '; } ?></li>
<?php } ?>
</ul>