<?php

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