<div class="page-header">
    <h3><?php echo $property->name;?> <small>Property</small></h3>
</div>
<p><strong>Belongs to:</strong> <a href="/<?php echo ($property->element->is_template == 0) ? 'elements' : 'templates' ?>/view/<?php echo $property->element->id ?>"><?php echo $property->element->name ?></a> <?php echo ($property->element->is_template == 0) ? 'element' : 'template' ?></p>
<h4>Attributes</h4>
<form method="POST" action="/properties/edit/<?php echo $property->id ?>">
	<label>Name</label>
	<input type="text" name="name" class="input-large" placeholder="Property name" value="<?php echo $property->name ?>"<?php if (!$isOwner) { ?> disabled<?php } ?>>
	<label>Category</label>
	<select name="category"<?php if (!$isOwner) { ?> disabled<?php } ?>>
	<?php foreach($categories as $category):?>
	<option value="<?php echo $category->id ?>" <?php echo ($property->category_id == $category->id) ? 'selected="selected"' : ''?>><?php echo $category->name ?></option>
	<?php endforeach;?>
	</select>
	<label>Value</label>
	<textarea name="value" rows="10" class="input-xxlarge" placeholder="Property value"<?php if (!$isOwner) { ?> disabled<?php } ?>><?php echo $property->value ?></textarea><br>
	<?php if ($isOwner) { ?>
	<button type="submit" class="btn">Save</button>
	<?php } ?>
</form>