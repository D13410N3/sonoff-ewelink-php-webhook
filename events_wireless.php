<?php
include_once 'core.php';
autOnly();

if(empty($_GET['id']))
	{
		header('Location: index.php?emptywirelessid');
		exit;
	}

$id = (int)$_GET['id'];

$q_client = mysql_query("SELECT * FROM `wireless_clients` WHERE `id` = ".$id);

if(mysql_num_rows($q_client) != 1)
	{
		header('Location: index.php?notfound');
		exit;
	}

$_CLIENT = mysql_fetch_assoc($q_client);

if(isset($_GET['action']))
	{
		if($_GET['action'] == 'delete')
			{
				$id_event = (int)$_GET['id_event'];
				
				mysql_query("DELETE FROM `wireless_clients_events` WHERE `id` = ".$id_event);
				header('Location: events_wireless.php?id='.$_CLIENT['id']);
				exit;
			}
	}

define('TITLE', $_CLIENT['name'].': история событий (300)');

getHeader();

$q_events = mysql_query("SELECT * FROM `wireless_clients_events` WHERE `id_client` = ".$_CLIENT['id']." ORDER BY `time` DESC LIMIT 300");

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
				$trClass = $event['status'] == 1 ? 'light' : 'dark';
				if($event['status'] == 0)
					{
						$q_last_on = mysql_query("SELECT * FROM `wireless_clients_events` WHERE `status` = 1 AND `id_client` = ".$_CLIENT['id']." AND `id` < ".$event['id']." ORDER BY `time` DESC LIMIT 1");
						$last_on_event = mysql_fetch_assoc($q_last_on);
						$interval = ' (Uptime: '.showTimeInterval($event['time'] - $last_on_event['time']).')';
					}
				else
					{
						$interval = '';
					}
				?>
				
				<tr class="table-<?=$trClass?> small">
					<td><?=$rows?></td>
					<td><?=showWhen($event['time']).$interval?></td>
					<td><a href="events_wireless.php?action=delete&id_event=<?=$event['id']?>&id=<?=$_CLIENT['id']?>" class="badge badge-light">Удалить</a></td>
				</tr>
				
				<?php
			}
			
			?>
			</tbody>
		</table>
	<?php
	}

getFooter();