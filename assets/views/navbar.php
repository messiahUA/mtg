<div class="navbar">
	<div class="navbar-inner">
	   	<span class="brand">MTG</span>
	   	<ul class="nav">
		   	<li<?php echo ($subview == 'home') ? ' class="active"':''?>><a href="/">Generator</a></li>
		   	<li<?php echo ($controller == 'elements') ? ' class="active"':''?>><a href="/elements/view/">Elements</a></li>
		   	<li<?php echo ($controller == 'components') ? ' class="active"':''?>><a href="/components/view/">Components</a></li>
		   	<li<?php echo ($controller == 'templates') ? ' class="active"':''?>><a href="/templates/view/">Templates</a></li>
		   	<li<?php echo ($controller == 'users') ? ' class="active"':''?>><a href="/users/view/">Users</a></li>
	   	</ul>
	   	<?php if (!$logged) { ?>
	   	<form class="navbar-form pull-right" method="POST" action="/users/login">
		   	<input type="text" name="username" class="input-small" placeholder="Username">
	   		<input type="password" name="password" class="input-small" placeholder="Password">
	   		<button type="submit" class="btn">Sign in</button>
	   	</form>
	   	<?php } else { ?>
	   	<p class="navbar-text pull-right">Logged as <?php echo $user->username ?> (<?php foreach ($user->roles->find_all() as $role) { echo $role->name; } ?>)
	   	<a href="/users/logout">Log out</a></p>
	   	<?php } ?>
   	</div>
</div>