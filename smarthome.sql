--
-- База данных: `smarthome`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ewelink_devices`
--

DROP TABLE IF EXISTS `ewelink_devices`;
CREATE TABLE `ewelink_devices` (
  `id` int(11) NOT NULL,
  `short_name` varchar(20) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `id_room` int(11) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  `time` int(11) DEFAULT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ewelink_events`
--

DROP TABLE IF EXISTS `ewelink_events`;
CREATE TABLE `ewelink_events` (
  `id` int(11) NOT NULL,
  `id_device` int(11) DEFAULT NULL,
  `action` tinyint(4) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL
) ENGINE=Aria DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0'
) ENGINE=Aria DEFAULT CHARSET=utf8;


ALTER TABLE `ewelink_devices`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `ewelink_events`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `ewelink_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  
ALTER TABLE `ewelink_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;