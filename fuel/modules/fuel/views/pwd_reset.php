<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
 	<title><?=$page_title?></title>
	<?=css('login', FUEL_FOLDER)?>
	<script type="text/javascript">
	<?=$this->load->module_view('fuel', '_blocks/fuel_header_jqx', array(), true)?>
	</script>
	<?=js('jquery/jquery', FUEL_FOLDER)?>
	<?=js('jqx/jqx', FUEL_FOLDER)?>
	<script type="text/javascript">
		jqx.addPreload('fuel.controller.BaseFuelController');
		jqx.init('fuel.controller.LoginController', {});
	</script>
</head>
<body>
		<div id="header_login">
		<center><img src="<?=img_path('aspen-logo1.png')?>" width="430" height="100" alt="FUEL CMS" border="0" id="login_logo" /></center>
		</div>
<div id="login">
	<div id="login_inner">
		<?=$form?>
	</div>
	<div id="login_notification" class="notification">
		<?=$notifications?>
	</div>
	<div id="settingdiv">
	<div id="leftpanel" align="left">	
		&nbsp;
	</div>
	<div id="rightpanel" align="right">	
		<a href="<?=fuel_url('login')?>" id="forgotten_pwd">Back to Login</a>
	</div>
	</div>
	<div id="login_footer">
		<div align="center">Copyright &copy; 2012 <a href="http://www.hooduku.com" target="_blank">Hooduku Inc</a>. All Rights Reserved.</div>
	</div>
</div>
</body>
</html>