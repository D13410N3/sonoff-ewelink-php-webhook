<?php

include_once 'core.php';


if(@$_GET['key'] != $_key)
	{
		header('Location: https://ifttt.com');
	}

if(isset($_GET['sensor']))
	{
		// фиксируем время
		$unixtime = time();
		$strtime = date('d.m.Y H:i:s', $unixtime);
		
		// проверяем наличие сенсора в бд
		$sensor = mysql_real_escape_string($_GET['sensor']);
		
		$q = mysql_query("SELECT * FROM `ewelink_sensors` WHERE `short_name` = '".$sensor."'");
		if(mysql_num_rows($q) < 1)
			{
				die(json_encode(array('error' => 'No such device')));
			}
		
		$_SENSOR = mysql_fetch_assoc($q);
		
		// пишем лог
		$log_string = $strtime.'/'.$unixtime.',,'.$sensor.PHP_EOL;
		$f = fopen('actions.log', 'a+');
		flock($f, LOCK_EX);
		fputs($f, $log_string);
		flock($f, LOCK_UN);
		fclose($f);
		
		// определяем комнату
		$_ROOM = mysql_fetch_assoc(mysql_query("SELECT * FROM `rooms` WHERE `id` = ".$_SENSOR['id_room']));
		
		// записываем в бд событие
		
		if(mysql_query("INSERT INTO `ewelink_sensors_events`(`id_sensor`, `time`) VALUES (".$_SENSOR['id'].", ".$unixtime.")"))
			{
				// событие успешно записано, определяем, нужно ли нам слать уведомление
				if($_SENSOR['notify'] == 1)
					{
						$_notify = true;
						$_message = '<b>'.$_SENSOR['full_name'].'</b> | '.$_ROOM['name'].' <i>('.$strtime.')</i>'.PHP_EOL;
						$_message .= 'Сработал датчик';
					}
				else
					{
						$_notify = false;
					}
			}
		else
			{
				$_notify = true;
				$_message = '(!!!) Сработал датчик <b>'.$_SENSOR['name'].'</b> | '.$_ROOM['name'].' <i>('.$strtime.')</i>'.PHP_EOL;
				$_message .= mysql_error();
			}
		
		if($_notify)
			{
				sendMessage($_CHAT['id'], $_message, 'HTML');
			}
	}