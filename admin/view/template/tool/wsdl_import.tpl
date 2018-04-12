<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<a href="<?php echo $back; ?>" data-toggle="tooltip" title="<?php echo $button_back; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<?php if ($success) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>

		<?php if ((!$error_warning) && (!$success)) { ?>

		<?php } ?>

		<div class="panel panel-default">
			<div class="panel-body">
				<form action="<?php echo $settings; ?>" method="post" enctype="multipart/form-data" id="settings" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-wsdl-url">1C Soap WSDL Url</label>
						<div class="col-sm-10">
							<input type="text" name="wsdl_import_url" id="input-wsdl-url" class="form-control" value="<?php echo $wsdl_import_url ?>"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-wsdl-login">Login</label>
						<div class="col-sm-10">
							<input type="text" name="wsdl_import_login" id="input-wsdl-login" class="form-control" value="<?php echo $wsdl_import_login ?>"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-wsdl-pass">Password</label>
						<div class="col-sm-10">
							<input type="password" name="wsdl_import_pass" id="input-wsdl-pass" class="form-control" value="<?php echo $wsdl_import_pass ?>"/>
						</div>
					</div>
					<button type="submit" class="btn btn-primary"><span>Update Settings</span></button>
					<a onclick="sync(); return false;" class="btn btn-primary"><span>Synchronize</span></a>
					<a onclick="doImport(); return false;" class="btn btn-primary"><span>Import</span></a>
				</form>
			</div>
		</div>

	</div>

<script type="text/javascript"><!--

function sync() {
    $.ajax({
        type: 'POST',
        url: 'index.php?route=tool/wsdl_import/sync&token=<?php echo $token; ?>',
        dataType: 'json',
        success: function(json) {
            if (json['error']) {
                $('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</b>' + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>').prependTo('#content .panel-body');
            } else if (json['message']) {
                $('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['message'] + '<b>' + json['count'] + '</b>' + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>').prependTo('#content .panel-body');
            } else {

            }
        },
        failure: function(){
        },
        error: function() {
        }
    });
}

function doImport() {
	$.ajax({
		type: 'POST',
		url: 'index.php?route=tool/wsdl_import/import&token=<?php echo $token; ?>',
		dataType: 'json',
		success: function(json) {
			if (json['error']) {
				$('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</b>' + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>').prependTo('#content .panel-body');
			} else if (json['message']) {
				$('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['message'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>').prependTo('#content .panel-body');
			} else {

			}
		},
		failure: function(){
		},
		error: function() {
		}
	});
}
//--></script>

</div>
<?php echo $footer; ?>
