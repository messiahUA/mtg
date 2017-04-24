<div class="row-fluid">
	<?php if (isset($template)) { ?>
	<div class="page-header">
    <h3><?php echo $template->name;?> <small>Template</small></h3>
    </div>
	<h4>Attributes</h4>
	<form method='POST' class="form-inline" action='/templates/edit/<?php echo $template->id; ?>'>
		<label>Name:</label>
		<input type="text" name="name" class="input-large" value="<?php echo $template->name;?>"<?php if ($user->id != $template->user_id) { ?> disabled<?php } ?>>
		<?php if ($user->id == $template->user_id) { ?>
		<button type="submit" class="btn">Save</button>
		<?php } ?>
	</form>
	<?php } ?>

	<h4>Templates</h4>
	<?php if (isset($templates) && $templates->valid()) { ?>
    <!-- <form class="form-inline" method="POST" action="/templates/edit/"> -->
	<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<!-- <td></td> -->
			<td>Name</td>
			<td>Linked with</td>
			<td>Actions</td>
			<td>Owner</td>
		</tr>
	</thead>
	<tbody>
	<?php foreach($templates as $t):?>
		<tr>
			<!-- <td style="width: 1px;">
    			<input type="checkbox" class="checkbox" name="selected_templates[]" value="<?php echo $t->id;?>">
    		</td> -->
    		<td style="width: 50%;">
    			<a href="/templates/view/<?php echo $t->id;?>"><?php echo $t->name;?></a>
    		</td>
    		<td>
    			<?php foreach($t->templates->find_all() as $linked):?>
    			<?php echo '<a href="/templates/view/'.$linked->id.'">'.$linked->name.'</a> '; ?>
    			<?php endforeach;?>
    		</td>
    		<td class="text-center">
    			<!-- <a href='' data-placement="top" data-toggle="tooltip" title="Copy"><i class="icon-arrow-right"></i></a>
    			<a href='' data-placement="top" data-toggle="tooltip" title="Cut"><i class="icon-share"></i></a> -->
    			<?php if ($user->id == $t->user_id) { ?>
    			<?php if (isset($template)) { ?>
    				<a href='/templates/unlink/?templateid=<?php echo $t->id;?>&amp;elementid=<?php echo $template->id;?>' data-placement="top" data-toggle="tooltip" title="Unlink"><i class="icon-resize-small"></i></a>
    			<?php } else { ?>
    				<a href='/templates/delete/<?php echo $t->id;?>' data-placement="top" data-toggle="tooltip" title="Delete"><i class="icon-remove"></i></a>
    			<?php } } ?>
			</td>
			<td>
				<?php echo $t->user->username ?>
			</td>
	<?php endforeach;?>
	</tbody>
	</table>
	<!-- <div class="well well-small">
		<input type="checkbox" class="checkbox" name="select_all_templates">
		With selected:
    	<button type="submit" class="btn btn-small">Delete</button>
	</div> -->
	</form>
<?php } ?>

	<div class="row-fluid">
	<?php if (isset($template)) { ?>
    <form class="form-inline" method="POST" action="/templates/link/<?php echo $template->id ?>">
    	<select name="template"<?php if ($user->id != $template->user_id) { ?> disabled<?php } ?>>
    	<?php foreach($alltemplates as $t):?>
    	<option value="<?php echo $t->id ?>"><?php echo $t->name ?></option>
    	<?php endforeach;?>
    	</select>
		<?php if ($user->id == $template->user_id) { ?>
    	<button type="submit" class="btn">Link</button>
		<?php } ?>
    </form>
	<?php } else { ?>
    <form class="form-inline" method="POST" action="/templates/add/">
    	<input type="text" name="name" class="input-xxlarge" placeholder="Template name">
    	<button type="submit" class="btn">Add</button>
    </form>
	<?php } ?>
	</div>

	<?php if (isset($template) || (isset($properties) && $properties->valid())) { ?>
	<h4>Properties</h4>
 	<table class="table table-striped table-condensed">
		<?php foreach($properties as $property):?>
		<?php $owner = $this->pixie->orm->get('element',$property->element_id); ?>
	            <tr>
	                <!-- <td style="width: 1px;"><input type="checkbox" class="checkbox" name="selected_properties" value="<?php echo $property->id;?>"></td> -->
	                <td>
	                    <?php echo ($property->element_id != $template->id) ? '<a href="/templates/view/'.$owner->id.'">'.$owner->name.'</a>: ' : ''; ?><a href="/properties/view/<?php echo $property->id;?>"><?php echo $property->name;?></a><?php if ($property->category->name != "None") {?> (<?php echo $property->category->name;?>)<?php } ?>
	                </td>
	                <td>
	                	<?php if ($user->id == $template->user_id) { ?>
	                		<a href='/properties/delete/<?php echo $property->id;?>' data-placement="top" data-toggle="tooltip" title="Delete"><i class="icon-remove"></i></a>
	                	<?php } ?>
	                	</td>
	            </tr>
	            <tr>
	            	<td><pre><?php echo $property->value ?></pre></td>
	            	<td></td>
	            </tr>
		<?php endforeach;?>
	</table>

	<?php if ($user->id == $template->user_id) { ?>
	<div class="row-fluid">
	<form method="POST" action="/properties/add/">
    	<input type="hidden" name="id" value="<?php echo $template->id; ?>">
    	<label>Name</label>
    	<input type="text" name="name" class="input-medium" placeholder="Name"><br>
    	<label>Category</label>
		<select name="category">
		<?php foreach($categories as $category):?>
		<option value="<?php echo $category->id ?>"><?php echo $category->name ?></option>
		<?php endforeach;?>
		</select><br>
		<label>Value</label>
    	<textarea name="value" placeholder="Value"></textarea><br>
    	<button type="submit" class="btn">Add</button>
    </form>
	</div>
	<?php } ?>
<?php } ?>
</div>

<script type="text/javascript">$(document).ready(function () {$("a").tooltip({'selector': ''});});</script>