<script type="text/javascript">
    $(function(){
        // Attach the dynatree widget to an existing <div id="tree"> element
        // and pass the tree options as an argument to the dynatree() function:
        $("#tree").dynatree({
		      checkbox: true,
		      persist: true,
		      selectMode: 3,
		      minExpandLevel: 2,
		      /*children: treeData,*/
		      /*onSelect: function(select, node) {
		        // Get a list of all selected nodes, and convert to a key array:
		        var selKeys = $.map(node.tree.getSelectedNodes(), function(node){
		          return node.data.key;
		        });
		        $("#echoSelection3").text(selKeys.join(", "));

		        // Get a list of all selected TOP nodes
		        var selRootNodes = node.tree.getSelectedNodes(true);
		        // ... and convert to a key array:
		        var selRootKeys = $.map(selRootNodes, function(node){
		          return node.data.key;
		        });
		        $("#echoSelectionRootKeys3").text(selRootKeys.join(", "));
		        $("#echoSelectionRoots3").text(selRootNodes.join(", "));
		      },*/
		      onDblClick: function(node, event) {
		        node.toggleSelect();
		      },
		      onKeydown: function(node, event) {
		        if( event.which == 32 ) {
		          node.toggleSelect();
		          return false;
		        }
		      },
		      // The following options are only required, if we have more than one tree on one page:
			  //        initId: "treeData",
		      cookieId: "dynatree-Cb3",
		      idPrefix: "dynatree-Cb3-"
	    }
	);
	$("#tree").click(
		function() {
			var tree = $("#tree").dynatree("getTree");
		  	var selRootNodes = tree.getSelectedNodes(true);
			var selRootKeys = $.map(selRootNodes, function(node){
	          	return node.data.key;
	        });

	        $("#selected_root_elements").remove();

	        $('<input>').attr({
			    type: 'hidden',
			    id: 'selected_root_elements',
			    name: 'selected_root_elements',
			    value: selRootKeys.join(",")
			}).appendTo('form');
		}
	);
	$("#tree").ready(
		function() {
			var tree = $("#tree").dynatree("getTree");
		  	var selRootNodes = tree.getSelectedNodes(true);
			var selRootKeys = $.map(selRootNodes, function(node){
	          	return node.data.key;
	        });

	        $("#selected_root_elements").remove();

	        $('<input>').attr({
			    type: 'hidden',
			    id: 'selected_root_elements',
			    name: 'selected_root_elements',
			    value: selRootKeys.join(",")
			}).appendTo('form');
		}
	);
		/*$("form").submit(function() {
	      // Serialize standard form fields:
	      //alert($(this));
	      //return false;
	      var formData = $(this).serializeArray();

	      // then append Dynatree selected 'checkboxes':
	      var tree = $("#tree").dynatree("getTree");
	      formData = formData.concat(tree.serializeArray());
		  var selRootNodes = tree.getSelectedNodes(true);
		  	var selRootKeys = $.map(selRootNodes, function(node){
          	return node.data.key;
        	});

		  //alert(selRootKeys);
		  //formData = formData.concat(selRootNodes);
	        formData.push({name: "selected_root_elements", value: selRootKeys.join(",")});
	      // and/or add the active node as 'radio button':
	      //if(tree.getActiveNode()){
	      //  formData.push({name: "activeNode", value: tree.getActiveNode().data.key});
	      //}

	      //alert("POSTing this:\n" + jQuery.param(formData));

	      /*$.post("/Index/generate",
	           formData,
	           function(response, textStatus, xhr){
	           		$("#debug").html(response);
	             	//alert("POST returned " + response + ", " + textStatus);
	           }
	      );
	      //$.submit(formData);
	      //return false;
	    });*/


    });
</script>

<?php
function walk($element,$parent=null){
	$children = $element['children'];
	$path = $element['id'];
	if (isset($parent))
		$path .= '/'.$parent['path'];
	$element['path'] = $path;
	if (count($children) <= 0) return '';

	$output = '';
	$output .= '<li id="'.$path.'"><a href="#">'.$element['name'].'</a>'."\n";
	$output .= '<ul>';
	foreach ($children as $child) {
		$output .= walk($child,$element);
	}
	$output .= '</ul>';

	$output .= '</li>'."\n";

	return $output;
}
?>

<form method="POST" action="/Index/generate">

<div class="row-fluid">
	<div class="span6 well">
		<h2>Elements</h2>
		<p>
			<button class="btn" type="button" onClick="$('#tree').dynatree('getRoot').visit(function(node){node.select(false);});">clear selection</button>
			<button class="btn" type="button" onClick="$('#tree').dynatree('getRoot').visit(function(node){node.expand(false);});">collapse all</button>
			<button class="btn" type="button" onClick="$('#tree').dynatree('getRoot').visit(function(node){node.expand(true);});">expand all</button>
			<select onchange="document.location.href = '?selected_category='+$(this).val();">
				<option value="-1">All</option>
				<?php foreach ($categories as $category) { ?>
					<option value="<?php echo $category->id ?>"<?php echo (isset($selected_category) && $selected_category == $category->id ? 'selected="selected"' : '' ) ?>><?php echo $category->name ?></option>
				<?php } ?>
			</select>
		</p>
		<div id="tree" name="selected_elements[]">
	    <ul>
		<?php foreach($elements as $element):?>
	    	<?php echo walk($element->subtree_withoutproperties); ?>
		<?php endforeach;?>
	    </ul>
		</div>
	</div>
	<div class="span6 well">
		<h2>Components</h2>
		<?php foreach($components as $component):?>
		<label class="checkbox">
			<input <?php if ($component->id == 11) { echo 'checked="checked"'; } ?>type="checkbox" name="selected_components[]" value="<?php echo $component->id;?>">
			<a href="/components/view/<?php echo $component->id;?>"><?php echo $component->name;?></a>
		</label>
		<?php endforeach;?>
	</div>
</div>
<div class="span11 well text-center">
	<button type="submit" class="btn btn-large btn-block btn-primary">Generate</button>
</div>

</form>
