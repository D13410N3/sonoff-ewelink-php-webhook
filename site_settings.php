<?php

include_once 'core.php';

autOnly();

if(isset($_POST['button']))
	{
		$formError = [];
		
		if(!empty($_POST['mikrotik_address']))
			{
				$db['mikrotik_address'] = dbFilter($_POST['mikrotik_address'], 100);
			}
		else
			{
				$db['mikrotik_address'] = '';
			}
		
		if(!empty($_POST['mikrotik_port']))
			{
				$db['mikrotik_port'] = (int)$_POST['mikrotik_port'];
			}
		else
			{
				$db['mikrotik_port'] = '';
			}
		
		if(!empty($_POST['mikrotik_username']))
			{
				$db['mikrotik_username'] = dbFilter($_POST['mikrotik_username'], 100);
			}
		else
			{
				$db['mikrotik_username'] = '';
			}
		
		if(!empty($_POST['mikrotik_password']))
			{
				$db['mikrotik_password'] = dbFilter($_POST['mikrotik_password'], 100);
			}
		else
			{
				$db['mikrotik_password'] = '';
			}
		
		if(!empty($_POST['telegram_token']))
			{
				$db['telegram_token'] = dbFilter($_POST['telegram_token'], 100);
			}
		else
			{
				$formError[] = 'Укажите token от Telegram-бота';
			}
		
		if(!empty($_POST['telegram_chat_id']))
			{
				$db['telegram_chat_id'] = dbFilter($_POST['telegram_chat_id'], 30);
			}
		else
			{
				$formError[] = 'Укажите id чата в Telegram';
			}
		
		if(!empty($_POST['ifttt_key']))
			{
				$db['ifttt_key'] = dbFilter($_POST['ifttt_key'], 50);
			}
		else
			{
				$formError[] = 'Укажите ключ IFTTT';
			}
		
		if(!empty($_POST['password1']) && !empty($_POST['password2']))
			{
				if(md5($_POST['password1']) == md5($_POST['password2']))
					{
						$db['mgmt_password'] = dbFilter($_POST['password1'], 120);
					}
				else
					{
						$formError[] = 'Введенные пароли не совпадают';
					}
			}
		else
			{
				$db['passwmgmt_passwordord'] = $_SETTINGS['mgmt_password'];
			}
		
		// maybe else later ?
		
		if(empty($formError))
			{
				if(mysql_query("UPDATE `settings` SET `mikrotik_address` = '".$db['mikrotik_address']."', `mikrotik_port` = '".$db['mikrotik_port']."', `mikrotik_username` = '".$db['mikrotik_username']."', `mikrotik_password` = '".$db['mikrotik_password']."', `telegram_token` = '".$db['telegram_token']."', `telegram_chat_id` = '".$db['telegram_chat_id']."', `ifttt_key` = '".$db['ifttt_key']."'"))
					{
						header('Location: index.php?settings_saved');
						exit;
					}
				else
					{
						fatalError(mysql_error());
					}
			}
	}

setTitle('Настройки сервиса');
getHeader();
showFormError(isset($formError) ? $formError : '');

?>

<div class="row">
	<form action="?" method="post">
		
		<div class="col-sm">
			Ключ IFTTT *:<br />
			<input type="text" name="ifttt_key" required="required" class="form-control" value="<?=isset($_POST['ifttt_key']) ? dbFilter($_POST['ifttt_key'], 50) : $_SETTINGS['ifttt_key']?>" />
		</div>
		
		<div class="col-sm">
			Telegram Bot Token *:<br />
			<input type="text" name="telegram_token" required="required" class="form-control" value="<?=isset($_POST['telegram_token']) ? dbFilter($_POST['telegram_token'], 100) : $_SETTINGS['telegram_token']?>" />
		</div>
		
		<div class="col-sm">
			Telegram ID чата *:<br />
			<input type="text" name="telegram_chat_id" required="required" class="form-control" value="<?=isset($_POST['telegram_chat_id']) ? dbFilter($_POST['telegram_chat_id'], 30) : $_SETTINGS['telegram_chat_id']?>" />
		</div>
		
		<div class="col-sm">
			Пароль (вход на сайт и процессинг запросов от IFTTT, если оставить пустым - не изменится):<br />
			<input type="password" name="password1" class="form-control" />
		</div>
		
		<div class="col-sm">
			Повторно:<br />
			<input type="password" name="password2" class="form-control" />
		</div>
		
		<div class="well">
			<div class="col-sm">
				Адрес Mikrotik:<br />
				<input type="text" name="mikrotik_address" class="form-control" value="<?=isset($_POST['mikrotik_address']) ? dbFilter($_POST['mikrotik_address'], 100) : $_SETTINGS['mikrotik_address']?>" />
			</div>
			
			<div class="col-sm">
				API-порт Mikrotik:<br />
				<input type="text" name="mikrotik_port" class="form-control" value="<?=isset($_POST['mikrotik_port']) ? (int)$_POST['mikrotik_port'] : $_SETTINGS['mikrotik_port']?>" />
			</div>
			
			<div class="col-sm">
				Имя пользователя Mikrotik:<br />
				<input type="text" name="mikrotik_username" class="form-control" value="<?=isset($_POST['mikrotik_username']) ? dbFilter($_POST['mikrotik_username'], 100) : $_SETTINGS['mikrotik_username']?>" />
			</div>
			
			<div class="col-sm">
				Пароль Mikrotik:<br />
				<input type="password" name="mikrotik_password" class="form-control" value="<?=isset($_POST['mikrotik_password']) ? dbFilter($_POST['mikrotik_password'], 100) : $_SETTINGS['mikrotik_password']?>" />
			</div>
		</div>
		
		<div class="col-sm">
			<input type="submit" name="button" value="Сохранить" class="btn btn-primary" />
		</div>
	</form>
</div>

<?php
getFooter();