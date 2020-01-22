<?php
include_once 'core.php';
autOnly();

if(isset($_GET['action']))
	{
		if($_GET['action'] == 'edit' OR $_GET['action'] == 'delete' OR $_GET['action'] == 'view')
			{
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
				
				if($_GET['action'] == 'edit')
					{
						if(!empty($_POST['button']))
							{
								$formError = array();
								
								if(!empty($_POST['full_name']))
									{
										$db['full_name'] = dbFilter($_POST['full_name'], 100);
									}
								else
									{
										$formError[] = 'Укажите полное имя';
									}
								
								if(!empty($_POST['short_name']))
									{
										if(preg_match('#^([a-zA-Z0-9_]{1,20})$#iu', $_POST['short_name']))
											{
												$q_check = mysql_query("SELECT * FROM `ewelink_devices` WHERE `short_name` = '".$_POST['short_name']."' AND `id` != ".$_DEVICE['id']);
												if(mysql_num_rows($q_check) != 0)
													{
														$formError[] = 'Это короткое имя уже занято';
													}
													
												$db['short_name'] = dbFilter($_POST['short_name'], 20);
											}
										else
											{
												$formError[] = 'Краткое название - от 1 до 20 символов латинского алфавита и цифр';
											}
									}
								else
									{
										$formError[] = 'Укажите краткое имя - от 1 до 20 символов латинского алфавита и цифр';
									}
								
								if(!empty($_POST['id_room']))
									{
										$id_room = (int)$_POST['id_room'];
										$q_check_room = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 AND `id` = ".$id_room);
										
										if(mysql_num_rows($q_check_room) != 1)
											{
												$formError[] = 'Комната не найдена';
											}
										else
											{
												$db['id_room'] = $id_room;
											}
									}
								else
									{
										$formError[] = 'Выберите комнату';
									}
								
								if(!empty($_POST['type']))
									{
										if($_POST['type'] != 'switch' && $_POST['type'] != 'light')
											{
												$formError[] = 'Неверный тип устройства';
											}
										else
											{
												$db['type'] = $_POST['type'];
											}
									}
								else
									{
										$error[] = 'Выберите тип устройства';
									}
								
								if(empty($formError))
									{
										if(mysql_query("UPDATE `ewelink_devices` SET `full_name` = '".$db['full_name']."', `short_name` = '".$db['short_name']."', `id_room` = ".$db['id_room']." WHERE `id` = ".$_DEVICE['id']))
											{
												header("Location: index.php?upd_success");
												exit;
											}
										else
											{
												fatalError(mysql_error());
											}
									}
							}
						
						setTitle('Редактировать устройство');
						getHeader();
						showFormError(isset($formError) ? $formError : '');
						
						?>
						
						
						<div class="row">
							<form action="device.php?action=edit&id_device=<?=$_DEVICE['id']?>" method="post">
								<div class="col-sm">
									Полное название (до 100 символов):<br />
									<input type="text" required="required" name="full_name" class="form-control" value="<?=isset($_POST['full_name']) ? dbFilter($_POST['full_name'], 100) : $_DEVICE['full_name']?>" />
								</div>
								
								<div class="col-sm">
									Краткое название (a-z, 0-9, до 20 символов):<br />
									<input type="text" required="required" name="short_name" class="form-control" value="<?=isset($_POST['short_name']) ? dbFilter($_POST['short_name'], 100) : $_DEVICE['short_name']?>" />
								</div>
								
								<div class="col-sm">
									Комната:<br />
									<?php
									$q_rooms = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 ORDER BY `name` ASC");
									
									if(mysql_num_rows($q_rooms) == 0)
										{
											echo showError('Комнаты не найдены. <a href="rooms.php?action=add">Создать комнату</a>');
										}
									else
										{
											echo '<select class="form-control" name="id_room">';
											while($_ROOM = mysql_fetch_assoc($q_rooms))
												{
													echo '<option value="'.$_ROOM['id'].'"'.($_DEVICE['id_room'] == $_ROOM['id'] ? ' selected="selected"' : '').'>'.$_ROOM['name'].'</option>'.PHP_EOL;
												}
											echo '</select>';
										}
								
									?>
								</div>
								
								<div class="col-sm">
									Тип:<br />
									<select name="type" class="form-control">
										<option value="light"<?=$_DEVICE['type'] == 'light' ? ' selected="selected"' : ''?>>Освещение</option>
										<option value="switch"<?=$_DEVICE['type'] == 'switch' ? ' selected="selected"' : ''?>>Розетка</option>
									</select>
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
				
				if($_GET['action'] == 'delete')
					{
						if(!empty($_POST['button']) && @$_POST['confirm'])
							{
								if(mysql_query("UPDATE `ewelink_devices` SET `deleted` = 1 WHERE `id` = ".$_DEVICE['id']))
									{
										header('Location: index.php?device_removed');
										exit;
									}
								else
									{
										fatalError(mysql_error());
									}
							}
						
						setTitle('Удаление устройства');
						getHeader();
						
						?>
						
						<div class="row">
							<form action="device.php?action=delete&id_device=<?=$_DEVICE['id']?>" method="post">
								<div class="col-sm">
									<input type="checkbox" name="confirm" />
									<input type="submit" name="button" value="Удалить" class="btn btn-danger" />
								</div>
							</form>
						</div>
						
						<?php
						
						getFooter();
						exit;
					}
				
				if($_GET['action'] == 'view')
					{
						$unixtime = time();

						$q_events = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']);
						$c_events = mysql_num_rows($q_events);

						// Первое событие:
						$q_first_event = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." ORDER BY `id` ASC LIMIT 1");
						$first_event = mysql_fetch_assoc($q_first_event);

						// Последнее событие:
						$q_last_event = mysql_query("SELECT * FROM `ewelink_events` WHERE `id_device` = ".$_DEVICE['id']." ORDER BY `id` DESC LIMIT 1");
						$last_event = mysql_fetch_assoc($q_last_event);

						// Высчитываем суммарное и среднее время сессии. Необходимо учесть, что если последнее соыбтие - включение, то надо зафиксировать в $sumEnd текущее выключение
						$sumStart = 0;
						$sumEnd = $last_event['action'] == 1 ? $unixtime : 0;
						$sumEvents = 0;
						$sumOn = 0;
						$sumOff = 0;

						while($event = mysql_fetch_assoc($q_events))
							{
								$sumEvents++;
								if($event['action'] == 1)
									{
										$sumStart += $event['time'];
										$sumOn++;
									}
								else
									{
										$sumEnd += $event['time'];
										$sumOff++;
									}
							}

						$uptime = $sumEnd - $sumStart;
						$average = ceil($uptime / $sumEvents);
						
						// определяем комнату
						$q_room = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 AND `id` = ".$_DEVICE['id_room']);
						if(mysql_num_rows($q_room) == 0)
							{
								fatalError('Ошибка: неизвестный ID комнаты. Комната не найдена. Исправьте БД');
							}
						
						$_ROOM = mysql_fetch_assoc($q_room);
						
						setTitle('Устройство '.$_DEVICE['full_name']);
						getHeader();

						?>

						<dl class="row">
							<dt class="col-sm-3">Имя устройства</dt>
							<dd class="col-sm-9"><?=$_DEVICE['full_name']?></dd>
							
							<dt class="col-sm-3">Краткое имя устройства</dt>
							<dd class="col-sm-9"><?=$_DEVICE['short_name']?></dd>
							
							<dt class="col-sm-3">Тип:</dt>
							<dd class="col-sm-9"><?=$_DEVICE['type'] == 'light' ? 'Освещение' : 'Розетка'?></dd>
							
							<dt class="col-sm-3">Комната:</dt>
							<dd class="col-sm-9"><?=$_ROOM['name']?></dd>
							
							<dt class="col-sm-3">Текущий статус</dt>
							<dd class="col-sm-9"><?=$last_event['action'] == 1 ? '<span class="badge badge-pill badge-primary">Включено</span>' : '<span class="badge badge-pill badge-secondary">Выключено</span>'?></dd>
							
							<dt class="col-sm-3">Первое событие</dt>
							<dd class="col-sm-9"><?=date('d.m.Y H:i:s', $first_event['time'])?></dd>
							
							<dt class="col-sm-3">Последнее событие</dt>
							<dd class="col-sm-9"><?=date('d.m.Y H:i:s', $last_event['time'])?></dd>
							
							<dt class="col-sm-3">Число событий</dt>
							<dd class="col-sm-9">
								<dl class="row">
									<dd class="col-sm-3">Всего: <span class="badge badge-pill badge-info"><?=$sumEvents?></span></dd>
									<dd class="col-sm-3">Включений: <span class="badge badge-pill badge-primary"><?=$sumOn?></span></dd>
									<dd class="col-sm-3">Выключений: <span class="badge badge-pill badge-secondary"><?=$sumOff?></span></dd>
								</dl>
							
							</dd>
							
							<dt class="col-sm-3">Общее время работы</dt>
							<dd class="col-sm-9"><?=showTimeInterval($uptime)?></dd>
							
							<dt class="col-sm-3">Среднее время работы</dt>
							<dd class="col-sm-9"><?=showTimeInterval($average)?></dd>
						</dl>

						<a class="btn btn-success" href="device.php?id_device=<?=$_DEVICE['id']?>&action=edit">Редактировать</a>
						<a class="btn btn-danger" href="device.php?id_device=<?=$_DEVICE['id']?>&action=delete">Удалить</a>
							
						<?php
							
						getFooter();
					}
				
				exit;
			}
		
		if($_GET['action'] == 'add')
			{
				if(!empty($_POST['button']))
					{
						$formError = array();
						
						if(!empty($_POST['full_name']))
							{
								$db['full_name'] = dbFilter($_POST['full_name'], 100);
							}
						else
							{
								$formError[] = 'Укажите полное имя';
							}
						
						if(!empty($_POST['short_name']))
							{
								if(preg_match('#^([a-zA-Z0-9_]{1,20})$#iu', $_POST['short_name']))
									{
										$q_check = mysql_query("SELECT * FROM `ewelink_devices` WHERE `short_name` = '".$_POST['short_name']."' AND `id` != ".$_DEVICE['id']);
										if(mysql_num_rows($q_check) != 0)
											{
												$formError[] = 'Это короткое имя уже занято';
											}
											
										$db['short_name'] = dbFilter($_POST['short_name'], 20);
									}
								else
									{
										$formError[] = 'Краткое название - от 1 до 20 символов латинского алфавита и цифр';
									}
							}
						else
							{
								$formError[] = 'Укажите краткое имя - от 1 до 20 символов латинского алфавита и цифр';
							}
						
						if(!empty($_POST['id_room']))
							{
								$id_room = (int)$_POST['id_room'];
								$q_check_room = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 AND `id` = ".$id_room);
								
								if(mysql_num_rows($q_check_room) != 1)
									{
										$formError[] = 'Комната не найдена';
									}
								else
									{
										$db['id_room'] = $id_room;
									}
							}
						else
							{
								$formError[] = 'Выберите комнату';
							}
						
						if(!empty($_POST['type']))
							{
								if($_POST['type'] != 'switch' && $_POST['type'] != 'light')
									{
										$error[] = 'Неверный тип устройства';
									}
								else
									{
										$db['type'] = $_POST['type'];
									}
							}
						else
							{
								$error[] = 'Выберите тип устройства';
							}
						
						if(empty($formError))
							{
								if(mysql_query("INSERT INTO `ewelink_devices`(`id_room`, `short_name`, `full_name`, `type`, `deleted`, `time`) VALUES (".$db['id_room'].", '".$db['short_name']."', '".$db['full_name']."', '".$db['type']."', 0, ".time().")"))
									{
										header('Location: index.php?device_create_success');
										exit;
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				
				setTitle('Добавить новое устройство');
				getHeader();
				
				showFormError(isset($formError) ? $formError : '');
				
				?>
				
				<div class="row">
					<form action="device.php?action=add" method="post">
						<div class="col-sm">
							Полное название (до 100 символов):<br />
							<input type="text" required="required" name="full_name" class="form-control" value="<?=isset($_POST['full_name']) ? dbFilter($_POST['full_name'], 100) : ''?>" />
						</div>
						
						<div class="col-sm">
							Краткое название (a-z, 0-9, до 20 символов):<br />
							<input type="text" required="required" name="short_name" class="form-control" value="<?=isset($_POST['short_name']) ? dbFilter($_POST['short_name'], 100) : ''?>" />
						</div>
						
						<div class="col-sm">
							Комната:<br />
							<?php
							$q_rooms = mysql_query("SELECT * FROM `rooms` ORDER BY `name` ASC");
							
							if(mysql_num_rows($q_rooms) == 0)
								{
									echo showError('Комнаты не найдены. <a href="rooms.php?action=add">Создать комнату</a>');
								}
							else
								{
									echo '<select class="form-control" name="id_room">';
									while($_ROOM = mysql_fetch_assoc($q_rooms))
										{
											echo '<option value="'.$_ROOM['id'].'">'.$_ROOM['name'].'</option>'.PHP_EOL;
										}
									echo '</select>';
								}
						
							?>
						</div>
						
						<div class="col-sm">
							Тип:<br />
							<select name="type" class="form-control">
								<option value="light"<?=@$_POST['type'] == 'light' ? ' selected="selected"' : ''?>>Освещение</option>
								<option value="switch"<?=@$_POST['type'] == 'switch' ? ' selected="selected"' : ''?>>Розетка</option>
							</select>
						</div>
						
						<div class="col-sm">
							<input type="submit" name="button" value="Добавить" class="btn btn-primary" />
						</div>
					</form>
				</div>
				
				<?php
				getFooter();
				exit;
			}			
		exit;
	}