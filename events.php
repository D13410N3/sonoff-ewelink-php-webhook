<?php
include_once 'core.php';
autOnly();

if(empty($_GET['id_device']))
	{
		header('Location: index.php?emptyid');
		exit;
	}

$id = (int)$_GET['id_device'];

$q_device = mysql_query("SELECT * FROM `ewelink_devices` WHERE `id` = ".$id);

if(mysql_num_rows($q_device) != 1)
	{
		header('Location: index.php?notfound');
		exit;
	}

$_DEVICE = mysql_fetch_assoc($q_device);


if(isset($_GET['action']))
	{
		if($_GET['action'] == 'delete')
			{
				$id_event = (int)$_GET['id_event'];
				
				mysql_query("DELETE FROM `ewelink_events` WHERE `id` = ".$id_event);
				header('Location: events.php?id_device='.$_DEVICE['id']);
				exit;
			}
	}

define('TITLE', $_DEVICE['full_name'].': история событий');

getHeader();

$q_events = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." ORDER BY `time` DESC");

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
					<th scope="col">Действия</th>
				</tr>
			</thead>
			
			<tbody>

		<?php
		$rows = 0;
		
		while($event = mysql_fetch_assoc($q_events))
			{
				$rows++;
				$trClass = $event['action'] == 1 ? 'light' : 'dark';
				if($event['action'] == 0)
					{
						$q_last_on = mysql_query("SELECT * FROM `ewelink_events` WHERE `action` = 1 AND `id_device` = ".$_DEVICE['id']." AND `id` < ".$event['id']." ORDER BY `time` DESC LIMIT 1");
						$last_on_event = mysql_fetch_assoc($q_last_on);
						$interval = ' ('.showTimeInterval($event['time'] - $last_on_event['time']).')';
					}
				else
					{
						$interval = '';
					}
				?>
				
				<tr class="table-<?=$trClass?> small">
					<td><?=$rows?></td>
					<td><?=showWhen($event['time']).$interval?></td>
					<td><a href="events.php?action=delete&id_event=<?=$event['id']?>&id_device=<?=$_DEVICE['id']?>" class="badge badge-light">Удалить</a></td>
				</tr>
				
				<?php
			}
			
			?>
			</tbody>
		</table>
	<?php
	}

getFooter();