<?php
include_once 'core.php';
autOnly();

if(isset($_GET['action']))
	{
		if($_GET['action'] == 'edit' OR $_GET['action'] == 'delete' OR $_GET['action'] == 'view')
			{
				if(empty($_GET['id_sensor']))
					{
						header('Location: index.php?emptyidsens');
						exit;
					}

				$id = (int)$_GET['id_sensor'];

				// die("SELECT * FROM `ewelink_sensors` WHERE `id` = ".$id);
				$q_sensor = mysql_query("SELECT * FROM `ewelink_sensors` WHERE `id` = ".$id);

				if(mysql_num_rows($q_sensor) != 1)
					{
						header('Location: index.php?notfoundsens');
						exit;
					}

				$_SENSOR = mysql_fetch_assoc($q_sensor);
				
				////////////////////////////
				
				if($_GET['action'] == 'edit')
					{
						if(isset($_POST['button']))
							{
								$formError = array();
								
								if(!empty($_POST['full_name']))
									{
										$db['full_name'] = dbFilter($_POST['full_name'], 200);
									}
								else
									{
										$db['full_name'] = $_SENSOR['full_name'];
									}
								
								if(!empty($_POST['short_name']))
									{
										if(!preg_match('#^([a-zA-Z0-9_]{1,20})$#iu', $_POST['short_name']))
											{
												$formError[] = 'Короткое название датчика должно содержать от 3 до 20 символов a-z, цифр и нижнего подчеркивания';
											}
										else
											{
												$db['short_name'] = dbFilter($_POST['short_name'], 20);
												
												$q_check = mysql_query("SELECT * FROM `ewelink_sensors` WHERE `short_name` = '".$db['short_name']."' AND `id` != ".$_SENSOR['id']);
												
												if(mysql_num_rows($q_check) != 0)
													{
														$formError[] = 'Данное короткое название уже используется другим датчиком';
													}
											}
									}
								else
									{
										$db['short_name'] = $_SENSOR['short_name'];
									}
								
								
								if(!empty($_POST['id_room']))
									{
										$db['id_room'] = (int)$_POST['id_room'];
										
										$q_check = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 AND `id` = ".$db['id_room']);
										if(mysql_num_rows($q_check) != 1)
											{
												$formError[] = 'Комната не найдена';
											}
									}
								else
									{
										$db['id_room'] = $_SENSOR['id_room'];
									}
								
								if(isset($_POST['notify']))
									{
										$db['notify'] = 1;
									}
								else
									{
										$db['notify'] = 0;
									}
								
								if(empty($formError))
									{
										if(mysql_query("UPDATE `ewelink_sensors` SET `full_name` = '".$db['full_name']."', `short_name` = '".$db['short_name']."', `id_room` = ".$db['id_room'].", `notify` = ".$db['notify']." WHERE `id` = ".$_SENSOR['id']))
											{
												header('Location: sensor.php?action=view&id_sensor='.$_SENSOR['id']);
												exit;
											}
										else
											{
												fatalError(mysql_error());
											}
									}
							}
						
						setTitle('Редактировать датчик');
						getHeader();
						
						showFormError(isset($formError) ? $formError : '');
						
						?>
						
						<div class="row">
							<form action="sensor.php?action=edit&id_sensor=<?=$_SENSOR['id']?>" method="post">
								<div class="col-sm">
									Полное название (до 100 символов):<br />
									<input type="text" required="required" name="full_name" class="form-control" value="<?=isset($_POST['full_name']) ? dbFilter($_POST['full_name'], 100) : $_SENSOR['full_name']?>" />
								</div>
								
								<div class="col-sm">
									Краткое название (a-z, 0-9, до 20 символов):<br />
									<input type="text" required="required" name="short_name" class="form-control" value="<?=isset($_POST['short_name']) ? dbFilter($_POST['short_name'], 100) : $_SENSOR['short_name']?>" />
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
													echo '<option value="'.$_ROOM['id'].'"'.($_SENSOR['id_room'] == $_ROOM['id'] ? ' selected="selected"' : '').'>'.$_ROOM['name'].'</option>'.PHP_EOL;
												}
											echo '</select>';
										}
								
									?>
								</div>
								
								<div class="col-sm">
									<input type="checkbox" name="notify" <?=$_SENSOR['notify'] == 1 ? ' checked="checked"' : ''?> /> Присылать уведомление о срабатывании датчика
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
								if(mysql_query("UPDATE `ewelink_sensors` SET `deleted` = 1 WHERE `id` = ".$_SENSOR['id']))
									{
										header('Location: index.php?sensor_removed');
										exit;
									}
								else
									{
										fatalError(mysql_error());
									}
							}
						
						setTitle('Удаление датчика');
						getHeader();
						
						?>
						
						<div class="row">
							<form action="sensor.php?action=delete&id_sensor=<?=$_SENSOR['id']?>" method="post">
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

						$q_events = mysql_query("SELECT * FROM `ewelink_sensors_events` WHERE `id_sensor` = ".$_SENSOR['id']);
						$c_events = mysql_num_rows($q_events);
						
						// первое событие
						$q_first = mysql_query("SELECT * FROM `ewelink_sensors_events` WHERE `id_sensor` = ".$_SENSOR['id']." ORDER BY `time` ASC LIMIT 1");
						if(mysql_num_rows($q_first) == 0)
							{
								$first_event = 'событий не было';
							}
						else
							{
								$_tmp = mysql_fetch_assoc($q_first);
								$first_event = date('d.m.Y H:i:s', $_tmp['time']);
							}
						
						// последнее событие
						$q_last = mysql_query("SELECT * FROM `ewelink_sensors_events` WHERE `id_sensor` = ".$_SENSOR['id']." ORDER BY `time` DESC LIMIT 1");
						if(mysql_num_rows($q_last) == 0)
							{
								$last_event = 'событий не было';
							}
						else
							{
								$_tmp = mysql_fetch_assoc($q_last);
								$last_event = date('d.m.Y H:i:s', $_tmp['time']);
							}
						
						// комната 
						$q_room = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 AND `id` = ".$_SENSOR['id_room']);
						if(mysql_num_rows($q_room) == 0)
							{
								$_ROOM['name'] = 'ОШИБКА! КОМНАТА НЕ НАЙДЕНА';
							}
						else
							{
								$_ROOM = mysql_fetch_assoc($q_room);
							}
						
						setTitle('Датчик '.$_SENSOR['full_name'].' | '.$_ROOM['name']);
						getHeader();
						
						?>
						
						<dl class="row">
							<dt class="col-sm-3">Имя устройства</dt>
							<dd class="col-sm-9"><?=$_SENSOR['full_name']?></dd>
							
							<dt class="col-sm-3">Краткое имя устройства</dt>
							<dd class="col-sm-9"><?=$_SENSOR['short_name']?></dd>
							
							<dt class="col-sm-3">Тип:</dt>
							<dd class="col-sm-9"><?=$_SENSOR['type']?></dd>
							
							<dt class="col-sm-3">Комната:</dt>
							<dd class="col-sm-9"><?=$_ROOM['name']?></dd>
							
							<dt class="col-sm-3">Число событий</dt>
							<dd class="col-sm-9"><span class="badge badge-pill badge-info"><?=$c_events?></span></dd>
							
							<dt class="col-sm-3">Уведомления:</dt>
							<dd class="col-sm-9"><?=$_SENSOR['notify'] == 1 ? '<span class="badge badge-pill badge-primary">Включены</span>' : '<span class="badge badge-pill badge-secondary">Выключены</span>'?></dd>
						</dl>
							
							<a class="btn btn-success" href="sensor.php?id_sensor=<?=$_SENSOR['id']?>&action=edit">Редактировать</a>
							<a class="btn btn-danger" href="sensor.php?id_sensor=<?=$_SENSOR['id']?>&action=delete">Удалить</a>
							
						<?php
							
						getFooter();
						exit;
					}
				exit;
			}
		else
			{
				if($_GET['action'] == 'add')
					{
						if(!empty($_POST['button']))
							{
								// echo '<pre>'; print_r($_POST); die;
								$formError = array();
								
								if(empty($_POST['full_name']))
									{
										$formError[] = 'Укажите полное имя датчика';
									}
								else
									{
										$db['full_name'] = dbFilter($_POST['full_name'], 100);
									}
								
								if(empty($_POST['short_name']))
									{
										$formError[] = 'Укажите краткое название датчика';
									}
								else
									{
										if(!preg_match('#^([a-zA-Z0-9_]{1,20})$#iu', $_POST['short_name']))
											{
												$formError[] = 'Короткое название датчика должно содержать от 3 до 20 символов a-z, цифр и нижнего подчеркивания';
											}
										else
											{
												$db['short_name'] = dbFilter($_POST['short_name'], 20);
												
												$q_check = mysql_query("SELECT * FROM `ewelink_sensors` WHERE `short_name` = '".$db['short_name']."'");
												if(mysql_num_rows($q_check) != 0)
													{
														$formError[] = 'Данное короткое название уже используется другим датчиком';
													}
											}
									}
								
								if(!empty($_POST['id_room']))
									{
										$db['id_room'] = (int)$_POST['id_room'];
										
										$q_check = mysql_query("SELECT * FROM `rooms` WHERE `deleted` = 0 AND `id` = ".$db['id_room']);
										if(mysql_num_rows($q_check) != 1)
											{
												$formError[] = 'Комната не найдена';
											}
									}
								else
									{
										$formError[] = 'Выберите комнату';
									}
								
								if(isset($_POST['notify']))
									{
										$db['notify'] = 1;
									}
								else
									{
										$db['notify'] = 0;
									}
								
								$db['time'] = time();
								$db['deleted'] = 0;
								$db['type'] = 'circuit'; // других у нас пока что нет
								
								if(empty($formError))
									{
										if(mysql_query("INSERT INTO `ewelink_sensors`(`id_room`, `short_name`, `full_name`, `type`, `deleted`, `time`, `notify`) VALUES (".$db['id_room'].", '".$db['short_name']."', '".$db['full_name']."', '".$db['type']."', ".$db['deleted'].", ".$db['time'].", ".$db['notify'].")"))
											{
												header('Location: index.php?sensor_created_success');
												exit;
											}
										else
											{
												fatalError(mysql_error());
											}
									}
							}
						
						setTitle('Добавить новый датчик');
						getHeader();
						
						showFormError(isset($formError) ? $formError : '');
						
						?>
				
						<div class="row">
							<form action="sensor.php?action=add" method="post">
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
													echo '<option value="'.$_ROOM['id'].'">'.$_ROOM['name'].'</option>'.PHP_EOL;
												}
											echo '</select>';
										}
								
									?>
								</div>
								
								<div class="col-sm">
									<input type="checkbox" name="notify" /> Присылать уведомление о срабатывании датчика
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
			}
	}
else
	{
		header('Location: index.php?err_emp_act_snsr');
	}