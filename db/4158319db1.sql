-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysqlsvr80.world4you.com
-- Erstellungszeit: 12. Nov 2024 um 11:35
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
);

--
-- Daten für Tabelle `config`
--

INSERT INTO `config` (`id`, `config_key`, `config_value`, `description`) VALUES
(1, 'tokensForFullProgress', '10000', 'Tokens required for full progress on bars'),
(2, 'decayRatePerHour', '10', 'Decay rate for each bar per hour'),
(3, 'tokenMintAddress', 'C7K4Tot6fnnNwhWpqw9H277QPcP56vHAEeXubRHDyCo9', 'Address of the token mint'),
(4, 'tokenDecimals', '\n6', 'Number of decimals for the token');

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
);

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
(31, '6578DBKzuS4pmyaRV1K921tFBA5ukipAZsN62Roc4yAbuRGj6WBobAaCSFoQEugVqwC9toAnrJpKiBvLnq9SznN2', 16, 1000.00000000, 'clean', '2024-11-01 22:38:25'),
(32, '5cJ3Jsf2d9SpH7C6823T5xKXHsU21kZJtS5X7ngUnxhP6BuKaZ15gPwreB1aNWVqqj3FGSvXdYSQbG97PDTRGNEF', 16, 12.00000000, 'clean', '2024-11-03 01:16:25'),
(33, '3DCqhdzGGr5sBXJbvmn614w8LywKmqCtPpdKFxuwSf8fSXv8LJey3onfYCumWDAqcyTBLKGufsHaZU6FaZJFAeVS', 16, 12.00000000, 'clean', '2024-11-03 01:49:09'),
(34, 'sig1', 18, 7021746.71000000, 'clean', '2024-10-12 01:01:11'),
(35, 'sig2', 19, 5645875.37000000, 'play', '2024-10-16 01:01:11'),
(36, 'sig3', 20, 4025914.44000000, 'feed', '2024-10-30 02:01:11'),
(37, 'sig4', 21, 5609498.70000000, 'clean', '2024-10-24 01:01:11'),
(38, 'sig5', 22, 327248.20000000, 'play', '2024-10-31 02:01:11'),
(39, 'sig6', 23, 5592230.16000000, 'feed', '2024-10-22 01:01:11'),
(40, 'sig7', 24, 3317142.00000000, 'clean', '2024-10-21 01:01:11'),
(41, 'sig8', 25, 2615629.32000000, 'play', '2024-10-06 01:01:11'),
(42, 'sig9', 26, 9762681.86000000, 'feed', '2024-11-03 02:01:11'),
(43, 'sig10', 27, 2020294.15000000, 'clean', '2024-10-07 01:01:11'),
(44, 'sig11', 28, 6352538.60000000, 'play', '2024-10-21 20:33:10'),
(45, 'sig12', 29, 4931007.62000000, 'feed', '2024-11-04 21:33:10'),
(46, 'sig13', 30, 6456300.12000000, 'clean', '2024-10-31 21:33:10'),
(47, 'sig14', 31, 8270636.44000000, 'play', '2024-10-15 20:33:10'),
(48, 'sig15', 32, 9035394.07000000, 'feed', '2024-10-21 20:33:10'),
(49, 'sig16', 33, 7236762.21000000, 'clean', '2024-10-31 21:33:10'),
(50, 'sig17', 34, 6139938.02000000, 'play', '2024-10-18 20:33:10'),
(51, 'sig18', 35, 1487965.22000000, 'feed', '2024-10-07 20:33:10'),
(52, 'sig19', 36, 2891899.77000000, 'clean', '2024-10-17 20:33:10'),
(53, 'sig20', 37, 1504411.48000000, 'play', '2024-10-07 20:33:10'),
(54, 'sig21', 18, 2466723.01000000, 'feed', '2024-09-28 20:33:10'),
(55, 'sig22', 19, 3268036.59000000, 'clean', '2024-10-18 20:33:10'),
(56, 'sig23', 20, 9832190.89000000, 'play', '2024-09-21 20:33:10'),
(57, 'sig24', 21, 7459300.42000000, 'feed', '2024-09-25 20:33:10'),
(58, 'sig25', 22, 3196992.79000000, 'clean', '2024-11-01 21:33:10'),
(59, 'sig26', 23, 3509161.93000000, 'play', '2024-09-26 20:33:10'),
(60, 'sig27', 24, 7841144.38000000, 'feed', '2024-10-17 20:33:10'),
(61, 'sig28', 25, 7419288.64000000, 'clean', '2024-11-02 21:33:10'),
(62, 'sig29', 26, 376187.51000000, 'play', '2024-11-02 21:33:10'),
(63, 'sig30', 27, 223967.70000000, 'feed', '2024-11-02 21:33:10'),
(64, 'sig31', 18, 5430388.45000000, 'clean', '2024-11-01 07:45:00'),
(65, 'sig32', 19, 199705.34000000, 'play', '2024-11-01 08:15:00'),
(66, 'sig33', 20, 4677361.67000000, 'feed', '2024-11-01 11:30:00'),
(67, 'sig34', 21, 2797692.64000000, 'clean', '2024-11-02 09:00:00'),
(68, 'sig35', 22, 9936378.51000000, 'play', '2024-11-02 13:20:00'),
(69, 'sig36', 23, 1318814.91000000, 'feed', '2024-11-03 08:50:00'),
(70, 'sig37', 24, 6744939.32000000, 'clean', '2024-11-03 10:40:00'),
(71, 'sig38', 25, 9778252.20000000, 'play', '2024-11-04 06:30:00'),
(72, 'sig39', 26, 8666443.32000000, 'feed', '2024-11-04 12:25:00'),
(73, 'sig40', 27, 3997460.32000000, 'clean', '2024-11-04 15:50:00'),
(74, 'sig41', 28, 3967971.94000000, 'play', '2024-11-05 09:10:00'),
(75, 'sig42', 29, 7837479.05000000, 'feed', '2024-11-05 11:45:00'),
(76, 'sig43', 30, 7293479.74000000, 'clean', '2024-11-06 14:30:00'),
(77, 'sig44', 31, 2954961.86000000, 'play', '2024-11-06 15:55:00'),
(78, 'sig45', 32, 2874370.38000000, 'feed', '2024-11-07 08:40:00'),
(79, 'sig46', 33, 5496966.64000000, 'clean', '2024-11-07 10:20:00'),
(80, 'sig47', 34, 8861722.38000000, 'play', '2024-11-07 12:15:00'),
(81, 'sig48', 35, 7827712.26000000, 'feed', '2024-11-08 13:05:00'),
(82, 'sig49', 36, 2553394.47000000, 'clean', '2024-11-08 14:40:00'),
(83, 'sig50', 37, 9253835.89000000, 'play', '2024-11-09 15:25:00'),
(84, '5ZmapJZhavXy526QBS63mk42qnUzGnfDCd7EFi3s3Wn5mRSwMduRCZGyXKjLzHfwrEWc5auvvtakbWD9azbrXbiu', 16, 1000.00000000, 'clean', '2024-11-10 23:31:26'),
(85, '4cXMw7DjqFxXpGGbaNrHe6diogtoBGiFtbM6H2VLQSNKzN3xiepeUJapaj6ZVi46VWKDS213YkJo4DXv9uWpEe8h', 16, 50.00000000, 'clean', '2024-11-11 19:11:01'),
(86, 'qoNxYMgDQCSFvwUAu6JZmpfSC8Uhf5F8W3ZAKVfDv19WJCuiMSM2tk4o9ws8d1eHPka32gR5Lc2VmPrt8W6Sz2m', 16, 100.00000000, 'clean', '2024-11-11 19:20:55'),
(87, 'eTA8S1ieXaVfVq9FBqp2huzdYajzfwKou13ZJ4kUWZgWn1Q9LpRR1BKdiGrFVZZBaRvECkRZ6bFgVAzp6SF3Fsp', 16, 1000.00000000, 'clean', '2024-11-11 22:15:33'),
(88, '2EvRVJjKBjQ1GaujMoJB1xrjNs6STLgj9YPwRiASM6TMUKezRqGixo2bAv9GZPFuBwbWLar7F8UXwNjxfRhwVwjt', 16, 1000.00000000, 'clean', '2024-11-11 22:16:49'),
(89, '4skL3CbGKxk3nAsZbiUHvhuzN6e7jrUTEozBQPCmmWRWwgduoLJeEg44LRqmPeC8mTCrvm3SK8CHuY2uudKKqheb', 16, 100.00000000, 'clean', '2024-11-11 22:26:12'),
(90, '3UAZbLHUnxz6y6b5m1RSLjo3eLCpD7aj5FQdSGKCCGy7pwoh2CszU7LbxqVnJTv57HPeaetoVGoKYQkUGRyf5fKQ', 16, 100.00000000, 'clean', '2024-11-11 22:43:02');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `failed_transactions`
--

CREATE TABLE `failed_transactions` (
  `id` int NOT NULL,
  `signature` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `state` enum('pending','success','failed') DEFAULT 'pending',
  `action` varchar(255) DEFAULT NULL,
  `user_id` int DEFAULT NULL
);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nonces`
--

CREATE TABLE `nonces` (
  `wallet_address` varchar(44) NOT NULL,
  `nonce` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
);

--
-- Daten für Tabelle `nonces`
--

INSERT INTO `nonces` (`wallet_address`, `nonce`, `created_at`) VALUES
('6hiwrkdRNmFT9tN9XkuxWgYCDc4kDkv8EDxqHJ93esLH', 'feaef216399fd8eae23574db3e8318c1', '2024-11-11 23:21:56');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(12) DEFAULT NULL,
  `wallet_address` varchar(255) NOT NULL
);

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`user_id`, `username`, `wallet_address`) VALUES
(16, 'Occid3re', '6hiwrkdRNmFT9tN9XkuxWgYCDc4kDkv8EDxqHJ93esLH'),
(17, 'AIBot', '8m2yEHreG41EuaQEqRtjyURcnXRqLf7pRnc21dPxaG4r'),
(18, 'Alice', 'wallet1abcDEF1234567890abcdefghijklmnopqrs'),
(19, 'Bob', 'wallet2abcDEF1234567890abcdefghijklmnopqrs'),
(20, 'Charlie', 'wallet3abcDEF1234567890abcdefghijklmnopqrs'),
(21, 'David', 'wallet4abcDEF1234567890abcdefghijklmnopqrs'),
(22, 'Eve', 'wallet5abcDEF1234567890abcdefghijklmnopqrs'),
(23, 'Frank', 'wallet6abcDEF1234567890abcdefghijklmnopqrs'),
(24, 'Grace', 'wallet7abcDEF1234567890abcdefghijklmnopqrs'),
(25, 'Hank', 'wallet8abcDEF1234567890abcdefghijklmnopqrs'),
(26, 'Ivy', 'wallet9abcDEF1234567890abcdefghijklmnopqrs'),
(27, 'Judy', 'wallet10abcDEF1234567890abcdefghijklmnopqrs'),
(28, 'Karen', 'wallet11abcDEF1234567890abcdefghijklmnopqrs'),
(29, 'Leo', 'wallet12abcDEF1234567890abcdefghijklmnopqrs'),
(30, 'Mona', 'wallet13abcDEF1234567890abcdefghijklmnopqrs'),
(31, 'Nick', 'wallet14abcDEF1234567890abcdefghijklmnopqrs'),
(32, 'Olive', 'wallet15abcDEF1234567890abcdefghijklmnopqrs'),
(33, 'Paul', 'wallet16abcDEF1234567890abcdefghijklmnopqrs'),
(34, 'Quincy', 'wallet17abcDEF1234567890abcdefghijklmnopqrs'),
(35, 'Rose', 'wallet18abcDEF1234567890abcdefghijklmnopqrs'),
(36, 'Steve', 'wallet19abcDEF1234567890abcdefghijklmnopqrs'),
(37, 'Tina', 'wallet20abcDEF1234567890abcdefghijklmnopqrs');

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
-- Indizes für die Tabelle `failed_transactions`
--
ALTER TABLE `failed_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `signature` (`signature`),
  ADD KEY `fk_user_id` (`user_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT für Tabelle `failed_transactions`
--
ALTER TABLE `failed_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `failed_transactions`
--
ALTER TABLE `failed_transactions`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
