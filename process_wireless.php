<?php

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

include_once 'core.php';
include_once 'mikrotik_api.php';

/*

## Подключаемся к микротику
#### Формируем список устройств из БД
## Получаем список registration-table, перевариваем его в вид массива с интересующими нас маками
## Сверяем каждое устройство через foreach:
1) Устройство было, устройство есть - NTD
2) Устройство было, устройство нет - меняем status на 0, обновляем time_update
3) Устройства не было, устройства нет - NTD
4) Устройства не было, устройство появилось - меняем status на 1, обновляем time_update

*/
if(!empty($_SETTINGS['mikrotik_address']) && !empty($_SETTINGS['mikrotik_port']) && !empty($_SETTINGS['mikrotik_username']) && !empty($_SETTINGS['mikrotik_password']))
	{
		while(true)
			{

				## Подключаемся к микротику
				$API = new routeros_api();
				$API -> debug = false;
				if($API->connect($_SETTINGS['mikrotik_address'], $_SETTINGS['mikrotik_port'], $_SETTINGS['mikrotik_username'], $_SETTINGS['mikrotik_password']))
					{
						$cmd = '/interface/wireless/registration-table/print';
						$result = $API -> comm($cmd);
						$_MACS = [];
						
						#### Формируем список устройств из БД
						$q_devices = mysql_query("SELECT * FROM `wireless_clients` WHERE `deleted` = 0");
						while($dev = mysql_fetch_assoc($q_devices))
							{
								$db_macs[$dev['mac']] = array('id' => $dev['id'], 'status' => $dev['status'], 'name' => $dev['name']);
							}
						
						foreach($result as $key => $device)
							{
								$mac = str_replace(':', '', $result[$key]['mac-address']);
								if(isset($db_macs[$mac]))
									{
										$_MACS[$mac] = 1;
									}
							}
						
						// print_r($_MACS); // Отфильтрованные по БД текущие клиенты
						
						foreach($db_macs as $_MAC => $info)
							{
								// Итерация одного мака из БД
								$unixtime = time();
								$strtime = date('d.m.Y H:i:s', $unixtime);
								echo $strtime.PHP_EOL;
								// Если устройство БЫЛО в сети
								if($info['status'] == 1)
									{
										// Проверяем, в сети ли устройство:
										if(isset($_MACS[$_MAC]))
											{
												// Устройство было и остается в сети. Nothing to do
												echo $_MAC.' already online - nothing to do...'.PHP_EOL;
											}
										else
											{
												## Фиксируем отключение устройства:
												mysql_query("UPDATE `wireless_clients` SET `status` = 0, `time_update` = ".$unixtime." WHERE `id` = ".$info['id']);
												
												## Записываем событие в БД:
												mysql_query("INSERT INTO `wireless_clients_events`(`id_client`, `status`, `time`) VALUES (".$info['id'].", 0, ".$unixtime.")");
												
												## Пишем в телегу, что кое-кто отключился
												$message = '<b>'.$info['name'].'</b>: Устройство отключилось <i>('.$strtime.')</i>';
												sendMessage($_CHAT['id'], $message, 'HTML');
												echo $_MAC.' NOW online - information saved...'.PHP_EOL;
											}
									}
								elseif($info['status'] == 0)
									{
										// Проверяем, в сети ли устройство
										if(!isset($_MACS[$_MAC]))
											{
												// Устройство в сети не было и не появилось. Nothing to do
												echo $_MAC.' still offline - nothing to do...'.PHP_EOL;
											}
										else
											{
												## Фиксируем включение устройства:
												mysql_query("UPDATE `wireless_clients` SET `status` = 1, `time_update` = ".$unixtime." WHERE `id` = ".$info['id']);
												
												## Записываем событие в БД:
												mysql_query("INSERT INTO `wireless_clients_events`(`id_client`, `status`, `time`) VALUES (".$info['id'].", 1, ".$unixtime.")");
												
												## Пишем в телегу, что кое-кто подключился
												$message = '<b>'.$info['name'].'</b>: Устройство подключилось <i>('.$strtime.')</i>';
												sendMessage($_CHAT['id'], $message, 'HTML');
												echo $_MAC.' NOW online - information saved...'.PHP_EOL;
											}
									}
							}
						
						// Обновляем в БД время последнего обновления списка устройств
						echo PHP_EOL.PHP_EOL.PHP_EOL;
						mysql_query("UPDATE `settings` SET `last_mikrotik_update` = ".time());
					}
				sleep(10);
			}
	}