<?php

include_once 'core.php';

if(AUT)
	{
		header('Location: index.php?already_aut');
		exit;
	}

define('TITLE', 'Вход');
getHeader();

if(isset($_POST['button']))
	{
		if(isset($_POST['key']))
			{
				$key = md5(md5($_POST['key']));
				
				if($key == md5(md5($_key)))
					{
						setcookie('sid', $key, time() + 86400 * 365);
						header('Location: index.php?aut');
						exit;
					}
				else
					{
						$error = true;
					}
			}
		else
			{
				$error = true;
			}
	}

echo isset($error) ? '<div class="alert alert-danger" role="alert">Неверный пароль</div>' : '';
?>

<div class="row">
	<form action="?" method="post">
	
	<div class="col-sm" style="margin-bottom: 5px;">
		<input type="password" class="form-control" name="key" />
	</div>
	
	<div class="col-sm">
		<input type="submit" name="button" value="Вход" class="btn btn-primary" />
	</div>
	
	</form>
</div>

<?php

getFooter();