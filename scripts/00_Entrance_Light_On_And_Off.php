<?php

// alpha

// $_SENSORS, $_DEVICES

if($_SENSORS['entrance_door_sensor']['back'] == 1)
	{
		// Датчик входной двери ИЛИ датчик движения в прихожей сработал 1 секунду назад. Проверим срабатывание датчиков тамбура:
		// Датчик движения в тамбуре - от 1 до 20 секунд назад
		// Датчик открытия двери в тамбуре - от 3 до 60 секунд назад
		// Дверь в тамбуре открылась РАНЬШЕ чем сработал датчик движения
		
		if($_SENSORS['vestibule_motion_sensor']['back'] >= 1 && $_SENSORS['vestibule_motion_sensor']['back'] <= 20)
			{
				if($_SENSORS['vestibule_door_sensor']['back'] >= 3 && $_SENSORS['vestibule_door_sensor']['back'] <= 60)
					{
						if($_SENSORS['vestibule_door_sensor']['back'] > $_SENSORS['vestibule_motion_sensor']['back'])
							{
								sendMessage(41851891, 'Зафиксирован вход в квартиру');
							}
					}
			}
		
	}

if($_SENSORS['vestibule_door_sensor']['back'] == 1)
	{
		// Датчик двери в тамбуре сработал 1 секунду назад. Проверим срабатывание остальных датчиков:
			// Датчик движения в тамбуре срабатывал от 1 до 20 секунд назад
			// Датчик входной двери срабатывал от 3 до 60 секунд назад
		if($_SENSORS['vestibule_motion_sensor']['back'] >= 1 && $_SENSORS['vestibule_motion_sensor']['back'] <= 20)
			{
				if($_SENSORS['entrance_door_sensor']['back'] >= 3 && $_SENSORS['entrance_door_sensor']['back'] <= 60)
					{
						sendMessage(41851891, 'Зафиксирован выход из квартиры');
					}
			}
	}
			

echo __FILE__.' - executed'.PHP_EOL.PHP_EOL;