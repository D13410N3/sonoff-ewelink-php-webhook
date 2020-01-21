<?php

include_once 'core.php';


if(@$_GET['key'] != $_key)
	{
		header('Location: https://ifttt.com');
	}

if(isset($_GET['switch']) && isset($_GET['action']))
	{
		// фиксируем время
		$unixtime = time();
		$strtime = date('d.m.Y H:i:s', $unixtime);
		
		// готовим запрос на определение устройства
		$switch = mysql_real_escape_string($_GET['switch']);
		
		// определяем действие
		switch($_GET['action'])
			{
				case 'on':		$action = 'вкл.';		$act = 1;		break;
				case 'off':		$action = 'выкл.';		$act = 0;		break;
				
				default:		$action = 'неизв.';		$act = 2;		break;
			}
		
		// пишем лог
		$log_string = $strtime.'/'.$unixtime.',,'.$switch.',,'.$act.PHP_EOL;
		$f = fopen('actions.log', 'a+');
		flock($f, LOCK_EX);
		fputs($f, $log_string);
		flock($f, LOCK_UN);
		fclose($f);
		
		# определяем, что это за устройство
		
		$q_device = mysql_query("SELECT * FROM `ewelink_devices` WHERE `short_name` = '".$switch."'");
		
		if(mysql_num_rows($q_device) == 0)
			{
				$message = 'Неизвестное устройство - <code>'.$switch.'</code>, событие - <code>'.$action.'</code>';
			}
		else
			{
				$_DEVICE = mysql_fetch_assoc($q_device);
				
				// проверяем на задвоение событий (ifttt/ewelink таким болеет).
				
				$q_check = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." ORDER BY `id` DESC LIMIT 1");
				
				if(mysql_num_rows($q_check) == 1)
					{
						$last_event = mysql_fetch_assoc($q_check);
						
						if($last_event['action'] == $act)
							{
								// последнее событие == текущему. Обновляем время последнего события вместо записи нового
								// mysql_query("UPDATE `ewelink_events` SET `time` = ".$unixtime." WHERE `id` = ".$last_event['id']);
								// Обновление не имеет смысла
								
								$double = true;
							}
					}
				
				// При первичном создании триггера на ifttt присылается вебхук с последним состоянием. Проверим: если таблица ewelink_events пустая и приходит выключение - событие не фиксируем
				
				if($act == 0)
					{
						if(mysql_num_rows(mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id'])) == 0)
							{
								$first_event_off = true;
							}
					}
				
				
				// фиксируем в базе произодшее событие
				if(!isset($double) && !isset($first_event_off))
					{
						if(mysql_query("INSERT INTO `ewelink_events`(`id_device`, `action`, `time`) VALUES (".$_DEVICE['id'].", ".$act.", ".$unixtime.")"))
							{
								$db_success = true;
							}
						else
							{
								$db_success = false;
							}
				
				
						// пишем в чат, что чечьня круто
						
						$message = '<b>'.$_DEVICE['full_name'].'</b> <i>('.$strtime.')</i>'.PHP_EOL;
						
						// определяем свет или просто подача питания
						if($_DEVICE['type'] == 'light')
							{
								$message .= 'свет ';
							}
						else
							{
								$message .= 'устройство ';
							}
						
						$message .= '<i>'.$action.'</i>';
						
						// Подсчитываем время последней сессии, если action = выключить
						
						if($act == 0)
							{
								$q_last = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." AND `action` = 1 ORDER BY `id` DESC LIMIT 1");
								
								// die(mysql_error());
								
								if(mysql_num_rows($q_last) == 0)
									{
										$string_duration = 'Не найдено время включения';
									}
								else
									{
										$last_session = mysql_fetch_assoc($q_last);
										
										$duration = $unixtime - $last_session['time'];
										
										$days = floor($duration / 86400);
										$hours = floor(($duration - $days * 86400) / 3600);
										$minutes = floor(($duration - $days * 86400 - $hours * 3600) / 60);
										$seconds = $duration - $days * 86400 - $hours * 3600 - $minutes * 60;
										
										$string_duration = 'Продолжительность сессии: ';
										
										if($days > 0)
											{
												$string_duration .= $days.' дн. ';
											}
										
										if($hours > 0)
											{
												$string_duration .= $hours.' ч. ';
											}
										
										if($minutes > 0)
											{
												$string_duration .= $minutes.' мин. ';
											}
										
										if($seconds > 0)
											{
												$string_duration .= $seconds.' сек.';
											}
										
									}
								
								// контактен....ция строк 
								
								$message .= PHP_EOL;
								$message .= $string_duration;
								
							}
					}
				else
					{
						if(isset($double))
							{
								$message = '<b>'.$_DEVICE['full_name'].'</b>: Задвоение последнего события проигнорировано';
							}
						
						if(isset($first_event_off))
							{
								$message = '<b>'.$_DEVICE['full_name'].'</b>: Событие "выключить" проигнорировано, т.к. история событий пуста';
							}
					}
			}
		
		sendMessage($_CHAT['id'], $message, 'HTML');
	}
