<?php
include_once 'core.php';
autOnly();

$unixtime = time();

if(isset($_GET['action']))
	{
		if($_GET['action'] == 'add')
			{
				if(!empty($_POST['button']))
					{
						$formError = array();
						
						if(!empty($_POST['name']))
							{
								$db['name'] = dbFilter($_POST['name'], 50);
								$q_check = mysql_query("SELECT * FROM `rooms` WHERE `name` = '".$db['name']."' AND `deleted` = 0");
								if(mysql_num_rows($q_check) != 0)
									{
										$formError[] = 'Такое название комнаты уже используется';
									}
							}
						else
							{
								$formError[] = 'Укажите название комнаты';
							}
						
						if(empty($formError))
							{
								if(mysql_query("INSERT INTO `rooms`(`name`, `time`, `deleted`) VALUES ('".$db['name']."', ".$unixtime.", 0)"))
									{
										header('Location: rooms.php?created_success');
										exit;
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				
				setTitle('Создать новую комнату');
				getHeader();
				
				showFormError(isset($formError) ? $formError : '');
				
				?>
				
				<div class="row">
					<form action="rooms.php?action=add" method="post">
						<div class="col-sm">
							Название комнаты:<br />
							<input type="text" required="required" name="name" class="form-control" value="<?=isset($_POST['name']) ? dbFilter($_POST['name'], 50) : ''?>" />
						</div>
						
						<div class="col-sm">
							<input type="submit" name="button" value="Создать" class="btn btn-primary" />
						</div>
					</form>
				</div>
				
				<?php
				getFooter();
				exit;
			}
		
		if($_GET['action'] == 'edit' OR $_GET['action'] == 'delete')
			{
				if(isset($_GET['id_room']))
					{
						$id = (int)$_GET['id_room'];
						
						$q_room = mysql_query("SELECT * FROM `rooms` WHERE `id` = ".$id);
						if(mysql_num_rows($q_room) != 1)
							{
								fatalError('Комната не найдена');
							}
							
						$_ROOM = mysql_fetch_assoc($q_room);
						
						
						// Редактирование
						if($_GET['action'] == 'edit')
							{
								if(isset($_POST['button']))
									{
										$formError = array();
										
										if(isset($_POST['name']))
											{
												$db['name'] = dbFilter($_POST['name'], 50);
												$q_check = mysql_query("SELECT * FROM `rooms` WHERE `name` = '".$db['name']."' AND `id` != ".$_ROOM['id']." AND `deleted` = 0");
												if(mysql_num_rows($q_check) != 0)
													{
														$formError[] = 'Такое название комнаты уже используется';
													}
											}
										else
											{
												$error[] = 'Укажите название комнаты';
											}
										
										if(empty($formError))
											{
												if(mysql_query("UPDATE `rooms` SET `name` = '".$db['name']."' WHERE `id` = ".$_ROOM['id']))
													{
														header('Locaion: rooms.php?id_room='.$_ROOM['id']);
														exit;
													}
												else
													{
														fatalError(mysql_error());
													}
											}
									}
								
								setTitle('Редактировать комнату');
								getHeader();
							
								showFormError(isset($formError) ? $formError : '');
								
								?>
								
								<div class="row">
									<form action="rooms.php?id_room=<?=$_ROOM['id']?>&action=edit" method="post">
										<div class="col-sm">
											Название комнаты<br />
											<input type="text" required="required" name="name" class="form-control" value="<?=isset($_POST['name']) ? dbfilter($_POST['name'], 50) : $_ROOM['name']?>" />
										</div>
										
										<div class="col-sm">
											<input type="submit" name="button" value="Сохранить" class="btn btn-primary" />
										</div>
									</form>
								</div>
								
								<?php
								getFooter();
								exit;
							}
						
						// Удаление (псевдо)
						
						if($_GET['action'] == 'delete')
							{
								if(isset($_POST['button']) && @$_POST['confirm'])
									{
										// помечаем удаление девайсов
										mysql_query("UPDATE `ewelink_devices` SET `deleted` = 1 WHERE `id_room` = ".$_ROOM['id']);
										
										// Помечаем удаление комнаты
										mysql_query("UPDATE `rooms` SET `deleted` = 1 WHERE `id` = ".$_ROOM['id']);
										
										header('Location: rooms.php?delete_success');
										exit;
									}
								
								setTitle('Удаление устройства');
								getHeader();
								
								echo showError('Удаление комнаты приведет к удалению всех устройств комнаты');
								?>
								
								<div class="row">
									<form action="rooms.php?id_room=<?=$_ROOM['id']?>&action=delete" method="post">
										<div class="col-sm">
											<input type="checkbox" name="confirm" class="form-control" />
											<input type="submit" name="button" value="Удалить" class="btn btn-danger" />
										</div>
									</form>
								</div>
								
								<?php
								
								getFooter();
								exit;
							}
						
						exit;
					}
				exit;
			}
		exit;
	}

setTitle('Список комнат');
getHeader();

?>

<a href="rooms.php?action=add" class="btn btn-primary">Добавить</a><br />

<?php
$q_rooms = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 ORDER BY `name` ASC");

if(mysql_num_rows($q_rooms) == 0)
	{
		echo showError('Нет комнат');
	}
else
	{
		echo '<div class="row">';
		
		while($_ROOM = mysql_fetch_assoc($q_rooms))
			{
				echo '<div class="col-sm-4 device_card">';
				echo '<div style="text-align: center"><b>'.$_ROOM['name'].'</b></div>';
				
				// число устройств в комнате
				$q_devices = mysql_query("SELECT * FROM `ewelink_devices` WHERE `id_room` = ".$_ROOM['id']);
				
				$c_devices = mysql_num_rows($q_devices);
				
				if($c_devices > 0)
					{
						// число событий
						$numEvents = 0;
						
						while($_DEVICE = mysql_fetch_assoc($q_devices))
							{
								$c_events = mysql_num_rows(mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']));
								$numEvents += $c_events;
							}
					}
				
				?>
				
				Всего устройств: <span class="badge badge-pill badge-info"><?=$c_devices?></span><br />
				<?=$c_devices > 0 ? 'Всего событий: <span class="badge badge-pill badge-info">'.$numEvents.'</span><br />' : ''?>
				<a href="rooms.php?action=edit&id_room=<?=$_ROOM['id']?>" class="badge badge-pill badge-success">Изменить</a> 
				<a href="rooms.php?action=delete&id_room=<?=$_ROOM['id']?>" class="badge badge-pill badge-danger">Удалить</a> 
				
				</div>
				<?php
			}
	}

getFooter();