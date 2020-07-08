<?php

include_once 'core.php';
autOnly();

if(empty($_GET['id_sensor']))
	{
		header('Location: index.php?emptyidsensor');
		exit;
	}

$id = (int)$_GET['id_sensor'];

$q_sensor = mysql_query("SELECT * FROM `ewelink_sensors` WHERE `id` = ".$id);

if(mysql_num_rows($q_sensor) != 1)
	{
		header('Location: index.php?notfound');
		exit;
	}

$_SENSOR = mysql_fetch_assoc($q_sensor);
$q_room = mysql_query("SELECT * FROM `rooms` WHERE `id` = ".$_SENSOR['id_room']);
if(mysql_num_rows($q_room) < 1)
	{
		$room = 'Неизвестная комната';
	}
else
	{
		$_tmp = mysql_fetch_assoc($q_room);
		$room = $_tmp['name'];
	}

if(isset($_GET['action']))
	{
		if($_GET['action'] == 'delete')
			{
				$id_event = (int)$_GET['id_event'];
				
				mysql_query("DELETE FROM `ewelink_sensors_events` WHERE `id` = ".$id_event);
				header('Location: events_sensor.php?id_sensor='.$_SENSOR['id']);
				exit;
			}
	}

define('TITLE', $_SENSOR['full_name'].' | '.$room.': история событий (300)');

getHeader();

$q_events = mysql_query("SELECT * FROM `ewelink_sensors_events` WHERE `id_sensor` = ".$_SENSOR['id']." ORDER BY `time` DESC LIMIT 300");

if(mysql_num_rows($q_events) == 0)
	{
		?>
		<div class="alert alert-danger" role="alert">
			Нет событий
		</div>
		<?php
	}
else
	{
		?>
			
		<table class="table">
		<!-- number, action, time -->

			<thead>
				<tr>
					<th scope="col">№</th>
					<th scope="col">Время</th>
					<th scope="col">Действие</th>
				</tr>
			</thead>
			
			<tbody>
			
		<?php
		$rows = 0;
			
		while($event = mysql_fetch_assoc($q_events))
			{
				$rows++;
				?>
				
				<tr class="table-light small">
					<td><?=$rows?></td>
					<td><?=showWhen($event['time']).$interval?></td>
					<td><a href="events_sensor.php?action=delete&id_event=<?=$event['id']?>&id_sensor=<?=$_SENSOR['id']?>" class="badge badge-light">Удалить</a></td>
				</tr>
				<?php
			}
				
				?>
			</tbody>
		</table>
		<?php
	}

	getFooter();