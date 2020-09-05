<?php

include_once 'core.php';

if(isset($_GET['action']))
	{
		///// добавление 
		if($_GET['action'] == 'add')
			{
				if(isset($_POST['button']))
					{
						$formError = array();
						
						if(!empty($_POST['mac']))
							{
								if(preg_match('#^([a-fA-F0-9]{12})$#iu', $_POST['mac']) OR preg_match('#^([a-fA-F0-9\:]{17})$#iu', $_POST['mac']))
									{
										$db['mac'] = mb_strtoupper($_POST['mac']);
										$db['mac'] = str_replace(':', '', $db['mac']);
									}
								else
									{
										$formError[] = 'Некорректный формат MAC-адреса';
									}
							}
						else
							{
								$formError[] = 'Укажите MAC-адрес устройства';
							}
						
						if(isset($_POST['name']))
							{
								$db['name'] = dbFilter($_POST['name'], 50);
							}
						else
							{
								$formError[] = 'Укажите имя устройства';
							}
						
						if(!empty($_POST['short_name']))
							{
								if(preg_match('#^([a-zA-Z0-9_]{1,20})$#iu', $_POST['short_name']))
									{
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
						
						// first check
						if(empty($formError))
							{
								$q_check = mysql_query("SELECT * FROM `wireless_clients` WHERE `mac` = '".$db['mac']."' OR `name` = '".$db['name']."' OR `short_name` = '".$db['short_name']."'");
								if(mysql_num_rows($q_check) != 0)
									{
										$formError[] = 'Устройство с таким именем и/или MAC-адресом уже есть';
									}
							}
						
						if(empty($formError))
							{
								if(mysql_query("INSERT INTO `wireless_clients`(`mac`, `name`, `deleted`, `status`, `time_added`, `time_update`, `short_name`) VALUES ('".$db['mac']."', '".$db['name']."', 0, 0, ".time().", ".time().", '".$db['short_name']."')"))
									{
										$__id = mysql_insert_id();
										
										header('Location: wireless_client.php?action=view&id='.$__id);
										exit;
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				
				setTitle('Добавить Wi-Fi устройство');
				getHeader();
				
				showFormError(isset($formError) ? $formError : '');
				
				?>
				
				<div class="row">
					<form action="wireless_client.php?action=add" method="post">
						<div class="col-sm">
							Название устройства:<br />
							<input type="text" required="required" name="name" class="form-control" value="<?=isset($_POST['name']) ? dbFilter($_POST['name'], 50) : ''?>" />
						</div>
						
						<div class="col-sm">
							Краткое название (a-z, 0-9, до 20 символов):<br />
							<input type="text" required="required" name="short_name" class="form-control" value="<?=isset($_POST['short_name']) ? dbFilter($_POST['short_name'], 100) : ''?>" />
						</div>
						
						<div class="col-sm">
							MAC-адрес устройства:<br />
							<input type="text" required="required" name="mac" class="form-control" value="<?=isset($_POST['mac']) ? dbFilter($_POST['mac'], 17) : ''?>" />
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
		
		if($_GET['action'] == 'edit' OR $_GET['action'] == 'delete' OR $_GET['action'] == 'view')
			{
				if(isset($_GET['id']))
					{
						$id = (int)$_GET['id'];
						$q_check = mysql_query("SELECT * FROM `wireless_clients` WHERE `id` = ".$id." AND `deleted` = 0");
						if(mysql_num_rows($q_check) == 1)
							{
								$_CLIENT = mysql_fetch_assoc($q_check);
							}
						else
							{
								fatalError('Устройство не найдено');
							}
					}
				else
					{
						fatalError('Устройство не найдено');
					}
				
				////// изменить
				
				if($_GET['action'] == 'edit')
					{
						if(isset($_POST['button']))
							{
								$formError = [];
								
								if(isset($_POST['mac']))
									{
										if(preg_match('#^([a-fA-F0-9]{12})$#iu', $_POST['mac']) OR preg_match('#^([a-fA-F0-9\:]{17})$#iu', $_POST['mac']))
											{
												$db['mac'] = mb_strtoupper($_POST['mac']);
												$db['mac'] = str_replace(':', '', $db['mac']);
												// проверка на задвоение после редактирования
												
												if($_CLIENT['mac'] != $db['mac'])
													{
														$q_check = mysql_query("SELECT * FROM `wireless_clients` WHERE `mac` = '".$db['mac']."'");
														if(mysql_num_rows($q_check) != 0)
															{
																$formError[] = 'Устройство с таким MAC-адресом уже есть';
															}
													}
											}
										else
											{
												$formError[] = 'Некорректный формат MAC-адреса';
											}
									}
								else
									{
										$formError[] = 'Укажите MAC-адрес устройства';
									}
								
								if(isset($_POST['name']))
									{
										$db['name'] = dbFilter($_POST['name'], 50);
										
										if($_CLIENT['name'] != $_POST['name'])
											{
												$q_check = mysql_query("SELECT * FROM `wireless_clients` WHERE `name` = '".$db['name']."'");
												if(mysql_num_rows($q_check) != 0)
													{
														$formError[] = 'Устройство с таким именем уже есть';
													}
											}
									}
								else
									{
										$formError[] = 'Укажите имя устройства';
									}
								
								if(!empty($_POST['short_name']))
									{
										if(preg_match('#^([a-zA-Z0-9_]{1,20})$#iu', $_POST['short_name']))
											{
												$db['short_name'] = dbFilter($_POST['short_name'], 20);
												
												$q_check = mysql_query("SELECT * FROM `wireless_clients` WHERE `short_name` = '".$db['short_name']."' AND `id` != ".$_CLIENT['id']);
												if(mysql_num_rows($q_check) != 0)
													{
														$formError[] = 'Это короткое имя уже занято';
													}
											}
										else
											{
												$formError[] = 'Код устройства - от 1 до 20 символов латинского алфавита и цифр';
											}
									}
								else
									{
										$formError[] = 'Код устройства - от 1 до 20 символов латинского алфавита и цифр';
									}
								
								if(empty($formError))
									{
										if(mysql_query("UPDATE `wireless_clients` SET `mac` = '".$db['mac']."', `name` = '".$db['name']."', `short_name` = '".$db['short_name']."' WHERE `id` = ".$_CLIENT['id']))
											{
												header('Location: wireless_client.php?action=view&id='.$_CLIENT['id']);
												exit;
											}
										else
											{
												fatalError(mysql_error());
											}
									}
							}
						
						setTitle($_CLIENT['name'].' | Редактировать');
						getHeader();
						
						showFormError(isset($formError) ? $formError : '');
						
						?>
						
						<div class="row">
							<form action="wireless_client.php?action=edit&id=<?=$_CLIENT['id']?>" method="post">
								<div class="col-sm">
									Название устройства:<br />
									<input type="text" required="required" name="name" class="form-control" value="<?=isset($_POST['name']) ? dbFilter($_POST['name'], 50) : $_CLIENT['name']?>" />
								</div>
								
								<div class="col-sm">
									Код устройства (a-z, 0-9, до 20 символов):<br />
									<input type="text" required="required" name="short_name" class="form-control" value="<?=isset($_POST['short_name']) ? dbFilter($_POST['short_name'], 100) : $_CLIENT['short_name']?>" />
								</div>
								
								<div class="col-sm">
									MAC-адрес устройства:<br />
									<input type="text" required="required" name="mac" class="form-control" value="<?=isset($_POST['mac']) ? dbFilter($_POST['mac'], 17) : $_CLIENT['mac']?>" />
								</div>
								
								<div class="col-sm">
									<input type="submit" name="button" value="Изменить" class="btn btn-primary" />
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
								if(mysql_query("UPDATE `wireless_clients` SET `deleted` = 1 WHERE `id` = ".$_CLIENT['id']))
									{
										header('Location: index.php?wireless_removed');
										exit;
									}
								else
									{
										fatalError(mysql_error());
									}
							}
						
						setTitle('Удаление Wi-Fi устройства');
						getHeader();
						
						?>
						
						<div class="row">
							<form action="wireless_client.php?action=delete&id=<?=$_CLIENT['id']?>" method="post">
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
						setTitle('Wi-Fi устройство "'.$_CLIENT['name'].'"');
						getHeader();
						
						// first event
						$q_first_event = mysql_query("SELECT * FROM `wireless_clients_events` WHERE `id` = ".$_CLIENT['id']." ORDER BY `time` ASC LIMIT 1");
						if(mysql_num_rows($q_first_event) == 1)
							{
								$first_event = mysql_fetch_assoc($q_first_event);
							}
						else
							{
								$first_event['time'] = 0;
							}
						
						// last event
						$q_last_event = mysql_query("SELECT * FROM `wireless_clients_events` WHERE `id` = ".$_CLIENT['id']." ORDER BY `time` DESC LIMIT 1");
						if(mysql_num_rows($q_last_event) == 1)
							{
								$last_event = mysql_fetch_assoc($q_last_event);
							}
						else
							{
								$last_event['time'] = 0;
							}
						
						
						// total status = 1
						$c_on_events = mysql_num_rows(mysql_query("SELECT * FROM `wireless_clients_events` WHERE `status` = 1 AND `id_client` = ".$_CLIENT['id']));
						
						// total status = 0
						$c_off_events = mysql_num_rows(mysql_query("SELECT * FROM `wireless_clients_events` WHERE `status` = 0 AND `id_client` = ".$_CLIENT['id']));
						
						// total events
						$c_total_events = $c_on_events + $c_off_events;
						
						?>
						
						<dl class="row">
							<dt class="col-sm-3">Статус</dt>
							<dd class="col-sm-9"><?=$_CLIENT['status'] == 1 ? '<span class="badge badge-success">Online</span> <span class="badge badge-info">'.$_CLIENT['interface'].'</span>' : '<span class="badge badge-secondary">Offline</span>'?> (<?=showTimeInterval(time() - $_CLIENT['time_update'])?>)</dd>
							<?php
								if($_CLIENT['status'] == 1)
									{
										list($rx, $tx) = explode(',', $_CLIENT['bytes']);
										?>
											<dt class="col-sm-3">Скорость соединения (прием/передача)</dt>
											<dd class="col-sm-9"><span class="badge badge-info"><?=$_CLIENT['tx_rate']?></span> / <span class="badge badge-info"><?=$_CLIENT['rx_rate']?></span></dd>
										
											<dt class="col-sm-3">Данные (принято/передано)</dt>
											<dd class="col-sm-9"><span class="badge badge-info"><?=formatBytes($rx, 2)?></span> / <span class="badge badge-info"><?=formatBytes($tx, 2)?></span></dd>
										
										<?php
									}
							?>
							<dt class="col-sm-3">Код устройства</dt>
							<dd class="col-sm-9"><?=$_CLIENT['short_name']?></dd>
							
							<dt class="col-sm-3">MAC-адрес</dt>
							<dd class="col-sm-9"><?=$_CLIENT['mac']?></dd>
							
							<dt class="col-sm-3">Первое событие</dt>
							<dd class="col-sm-9"><?=showWhen($first_event['time'])?></dd>
							
							<dt class="col-sm-3">Последнее событие</dt>
							<dd class="col-sm-9"><?=showWhen($last_event['time'])?></dd>
							
							<dt class="col-sm-3">Число событий</dt>
							<dd class="col-sm-9">
								<dl class="row">
									<dd class="col-sm-3">Всего: <span class="badge badge-pill badge-info"><?=$c_total_events?></span></dd>
									<dd class="col-sm-3">Подключений: <span class="badge badge-pill badge-primary"><?=$c_on_events?></span></dd>
									<dd class="col-sm-3">Отключений: <span class="badge badge-pill badge-secondary"><?=$c_off_events?></span></dd>
								</dl>
							</dd>
						</dl>
						
						<a class="btn btn-success" href="wireless_client.php?id=<?=$_CLIENT['id']?>&action=edit">Редактировать</a>
						<a class="btn btn-danger" href="wireless_client.php?id=<?=$_CLIENT['id']?>&action=delete">Удалить</a>
						
						<?php
							
						getFooter();
						exit;
					}
			}
		die('how do you do fellow kids?');
	}