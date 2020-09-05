-- phpMyAdmin SQL Dump
-- version 4.7.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Сен 05 2020 г., 10:50
-- Версия сервера: 10.0.38-MariaDB-0ubuntu0.16.04.1
-- Версия PHP: 7.0.33-12+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `smarthome`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ewelink_devices`
--

CREATE TABLE `ewelink_devices` (
  `id` int(11) NOT NULL,
  `short_name` varchar(20) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `id_room` int(11) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  `time` int(11) DEFAULT NULL,
  `power` mediumint(9) DEFAULT '0'
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ewelink_events`
--

CREATE TABLE `ewelink_events` (
  `id` int(11) NOT NULL,
  `id_device` int(11) DEFAULT NULL,
  `action` tinyint(4) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ewelink_sensors`
--

CREATE TABLE `ewelink_sensors` (
  `id` int(11) NOT NULL,
  `id_room` int(11) DEFAULT NULL,
  `short_name` tinytext,
  `full_name` tinytext,
  `type` tinyint(4) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  `time` int(11) DEFAULT NULL,
  `notify` tinyint(4) DEFAULT '0'
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ewelink_sensors_events`
--

CREATE TABLE `ewelink_sensors_events` (
  `id` int(11) NOT NULL,
  `id_sensor` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ewelink_sensors_types`
--

CREATE TABLE `ewelink_sensors_types` (
  `id` int(11) NOT NULL,
  `name` tinytext
) ENGINE=Aria DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `ewelink_sensors_types`
--

INSERT INTO `ewelink_sensors_types` (`id`, `name`) VALUES
(1, 'Датчик дыма'),
(2, 'Датчик движения'),
(3, 'Геркон'),
(4, 'Датчик движения'),
(5, 'Датчик протечки воды'),
(6, 'Датчик утечки газа');

-- --------------------------------------------------------

--
-- Структура таблицы `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0'
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `last_mikrotik_update` int(11) DEFAULT NULL,
  `mikrotik_address` tinytext,
  `mikrotik_username` tinytext,
  `mikrotik_password` tinytext,
  `mikrotik_port` tinytext,
  `telegram_token` tinytext,
  `telegram_chat_id` tinytext,
  `ifttt_key` tinytext,
  `mgmt_password` tinytext
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `wireless_clients`
--

CREATE TABLE `wireless_clients` (
  `id` int(11) NOT NULL,
  `mac` tinytext,
  `name` tinytext,
  `status` tinyint(4) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  `time_update` int(4) DEFAULT NULL,
  `time_added` int(11) DEFAULT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `wireless_clients_events`
--

CREATE TABLE `wireless_clients_events` (
  `id` int(11) NOT NULL,
  `id_client` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `time` int(11) DEFAULT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `ewelink_devices`
--
ALTER TABLE `ewelink_devices`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ewelink_events`
--
ALTER TABLE `ewelink_events`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ewelink_sensors`
--
ALTER TABLE `ewelink_sensors`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ewelink_sensors_events`
--
ALTER TABLE `ewelink_sensors_events`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ewelink_sensors_types`
--
ALTER TABLE `ewelink_sensors_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `wireless_clients`
--
ALTER TABLE `wireless_clients`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `wireless_clients_events`
--
ALTER TABLE `wireless_clients_events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `ewelink_devices`
--
ALTER TABLE `ewelink_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ewelink_events`
--
ALTER TABLE `ewelink_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ewelink_sensors`
--
ALTER TABLE `ewelink_sensors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ewelink_sensors_events`
--
ALTER TABLE `ewelink_sensors_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ewelink_sensors_types`
--
ALTER TABLE `ewelink_sensors_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `wireless_clients`
--
ALTER TABLE `wireless_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `wireless_clients_events`
--
ALTER TABLE `wireless_clients_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
