-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysqlsvr80.world4you.com
-- Erstellungszeit: 02. Nov 2024 um 12:44
-- Server-Version: 8.0.39
-- PHP-Version: 8.1.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `4158319db1`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `config`
--

CREATE TABLE `config` (
  `id` int NOT NULL,
  `config_key` varchar(50) NOT NULL,
  `config_value` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `config`
--

INSERT INTO `config` (`id`, `config_key`, `config_value`, `description`) VALUES
(1, 'tokensForFullProgress', '10000', 'Tokens required for full progress on bars'),
(2, 'decayRatePerHour', '10', 'Decay rate for each bar per hour');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `donations`
--

CREATE TABLE `donations` (
  `donation_id` int NOT NULL,
  `signature` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `donation_amount` decimal(18,8) NOT NULL,
  `action` varchar(10) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `donations`
--

INSERT INTO `donations` (`donation_id`, `signature`, `user_id`, `donation_amount`, `action`, `timestamp`) VALUES
(20, '5zmTyTka2WnUR7zmizVDuWT4N6s8yhQWWEM6XGf5xcJxoce9ZHU3easgpKBUoXkpQ1xSNJpWa3NKN22vdWGMcnVC', 16, 1858.00000000, 'clean', '2024-10-31 14:48:32'),
(21, '4L4gRg556WscwcDJj6XXU56outpcBJgt2EV6iMxA5FtN7jBtuZnuDdqRcy559ez3ykzWgRr7NrSrbc2sTwYC5DvZ', 16, 10.00000000, 'clean', '2024-11-01 15:40:06'),
(23, '4AefiqmfRa2C9b9LrpSE6tyJDgLdWz5NV3QAstGnS8sYCvsGWvgqmwz6sJGnNnrBZvfCCxtA1BMVRpKHPeyYatoL', 16, 10.00000000, 'clean', '2024-11-01 15:53:02'),
(24, 'iMnSMr7sTRDfL5AXN9LEDnvFmitAVYXScVK4ARv5QFFjttzJv5kw6YjGKj6q15m2kUQumYrkUBDWVTZWTcxQ72M', 16, 10.00000000, 'clean', '2024-11-01 15:57:29'),
(25, '44s8GtwoMd9iWZTnfDrvtZjiPgZeT9J5gYoVVdr3Dedkg8YRDrgJUJPb2SDcLfsEVEXnnzeUfYXTNwa3Te2VHAGw', 16, 10.00000000, 'clean', '2024-11-01 16:25:23'),
(26, '3MLF7vVHxLBKZ5AicDEdgyLHfvtnWY7roHt521TFRSyi3WXSg1Xn5wHogXpjttWYArsUFyBQBL4YKze9NwVfUtyG', 16, 10.00000000, 'feed', '2024-11-01 16:28:43'),
(27, 'S5Y6VvFum1UxvcizYhiTX27X2aEaHGWhDKXPHgA2ueKWBUzCg6vn1Bq7Hkm7rh9q4qY2WU4jwm9gmnUhSakuyeU', 16, 10.00000000, 'feed', '2024-11-01 16:37:25'),
(28, '5RSRtPh2Cr2t7yGVKYm7tto28FLPuhmhvmhWWffaqmzvL1YhJTtHNT14jWjaGhxQ984ggSKQezmb6RJrNMWS8DY9', 16, 10.00000000, 'clean', '2024-11-01 18:00:26'),
(29, '4wQrUfdRgpJCwSHeuHLw4dn9K4jCPwZQ1CjGj3ZF17d5Yfcjnmtwckdy6tSQpnUt7UTfo7miPEdQYvU81gNkgGAz', 16, 10.00000000, 'clean', '2024-11-01 18:16:25'),
(30, '47jaHsw5SKYhFpA7ufEt8YN8DZce6CdrQ3FqVoWVQRrDkaAGcLcgia5NaLHWfPncwjUAT6gCqxtvNYXNQX3Z6hQc', 16, 1000.00000000, 'play', '2024-11-01 19:21:41'),
(31, '6578DBKzuS4pmyaRV1K921tFBA5ukipAZsN62Roc4yAbuRGj6WBobAaCSFoQEugVqwC9toAnrJpKiBvLnq9SznN2', 16, 1000.00000000, 'clean', '2024-11-01 22:38:25');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nonces`
--

CREATE TABLE `nonces` (
  `wallet_address` varchar(44) NOT NULL,
  `nonce` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `nonces`
--

INSERT INTO `nonces` (`wallet_address`, `nonce`, `created_at`) VALUES
('6hiwrkdRNmFT9tN9XkuxWgYCDc4kDkv8EDxqHJ93esLH', 'ca8449ed60e152767cb66e2323666e73fffd972f104d409c7558e0792b0918bc', '2024-11-01 22:37:20'),
('8m2yEHreG41EuaQEqRtjyURcnXRqLf7pRnc21dPxaG4r', '372d2dcbe3f78508e3de4edd8733ebd7', '2024-11-01 19:20:57');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(12) DEFAULT NULL,
  `wallet_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`user_id`, `username`, `wallet_address`) VALUES
(16, 'Occid3re', '6hiwrkdRNmFT9tN9XkuxWgYCDc4kDkv8EDxqHJ93esLH'),
(17, 'AIBot', '8m2yEHreG41EuaQEqRtjyURcnXRqLf7pRnc21dPxaG4r');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indizes für die Tabelle `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD UNIQUE KEY `signature` (`signature`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `nonces`
--
ALTER TABLE `nonces`
  ADD PRIMARY KEY (`wallet_address`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `wallet_address` (`wallet_address`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `config`
--
ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
