<?php

require_once 'settings.php';

@mysql_connect($_DATABASE['host'], $_DATABASE['user'], $_DATABASE['password']) or die(mysql_error());
mysql_select_db($_DATABASE['db']) or die(mysql_error());
mysql_set_charset('utf8');

$q_settings = mysql_query("SELECT * FROM `settings` LIMIT 1");
if(mysql_num_rows($q_settings) != 1)
	{
		die('Отсутствует строка конфигурации');
	}
else
	{
		$_SETTINGS = mysql_fetch_assoc($q_settings);
	}


$_IFTTT['key'] = $_SETTINGS['ifttt_key'];
$_PASSWORD = $_SETTINGS['mgmt_password'];

define('API_URL', 'https://api.telegram.org/bot'.$_SETTINGS['telegram_token'].'/');

$_CHAT['id'] = $_SETTINGS['telegram_chat_id'];
$_key = $_PASSWORD;


function sendMessage($id_chat, $text, $mark = '', $id_message = '')
	{
		// $text = empty($text) ? 'undef or empty var' : $text;
		$toSend = array('method' => 'sendMessage', 'chat_id' => $id_chat, 'text' => $text);
		!empty($id_message) ? $toSend['reply_to_id_message'] = $id_message : '';
		!empty($mark) ? $toSend['parse_mode'] = $mark : '';
		
		$toSend['reply_to_id_message'] = (int)$id_message;
		$ch = curl_init(API_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($toSend));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$a = curl_exec($ch);
		return json_decode($a, true);
	}

function setTitle($title = 'Default Title')
	{
		define('TITLE', $title);
	}

function getHeader()
	{
		$_PAGE['title'] = defined('TITLE') ? TITLE : 'Smart Home';
		?>
		
<!doctype html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
		<meta name="generator" content="Jekyll v3.8.6">
		<title><?=$_PAGE['title']?></title>

		<link rel="canonical" href="https://getbootstrap.com/docs/4.4/examples/sticky-footer-navbar/">

		<!-- Bootstrap core CSS -->
		<link href="bootstrap-4.4.1-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

		<meta name="theme-color" content="#563d7c">


		<style>
			.bd-placeholder-img 
				{
					font-size: 1.125rem;
					text-anchor: middle;
					-webkit-user-select: none;
					-moz-user-select: none;
					-ms-user-select: none;
					user-select: none;
				}

			@media (min-width: 768px) 
				{
					.bd-placeholder-img-lg 
					{
						font-size: 3.5rem;
					}
				}

			.copyleft 
				{
					display:inline-block;
					transform: rotate(180deg);
				}
			
			.device_card 
				{
					border-bottom: 1px solid #eee; padding: 10px;
				}
		</style>
		<!-- Custom styles for this template -->
	</head>
	
	<body class="d-flex flex-column h-100">
		<header>
		<!-- Fixed navbar -->
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
			<a class="navbar-brand" href="#">Smart Home</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarCollapse">

		<?php
		if(AUT)
			{
		?>
				<ul class="navbar-nav mr-auto">
					<li class="nav-item">
						<a class="nav-link" href="index.php?">Все устройства</a>
					</li>
					
					<li class="nav-item">
						<a class="nav-link" href="rooms.php">Комнаты</a>
					</li>
					
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Добавить</a>
						<div class="dropdown-menu" aria-labelledby="dropdown01">
							<a class="dropdown-item" href="device.php?action=add">Устройство Ewelink</a>
							<a class="dropdown-item" href="sensor.php?action=add">Датчик</a>
							<a class="dropdown-item" href="wireless_client.php?action=add">Wi-Fi устройство</a>
						</div>
					</li>
					
					<li class="nav-item">
						<a class="nav-link" href="site_settings.php">Настройки</a>
					</li>
					
				</ul>

				<ul class="navbar-nav ml-auto">
					<li>
						<a class="nav-link" href="logout.php?">Выход</a>
					</li>
				</ul>
				
				<?php
			}
				else
			{
				?>
				
				<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
				<a class="nav-link" href="login.php?">Вход</a>
				</li>
				</ul>
				
				<?php
			}
				?>
				
				</ul>
			</div>
		</nav>
		</header>

	<!-- Begin page content -->
	<main role="main" class="flex-shrink-0">
		<div class="container">
			<h1 class="mt-5"><?=$_PAGE['title']?></h1>
			
			
			
		<?php
	}


function getFooter()
	{
		?>
		
		</div>
	</main>

	<footer class="footer mt-auto py-3">
		<div class="container">
			<span class="text-muted"><span class="copyleft">&copy;</span> <a href="https://github.com/ICQFan4ever" target="_blank">D13410N3</a></span>
		</div>
	</footer>

	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"><\/script>')</script><script src="bootstrap-4.4.1-dist/js/bootstrap.bundle.min.js" integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm" crossorigin="anonymous"></script>
	</body>
</html>

		<?php
	}

if(isset($_COOKIE['sid']))
	{
		if(preg_match('#^([a-f0-9]{32})$#iu', $_COOKIE['sid']))
			{
				$__md5 = md5(md5($_key));
				
				if($__md5 == $_COOKIE['sid'])
					{
						define('AUT', true);
					}
				else
					{
						define('AUT', false);
					}
			}
		else
			{
				define('AUT', false);
			}
	}
else
	{
		define('AUT', false);
	}

function autOnly()
	{
		if(!AUT)
			{
				header('Location: login.php');
				exit;
			}
	}

function showWhen($wasTime = 0)
	{
		$nowTime = time();
		
		$was['day'] = date('d', $wasTime);
		$was['month'] = date('m', $wasTime);
		$was['year'] = date('Y', $wasTime);
		
		$now['day'] = date('d', $nowTime);
		$now['month'] = date('m', $nowTime);
		$now['year'] = date('Y', $nowTime);
		
		
		if($was['day'] == $now['day'] && $was['month'] == $now['month'] && $was['year'] == $now['year'])
			{
				$return = date('H:i:s', $wasTime);
			}
		elseif($was['month'] == $now['month'] && $was['day'] == $now['day'] - 1)
			{
				$return = 'Вчера в '.date('H:i', $wasTime);
			}
		else
			{
				$return = date('d.m.Y H:i', $wasTime);
			}
		
		
		return $return;
	}

function showTimeInterval($uptime = 100, $roundToFullDay = true)
	{
		// Небольшое пояснение:
		// за начало дня берется 00:00:00,а за конец дня - 23:59:59
		// Получается, что если устройство работало весь день, на выходе будет 86399 секунд, что приводит к слегка неверному результату 
		// 23 часа, 59 минут, 59 секунд, хотя по факту устройство работало 24 часа
		// Для использования полного интервала нужно передать функции showTimeInterval второй аргумент false
		if($roundToFullDay && $uptime == 86399)
			{
				return '24 часа';
			}
		else
			{
				$str['d'] = floor($uptime / 86400);
				$str['h'] = floor(($uptime - $str['d'] * 86400) / 3600);
				$str['m'] = floor(($uptime - $str['d'] * 86400 - $str['h'] * 3600) / 60);
				$str['s'] = $uptime - $str['d'] * 86400 - $str['h'] * 3600 - $str['m'] * 60;
				
				$string = '';
				
				$string .= $str['d'] > 0 ? $str['d'].' дн. ' : '';
				$string .= $str['h'] > 0 ? $str['h'].' ч. ' : '';
				$string .= $str['m'] > 0 ? $str['m'].' мин. ' : '';
				$string .= $str['s'] > 0 ? $str['s'].' сек. ' : '';
				
				return empty($string) ? '' : $string;
			}
	}

function showError($text = '')
	{
		return '<div class="alert alert-danger" role="alert">'.$text.'</div>';
	}

function showFormError($array)
	{
		if(empty($array))
			{
				echo '';
			}
		else
			{
				echo showError(implode('<br />', $array));
			}
	}

function fatalError($text = 'Ошибка')
	{
		setTitle('Ошибка');
		getHeader();
		echo showError($text);
		getFooter();
		exit;
	}

function dbFilter($string, $length)
	{
		return mb_substr(htmlspecialchars(mysql_real_escape_string($string)), 0, $length, 'utf-8');
	}

// функция включения реле через IFTTT

function switchRelay($relay = '', $action = 'off', $byScript = false, $notify = false)
	{
		global $_CHAT; // вот такая вот я мразь, да
		global $_IFTTT; // больше глобалок богу глобалок 
		
		$unixtime = time;
		
		/*
		
		1) Делаем запрос к IFTTT
		2) Записываем событие в свою БД
		3) Шлем сообщение в Telegram		
		*/
		
		$output = [];
		
		if(empty($relay))
			{
				// пустое имя
				$output['status'] = false;
				$output['error'] = 'Empty relay name';
			}
		else
			{
				$query_name = dbFilter($relay, 30);
				$q = mysql_query("SELECT * FROM `ewelink_devices` WHERE `short_name` = '".$query_name."'");
				if(mysql_num_rows($q) < 1)
					{
						// не найдено
						$output['status'] = false;
						$output['error'] = 'Relay not found';
					}
				else
					{
						// ошибок нет, продолжаем 
						$_DEVICE = mysql_fetch_assoc($q);
						
						$action = $action == 'on' ? 'on' : 'off';
						$act = $action == 'on' ? 1 : 0;
						$ru_action = $action == 'on' ? 'вкл.' : 'выкл.';
						
						$link = 'https://maker.ifttt.com/trigger/ewelink_'.$_DEVICE['short_name'].'_'.$action.'/with/key/'.$_IFTTT['key'];
						
						// making a request 
						$ch = curl_init($link);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
						curl_setopt($ch, CURLOPT_USERAGENT, 'PHPscript for Smarthome by D13410N3/1.0');
						
						$a = curl_exec($ch);
						
						
						//// записываем событие 
						mysql_query("INSERT INTO `ewelink_events`(`id_device`, `time`, `action`) VALUES (".$_DEVICE['id'].", ".$unixtime.", ".$act.")");
						
						if(@$notify === true)
							{
								// собираем базу комнат
								$q_rooms = mysql_query("SELECT * FROM `rooms`");
								$_ROOMS = array();

								while($_tmp = mysql_fetch_assoc($q_rooms))
									{
										$_ROOMS[$_tmp['id']] = $_tmp['name'];
									}
								
								$message = '<b>'.$_DEVICE['full_name'].'</b> | '.$_ROOMS[$_DEVICE['id_room']].' <i>('.$strtime.')</i>'.PHP_EOL;
		
		
								// определяем свет или просто подача питания
								if($_DEVICE['type'] == 'light')
									{
										$message .= 'свет ';
									}
								else
									{
										$message .= 'устройство ';
									}
								
								$message .= '<i>'.$ru_action.'</i>';
								
								if(@$byScript === true)
									{
										$message .= ' (скриптом)';
									}
								
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
								sendMessage($_CHAT['id'], $message, 'HTML');
								
							}
						
					}
			}
	}

function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('байт', 'кб', 'мб', 'гб', 'тб');   

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}