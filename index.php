<?php

include_once 'core.php';

autOnly();

define('TITLE', 'Все устройства');
getHeader();

// fix: rooms
// fix: day-work-timer for previous dates if worked all day
// added: total stats
// fix: don't show on/off time-stats for non-today
// removed: collapse with full stats (commented)
// todo: today's stats
// todo: similar col-sm-4 divs for stats (next row)
// todo: timeBack (abs() to showTimeInterval)

$date = @preg_match('#^(?:[0-9]{4})\-(?:[0-9]{2})\-(?:[0-9]{2})$#', $_GET['date']) ? $_GET['date'] : date('Y-m-d');
$view_date = explode('-', $date);
$view_date = array_reverse($view_date);
$view_date = implode('.', $view_date);

// Определяем даты
$day_start = strtotime($date.' 00:00:00');
$day_end = strtotime($date.' 23:59:59');
$unixtime = time();

// Определяем, сегодняшнюю ли дату смотрим
$__today_date = explode('.', date('d.m.Y', $unixtime));
$__stats_date = explode('.', date('d.m.Y', $day_start));

$_TODAY = $__today_date == $__stats_date ? TRUE : FALSE;

// Запрос на список всех устройств
$q_devices = mysql_query("SELECT * FROM `ewelink_devices` WHERE `deleted` = 0 ORDER BY `id_room` ASC");

// "глобальные" счетчики для всего
$_SUM['rooms'] = 0;
$_SUM['devices'] = 0;
$_SUM['events'] = 0;
$_SUM['on_events'] = 0;
$_SUM['off_events'] = 0;
$_SUM['uptime'] = 0;


// запрос на список комнат
$q_rooms = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0");
$_ROOMS = array();

while($_tmp = mysql_fetch_assoc($q_rooms))
	{
		$_ROOMS[$_tmp['id']] = $_tmp['name'];
		$_SUM['rooms']++;
	}

?>

<div class="alert alert-primary" role="alert">
  Статистика устройств за <b><?=$view_date?></b>
</div>

<div class="row">
	<form action="index.php" method="get">
		<div class="col-sm">
			<input type="date" value="<?=$date?>" id="date" name="date" class="form-control" style="margin-bottom: 5px;" />
		</div>
		
		
		<div class="col-sm">
			<input type="submit" value="Посмотреть" class="btn btn-primary" />
			<a href="index.php" class="btn btn-default" href="Сегодня">Сегодня</a>
		</div>
	</form>
</div>

<div class="row">

<?php

while($_DEVICE = mysql_fetch_assoc($q_devices))
	{
		$_SUM['devices']++;
		##################### делаем запрос на определение последнего события
		$q_last = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." ORDER BY `id` DESC LIMIT 1");
		
		if(mysql_num_rows($q_last) == 0)
			{
				$status = 0;
				$noEvents = true;
			}
		else
			{
				$event_last = mysql_fetch_assoc($q_last);
				$noEvents = false;
				
				$status = $event_last['action'];
			}
		##################### Начинаем рисовать блок. Выводим название
		echo '<div class="col-sm-4 device_card">';
		echo '<div style="text-align: center"><b>'.$_DEVICE['full_name'].' | '.$_ROOMS[$_DEVICE['id_room']].'</b> ';
		
		##################### Определяем статус устройства, рисуем badge 
		if($status == 0)
			{
				echo '<a class="badge badge-pill badge-secondary" href="ifttt_link.php?id_device='.$_DEVICE['id'].'&action=on&date='.$date.'">off</a>';
			}
		else
			{
				echo '<a class="badge badge-pill badge-primary" href="ifttt_link.php?id_device='.$_DEVICE['id'].'&action=off&date='.$date.'">on</a>';
			}
		
		echo '</div>';
		
		
		#####################  Если девайс включен - рисуем сколько уже работает, если нет - когда выключился. Только для просмотра статистики текущего дня
		
		if(!$noEvents && $_TODAY)
			{
				if($status == 1)
					{
						$string_now_uptime = showTimeInterval($unixtime - $event_last['time']);
						$string_now_uptime = empty($string_now_uptime) ? '0 сек' : $string_now_uptime;
						echo '<span class="small">Работает</span> <span class="badge badge-info">'.$string_now_uptime.'</span>';
					}
				else
					{
						$string_when_off = showWhen($event_last['time']);
						echo '<span class="small">Выкл. с</span> <span class="badge badge-secondary">'.$string_when_off.'</span>';
					}
				echo '<br />';
			}
		
		
		
		##################### Работаем с событиями

		// фикс если запускается раньше конца текущего дня
		if($day_end > $unixtime)
			{
				$day_end = $unixtime;
			}
		
		$q = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." AND (`time` BETWEEN ".$day_start." AND ".$day_end.")");
		
		if(mysql_num_rows($q) > 0)
			{
				// Собираем список событий
				$events = array();

				while($event = mysql_fetch_assoc($q))
					{
						$events[] = array($event['action'], $event['time']);
					}
				
				// Определяем последнее событие
				$c_events = count($events);
				$last_event = $events[$c_events - 1][0];
				
				
				// определяем первое событие
				$first_event = $events[0][0];

				// Если первое событие - включение, то ничего не делаем, если выключение - фиксируем включение в 00:00:00. Третий аргумент для пометки фейкового включения 
				if($first_event == 0)
					{
						// die('СВЕТ БЫЛ ВКЛЮЧЕН!');
						$events[] = array(1, $day_start, true);
					}
				

				// Если последнее событие - выключение, то ничего не делаем, если включение - фиксируем выключение в 23:59:59. Третий аргумент для пометки фейкового выключения
				if($last_event == 1)
					{
						$events[] = array(0, $day_end, true);
					}

				// считаем сумму и включения-выключения
				$sum_off = 0;
				$c_off = 0;
				$sum_on = 0;
				$c_on = 0;
				
				
				foreach($events as $event)
					{
						if($event[0] == 0)
							{
								$sum_off += $event[1];
								// фикс того, будто мы считаем включение в 00:00:00, чтобы не было расхождения total счетчика
								if(!isset($event[2]))
									{
										$c_off++;
									}
							}
						else
							{
								$sum_on += $event[1];
								// фикс того, будто мы считаем выключение в 23:59:59, чтобы не было расхождения total счетчика
								if(!isset($event[2]))
									{
										$c_on++;
									}
							}
					}

				$uptime = $sum_off - $sum_on;
			}
		else
			{
				// фикс: если свет включен, но событий за сегодня нет, для подсчета аптайма за сегодня берем начало текущего дня
				if($status == 1)
					{
						$uptime = $day_end - $day_start;
					}
				else
					{
						// свет выключен, событий за прошлую дату нет -> система думает, что свет выключен
						// ищем последнее событие до $day_start. если оно - включение, считаем, что свет работал весь день
						$q_check_last_query = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." AND `time` < ".$day_start." ORDER BY `time` DESC LIMIT 1");
						if(mysql_num_rows($q_check_last_query) > 0)
							{
								$check_last_query = mysql_fetch_assoc($q_check_last_query);
								if($check_last_query['action'] == 1)
									{
										// последнее событие - включение. устройство работало весь день
										$uptime = 86399; // не спрашивайте почему
									}
								else
									{
										// последнее соыбтие - выключение. устройство не работало
										$uptime = 0;
									}
							}
						else
							{
								// событий вообще нет. устройство не работало, судя по всему еще ни разу
								$uptime = 0;
							}
					
					}
				$c_on = 0;
				$c_off = 0;
				
			}

		# высчитываем аптайм
		$string = showTimeInterval($uptime);
		
		#####################  Рисуем статистику
		echo '<span class="small">'.($_TODAY ? 'За сегодня' : 'Всего за '.date('d.m', $day_start)).': </span> ';
		echo !empty($string) ? '<span class="badge badge-info">'.$string : '<span class="badge badge-warning">не работало';
		echo '</span><br />';
		
		// Подсчет общей статистики
		/*
		$q = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']);
		
		if(mysql_num_rows($q) > 0)
			{
				$events = array();

				while($event = mysql_fetch_assoc($q))
					{
						$_SUM['events']++;
						
						$events[] = array($event['action'], $event['time']);
						
						if($event['action'] == 0)
							{
								$_SUM['off_events']++;
							}
						else
							{
								$_SUM['on_events']++;
							}
					}


				// определяем первое событие
				$first_event = $events[0][0];

				// Если первое событие - включение, то ничего не делаем, если выключение - фиксируем включение в 00:00:00 1 января 1970 года. Третий аргумент для пометки фейкового включения 
				if($first_event == 0)
					{
						$events[] = array(1, 0, true);
					}


				// Определяем последнее событие
				$c_events = count($events);
				$last_event = $events[$c_events - 1][0];

				// Если последнее событие - выключение, то ничего не делаем, если включение - фиксируем выключение сегодня в 23:59:59. Третий аргумент для пометки фейкового выключения
				if($last_event == 1)
					{
						$events[] = array(0, $day_end, true);
					}

				// считаем сумму и включения-выключения
				$sum_off = 0;
				$c_off = 0;
				$sum_on = 0;
				$c_on = 0;
				
				foreach($events as $event)
					{
						if($event[0] == 0)
							{
								$sum_off += $event[1];
								// фикс того, будто мы считаем включение в 00:00:00, чтобы не было расхождения total счетчика
								if(!isset($event[2]))
									{
										$c_off++;
									}
							}
						else
							{
								$sum_on += $event[1];
								// фикс того, будто мы считаем выключение в 23:59:59, чтобы не было расхождения total счетчика
								if(!isset($event[2]))
									{
										$c_on++;
									}
							}
					}

				$uptime = $sum_off - $sum_on;
				$_SUM['uptime'] += $uptime;
			}
		else
			{
				$uptime = 0;
			}
		
		# высчитываем аптайм
		
		$string = showTimeInterval($uptime);
		
		##################### рисуем коллапс с общей статой
		
		echo '<a data-toggle="collapse" href="#collapse_'.$_DEVICE['id'].'" role="button" aria-expanded="false" aria-controls="collapseExample" class="badge badge-light">За всё время</a>
		
		<div class="collapse" id="collapse_'.$_DEVICE['id'].'" style="margin-top: 5px;">
			<div class="alert alert-light" style="font-size: small;">';
				
				echo !empty($string) ? '<span class="badge badge-info">'.$string : '<span class="badge badge-warning">Не использовалось';
				echo '</span><br />

				<span class="badge badge-pill badge-light">'.$c_on.' вкл.</span> / <span class="badge badge-pill badge-dark">'.$c_off.' выкл</span>
			</div>
		</div>';
		*/
		
		echo '<a href="events.php?id_device='.$_DEVICE['id'].'" class="badge badge-light">События</a>
		<a href="device.php?id_device='.$_DEVICE['id'].'&action=view" class="badge badge-light">Подробно</a>
		
		</div>
		';
	}
echo '</div><br /><br />

<h5>Полная статистика:</h5>

<div class="row">
	<div class="col-sm-4">
		<ul class="list-group list-group-flush">
			<li class="list-group-item">Всего комнат: '.$_SUM['rooms'].'</li>
			<li class="list-group-item">Всего устройств: '.$_SUM['devices'].'</li>
			<li class="list-group-item">Всего событий: '.$_SUM['events'].'</li>
			<li class="list-group-item">Всего включений: '.$_SUM['on_events'].'</li>
			<li class="list-group-item">Всего выключений: '.$_SUM['off_events'].'</li>
			<li class="list-group-item">Общее время работы: '.showTimeInterval($_SUM['uptime']).'</li>
		</ul>
	</div>
</div>

';


getFooter();