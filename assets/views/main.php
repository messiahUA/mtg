<!DOCTYPE html>
<html>
	<head>
		<title>MTG</title>
		<link rel="Shortcut Icon" href="/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="/css/bootstrap.min.css" media="screen">
		<?php if ($controller == 'Index') { ?><link rel="stylesheet" href="/css/dynatree/skin/ui.dynatree.css"><?php } ?>

		<link rel="stylesheet" href="/lib/codemirror.css">
		
		<script type="text/javascript" src="/js/jquery-1.10.2.min.js"></script>

		<script type="text/javascript" src="/js/jquery.jeditable.mini.js"></script>
		
		<?php if ($controller == 'Index') { ?>
		<script type="text/javascript" src="/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script type="text/javascript" src="/js/jquery.cookie.js"></script>
		<script type="text/javascript" src="/js/jquery.dynatree.min.js"></script>
		<?php } ?>

		<script type="text/javascript" src="/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="/js/bootstrap-transition.js"></script>
		<script type="text/javascript" src="/js/bootstrap-collapse.js"></script>
		<script type="text/javascript" src="/js/bootstrap-tooltip.js"></script>
		<script type="text/javascript" src="/js/bootstrap-tab.js"></script>
		<style>
			.page-header {margin: 0; padding: 0;}
			.navbar {margin: 0;}
			.breadcrumb {margin: 0;}
      		.CodeMirror {border-top: 1px solid black; border-bottom: 1px solid black;}
      		.CodeMirror-activeline-background {background: #e8f2ff !important;}
		</style>
	</head>
	<body>
	    <div class="container-fluid">
		    <div class="span12 offset3">
				<?php include('navbar.php'); ?>
				<?php if (isset($error)) { ?>
				<div class="alert alert-error text-center lead">
					Error: <?php echo $error ?>
				</div>
				<?php } ?>
				<?php if ($subview) { include($subview.'.php'); } ?>
		    </div>
		    <?php if (isset($buffer)) { ?>
		    <div class="span3 well">
		    	<div class="page-header text-center"><strong>Buffer</strong> <a href="/buffer/clear/?controller=<?php echo $controller; ?>"><i class="icon-trash"></i></a></div>
		    	<table class="table table-condensed">
			    	<?php foreach ($buffer as $element => $action) { ?>
		    		<tr>
			    	<td><i class="<?php if ($action == 'copy') { ?>icon-arrow-right<?php } else { ?>icon-share<?php } ?>"></i></td>
			    	<td><small><?php echo $this->pixie->orm->get(rtrim($controller,'s'))->where('id',$element)->find()->name ?></small></td>
			    	<td><a href="/buffer/clear/<?php echo $element ?>?controller=<?php echo $controller; ?>"><i class="icon-remove"></i></a></td>
		    		</tr>
			    	<?php } ?>
		    	</table>
		    	<form class="form-inline" method="GET" action="/buffer/paste">
		    		<input type="hidden" name="controller" value="<?php echo $controller ?>">
		    		<input type="hidden" name="id" value="<?php echo $id ?>">
		    		<button type="submit" class="btn btn-small">Paste</button>
		    	</form>
		    </div>
		    <?php } ?>
	    </div>
	</body>
</html>
