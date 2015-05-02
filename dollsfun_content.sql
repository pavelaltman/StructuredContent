-- phpMyAdmin SQL Dump
-- version 4.0.10.2
-- http://www.phpmyadmin.net
--
-- Хост: dollsfun.mysql.ukraine.com.ua
-- Время создания: Апр 25 2015 г., 17:38
-- Версия сервера: 5.1.72-cll-lve
-- Версия PHP: 5.2.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `dollsfun_content`
--

-- --------------------------------------------------------

--
-- Структура таблицы `sc_Definitions`
--

CREATE TABLE IF NOT EXISTS `sc_Definitions` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Words` int(10) unsigned NOT NULL DEFAULT '0',
  `Parts` tinyint(2) unsigned NOT NULL,
  `Definition` text NOT NULL,
  `Example` text NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `word_id` (`Words`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

--
-- Дамп данных таблицы `sc_Definitions`
--

INSERT INTO `sc_Definitions` (`Id`, `Words`, `Parts`, `Definition`, `Example`) VALUES
(1, 1, 6, 'to deter by advice or persuasion; persuade not to do something (often followed by from)', 'She dissuaded him from leaving home'),
(2, 2, 6, 'to receive or come to have possession, use, or enjoyment of', 'to get a birthday present'),
(6, 15, 6, 'to decrease or fall suddenly and markedly, as prices or the market', 'Stocks suffered their biggest losses in years and the dollar slumped on Wednesday after the latest inflation data'),
(7, 30, 2, 'small animal', 'cat jmps'),
(9, 35, 6, 'to control as with a curb; restrain; check', 'AMZN: Stone: Declining stock options could curb Amazon''s spending'),
(10, 34, 2, 'a retail or wholesale dealer in meat', 'A rabbit walks in a butcher''s store'),
(11, 36, 2, 'grievous distress, affliction, or trouble', 'poor execution (regarding the production of metal printers) only added to 3D Systems'' woes'),
(12, 37, 2, 'counsel, advice, or caution', 'The admonition to stay with your original answer because your first instinct is likely to be c orrect is actually incorrect'),
(13, 38, 2, 'a small compartment or boxlike room for a specific use by one occupant', 'The PTCs offer private, modular testing booths with ample workspace, comfortable seating, proper lighting, and ventilation'),
(14, 39, 6, 'to lessen in force or intensity, as wrath, grief, harshness, or pain; moderate', 'How could the use of variable (direct) costing mitigate the problem of how to allocate the fixed costs to individual units?'),
(15, 40, 5, 'having a natural inclination or tendency to something; disposed; liable', 'Western individuals are prone to make final decisions within their jurisdiction'),
(16, 41, 5, 'making claims or pretensions to superior importance or rights; overbearingly assuming; insolently proud', 'this manner may be interpreted as arrogant or disrespectful'),
(21, 47, 5, 'frank; outspoken; open and sincere', 'At the end of his life, he wrote an autobiography for his children that was totally candid, and not intended for publication'),
(22, 48, 5, 'lacking grace or ease in movement', 'an awkward dancer'),
(23, 49, 2, 'small or large animal', 'cat and dog'),
(24, 50, 2, 'the legal claim of one person upon the property of another person to secure the payment of a debt or the satisfaction of an obligation', 'Credit risks can be lowered by the use of credit insurance, liens on customer assets, and government loan guarantees for exports'),
(25, 51, 6, 'to drive off in various directions, to cause to vanish', 'to dispel her fears'),
(26, 52, 2, 'trim and graceful; finely contoured; streamlined', 'sleek device'),
(27, 53, 2, 'a problem brought about by pressures of time, money, inconvenience, etc.', 'Finding a decent place to have lunch in this neighborhood is always a hassle.'),
(28, 54, 2, 'overcrowding', 'severe traffic congestion'),
(29, 55, 2, 'to lead or move by persuasion or influence, as to some action or state of mind', 'to induce a person to buy a raffle ticket'),
(30, 56, 2, 'to make weak or feeble', 'Diabetes can rapidly debilitate a breadwinner and impose impotency, either outcome a solid marriage wrecker'),
(31, 57, 2, 'damaging, harmful', 'There is no evidence that eating microwaved foods is detrimental to humans or animals'),
(32, 58, 2, 'a strong feeling of dislike, opposition, repugnance, or antipathy (usually followed by to)', 'a strong aversion to snakes and spiders'),
(33, 59, 6, 'to go on a walking excursion or expedition, hike', 'By day he tramps the streets asking for money'),
(34, 0, 6, 'disgustingly or completely dirty', 'At night he finds a filthy corner '),
(35, 60, 5, 'disgustingly or completely dirty', 'At night he finds a filthy corner'),
(36, 61, 5, 'old, thin (of clothes)', 'wraped in a threadbare coat he found in a bin'),
(37, 62, 6, 'to be qualified for, or have a claim to (reward, assistance, punishment, etc.) because of actions, qualities, or situation', 'These kids deserve a warm bed and a warm meal'),
(38, 63, 5, 'causing dismay or horror', 'an appalling accident, an appalling lack of manners'),
(39, 64, 5, 'without means of subsistence, lacking food, clothing, and shelter', 'the one in seven Americans are destitute'),
(40, 65, 5, 'tyrannized over, oppressed', 'the downtrodden plebeians of ancient Rome'),
(41, 66, 5, 'causing great fear, or terror, terrible,  extremely bad, unpleasant, or ugly', 'a dreadful hat'),
(42, 67, 5, 'involving or full of grave risk, hazardous, dangerous', 'a perilous voyage across the Atlantic in a small boat'),
(43, 68, 6, 'to bring under complete control or subjection, to make submissive or subservient, enslave', 'Such powers have spheres of influence and subjugate lesser powers'),
(44, 69, 2, 'the husk, shell, or outer covering of a seed or fruit', 'coffe bean hull'),
(45, 70, 2, 'the dry external covering of certain fruits or seeds, especially of an ear of corn', 'They took something called rice husk ash: the leftover husk s from processing rice are burned for energy, and the ash remains'),
(46, 71, 2, 'the space of fourteen nights and days, two weeks', 'Fairtrade Fortnight'),
(47, 72, 6, 'to bargain in a petty, quibbling, and often contentious manner', 'They spent hours haggling over the price of fish'),
(48, 73, 2, 'an artificial variety of a species of domestic animal or cultivated plant', 'newly developed strains of coffee trees '),
(49, 74, 2, 'a period of dry weather, especially a long one that is injurious to crops', 'Australia is struggling to cope with the consequences of a devastating drought'),
(50, 75, 5, 'highly objectionable or offensive, odious', 'obnoxious behavior'),
(51, 76, 0, 'an advance from one place, position, or situation to another without progressing through all or any of the places or stages in between', 'a leapfrog from bank teller to vice president in one short year'),
(52, 78, 5, 'doubtful; questionable', 'An early decision on this is iffy'),
(53, 79, 5, 'of the third order, rank, stage, formation, etc., third', 'tertiary education'),
(54, 80, 5, 'not readily handled or managed in use or action, as from size, shape, or weight', 'unwieldy task'),
(55, 81, 6, 'study intensively for a short time', 'to cram for exam');

-- --------------------------------------------------------

--
-- Структура таблицы `sc_Parts`
--

CREATE TABLE IF NOT EXISTS `sc_Parts` (
  `Id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `Part` char(12) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=ucs2 AUTO_INCREMENT=9 ;

--
-- Дамп данных таблицы `sc_Parts`
--

INSERT INTO `sc_Parts` (`Id`, `Part`) VALUES
(1, 'article'),
(2, 'noun'),
(3, 'adverb'),
(4, 'conjunction'),
(5, 'adjective'),
(6, 'verb'),
(7, 'preposition'),
(8, 'pronoun');

-- --------------------------------------------------------

--
-- Структура таблицы `sc_Themes`
--

CREATE TABLE IF NOT EXISTS `sc_Themes` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Theme` char(50) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `theme` (`Theme`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Дамп данных таблицы `sc_Themes`
--

INSERT INTO `sc_Themes` (`Id`, `Theme`) VALUES
(1, 'Health and well being'),
(2, 'Trends'),
(3, 'Ethics and social responsibility'),
(4, 'You and your money'),
(5, 'Education'),
(6, 'Vocabulary advanced');

-- --------------------------------------------------------

--
-- Структура таблицы `sc_Topics`
--

CREATE TABLE IF NOT EXISTS `sc_Topics` (
  `Id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `Themes` tinyint(4) NOT NULL,
  `Topic` char(50) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `topic` (`Topic`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

--
-- Дамп данных таблицы `sc_Topics`
--

INSERT INTO `sc_Topics` (`Id`, `Themes`, `Topic`) VALUES
(7, 0, 'animals'),
(4, 0, 'finance'),
(16, 1, '46. The alternative way'),
(20, 1, '51. Mind over body'),
(14, 2, '36. Gadgets'),
(19, 0, 'delete this'),
(22, 0, 'delete too'),
(23, 1, '50. Health education'),
(24, 3, '38. Charity'),
(25, 3, '40. Fair Trade'),
(26, 4, '42. Consumer issues'),
(27, 4, '43. Economic issues'),
(28, 5, '54. MOOCs'),
(29, 6, '11. Study and academic work');

-- --------------------------------------------------------

--
-- Структура таблицы `sc_Words`
--

CREATE TABLE IF NOT EXISTS `sc_Words` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Word` char(30) NOT NULL,
  `Added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Topics` int(10) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `word` (`Word`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;

--
-- Дамп данных таблицы `sc_Words`
--

INSERT INTO `sc_Words` (`Id`, `Word`, `Added`, `Topics`) VALUES
(1, 'dissuade', '2014-10-22 19:39:00', 0),
(2, 'get', '2014-10-22 19:39:04', 0),
(40, 'prone', '2015-04-19 07:43:06', 0),
(36, 'woe', '2015-04-19 07:43:06', 0),
(15, 'slump', '2014-10-24 14:59:52', 0),
(39, 'mitigate', '2015-04-19 07:43:06', 0),
(35, 'curb', '2015-04-19 07:43:06', 0),
(34, 'butcher', '2014-10-25 09:33:32', 0),
(37, 'admonition', '2015-04-19 07:43:06', 0),
(38, 'booth', '2015-04-19 07:43:06', 0),
(30, 'cat', '2015-04-19 07:43:06', 0),
(41, 'arrogant', '2015-04-19 07:43:06', 0),
(47, 'candid', '2015-04-19 07:43:06', 0),
(48, 'awkward', '2015-04-19 07:43:06', 0),
(49, 'dog', '2015-04-19 07:43:06', 0),
(50, 'lien', '2015-04-19 07:43:06', 0),
(51, 'dispel', '2015-04-19 07:43:06', 0),
(52, 'sleek', '2015-04-19 07:43:06', 14),
(53, 'hassle', '2015-04-19 07:43:06', 0),
(54, 'Congestion', '2015-04-19 07:43:06', 20),
(55, 'induce', '2015-04-19 07:43:06', 20),
(56, 'dibilitate', '2015-04-19 07:43:06', 20),
(57, 'detrimental', '2015-04-19 07:43:06', 20),
(58, 'aversion', '2015-04-19 07:43:06', 16),
(59, 'tramp', '2015-04-19 07:43:06', 24),
(60, 'filthy', '2015-04-19 07:43:06', 24),
(61, 'threadbare', '2015-04-19 07:43:06', 24),
(62, 'deserve', '2015-04-19 07:43:06', 24),
(63, 'appalling', '2015-04-19 07:43:06', 24),
(64, 'destitute', '2015-04-19 07:43:06', 24),
(65, 'downtrodden', '2015-04-19 07:43:06', 24),
(66, 'dreadful', '2015-04-19 07:43:06', 24),
(67, 'perilous', '2015-04-19 07:43:06', 24),
(68, 'subjugate', '2015-04-19 07:43:06', 24),
(69, 'hull', '2015-04-19 07:43:06', 25),
(70, 'husk', '2015-04-19 07:43:06', 25),
(71, 'fortnight', '2015-04-19 07:43:06', 25),
(72, 'haggle', '2015-04-19 07:43:06', 25),
(73, 'strain', '2015-04-19 07:43:06', 25),
(74, 'drought', '2015-04-19 07:43:06', 25),
(75, 'obnoxious', '2015-04-19 07:43:06', 26),
(76, 'leapfrog', '2015-04-19 07:43:06', 27),
(77, 'kjhkjhkj', '2014-11-23 14:10:57', 0),
(78, 'iffy', '2015-04-19 07:43:06', 28),
(79, 'tertiary', '2015-04-19 07:43:06', 28),
(80, 'unwieldy', '2015-04-19 07:43:06', 28),
(81, 'cram', '2015-04-19 07:43:06', 29);

-- --------------------------------------------------------

--
-- Структура таблицы `sc__content`
--

CREATE TABLE IF NOT EXISTS `sc__content` (
  `Name` char(20) NOT NULL,
  `ClassName` char(30) NOT NULL,
  `ParentName` char(20) DEFAULT NULL,
  `Size` int(10) DEFAULT NULL,
  `DisplayChild` char(20) DEFAULT NULL,
  `FilteredByChild` char(20) DEFAULT NULL,
  `FiltersOutput` int(10) DEFAULT NULL,
  `Ord` int(10) DEFAULT NULL,
  `Chldrn` int(11) NOT NULL,
  PRIMARY KEY (`Name`),
  KEY `Order` (`Ord`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `sc__content`
--

INSERT INTO `sc__content` (`Name`, `ClassName`, `ParentName`, `Size`, `DisplayChild`, `FilteredByChild`, `FiltersOutput`, `Ord`, `Chldrn`) VALUES
('Theme', 'StringContent', 'Themes', 30, NULL, NULL, NULL, 11, 0),
('Themes', 'AttributeTable', 'Topics', NULL, 'Theme', NULL, 1, 10, 1),
('Topic', 'StringContent', 'Topics', 30, NULL, NULL, NULL, 9, 0),
('Topics', 'AttributeTable', 'Words', NULL, 'Topic', 'Themes', 1, 8, 2),
('Example', 'StringContent', 'Definitions', 100, NULL, NULL, NULL, 7, 0),
('Definition', 'StringContent', 'Definitions', 100, NULL, NULL, NULL, 6, 0),
('Part', 'StringContent', 'Parts', 10, NULL, NULL, NULL, 5, 0),
('Parts', 'AttributeTable', 'Definitions', NULL, 'Part', NULL, NULL, 4, 1),
('Definitions', 'MultiDetailTable', 'Words', NULL, NULL, NULL, NULL, 3, 3),
('Word', 'StringContent', 'Words', 20, NULL, NULL, NULL, 2, 0),
('Words', 'MasterTable', NULL, NULL, NULL, NULL, NULL, 1, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `wl_state`
--

CREATE TABLE IF NOT EXISTS `wl_state` (
  `part_id` tinyint(4) NOT NULL,
  `topic_id` int(10) NOT NULL,
  `theme_id` int(10) NOT NULL,
  `meaning_id` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `wl_state`
--

INSERT INTO `wl_state` (`part_id`, `topic_id`, `theme_id`, `meaning_id`) VALUES
(0, 25, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
