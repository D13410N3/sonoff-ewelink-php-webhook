<?php

########################################

#### Подключение модулей
#### while(true) секция
######### Обновление состояния датчиков
######### Подгрузка скриптов из БД
######### Инклюд скриптов

// Запрещаем запуск вне cli
if(php_sapi_name() != 'cli')
	{
		die('Only cli running');
	}

function cron_lock($title) 
	{
		static $locks = [];

		$key = md5($title);

		if (!$locks) 
			{
				register_shutdown_function(function () use (&$locks) 
					{
						foreach ($locks as $lock) 
						{
							flock($lock['fp'], LOCK_EX | LOCK_NB);
							fclose($lock['fp']);
							unlink($lock['file']);
						}
					});
			}

		$fp = fopen('/tmp/lock'.$key, "w+");
		if (!$fp || !flock($fp, LOCK_EX | LOCK_NB))
		return false;

		$locks[$key] = [
			'fp' => $fp, 
			'file' => '/tmp/lock'.$key
		];

		return true;
	}

if (!cron_lock(__FILE__))
	{
		die("double run");
	}

require_once 'core.php';


while(true)
	{
		# фиксируем текущее время
		$unixtime = time();
		// to be continued?
		#####################################################################################################
		#####################################################################################################
		#####################################################################################################
		##############################################Датчики################################################
		#####################################################################################################
		#####################################################################################################
		#####################################################################################################
		# Получаем состояние датчиков
		$q_sensors = mysql_query("SELECT * FROM (SELECT DISTINCT `ewelink_sensors_events`.`id_sensor`, `ewelink_sensors_events`.`time`, `ewelink_sensors`.`short_name` FROM `ewelink_sensors_events` INNER JOIN `ewelink_sensors` on `ewelink_sensors`.`id` = `ewelink_sensors_events`.`id_sensor` ORDER BY `time` DESC) t GROUP BY `id_sensor`");
		
		$_SENSORS = [];
		
		while($tmp = mysql_fetch_assoc($q_sensors))
			{
				// считаем timeback в секундах 
				$ago = $unixtime - $tmp['time'];
				
				// maybe something else l8r
				
				// записываем данные
				$_SENSORS[$tmp['short_name']] = array('time' => $tmp['time'], 'back' => $ago);
			}
		
		print_r($_SENSORS);
		
		
		#####################################################################################################
		#####################################################################################################
		#####################################################################################################
		################################################Реле#################################################
		#####################################################################################################
		#####################################################################################################
		#####################################################################################################
		# получаем состояние реле
		$q_devices = mysql_query("SELECT `ewelink_events`.`id_device`, `ewelink_events`.`action`, max(`ewelink_events`.`time`) as `time`, `ewelink_devices`.`short_name` FROM `ewelink_events` INNER JOIN `ewelink_devices` ON `ewelink_devices`.`id` = `ewelink_events`.`id_device` GROUP BY `ewelink_events`.`id_device`, `ewelink_events`.`action`");
		
		$_DEVICES = [];
		
		while($tmp = mysql_fetch_assoc($q_devices))
			{
				// в каждом массиве - два подмассива, на action 0, 1
				$action = $tmp['action'];
				
				// считаем timeback в секундах
				$ago = $unixtime - $tmp['time'];
				
				// maybe something else l8r
				
				// записываем данные 
				$_DEVICES[$tmp['short_name']][$action] = array('time' => $tmp['time'], 'back' => $ago);
			}
		print_r($_DEVICES);
		
		#####################################################################################################
		#####################################################################################################
		#####################################################################################################
		################################################Wi-Fi################################################
		#####################################################################################################
		#####################################################################################################
		#####################################################################################################
		
		
		$q_clients = mysql_query("SELECT * FROM `wireless_clients` ORDER BY `time_update` ASC");
		
		$_CLIENTS = [];
		while($tmp = mysql_fetch_assoc($q_clients))
			{
				// timeback
				$ago = $unixtime - $tmp['time_update'];
				
				$_CLIENTS[$tmp['short_name']] = array('status' => $tmp['status'], 'time' => $tmp['time_update'], 'ago' => $ago);
			}
		
		print_r($_CLIENTS);
		
		
		$directory = 'scripts/';
		
		if($dh = opendir($directory))
			{
				while(($file = readdir($dh)) != false)
					{
						if($file != '.' && $file != '..')
							{
								require 'scripts/'.$file;
							}
					}
			}
		
		sleep(1);
	}