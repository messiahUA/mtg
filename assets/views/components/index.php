<div class="row-fluid">
	<h3>Components</h3>
	<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<td>Name</td>
			<td>Order</td>
			<td>Actions</td>
			<td>Owner</td>
		</tr>
	</thead>
	<?php foreach($components as $component):?>
		<tr>
			<td>
				<a href="/components/view/<?php echo $component->id;?>"><?php echo $component->name; ?></a>
			</td>
			<td>
	    			<?php echo $component->order; ?></span>
	    			<a href='/components/orderup/<?php echo $component->id;?>' data-placement="top" data-toggle="tooltip" title="Up"><i class="icon-chevron-up"></i></a>
	    			<a href='/components/orderdown/<?php echo $component->id;?>' data-placement="top" data-toggle="tooltip" title="Down"><i class="icon-chevron-down"></i></a>
			</td>
			<td>
    			<a href='/buffer/copy/<?php echo $component->id;?>?controller=<?php echo $controller; ?>' data-placement="top" data-toggle="tooltip" title="Copy"><i class="icon-arrow-right"></i></a>
    			<?php if ($component->isOwner()) { ?>
				<a href="/components/delete/<?php echo $component->id ?>" data-placement="top" data-toggle="tooltip" title="Delete"><i class="icon-remove"></i></a>
				<?php } ?>
			</td>
			<td>
				<?php echo $component->user->username ?>
			</td>
		</tr>
	<?php endforeach;?>
	</table>

	<form method="POST" action="/components/add/" class="form-inline">
		<input type="text" name="name" class="input-xlarge" placeholder="Component name">
		<button type="submit" class="btn">Add</button>
	</form>
</div>

<script type="text/javascript">$(document).ready(function () {$("a").tooltip({'selector': ''});});</script>