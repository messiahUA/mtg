<script type="text/javascript" src="/lib/codemirror.js"></script>
<script type="text/javascript" src="/addon/edit/matchbrackets.js"></script>
<script type="text/javascript" src="/mode/htmlmixed/htmlmixed.js"></script>
<script type="text/javascript" src="/mode/xml/xml.js"></script>
<script type="text/javascript" src="/mode/javascript/javascript.js"></script>
<script type="text/javascript" src="/mode/css/css.js"></script>
<script type="text/javascript" src="/mode/clike/clike.js"></script>
<script type="text/javascript" src="/mode/php/php.js"></script>
<script type="text/javascript" src="/addon/selection/active-line.js"></script>

<div class="row-fluid">

	<div class="page-header">
	<h3><?php echo $component->name;?> <small>Component</small></h3>
	</div>

	<h4>Attributes</h4>
	<form class="form" method="POST" action="/components/edit/<?php echo $component->id ?>">
		<label>Name</label>
		<input type="text" name="name" class="input-large" value="<?php echo $component->name ?>"<?php if (!$isOwner) { ?> disabled<?php } ?>>
		<label>Owner</label>
		<select name="owner"<?php if (!$isOwner) { ?> disabled<?php } ?>>
			<?php foreach($users as $user):?>
			<option value="<?php echo $user->id ?>" <?php echo ($component->user_id == $user->id) ? 'selected="selected"' : ''?>><?php echo $user->username ?></option>
			<?php endforeach;?>
		</select>
		<div class="accordion" id="accordion_main">
			<div class="accordion-group">
				<div class="accordion-heading" style="margin: 5px;">
					<a data-toggle="collapse" data-parent="#accordion_main" href="#collapse_main">Code</a>
				</div>
				<div id="collapse_main" class="accordion-body collapse">
					<div class="accordion-inner">
						<textarea rows="20" name="code" id="codemirrormain" style="width: 100%;"<?php if (!$isOwner) { ?> disabled<?php } ?>><?php echo $component->code;?></textarea><br>
					</div>
				</div>
			</div>
		</div>

		<label>Depends on:</label>
		<ul>
		<?php foreach ($component->dependson->find_all() as $value) { ?>
			<li>
				Remove:
				<label class="checkbox">
					<input type="checkbox" name="del_dependson[]" value="<?php echo $value->id; ?>"<?php if (!$isOwner) { ?> disabled<?php } ?>>
						<a href="/components/view/<?php echo $value->id ?>"><?php echo $value->name ?></a>
					</input>
				</label>
			</li>
		<?php } ?>
		<?php foreach ($allcomponents as $value) {
			$skip = false;
			foreach ($component->dependson->find_all() as $v) {
				if ($value->id == $v->id)
				{
					$skip = true;
					break;
				}
			}
			if ($skip || $component->name == $value->name)
				continue;
			if ($isOwner) {
		?>
		<li>
			Add:
			<label class="checkbox">
				<input type="checkbox" name="add_dependson[]" value="<?php echo $value->id; ?>">
					<a href="/components/view/<?php echo $value->id ?>"><?php echo $value->name; ?></a>
				</input>
			</label>
		</li>
		<?php } } ?>
		</ul>

		<?php if ($isOwner) { ?>
		<button type="submit" class="btn">Save</button>
		<?php } ?>
	</form>

	<script type="text/javascript">
		var editor = CodeMirror.fromTextArea(document.getElementById("codemirrormain"), {
			matchBrackets: true,
			mode: "application/x-httpd-php",
			indentUnit: 4,
			indentWithTabs: true,
			enterMode: "keep",
			tabMode: "shift",
			lineNumbers: true,
			styleActiveLine: true,
			textWrapping: true
		});
	</script>

	<h4>Files</h4>

	<?php if ($files->valid()) { ?>

	<div class="accordion" id="accordion2">
	<?php foreach($files as $file):?>
		<div class="accordion-group">
			<div class="accordion-heading" style="margin: 5px;">
				<!-- <input type="checkbox" class="checkbox" name="selected_files" value="<?php echo $file->id;?>"> -->
				<?php echo $file->path;?> / <a data-toggle="collapse" data-parent="#accordion2" href="#collapse<?php echo $file->id; ?>"><?php echo $file->name;?></a>
				<?php if ($isOwner) { ?>
					<a href="/files/delete/<?php echo $file->id ?>" data-placement="top" data-toggle="tooltip" title="Delete"><i class="icon-remove"></i></a>
				<?php } ?>
			</div>
			<div id="collapse<?php echo $file->id; ?>" class="accordion-body collapse">
				<div class="accordion-inner">
					<form class="form" method="POST" action="/files/edit/<?php echo $file->id ?>">
						<p><textarea rows="20" id="codemirrorarea<?php echo $file->id; ?>" name="content" style="width: 100%;"><?php echo $file->content;?></textarea></p>
						<input type="text" name="path" class="input-large" placeholder="Path" value="<?php echo $file->path ?>"<?php if (!$isOwner) { ?> disabled<?php } ?>>
						<input type="text" name="name" class="input-large" placeholder="Name" value="<?php echo $file->name ?>"<?php if (!$isOwner) { ?> disabled<?php } ?>>
						<br>
						<textarea rows="5" name="name_template" placeholder="Name template" class="input-xxlarge"<?php if (!$isOwner) { ?> disabled<?php } ?>><?php echo $file->name_template ?></textarea><br>
						<?php if ($isOwner) { ?>
						<button type="submit" class="btn">Save</button>
						<?php } ?>
					</form>
				</div>
			</div>
			<script type="text/javascript">
				var editor = CodeMirror.fromTextArea(document.getElementById("codemirrorarea<?php echo $file->id; ?>"), {
					matchBrackets: true,
					mode: "application/x-httpd-php",
					indentUnit: 4,
					indentWithTabs: true,
					enterMode: "keep",
					tabMode: "shift",
					lineNumbers: true,
					styleActiveLine: true,
					textWrapping: true
				});
			</script>
		</div>
	<?php endforeach;?>
	<?php } ?>
	</div>

	<?php if ($isOwner) { ?>
	<form method="POST" action="/files/add/" class="form">
		<input type="hidden" name="id" value="<?php echo $component->id ?>">
		<label>Path</label>
		<input type="text" name="path" class="input-medium" placeholder="blank == root"><br>
		<label>Name</label>
		<input type="text" name="name" class="input-large" placeholder="File name"><br>
		<label>Template for name</label>
		<textarea rows="5" name="name_template" class="input-xxlarge" placeholder="Code"></textarea><br>
		<button type="submit" class="btn">Add</button>
	</form>
	<?php } ?>

	<h4>Properties</h4>
	<?php if ($isOwner) { ?>
	<div class="row-fluid">
		<form class="form-inline" method="POST" action="/componentproperties/add/">
			<input type="hidden" name="id" value="<?php echo $component->id; ?>">
			<input type="text" name="name" class="input-medium" placeholder="Name">
			<select name="category">
			<?php foreach($categories as $category):?>
			<option value="<?php echo $category->id ?>"><?php echo $category->name ?></option>
			<?php endforeach;?>
			</select><br>
			<textarea name="value" placeholder="Value"></textarea><br>
			<button type="submit" class="btn">Add</button>
		</form>
	</div>
	<?php } ?>
	<?php foreach($properties as $property):?>
	<form class="form" method="POST" onSubmit="$.post('/componentproperties/edit/', $(this).serialize()); $(this).find('div').removeClass().addClass('control-group success'); return false;">
		<div class="control-group">
			<input type="hidden" name="id" value="<?php echo $property->id; ?>">
			<select onChange="$(this).parent().removeClass().addClass('control-group info');" name="category"<?php if (!$isOwner) { ?> disabled<?php } ?>>
			<?php foreach($categories as $category):?>
			<option value="<?php echo $category->id ?>" <?php echo ($property->category_id == $category->id) ? 'selected="selected"' : ''?>><?php echo $category->name ?></option>
			<?php endforeach;?>
			</select>
			<input onChange="$(this).parent().removeClass().addClass('control-group info');" type="text" name="name" value="<?php echo $property->name ?>"<?php if (!$isOwner) { ?> disabled<?php } ?>>
			<?php if ($isOwner) { ?>
				<a href='/componentproperties/delete/<?php echo $property->id;?>' data-placement="top" data-toggle="tooltip" title="Delete"><i class="icon-remove"></i></a>
			<?php } ?>

			<textarea onChange="$(this).parent().removeClass().addClass('control-group info');" name="value" rows="10" style="width: 100%;"<?php if (!$isOwner) { ?> disabled<?php } ?>><?php echo $property->value ?></textarea>

			<?php if ($isOwner) { ?><button type="submit" class="btn">Save</button><?php } ?>
		</div>
	</form>
	<?php endforeach;?>
</div>

<script type="text/javascript">$(document).ready(function () {$("a").tooltip({'selector': ''});});</script>