<script>
jQuery(function($) { $.extend({
    form: function(url, data, method) {
        if (method == null) method = 'POST';
        if (data == null) data = {};

        var form = $('<form>').attr({
            method: method,
            action: url
         }).css({
            display: 'none'
         });

        var addData = function(name, data) {
            if ($.isArray(data)) {
                for (var i = 0; i < data.length; i++) {
                    var value = data[i];
                    addData(name + '[]', value);
                }
            } else if (typeof data === 'object') {
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        addData(name + '[' + key + ']', data[key]);
                    }
                }
            } else if (data != null) {
                form.append($('<input>').attr({
                  type: 'hidden',
                  name: String(name),
                  value: String(data)
                }));
            }
        };

        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                addData(key, data[key]);
            }
        }

        return form.appendTo('body');
    }
}); });
</script>

<script>
	$(function() { 
		$('a[data-toggle="tab"]').click(function (e) {
		localStorage.setItem('lastTab', $(e.target).attr('id'));
		localStorage.setItem('lastElement',<?php echo isset($element) ? $element->id : 0 ?>);
		});
		var lastTab = localStorage.getItem('lastTab');
		var lastElement = localStorage.getItem('lastElement');
		if (lastTab && lastElement == <?php echo isset($element) ? $element->id : 0 ?>) {
		    $('#'+lastTab).tab('show');
		}
		else
		{
			<?php if (isset($element)) { ?>
			$('#Elements').tab('show');
			<?php } else { ?>
			$('#elements').addClass('active');
			<?php } ?>
		}
	});
</script>

<ul class="breadcrumb">
	<?php if (!isset($ancestors)) { ?>
		<li class="active">Root</li>
	<?php } else { ?>
		<li><a href="/elements/view">Root</a> <span class="divider">/</span></li>
	<?php } ?>
	<?php if (isset($ancestors)) { ?>
		<?php foreach($ancestors as $ancestor):?>
		<li><a href="<?php echo $this->pixie->router->get('default')->url(array('controller'=>'elements','action'=>'view','id'=>$ancestor->id)); ?>">
			<?php echo ($ancestor->category->name != 'None' ? $ancestor->category->name.' ' : '') ?><?php echo $ancestor->name; ?></a> <span class="divider">/</span></li>
		<?php endforeach;?>
		<li class="active"><?php echo $element->name; ?></li>
	<?php } ?>
</ul>

<div class="row-fluid">

	<?php if (isset($element)) { ?>
	<div class="page-header">
    	<h3><?php echo ($element->category->name != 'None' ? $element->category->name : '') ?> <?php echo $element->name ?> <small><?php echo $element->type->name ?></small></h3>
    </div>

		<?php if (isset($inherit)) { ?>
		<div class="alert alert">
			<strong>NOTICE:</strong> Inherits <a href="/elements/view/<?php echo $inherit->id ?>"><?php echo $inherit->name ?></a>
		</div>
		<?php } ?>

    <ul class="nav nav-tabs">
	    <li><a id="Attributes" href="#attributes" data-toggle="tab">Attributes</a></li>
	    <li><a id="Elements" href="#elements" data-toggle="tab">Elements</a></li>
	    <li><a id="Properties" href="#properties" data-toggle="tab">Properties</a></li>
	    <li><a id="Templates" href="#templates" data-toggle="tab">Templates</a></li>
    </ul>

	<?php } ?>

	<div id="myTabContent" class="tab-content">
	
	<?php if (isset($element)) { ?>

	<div class="tab-pane fade" id="attributes">
		<h4>Attributes</h4>
		<form method="POST" class="form" action="/elements/edit/<?php echo $element->id; ?>">
			<label>Name:</label>
			<input type="text" name="name" class="input-large" value="<?php echo $element->name;?>"<?php if (!$isOwner) { ?> disabled<?php } ?>>
			
			<label>Type:</label>
			<select name="type"<?php if (!$isOwner) { ?> disabled<?php } ?>>
	    	<?php foreach($types as $type):?>
	    	<option value="<?php echo $type->id ?>"<?php echo ($type->id == $element->type_id) ? ' selected="selected"' : ''?>><?php echo $type->name ?></option>
	    	<?php endforeach;?>
	    	</select>

	    	<label>Inherits from:</label>
			<select name="inherit"<?php if (!$isOwner) { ?> disabled<?php } ?>>
			<option <?php echo (!isset($inherit)) ? 'selected="selected"' : ''?>>None</option>
	    	<?php foreach($siblings as $sibling):?>
	    	<option value="<?php echo $sibling->id ?>" <?php echo (isset($inherit) && $sibling->id == $inherit->id) ? 'selected="selected"' : ''?>><?php echo $sibling->name ?></option>
	    	<?php endforeach;?>
	    	</select>

	    	<br>

	    	<label>Category</label>
			<select name="category"<?php if (!$isOwner) { ?> disabled<?php } ?>>
			<?php foreach($categories as $category):?>
			<option value="<?php echo $category->id ?>" <?php echo ($element->category_id == $category->id) ? 'selected="selected"' : ''?>><?php echo $category->name ?></option>
			<?php endforeach;?>
			</select>

			<label>Owner</label>
			<select name="owner"<?php if (!$isOwner) { ?> disabled<?php } ?>>
			<?php foreach($users as $user):?>
			<option value="<?php echo $user->id ?>" <?php echo ($element->user_id == $user->id) ? 'selected="selected"' : ''?>><?php echo $user->username ?></option>
			<?php endforeach;?>
			</select>

			<div class="checkbox">
				<label><input type="checkbox" name="recursive" value="recursive"<?php if (!$isOwner) { ?> disabled<?php } ?>>recursively change owner</label>
			</div>

			<br>

	    	<?php if ($isOwner) { ?>
			<button type="submit" class="btn">Save</button>
			<?php } ?>
		</form>
	</div>
	<?php } ?>

	<div class="tab-pane fade in" id="elements">

		<h4>Elements</h4>

		<?php if (isset($children) && $children->valid()) { ?>
	    <form class="form-inline" method="POST" action="/elements/edit/">
		<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<td>Category</td>
				<td>Type / Name</td>
				<td>Order</td>
				<td>Actions</td>
				<td>Owner</td>
			</tr>
		</thead>
		<tbody>
		<?php foreach($children as $child):?>
			<tr>
				<!-- <td style="width: 1px;">
	    			<?php if (!isset($inherit)) { ?>
	    			<input type="checkbox" class="checkbox" name="selected_elements[]" value="<?php echo $child->id;?>">
	    			<?php } ?>
	    		</td>-->
	    		<td>
	    			<?php echo $child->category->name ?>
	    		</td>
	    		<td style="width: 50%;">
	    			<small><?php echo $child->type->name; ?></small> 
	    			<a href="/elements/view/<?php echo $child->id;?>"><?php echo $child->name;?></a>
	    			<?php if (isset($child->inherit->name)) { ?>
	    			: 
	    			<a href="/elements/view/<?php echo $child->inherit_id;?>"><?php echo $child->inherit->name ?></a>
	    			<?php } ?>
	    		</td>
	    		<td>
	    			<span class="order<?php echo $child->order; ?>"><?php echo $child->order; ?></span>
	    			<?php if (!isset($inherit) && $child->isOwner()) { ?>
	    			<a href='/elements/orderup/<?php echo $child->id;?>' data-placement="top" data-toggle="tooltip" title="Up"><i class="icon-chevron-up"></i></a>
	    			<a href='/elements/orderdown/<?php echo $child->id;?>' data-placement="top" data-toggle="tooltip" title="Down"><i class="icon-chevron-down"></i></a>
	    			<?php } ?>
	    		</td>
	    		<td class="text-right">
	    			<?php if (!isset($inherit)) { ?>
	    			<a href='/buffer/copy/<?php echo $child->id;?>?controller=<?php echo $controller; ?>' data-placement="top" data-toggle="tooltip" title="Copy"><i class="icon-arrow-right"></i></a>
	    			<?php if ($child->isOwner()) { ?>
	    			<a href='/buffer/cut/<?php echo $child->id;?>?controller=<?php echo $controller; ?>' data-placement="top" data-toggle="tooltip" title="Cut"><i class="icon-share"></i></a>
	    			<a href='/elements/delete/<?php echo $child->id;?>' data-placement="top" data-toggle="tooltip" title="Delete"><i class="icon-remove"></i></a>
	    			<?php } } ?>
				</td>
				<td>
					<?php echo $child->user->username; ?>
				</td>
		<?php endforeach;?>
		</tbody>
		</table>
		<!-- <div class="well well-small">
			<input type="checkbox" class="checkbox" name="select_all_elements">
			With selected:
	    	<button type="submit" class="btn btn-small">Copy</button>
	    	<button type="submit" class="btn btn-small">Delete</button>
		</div> -->
		</form>
		<?php } ?>

		<?php if (!isset ($element) || (isset ($element))) { ?>
		<div class="row-fluid">
	    <form class="form-inline" method="POST" action="/elements/add/">
	    	<?php if (isset($element)) { ?>
	    		<input type="hidden" name="id" value="<?php echo $element->id; ?>">
	    	<?php } ?>
	    	<select name="category">
	    	<?php foreach($categories as $category):?>
	    		<option value="<?php echo $category->id ?>"><?php echo $category->name ?></option>
	    	<?php endforeach;?>
	    	</select>
	    	<input type="text" name="name" class="input-xlarge" placeholder="Element name">
	    	<select name="type">
	    	<?php foreach($types as $type):?>
	    		<option value="<?php echo $type->id ?>"><?php echo $type->name ?></option>
	    	<?php endforeach;?>
	    	</select>
	    	<button type="submit" class="btn">Add</button>
	    </form>
		</div>
		<?php } ?>

	</div>

<?php if (isset($element) || (isset($properties) && $properties->valid())) { ?>

	<div class="tab-pane fade" id="properties">
		<script>
		function textAreaAdjust(o) {
			o.style.height = "1px";
			o.style.height = (o.scrollHeight)+"px";
		}
		function highLightInputOnChange(o) {
			o.parent().removeClass().addClass('control-group info');
		}
		</script>
		<h4>Properties</h4>
		<?php if ($isOwner) { ?>
		<div class="row-fluid">
			<form class="form-inline" method="POST" action="/properties/add/">
				<input type="hidden" name="id" value="<?php echo $element->id; ?>">
		    	<input style="width: 20%;" type="text" name="name" class="input-medium" placeholder="Name">
		    	<select name="category">
				<?php foreach($categories as $category):?>
				<option value="<?php echo $category->id ?>"><?php echo $category->name ?></option>
				<?php endforeach;?>
				</select>
				<button type="submit" class="btn">Add</button>
				<br>
		    	<textarea style="width: 90%; height: 20px;" onkeyup="textAreaAdjust(this)" name="value" placeholder="Value"></textarea><br>
		    	
		    </form>
		</div>
		<?php } ?>
	 	<!-- <table class="table table-striped table-condensed"> -->
			<?php foreach($properties as $property):?>
			<?php //$owner = $this->pixie->orm->get('element',$property->element_id)->name; ?>
			<?php echo ($property->element_id != $element->id) ? '<a href="/'.($property->element->is_template == 1 ? 'templates' : 'elements').'/view/'.$property->element_id.'">'.$property->element->name.'</a>: ' : ''; ?>
				<form method="POST" class="form form-inline" method="POST" onSubmit="$.post('/properties/edit/<?php echo $property->id; ?>', $(this).serialize()); $(this).find('div').removeClass().addClass('control-group success'); return false;">
						<div class="control-group">
							<input style="width: 20%;" onChange="highLightInputOnChange($(this));" type="text" name="name" value="<?php echo $property->name ?>"<?php if (!$isOwner || $property->element_id != $element->id) { ?> disabled<?php } ?>>
							<select onChange="highLightInputOnChange($(this));" id="propertyCategory" name="category"<?php if (!$isOwner) { ?> disabled<?php } ?>>
							<?php foreach($categories as $category):?>
							<option value="<?php echo $category->id ?>" <?php echo ($property->category_id == $category->id) ? 'selected="selected"' : ''?>><?php echo $category->name ?></option>
							<?php endforeach;?>
							</select>
							<?php if ($isOwner && $property->element_id == $element->id) { ?>
							<button type="submit" class="btn btn-success">Save</button>
							<button type="button" class="btn btn-danger" onClick="$.get('/properties/delete/<?php echo $property->id ?>'); $(this).parent().parent().remove(); return false;">Delete</button>
							<?php } else { ?>
							<button type="button" class="btn" onClick="$.form('/properties/add/', { id: '<?php echo $element->id; ?>', name: '<?php echo $property->name ?>', value: $(this).siblings('#propertyValue').val(), category: $(this).siblings('#propertyCategory').val() }).submit();">Change</button>
							<?php } ?>
							<br>
							<textarea id="propertyValue" style="width: 90%; height: 20px;" onkeyup="textAreaAdjust(this)" style="overflow:hidden" onChange="highLightInputOnChange($(this));" name="value"<?php if (!$isOwner) { ?> disabled<?php } ?>><?php echo $property->value ?></textarea>
						</div>
		    	</form>
		        <!--<tr>
		            <td>
		                  <?php echo ($property->element_id != $element->id) ? '<a href="/'.($property->element->is_template == 1 ? 'templates' : 'elements').'/view/'.$property->element_id.'">'.$property->element->name.'</a>: ' : ''; ?><a href="/properties/view/<?php echo $property->id;?>"><?php echo $property->name;?></a><?php if ($property->category->name != "None") {?> (<?php echo $property->category->name;?>)<?php } ?>
		            </td>
		            <td class="text-right">
		              	<?php if ($property->element_id == $element->id && $isOwner) { ?>
		    		<a href='/properties/delete/<?php echo $property->id;?>' data-placement="top" data-toggle="tooltip" title="Delete"><i class="icon-remove"></i></a>
		    		<?php } ?>
					</td>
		        </tr>
		        <tr>
		        	<td><pre style="padding:0;padding-left:4px;"><?php echo $property->value ?></pre></td>
		        </tr>-->
			<?php endforeach;?>
		<!-- </table> -->
	</div>

<?php } ?>

<?php if (isset($element)) { ?>

	<div class="tab-pane fade" id="templates">

		<h4>Templates</h4>
		<?php if (isset($templates)) { ?>
	 	<table class="table table-striped table-condensed">
			<?php foreach($templates as $template):?>
			<tr>
		        <!-- <td style="width: 1px;">
		        	<input type="checkbox" class="checkbox" name="selected_templates" value="<?php echo $template->id;?>">
		        </td> -->
				<td>
					<a href="/templates/view/<?php echo $template->id;?>"><?php echo $template->name; ?></a>
				</td>
				<td class="text-right">
	    			<?php if ((!isset($inherit) || (isset($template->element_id) && $template->element_id == $element->id)) && $isOwner) { ?>
		    		<a href='/elements/unlink/?templateid=<?php echo $template->id;?>&amp;elementid=<?php echo $element->id;?>' data-placement="top" data-toggle="tooltip" title="Unlink">
		    			<i class="icon-resize-small"></i>
		    		</a>
		    		<?php } ?>
				</td>
			</tr>
			<?php endforeach;?>
		</table>
		<?php } ?>

		<?php if ($isOwner) { ?>
		<div class="row-fluid">
		<form class="form-inline" method="POST" action="/elements/link/<?php echo $element->id; ?>">
	    	<select name="template">
	    	<?php foreach($alltemplates as $t):?>
	    	<option value="<?php echo $t->id ?>"><?php echo $t->name ?></option>
	    	<?php endforeach;?>
	    	</select>
	    	<button type="submit" class="btn">Link</button>
	    </form>
		</div>
		<?php } ?>

	</div>

<?php } ?>

	</div>

</div>

<script type="text/javascript">$(document).ready(function () {$("a").tooltip({'selector': ''});});</script>