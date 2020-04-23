-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 10. Okt 2019 um 14:43
-- Server-Version: 5.7.27-0ubuntu0.18.04.1
-- PHP-Version: 7.2.19-0ubuntu0.18.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `masterplan`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Absence`
--

CREATE TABLE `Absence` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `absent_type_id` int(11) NOT NULL,
  `submitted` date DEFAULT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `start_time` text,
  `end_time` text,
  `comment` text NOT NULL,
  `approved1` bit(1) NOT NULL DEFAULT b'0',
  `approved2` bit(1) NOT NULL DEFAULT b'0',
  `approved1_by_user_id` int(11) DEFAULT NULL,
  `approved2_by_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `AbsentType`
--

CREATE TABLE `AbsentType` (
  `id` int(11) NOT NULL,
  `shortname` text NOT NULL,
  `title` text NOT NULL,
  `color` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Holiday`
--

CREATE TABLE `Holiday` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `day` date NOT NULL,
  `service_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `PlannedService`
--

CREATE TABLE `PlannedService` (
  `id` int(11) NOT NULL,
  `day` date NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `icsmail_sent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `PlannedServiceFile`
--

CREATE TABLE `PlannedServiceFile` (
  `id` int(11) NOT NULL,
  `day` text NOT NULL,
  `service_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `file` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `PlannedServiceNote`
--

CREATE TABLE `PlannedServiceNote` (
  `id` int(11) NOT NULL,
  `day` text NOT NULL,
  `service_id` int(11) NOT NULL,
  `note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `PlannedServiceResource`
--

CREATE TABLE `PlannedServiceResource` (
  `id` int(11) NOT NULL,
  `day` text NOT NULL,
  `service_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ReleasedPlan`
--

CREATE TABLE `ReleasedPlan` (
  `id` int(11) NOT NULL,
  `roster_id` int(11) NOT NULL,
  `day` date NOT NULL,
  `note` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Resource`
--

CREATE TABLE `Resource` (
  `id` int(11) NOT NULL,
  `type` text NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `icon` text NOT NULL,
  `color` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Role`
--

CREATE TABLE `Role` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `max_hours_per_day` int(11) NOT NULL DEFAULT '-1',
  `max_services_per_week` int(11) NOT NULL DEFAULT '-1',
  `max_hours_per_week` int(11) NOT NULL DEFAULT '-1',
  `max_hours_per_month` int(11) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Roster`
--

CREATE TABLE `Roster` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `autoplan_logic` int(11) NOT NULL DEFAULT '0',
  `ignore_working_hours` bit(1) NOT NULL DEFAULT b'0',
  `icsmail_sender_name` text NOT NULL,
  `icsmail_sender_address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Service`
--

CREATE TABLE `Service` (
  `id` int(11) NOT NULL,
  `roster_id` int(11) NOT NULL,
  `shortname` text NOT NULL,
  `title` text NOT NULL,
  `location` text NOT NULL,
  `employees` int(11) NOT NULL,
  `start` text NOT NULL,
  `end` text NOT NULL,
  `date_start` text NOT NULL,
  `date_end` text NOT NULL,
  `color` text NOT NULL,
  `wd1` bit(1) NOT NULL DEFAULT b'0',
  `wd2` bit(1) NOT NULL DEFAULT b'0',
  `wd3` bit(1) NOT NULL DEFAULT b'0',
  `wd4` bit(1) NOT NULL DEFAULT b'0',
  `wd5` bit(1) NOT NULL DEFAULT b'0',
  `wd6` bit(1) NOT NULL DEFAULT b'0',
  `wd7` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Setting`
--

CREATE TABLE `Setting` (
  `setting` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `SwapService`
--

CREATE TABLE `SwapService` (
  `id` int(11) NOT NULL,
  `planned_service_id` int(11) NOT NULL,
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `superadmin` int(11) NOT NULL,
  `login` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `fullname` text NOT NULL,
  `email` text,
  `phone` text,
  `mobile` text,
  `birthday` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `id_no` text,
  `description` text,
  `password` text,
  `ldap` bit(1) NOT NULL DEFAULT b'0',
  `locked` bit(1) NOT NULL DEFAULT b'0',
  `max_hours_per_day` int(11) NOT NULL DEFAULT '0',
  `max_services_per_week` int(11) NOT NULL DEFAULT '0',
  `max_hours_per_week` int(11) NOT NULL DEFAULT '0',
  `max_hours_per_month` int(11) NOT NULL DEFAULT '0',
  `color` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserConstraint`
--

CREATE TABLE `UserConstraint` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `wd1` bit(1) NOT NULL DEFAULT b'1',
  `wd2` bit(1) NOT NULL DEFAULT b'1',
  `wd3` bit(1) NOT NULL DEFAULT b'1',
  `wd4` bit(1) NOT NULL DEFAULT b'1',
  `wd5` bit(1) NOT NULL DEFAULT b'1',
  `wd6` bit(1) NOT NULL DEFAULT b'1',
  `wd7` bit(1) NOT NULL DEFAULT b'1',
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserToRole`
--

CREATE TABLE `UserToRole` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserToRoster`
--

CREATE TABLE `UserToRoster` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `roster_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `UserToRosterAdmin`
--

CREATE TABLE `UserToRosterAdmin` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `roster_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Absence`
--
ALTER TABLE `Absence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `absent_type_id` (`absent_type_id`),
  ADD KEY `fk_approve1user_absence` (`approved1_by_user_id`),
  ADD KEY `fk_approve2user_absence` (`approved2_by_user_id`);

--
-- Indizes für die Tabelle `AbsentType`
--
ALTER TABLE `AbsentType`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Holiday`
--
ALTER TABLE `Holiday`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service_10` (`service_id`);

--
-- Indizes für die Tabelle `PlannedService`
--
ALTER TABLE `PlannedService`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `PlannedServiceFile`
--
ALTER TABLE `PlannedServiceFile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service4` (`service_id`);

--
-- Indizes für die Tabelle `PlannedServiceNote`
--
ALTER TABLE `PlannedServiceNote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service3` (`service_id`);

--
-- Indizes für die Tabelle `PlannedServiceResource`
--
ALTER TABLE `PlannedServiceResource`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service5` (`service_id`),
  ADD KEY `fk_resource` (`resource_id`);

--
-- Indizes für die Tabelle `ReleasedPlan`
--
ALTER TABLE `ReleasedPlan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roster_id` (`roster_id`);

--
-- Indizes für die Tabelle `Resource`
--
ALTER TABLE `Resource`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Role`
--
ALTER TABLE `Role`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Roster`
--
ALTER TABLE `Roster`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Service`
--
ALTER TABLE `Service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roster_id` (`roster_id`);

--
-- Indizes für die Tabelle `Setting`
--
ALTER TABLE `Setting`
  ADD PRIMARY KEY (`setting`);

--
-- Indizes für die Tabelle `SwapService`
--
ALTER TABLE `SwapService`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `planned_service_id_2` (`planned_service_id`),
  ADD KEY `planned_service_id` (`planned_service_id`);

--
-- Indizes für die Tabelle `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `UserConstraint`
--
ALTER TABLE `UserConstraint`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `UserToRole`
--
ALTER TABLE `UserToRole`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_role` (`role_id`),
  ADD KEY `fk_user4` (`user_id`);

--
-- Indizes für die Tabelle `UserToRoster`
--
ALTER TABLE `UserToRoster`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `roster_id` (`roster_id`);

--
-- Indizes für die Tabelle `UserToRosterAdmin`
--
ALTER TABLE `UserToRosterAdmin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `roster_id` (`roster_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `Absence`
--
ALTER TABLE `Absence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `AbsentType`
--
ALTER TABLE `AbsentType`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `Holiday`
--
ALTER TABLE `Holiday`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `PlannedService`
--
ALTER TABLE `PlannedService`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `PlannedServiceFile`
--
ALTER TABLE `PlannedServiceFile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `PlannedServiceNote`
--
ALTER TABLE `PlannedServiceNote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `PlannedServiceResource`
--
ALTER TABLE `PlannedServiceResource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `ReleasedPlan`
--
ALTER TABLE `ReleasedPlan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `Resource`
--
ALTER TABLE `Resource`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `Role`
--
ALTER TABLE `Role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `Roster`
--
ALTER TABLE `Roster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `Service`
--
ALTER TABLE `Service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `SwapService`
--
ALTER TABLE `SwapService`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `UserConstraint`
--
ALTER TABLE `UserConstraint`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `UserToRole`
--
ALTER TABLE `UserToRole`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `UserToRoster`
--
ALTER TABLE `UserToRoster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT für Tabelle `UserToRosterAdmin`
--
ALTER TABLE `UserToRosterAdmin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `Absence`
--
ALTER TABLE `Absence`
  ADD CONSTRAINT `fk_approve1user_absence` FOREIGN KEY (`approved1_by_user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_approve2user_absence` FOREIGN KEY (`approved2_by_user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_type_absence` FOREIGN KEY (`absent_type_id`) REFERENCES `AbsentType` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_absence` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `Holiday`
--
ALTER TABLE `Holiday`
  ADD CONSTRAINT `fk_service_10` FOREIGN KEY (`service_id`) REFERENCES `Service` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `PlannedService`
--
ALTER TABLE `PlannedService`
  ADD CONSTRAINT `fk_service2` FOREIGN KEY (`service_id`) REFERENCES `Service` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user3` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `PlannedServiceFile`
--
ALTER TABLE `PlannedServiceFile`
  ADD CONSTRAINT `fk_service4` FOREIGN KEY (`service_id`) REFERENCES `Service` (`id`);

--
-- Constraints der Tabelle `PlannedServiceNote`
--
ALTER TABLE `PlannedServiceNote`
  ADD CONSTRAINT `fk_service3` FOREIGN KEY (`service_id`) REFERENCES `Service` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `PlannedServiceResource`
--
ALTER TABLE `PlannedServiceResource`
  ADD CONSTRAINT `fk_resource` FOREIGN KEY (`resource_id`) REFERENCES `Resource` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service5` FOREIGN KEY (`service_id`) REFERENCES `Service` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `ReleasedPlan`
--
ALTER TABLE `ReleasedPlan`
  ADD CONSTRAINT `fk_roster_releasedplan` FOREIGN KEY (`roster_id`) REFERENCES `Roster` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `Service`
--
ALTER TABLE `Service`
  ADD CONSTRAINT `fk_roster2` FOREIGN KEY (`roster_id`) REFERENCES `Roster` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `SwapService`
--
ALTER TABLE `SwapService`
  ADD CONSTRAINT `fk_planned_service_swap` FOREIGN KEY (`planned_service_id`) REFERENCES `PlannedService` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `UserConstraint`
--
ALTER TABLE `UserConstraint`
  ADD CONSTRAINT `fk_service` FOREIGN KEY (`service_id`) REFERENCES `Service` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `UserToRole`
--
ALTER TABLE `UserToRole`
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`role_id`) REFERENCES `Role` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user4` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `UserToRoster`
--
ALTER TABLE `UserToRoster`
  ADD CONSTRAINT `fk_roster` FOREIGN KEY (`roster_id`) REFERENCES `Roster` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `UserToRosterAdmin`
--
ALTER TABLE `UserToRosterAdmin`
  ADD CONSTRAINT `fk_roster_rosteradmin` FOREIGN KEY (`roster_id`) REFERENCES `Roster` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_rosteradmin` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
