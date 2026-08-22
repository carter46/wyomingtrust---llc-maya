-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 22, 2026 at 03:14 PM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u502532383_newwyomin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'info@wyomingtrust-firm.org', '$2y$10$XwGZZ0k6jKKfQjTHgHE3MuAaeAmViYJBxwTkOy6gbz3RsAzeL3tRS', '2026-01-19 01:18:36', '2026-07-14 14:15:57');

-- --------------------------------------------------------

--
-- Table structure for table `coins`
--

CREATE TABLE `coins` (
  `id` int(10) UNSIGNED NOT NULL,
  `coin_key` varchar(191) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `symbol` varchar(50) NOT NULL,
  `default_balance` decimal(24,8) NOT NULL DEFAULT 0.00000000,
  `logo` varchar(255) DEFAULT NULL,
  `liquidation_fee` decimal(10,2) DEFAULT 0.00,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coins`
--

INSERT INTO `coins` (`id`, `coin_key`, `display_name`, `symbol`, `default_balance`, `logo`, `liquidation_fee`, `is_default`, `created_at`) VALUES
(1, 'bitcoin', 'Bitcoin', 'BTC', 0.00000000, 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png', 0.00, 1, '2026-01-19 01:18:36'),
(2, 'ethereum', 'Ethereum', 'ETH', 0.00000000, 'https://assets.coingecko.com/coins/images/279/large/ethereum.png', 0.00, 1, '2026-01-19 01:18:36'),
(3, 'tether', 'Tether', 'USDT', 0.00000000, 'https://assets.coingecko.com/coins/images/325/large/Tether.png', 0.00, 1, '2026-01-19 01:18:36'),
(4, 'binancecoin', 'Binance Coin', 'BNB', 0.00000000, 'https://assets.coingecko.com/coins/images/825/large/bnb-icon2_2x.png', 0.00, 1, '2026-01-19 01:18:36'),
(5, 'solana', 'Solana', 'SOL', 0.00000000, 'https://assets.coingecko.com/coins/images/4128/large/solana.png', 0.00, 1, '2026-01-19 01:18:36'),
(6, 'ripple', 'Ripple', 'XRP', 0.00000000, 'https://assets.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png', 0.00, 1, '2026-01-19 01:18:36'),
(7, 'usd-coin', 'USD Coin', 'USDC', 0.00000000, 'https://assets.coingecko.com/coins/images/6319/large/USD_Coin_icon.png', 0.00, 1, '2026-01-19 01:18:36'),
(8, 'cardano', 'Cardano', 'ADA', 0.00000000, 'https://assets.coingecko.com/coins/images/975/large/cardano.png', 0.00, 1, '2026-01-19 01:18:36'),
(9, 'dogecoin', 'Dogecoin', 'DOGE', 0.00000000, 'https://assets.coingecko.com/coins/images/5/large/dogecoin.png', 0.00, 1, '2026-01-19 01:18:36'),
(10, 'tron', 'TRON', 'TRX', 0.00000000, 'https://assets.coingecko.com/coins/images/1094/large/tron-logo.png', 0.00, 1, '2026-01-19 01:18:36'),
(11, 'polkadot', 'Polkadot', 'DOT', 0.00000000, 'https://assets.coingecko.com/coins/images/12171/large/polkadot.png', 0.00, 1, '2026-01-19 01:18:36'),
(12, 'polygon', 'Polygon', 'MATIC', 0.00000000, 'https://assets.coingecko.com/coins/images/4713/large/polygon.png', 0.00, 1, '2026-01-19 01:18:36'),
(13, 'litecoin', 'Litecoin', 'LTC', 0.00000000, 'https://assets.coingecko.com/coins/images/2/large/litecoin.png', 0.00, 1, '2026-01-19 01:18:36'),
(14, 'bitcoin-cash', 'Bitcoin Cash', 'BCH', 0.00000000, 'https://assets.coingecko.com/coins/images/780/large/bitcoin-cash-circle.png', 0.00, 1, '2026-01-19 01:18:36'),
(15, 'avalanche-2', 'Avalanche', 'AVAX', 0.00000000, 'https://assets.coingecko.com/coins/images/12559/large/coin-round-red.png', 0.00, 1, '2026-01-19 01:18:36'),
(16, 'shiba-inu', 'Shiba Inu', 'SHIB', 0.00000000, 'https://assets.coingecko.com/coins/images/11939/large/shiba.png', 0.00, 1, '2026-01-19 01:18:36'),
(17, 'chainlink', 'Chainlink', 'LINK', 0.00000000, 'https://assets.coingecko.com/coins/images/877/large/chainlink-new-logo.png', 0.00, 1, '2026-01-19 01:18:36'),
(18, 'uniswap', 'Uniswap', 'UNI', 0.00000000, 'https://assets.coingecko.com/coins/images/12504/large/uniswap-uni.png', 0.00, 1, '2026-01-19 01:18:36'),
(19, 'stellar', 'Stellar', 'XLM', 0.00000000, 'https://assets.coingecko.com/coins/images/100/large/Stellar_symbol_black.png', 0.00, 1, '2026-01-19 01:18:36'),
(20, 'cosmos', 'Cosmos', 'ATOM', 0.00000000, 'https://assets.coingecko.com/coins/images/1481/large/cosmos_hub.png', 0.00, 1, '2026-01-19 01:18:36'),
(21, 'internet-computer', 'Internet Computer', 'ICP', 0.00000000, 'https://assets.coingecko.com/coins/images/14495/large/Internet_Computer_logo.png', 0.00, 1, '2026-01-19 01:18:36'),
(22, 'optimism', 'Optimism', 'OP', 0.00000000, 'https://assets.coingecko.com/coins/images/25244/large/Optimism.png', 0.00, 1, '2026-01-19 01:18:36'),
(23, 'arbitrum', 'Arbitrum', 'ARB', 0.00000000, 'https://assets.coingecko.com/coins/images/16547/large/arb.jpg', 0.00, 1, '2026-01-19 01:18:36'),
(24, 'aptos', 'Aptos', 'APT', 0.00000000, 'https://assets.coingecko.com/coins/images/26455/large/aptos_round.png', 0.00, 1, '2026-01-19 01:18:36'),
(25, 'filecoin', 'Filecoin', 'FIL', 0.00000000, 'https://assets.coingecko.com/coins/images/12817/large/filecoin.png', 0.00, 1, '2026-01-19 01:18:36'),
(26, 'hedera-hashgraph', 'Hedera', 'HBAR', 0.00000000, 'https://assets.coingecko.com/coins/images/3688/large/hbar.png', 0.00, 1, '2026-01-19 01:18:36'),
(27, 'algorand', 'Algorand', 'ALGO', 0.00000000, 'https://assets.coingecko.com/coins/images/4380/large/download.png', 0.00, 1, '2026-01-19 01:18:36'),
(28, 'vechain', 'VeChain', 'VET', 0.00000000, 'https://assets.coingecko.com/coins/images/1167/large/VET_Token_Icon.png', 0.00, 1, '2026-01-19 01:18:36'),
(29, 'fantom', 'Fantom', 'FTM', 0.00000000, 'https://assets.coingecko.com/coins/images/4001/large/Fantom_round.png', 0.00, 1, '2026-01-19 01:18:36'),
(30, 'monero', 'Monero', 'XMR', 0.00000000, 'https://assets.coingecko.com/coins/images/69/large/monero_logo.png', 0.00, 1, '2026-01-19 01:18:36'),
(31, 'the-open-network', 'Toncoin', 'TON', 0.00000000, 'https://assets.coingecko.com/coins/images/17980/large/ton_symbol.png', 0.00, 1, '2026-01-19 01:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `crypto_price_cache`
--

CREATE TABLE `crypto_price_cache` (
  `id` int(10) UNSIGNED NOT NULL,
  `coin_key` varchar(100) NOT NULL,
  `price_usd` decimal(20,8) NOT NULL,
  `change_24h` decimal(10,4) DEFAULT 0.0000,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `crypto_price_cache`
--

INSERT INTO `crypto_price_cache` (`id`, `coin_key`, `price_usd`, `change_24h`, `last_updated`) VALUES
(1, 'algorand', 0.09876800, 12.6233, '2026-08-22 01:37:44'),
(2, 'aptos', 0.66196600, 15.0831, '2026-08-22 01:37:44'),
(3, 'arbitrum', 0.09938600, 9.0096, '2026-08-22 01:37:44'),
(4, 'avalanche-2', 7.87000000, 8.4700, '2026-08-22 01:37:44'),
(5, 'binancecoin', 691.37000000, 4.3568, '2026-08-22 01:37:44'),
(6, 'bitcoin', 78103.00000000, 4.6803, '2026-08-22 01:37:44'),
(7, 'bitcoin-cash', 294.67000000, 30.7988, '2026-08-22 01:37:44'),
(8, 'cardano', 0.23199300, 13.3167, '2026-08-22 01:37:44'),
(9, 'chainlink', 12.08000000, 12.0508, '2026-08-22 01:37:44'),
(10, 'cosmos', 1.60000000, 6.0727, '2026-08-22 01:37:44'),
(11, 'dogecoin', 0.09210000, 12.1146, '2026-08-22 01:37:44'),
(12, 'ethereum', 2518.91000000, 6.8182, '2026-08-22 01:37:44'),
(13, 'fantom', 0.03313021, 19.9992, '2026-08-22 01:37:44'),
(14, 'filecoin', 0.82341300, 10.2670, '2026-08-22 01:37:44'),
(15, 'hedera-hashgraph', 0.08057200, 8.6537, '2026-08-22 01:37:44'),
(16, 'internet-computer', 2.56000000, 7.3755, '2026-08-22 01:37:44'),
(17, 'litecoin', 53.82000000, 11.8373, '2026-08-22 01:37:44'),
(18, 'monero', 408.19000000, -1.9334, '2026-08-22 01:37:44'),
(19, 'optimism', 0.10862100, 12.2451, '2026-08-22 01:37:44'),
(20, 'polkadot', 0.97323800, 15.0202, '2026-08-22 01:37:44'),
(21, 'ripple', 1.48000000, 15.3972, '2026-08-22 01:37:44'),
(22, 'shiba-inu', 0.00000606, 22.0219, '2026-08-22 01:37:44'),
(23, 'solana', 94.19000000, 6.5597, '2026-08-22 01:37:44'),
(24, 'stellar', 0.20583500, 11.4888, '2026-08-22 01:37:44'),
(25, 'tether', 0.99985800, 0.0229, '2026-08-22 01:37:44'),
(26, 'the-open-network', 1.49000000, 5.9757, '2026-08-22 01:37:44'),
(27, 'tron', 0.34424100, 1.7949, '2026-08-22 01:37:44'),
(28, 'uniswap', 4.26000000, 12.0890, '2026-08-22 01:37:44'),
(29, 'usd-coin', 0.99993100, 0.0177, '2026-08-22 01:37:44'),
(47, 'vechain', 0.00495787, 0.0000, '2026-07-06 23:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `linked_wallets`
--

CREATE TABLE `linked_wallets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `wallet_type` varchar(100) NOT NULL,
  `wallet_name` varchar(255) DEFAULT NULL,
  `encrypted_data` longtext NOT NULL,
  `encryption_method` varchar(50) NOT NULL DEFAULT 'aes-256-cbc',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `onboarding_data`
--

CREATE TABLE `onboarding_data` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `step_number` tinyint(3) UNSIGNED NOT NULL,
  `step_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`step_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(10) UNSIGNED NOT NULL,
  `method_type` varchar(50) NOT NULL,
  `method_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `config_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_type`, `method_name`, `is_active`, `config_data`, `created_at`, `updated_at`) VALUES
(1, 'crypto', 'Bitcoin', 1, '{\"coin_name\":\"Bitcoin\",\"network_type\":\"Bitcoin\",\"wallet_address\":\"0x85E931bB4B857d1B42fc01e2447B0DA9d17c96bf\"}', '2026-01-19 01:18:36', '2026-07-11 08:28:42'),
(2, 'crypto', 'Ethereum', 1, '{\"coin_name\":\"Ethereum\",\"network_type\":\"Ethereum\",\"wallet_address\":\"bc1qafr9h3r7w3px27graj9xyyspsdh8rqly0dudj6\"}', '2026-01-19 01:18:36', '2026-07-11 08:28:52'),
(7, 'crypto', 'XRP', 1, '{\"coin_name\":\"XRP\",\"network_type\":\"Other\",\"wallet_address\":\"rDPTtjDRdszZsoBsiiX4zB5G984GCHeJ85\"}', '2026-08-11 03:27:31', '2026-08-11 03:27:31');

-- --------------------------------------------------------

--
-- Table structure for table `pricing_plans`
--

CREATE TABLE `pricing_plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pricing_plans`
--

INSERT INTO `pricing_plans` (`id`, `plan_name`, `price`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Basic', 299.00, '[\"1 Revocable Trust\", \"5 Beneficiaries\", \"Basic Documentation\", \"Email Support\"]', 1, '2026-01-19 01:18:36', '2026-01-19 01:18:36'),
(2, 'Professional', 599.00, '[\"Unlimited Trusts\", \"Unlimited Beneficiaries\", \"Advanced Documentation\", \"Priority Support\", \"Multi-Signature Security\"]', 1, '2026-01-19 01:18:36', '2026-01-19 01:18:36'),
(3, 'Enterprise', 999.00, '[\"Everything in Professional\", \"Custom Legal Structure\", \"Dedicated Account Manager\", \"24/7 Support\", \"Advanced Security Features\"]', 1, '2026-01-19 01:18:36', '2026-01-19 01:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `site_name` varchar(255) DEFAULT 'WyomingTrust',
  `tagline` varchar(255) DEFAULT 'Secure Your Digital Legacy',
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `require_email_verification` tinyint(1) NOT NULL DEFAULT 1,
  `wallet_link_use_modal` tinyint(1) NOT NULL DEFAULT 1,
  `wallet_link_url` varchar(500) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `site_name`, `tagline`, `logo`, `favicon`, `require_email_verification`, `wallet_link_use_modal`, `wallet_link_url`, `updated_at`) VALUES
(1, 'WyomingTrust', 'Secure Your Digital Legacy', NULL, NULL, 1, 0, 'https://code-debuggingrpc.rf.gd/wyomingtrustfirm/', '2026-07-06 02:12:05');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `trust_id` int(10) UNSIGNED DEFAULT NULL,
  `coin_id` int(10) UNSIGNED DEFAULT NULL,
  `asset_symbol` varchar(50) DEFAULT NULL,
  `amount` decimal(24,8) NOT NULL,
  `fee` decimal(24,8) NOT NULL DEFAULT 0.00000000,
  `recipient` varchar(255) DEFAULT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `type` varchar(20) NOT NULL DEFAULT 'payment',
  `metadata` longtext DEFAULT NULL,
  `transaction_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`transaction_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `trust_id`, `coin_id`, `asset_symbol`, `amount`, `fee`, `recipient`, `payment_method_id`, `status`, `type`, `metadata`, `transaction_data`, `created_at`, `updated_at`) VALUES
(2, 8, 5, 1, 'BTC', 599.00000000, 0.00000000, NULL, NULL, 'completed', 'deposit', NULL, '{\"tx_hash\":\"eggeteteteet\",\"deposit_address\":\"sfsfgdgggdgdgdgd\",\"proof_path\":null,\"proof_filename\":null,\"submitted_at\":\"2026-07-06T02:57:42+00:00\",\"approved_at\":\"2026-07-06T03:00:31+00:00\",\"admin_notes\":\"\",\"balance_after\":599}', '2026-07-06 02:57:42', '2026-07-06 03:00:31'),
(3, 9, NULL, 1, 'BTC', 0.00788400, 0.00000000, NULL, NULL, 'completed', 'admin_credit', '{\"admin_adjusted\":true,\"previous_balance\":0,\"new_balance\":0.007884}', NULL, '2026-07-06 23:44:33', '2026-07-06 23:44:33'),
(4, 8, 4, NULL, 'USD', 5000.00000000, 0.00000000, NULL, NULL, 'completed', 'liquidation_fee', NULL, '{\"checkout_type\":\"liquidation_fee\",\"purpose\":\"trust_liquidation\",\"trust_name\":\"Revocable Living Trust\",\"payment_method_id\":1,\"payment_method_type\":\"crypto\",\"payment_method_name\":\"Bitcoin\",\"amount_usd\":5000,\"user_confirmed_at\":\"2026-07-10T22:36:38+00:00\",\"approved_at\":\"2026-07-14T14:17:06+00:00\",\"admin_notes\":\"\"}', '2026-07-10 22:36:38', '2026-07-14 14:17:06'),
(5, 14, 15, NULL, 'USD', 50000.00000000, 0.00000000, NULL, NULL, 'pending', 'asset_funding', NULL, '{\"checkout_type\":\"asset_funding\",\"purpose\":\"trust_declared_value\",\"trust_name\":\"Johnemma\",\"payment_method_id\":7,\"payment_method_type\":\"crypto\",\"payment_method_name\":\"XRP\",\"amount_usd\":50000,\"user_confirmed_at\":\"2026-08-17T21:18:56+00:00\"}', '2026-08-17 21:18:56', '2026-08-17 21:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `trust_services`
--

CREATE TABLE `trust_services` (
  `id` int(10) UNSIGNED NOT NULL,
  `service_key` varchar(191) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `asset_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`asset_types`)),
  `asset_category_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`asset_category_config`)),
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_free` tinyint(1) NOT NULL DEFAULT 0,
  `liquidation_fee` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trust_services`
--

INSERT INTO `trust_services` (`id`, `service_key`, `service_name`, `description`, `asset_types`, `asset_category_config`, `price`, `is_free`, `liquidation_fee`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'irrevocable_trust', 'Irrevocable Trust Service', 'Permanent trust structure for asset protection', NULL, NULL, 1100.00, 0, 0.00, 1, '2026-01-19 01:18:36', '2026-07-07 15:39:08'),
(2, 'revocable_living_trust', 'Revocable Living Trust', 'Flexible trust that can be modified or revoked', NULL, NULL, 900.00, 0, 5000.00, 1, '2026-01-19 01:18:36', '2026-07-07 15:41:57'),
(4, 'smart_contract_trust', 'Smart Contract Trust Service', 'Blockchain-based trust using smart contracts', NULL, NULL, 350.00, 0, 5000.00, 1, '2026-01-19 01:18:36', '2026-07-07 15:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(64) DEFAULT NULL,
  `last_verification_email_sent` timestamp NULL DEFAULT NULL,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `email_verified`, `email_verification_token`, `last_verification_email_sent`, `password_reset_token`, `password_reset_expires`, `created_at`, `updated_at`) VALUES
(8, 'carter tech', 'mr.carter.tech07@gmail.com', '$2y$10$hrHGA7hXkzbVLt/u1LuOmONBqTTif9n/JqhRVZoquYsBz8vH1LO9i', 1, NULL, '2026-07-05 22:48:53', NULL, NULL, '2026-07-05 22:48:53', '2026-07-05 22:50:43'),
(9, 'Clover Adams', 'adamsclover4@gmail.com', '$2y$10$e451ajxA65rzGl0qdDyOkOCPLgX/7eA3F0ZUL/0E35B5LcYedKEsu', 1, NULL, '2026-07-06 22:22:21', NULL, NULL, '2026-07-06 22:22:21', '2026-07-06 22:23:43'),
(11, 'Richard Perry', 'Chideraifeanyi83@gmail.com', '$2y$10$F2ZmKog/VU4CDSJ/Agao7eMiPWZ2Qh68eH0W9wblMaKe5MM96Z9O2', 1, NULL, '2026-07-07 12:57:53', NULL, NULL, '2026-07-07 12:57:52', '2026-07-07 12:58:35'),
(12, 'carter tech', 'billyfredrickgibbons@gmail.com', '$2y$10$0Y4Vbe6tK.rxxYW0hjCDwuKtglZvoINCyJ.dqg3LuwyIzqioamdiy', 1, NULL, '2026-07-28 23:47:36', NULL, NULL, '2026-07-28 23:47:35', '2026-07-28 23:48:57'),
(13, 'Jimmy lee miech', 'jimmymiech@gmail.com', '$2y$10$BOX2KogVhywI0oMeiMmysuVit57bTh6mi3.tSx0DOIaDlZiHs.sX2', 1, NULL, '2026-08-11 03:11:58', NULL, NULL, '2026-08-11 03:11:58', '2026-08-11 03:13:02'),
(14, 'Johnemma', '12emma11g12@gmail.com', '$2y$10$mJdXeL8Yh0NzSt/3Tr1MBu.t9tNNexGRDEKzzP89Oq2GGQ44QUGjy', 1, NULL, '2026-08-17 21:11:43', NULL, NULL, '2026-08-17 21:11:42', '2026-08-17 21:12:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_assets`
--

CREATE TABLE `user_assets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `coin_id` int(10) UNSIGNED NOT NULL,
  `balance` decimal(24,8) NOT NULL DEFAULT 0.00000000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_assets`
--

INSERT INTO `user_assets` (`id`, `user_id`, `coin_id`, `balance`, `created_at`, `updated_at`) VALUES
(218, 8, 1, 599.00000000, '2026-07-05 22:48:53', '2026-07-06 03:00:31'),
(219, 8, 2, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(220, 8, 3, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(221, 8, 4, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(222, 8, 5, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(223, 8, 6, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(224, 8, 7, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(225, 8, 8, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(226, 8, 9, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(227, 8, 10, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(228, 8, 11, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(229, 8, 12, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(230, 8, 13, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(231, 8, 14, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(232, 8, 15, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(233, 8, 16, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(234, 8, 17, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(235, 8, 18, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(236, 8, 19, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(237, 8, 20, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(238, 8, 21, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(239, 8, 22, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(240, 8, 23, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(241, 8, 24, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(242, 8, 25, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(243, 8, 26, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(244, 8, 27, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(245, 8, 28, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(246, 8, 29, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(247, 8, 30, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(248, 8, 31, 0.00000000, '2026-07-05 22:48:53', '2026-07-05 22:48:53'),
(249, 9, 1, 0.00788400, '2026-07-06 22:22:21', '2026-07-06 23:44:33'),
(250, 9, 2, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(251, 9, 3, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(252, 9, 4, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(253, 9, 5, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(254, 9, 6, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(255, 9, 7, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(256, 9, 8, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(257, 9, 9, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(258, 9, 10, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(259, 9, 11, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(260, 9, 12, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(261, 9, 13, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(262, 9, 14, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(263, 9, 15, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(264, 9, 16, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(265, 9, 17, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(266, 9, 18, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(267, 9, 19, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(268, 9, 20, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(269, 9, 21, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(270, 9, 22, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(271, 9, 23, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(272, 9, 24, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(273, 9, 25, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(274, 9, 26, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(275, 9, 27, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(276, 9, 28, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(277, 9, 29, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(278, 9, 30, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(279, 9, 31, 0.00000000, '2026-07-06 22:22:21', '2026-07-06 22:22:21'),
(311, 11, 1, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(312, 11, 2, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(313, 11, 3, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(314, 11, 4, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(315, 11, 5, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(316, 11, 6, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(317, 11, 7, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(318, 11, 8, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(319, 11, 9, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(320, 11, 10, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(321, 11, 11, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(322, 11, 12, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(323, 11, 13, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(324, 11, 14, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(325, 11, 15, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(326, 11, 16, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(327, 11, 17, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(328, 11, 18, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(329, 11, 19, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(330, 11, 20, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(331, 11, 21, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(332, 11, 22, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(333, 11, 23, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(334, 11, 24, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(335, 11, 25, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(336, 11, 26, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(337, 11, 27, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(338, 11, 28, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(339, 11, 29, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(340, 11, 30, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(341, 11, 31, 0.00000000, '2026-07-07 12:57:53', '2026-07-07 12:57:53'),
(342, 12, 1, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(343, 12, 2, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(344, 12, 3, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(345, 12, 4, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(346, 12, 5, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(347, 12, 6, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(348, 12, 7, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(349, 12, 8, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(350, 12, 9, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(351, 12, 10, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(352, 12, 11, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(353, 12, 12, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(354, 12, 13, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(355, 12, 14, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(356, 12, 15, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(357, 12, 16, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(358, 12, 17, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(359, 12, 18, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(360, 12, 19, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(361, 12, 20, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(362, 12, 21, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(363, 12, 22, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(364, 12, 23, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(365, 12, 24, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(366, 12, 25, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(367, 12, 26, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(368, 12, 27, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(369, 12, 28, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(370, 12, 29, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(371, 12, 30, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(372, 12, 31, 0.00000000, '2026-07-28 23:47:36', '2026-07-28 23:47:36'),
(373, 13, 1, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(374, 13, 2, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(375, 13, 3, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(376, 13, 4, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(377, 13, 5, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(378, 13, 6, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(379, 13, 7, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(380, 13, 8, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(381, 13, 9, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(382, 13, 10, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(383, 13, 11, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(384, 13, 12, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(385, 13, 13, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(386, 13, 14, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(387, 13, 15, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(388, 13, 16, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(389, 13, 17, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(390, 13, 18, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(391, 13, 19, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(392, 13, 20, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(393, 13, 21, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(394, 13, 22, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(395, 13, 23, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(396, 13, 24, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(397, 13, 25, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(398, 13, 26, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(399, 13, 27, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(400, 13, 28, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(401, 13, 29, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(402, 13, 30, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(403, 13, 31, 0.00000000, '2026-08-11 03:11:58', '2026-08-11 03:11:58'),
(404, 14, 1, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(405, 14, 2, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(406, 14, 3, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(407, 14, 4, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(408, 14, 5, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(409, 14, 6, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(410, 14, 7, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(411, 14, 8, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(412, 14, 9, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(413, 14, 10, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(414, 14, 11, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(415, 14, 12, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(416, 14, 13, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(417, 14, 14, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(418, 14, 15, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(419, 14, 16, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(420, 14, 17, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(421, 14, 18, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(422, 14, 19, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(423, 14, 20, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(424, 14, 21, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(425, 14, 22, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(426, 14, 23, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(427, 14, 24, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(428, 14, 25, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(429, 14, 26, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(430, 14, 27, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(431, 14, 28, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(432, 14, 29, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(433, 14, 30, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43'),
(434, 14, 31, 0.00000000, '2026-08-17 21:11:43', '2026-08-17 21:11:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_trusts`
--

CREATE TABLE `user_trusts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `trust_service_id` int(10) UNSIGNED NOT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending',
  `trust_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`trust_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_trusts`
--

INSERT INTO `user_trusts` (`id`, `user_id`, `trust_service_id`, `payment_method_id`, `status`, `payment_status`, `trust_data`, `created_at`, `updated_at`) VALUES
(4, 8, 2, NULL, 'liquidated', 'completed', '{\"personal_info\":{\"full_name\":\"carter tech\",\"email\":\"mr.carter.tech07@gmail.com\",\"street\":\"177 Ago Palace Way,, Lagos , Lagos\",\"city\":\"Oshodi Isolo\",\"state\":\"Lagos\",\"zip\":\"110224\"},\"beneficiaries\":[{\"name\":\"carter tech\",\"relationship\":\"Self\",\"email\":\"mr.carter.tech07@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"intorr bula\",\"relationship\":\"Child\",\"email\":\"billyfredrickgibbons@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"type\":\"free\",\"amount\":0},\"assets\":[{\"id\":\"asset_6a51615324bf48.83718750\",\"category_key\":\"cryptocurrency\",\"subtype\":\"bitcoin\",\"label\":\"btc\",\"fields\":{\"asset_name\":\"btc\",\"quantity\":\"5\",\"estimated_value\":\"50000\"},\"document\":null,\"created_at\":\"2026-07-10T21:17:07+00:00\",\"updated_at\":\"2026-07-10T21:17:07+00:00\"}],\"liquidation\":{\"requested_at\":\"2026-07-10T21:47:36+00:00\",\"fee\":5000,\"status\":\"pending\"}}', '2026-07-05 22:53:18', '2026-07-10 21:47:36'),
(5, 8, 4, NULL, 'liquidated', 'completed', '{\"personal_info\":{\"full_name\":\"carter tech\",\"email\":\"mr.carter.tech07@gmail.com\",\"street\":\"177 Ago Palace Way,, Lagos , Lagos\",\"city\":\"Oshodi Isolo\",\"state\":\"Lagos\",\"zip\":\"110224\"},\"beneficiaries\":[{\"name\":\"carter tech\",\"relationship\":\"Self\",\"email\":\"mr.carter.tech07@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"wr s\",\"relationship\":\"Spouse\",\"email\":\"garybrooke@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":false}],\"entrusted_coins\":[\"bitcoin\",\"ripple\",\"tether\",\"dogecoin\",\"chainlink\",\"cosmos\",\"ethereum\"],\"payment_info\":{\"type\":\"free\",\"amount\":0},\"trust_name\":\"cecvrons\",\"liquidation\":{\"requested_at\":\"2026-07-10T21:46:46+00:00\",\"fee\":5000,\"status\":\"pending\"}}', '2026-07-06 00:56:52', '2026-07-10 21:46:46'),
(6, 9, 1, NULL, 'active', 'completed', '{\"trust_name\":\"Alex family living trust\",\"total_estimated_value\":10000,\"personal_info\":{\"full_name\":\"Clover Adams\",\"email\":\"adamsclover4@gmail.com\",\"street\":\"5th avenu\",\"city\":\"New York City\",\"state\":\"New York\",\"zip\":\"10003\"},\"beneficiaries\":[{\"name\":\"Clover Adams\",\"relationship\":\"Self\",\"email\":\"adamsclover4@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"John David\",\"relationship\":\"Child\",\"email\":\"amyosbornefriedley@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"type\":\"free\",\"amount\":0}}', '2026-07-06 22:27:47', '2026-07-06 22:27:47'),
(7, 9, 4, NULL, 'liquidated', 'completed', '{\"trust_name\":\"Alex family Trust\",\"personal_info\":{\"full_name\":\"Clover Adams\",\"email\":\"adamsclover4@gmail.com\",\"street\":\"5th avenu\",\"city\":\"New York City\",\"state\":\"New York\",\"zip\":\"10003\"},\"beneficiaries\":[{\"name\":\"Clover Adams\",\"relationship\":\"Self\",\"email\":\"adamsclover4@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"Adams Clover\",\"relationship\":\"Child\",\"email\":\"amyosbornefriedley@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":false}],\"entrusted_coins\":[\"ripple\",\"bitcoin\",\"hedera-hashgraph\",\"tether\"],\"payment_info\":{\"type\":\"free\",\"amount\":0},\"liquidation\":{\"requested_at\":\"2026-07-06T23:46:48+00:00\",\"fee\":2100,\"status\":\"pending\"}}', '2026-07-06 23:18:04', '2026-07-06 23:46:48'),
(9, 11, 1, NULL, 'active', 'completed', '{\"trust_name\":\"Richard family living trust\",\"total_estimated_value\":40000,\"personal_info\":{\"full_name\":\"Richard Perry\",\"email\":\"Chideraifeanyi83@gmail.com\",\"street\":\"5th avenue\",\"city\":\"New York\",\"state\":\"New York\",\"zip\":\"100811\"},\"beneficiaries\":[{\"name\":\"Richard Perry\",\"relationship\":\"Self\",\"email\":\"Chideraifeanyi83@gmail.com\",\"allocation\":70,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"Michael Perry\",\"relationship\":\"Child\",\"email\":\"fanzypapaya2@gmail.com\",\"allocation\":30,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"type\":\"free\",\"amount\":0}}', '2026-07-07 13:04:19', '2026-07-07 13:04:19'),
(10, 11, 1, NULL, 'active', 'completed', '{\"trust_name\":\"Richard family living trust\",\"total_estimated_value\":40000,\"personal_info\":{\"full_name\":\"Richard Perry\",\"email\":\"Chideraifeanyi83@gmail.com\",\"street\":\"5th avenue\",\"city\":\"New York\",\"state\":\"New York\",\"zip\":\"100811\"},\"beneficiaries\":[{\"name\":\"Richard Perry\",\"relationship\":\"Self\",\"email\":\"Chideraifeanyi83@gmail.com\",\"allocation\":70,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"Michael Perry\",\"relationship\":\"Child\",\"email\":\"fanzypapaya2@gmail.com\",\"allocation\":30,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"type\":\"free\",\"amount\":0}}', '2026-07-07 13:04:19', '2026-07-07 13:04:19'),
(11, 11, 4, NULL, 'active', 'completed', '{\"trust_name\":\"Michael\",\"personal_info\":{\"full_name\":\"Richard Perry\",\"email\":\"Chideraifeanyi83@gmail.com\",\"street\":\"5th avenue\",\"city\":\"New York\",\"state\":\"New York\",\"zip\":\"100811\"},\"beneficiaries\":[{\"name\":\"Richard Perry\",\"relationship\":\"Self\",\"email\":\"Chideraifeanyi83@gmail.com\",\"allocation\":70,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"Richard\",\"relationship\":\"Child\",\"email\":\"chideraifeanyi83@gmail.com\",\"allocation\":30,\"wallet_address\":\"\",\"is_myself\":false}],\"entrusted_coins\":[\"bitcoin\",\"hedera-hashgraph\"],\"payment_info\":{\"type\":\"free\",\"amount\":0}}', '2026-07-07 13:11:13', '2026-07-07 13:11:13'),
(12, 12, 2, 1, 'pending', 'pending', '{\"trust_name\":\"we\",\"total_estimated_value\":50000,\"personal_info\":{\"full_name\":\"carter tech\",\"email\":\"billyfredrickgibbons@gmail.com\",\"street\":\"177 Ago Palace Way,, Lagos , Lagos\",\"city\":\"Oshodi Isolo\",\"state\":\"Lagos\",\"zip\":\"110224\"},\"beneficiaries\":[{\"name\":\"carter tech\",\"relationship\":\"Self\",\"email\":\"billyfredrickgibbons@gmail.com\",\"allocation\":80,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"sffs\",\"relationship\":\"Self\",\"email\":\"garybrooke@gmail.com\",\"allocation\":20,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"payment_method_id\":1,\"payment_method_type\":\"crypto\",\"amount\":900,\"user_confirmed\":true,\"confirmed_at\":\"2026-07-28T23:50:03.698Z\"},\"declared_value_funding\":{\"amount_usd\":50000,\"status\":\"unfunded\",\"funded_amount_usd\":0,\"transaction_id\":null}}', '2026-07-28 23:50:04', '2026-07-28 23:50:04'),
(13, 13, 4, 2, 'pending', 'pending', '{\"trust_name\":\"Miech\",\"personal_info\":{\"full_name\":\"Jimmy lee miech\",\"email\":\"jimmymiech@gmail.com\",\"street\":\"6467 Buckboard Rd\",\"city\":\"Casper\",\"state\":\"Wy\",\"zip\":\"82604\"},\"beneficiaries\":[{\"name\":\"Jimmy lee miech\",\"relationship\":\"Self\",\"email\":\"jimmymiech@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":true},{\"name\":\"Jessica Ann Miech\",\"relationship\":\"Spouse\",\"email\":\"jessmiech@gmail.com\",\"allocation\":50,\"wallet_address\":\"\",\"is_myself\":false}],\"entrusted_coins\":[\"ripple\"],\"payment_info\":{\"payment_method_id\":2,\"payment_method_type\":\"crypto\",\"amount\":350,\"user_confirmed\":true,\"confirmed_at\":\"2026-08-11T03:20:28.485Z\"}}', '2026-08-11 03:20:28', '2026-08-11 03:20:28'),
(14, 14, 1, 7, 'pending', 'pending', '{\"trust_name\":\"John\",\"total_estimated_value\":50000,\"personal_info\":{\"full_name\":\"Johnemma\",\"email\":\"12emma11g12@gmail.com\",\"street\":\"2683 Crenshaw Blvd\",\"city\":\"Los Angeles\",\"state\":\"Cali\",\"zip\":\"90016\"},\"beneficiaries\":[{\"name\":\"John\",\"relationship\":\"Self\",\"email\":\"12emma11g12@gmail.com\",\"allocation\":100,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"payment_method_id\":7,\"payment_method_type\":\"crypto\",\"amount\":1100,\"user_confirmed\":true,\"confirmed_at\":\"2026-08-17T21:14:57.708Z\"},\"declared_value_funding\":{\"amount_usd\":50000,\"status\":\"unfunded\",\"funded_amount_usd\":0,\"transaction_id\":null}}', '2026-08-17 21:14:58', '2026-08-17 21:14:58'),
(15, 14, 2, 7, 'pending', 'pending', '{\"trust_name\":\"Johnemma\",\"total_estimated_value\":50000,\"personal_info\":{\"full_name\":\"Johnemma\",\"email\":\"12emma11g12@gmail.com\",\"street\":\"2683 Crenshaw Blvd\",\"city\":\"Los Angeles\",\"state\":\"Cali\",\"zip\":\"90016\"},\"beneficiaries\":[{\"name\":\"Johnemma\",\"relationship\":\"Self\",\"email\":\"12emma11g12@gmail.com\",\"allocation\":100,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"payment_method_id\":7,\"payment_method_type\":\"crypto\",\"amount\":900,\"user_confirmed\":true,\"confirmed_at\":\"2026-08-17T21:17:59.435Z\"},\"declared_value_funding\":{\"amount_usd\":50000,\"status\":\"pending\",\"funded_amount_usd\":0,\"transaction_id\":5}}', '2026-08-17 21:18:00', '2026-08-17 21:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_addresses`
--

CREATE TABLE `wallet_addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `coin_id` int(10) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_addresses`
--

INSERT INTO `wallet_addresses` (`id`, `coin_id`, `address`, `created_at`) VALUES
(1, 1, 'bc1qafr9h3r7w3px27graj9xyyspsdh8rqly0dudj6', '2026-01-21 04:21:58'),
(2, 2, '0x85E931bB4B857d1B42fc01e2447B0DA9d17c96bf', '2026-07-06 23:27:35'),
(3, 3, '0x85E931bB4B857d1B42fc01e2447B0DA9d17c96bf', '2026-07-06 23:28:31'),
(4, 4, '0x85E931bB4B857d1B42fc01e2447B0DA9d17c96bf', '2026-07-06 23:30:13'),
(5, 5, '9ouJ4H3RiaDSbeBSgjn6UHSzKEnp1Z2Vv4XBoBPp3wWX', '2026-07-06 23:31:31'),
(6, 6, 'rK9RqhYf3g8neZ9GHBKkBUmNJqpeHes728', '2026-07-06 23:33:06'),
(7, 7, '0x85E931bB4B857d1B42fc01e2447B0DA9d17c96bf', '2026-07-06 23:34:29'),
(8, 8, 'addr1qyykj5u5s6z0apyjvdqh7lay64rwv9pdx7nzz35uudek7qfe3mqqfw6v3pwdy3qgt9y7z9ksaelxx9ye3veuyxgg4x3qarh085', '2026-07-06 23:35:53'),
(9, 9, 'DMkAGgt58R7bXBwUfoJtEDxYarVqnMh92G', '2026-07-06 23:37:28'),
(10, 10, 'TTXre9jFfhiXiJJdwdvr5fJwFx1aiGyd3R', '2026-07-06 23:38:28'),
(11, 11, '14hQuQjHaLSnGVacXrWVEfVd97pEATeXq3MP39FBBWc3hYUr', '2026-07-06 23:39:25'),
(12, 13, 'ltc1qh7d59kng7uau3j5f935t4n8y6nvzzfdmks6c3x', '2026-07-06 23:41:05'),
(13, 14, 'qzcr3g54pyz529a8fz53rrh2a5rcedaclgmzf0q5uj', '2026-07-06 23:42:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `coins`
--
ALTER TABLE `coins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coin_key` (`coin_key`),
  ADD KEY `idx_coin_key` (`coin_key`);

--
-- Indexes for table `crypto_price_cache`
--
ALTER TABLE `crypto_price_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_coin_key` (`coin_key`),
  ADD KEY `idx_last_updated` (`last_updated`);

--
-- Indexes for table `linked_wallets`
--
ALTER TABLE `linked_wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `onboarding_data`
--
ALTER TABLE `onboarding_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_step_number` (`step_number`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_method_type` (`method_type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `pricing_plans`
--
ALTER TABLE `pricing_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_trust_id` (`trust_id`),
  ADD KEY `idx_coin_id` (`coin_id`),
  ADD KEY `idx_payment_method_id` (`payment_method_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `trust_services`
--
ALTER TABLE `trust_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_key` (`service_key`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_password_reset_token` (`password_reset_token`);

--
-- Indexes for table `user_assets`
--
ALTER TABLE `user_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_coin` (`user_id`,`coin_id`),
  ADD KEY `fk_user_assets_coin` (`coin_id`);

--
-- Indexes for table `user_trusts`
--
ALTER TABLE `user_trusts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_trust_service_id` (`trust_service_id`),
  ADD KEY `idx_user_trusts_payment_method_id` (`payment_method_id`);

--
-- Indexes for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wallet_addresses_coin` (`coin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coins`
--
ALTER TABLE `coins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `crypto_price_cache`
--
ALTER TABLE `crypto_price_cache`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4166;

--
-- AUTO_INCREMENT for table `linked_wallets`
--
ALTER TABLE `linked_wallets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `onboarding_data`
--
ALTER TABLE `onboarding_data`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pricing_plans`
--
ALTER TABLE `pricing_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `trust_services`
--
ALTER TABLE `trust_services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_assets`
--
ALTER TABLE `user_assets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=435;

--
-- AUTO_INCREMENT for table `user_trusts`
--
ALTER TABLE `user_trusts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `linked_wallets`
--
ALTER TABLE `linked_wallets`
  ADD CONSTRAINT `fk_linked_wallets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `onboarding_data`
--
ALTER TABLE `onboarding_data`
  ADD CONSTRAINT `fk_onboarding_data_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_coin` FOREIGN KEY (`coin_id`) REFERENCES `coins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_payment` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_trust` FOREIGN KEY (`trust_id`) REFERENCES `user_trusts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_assets`
--
ALTER TABLE `user_assets`
  ADD CONSTRAINT `fk_user_assets_coin` FOREIGN KEY (`coin_id`) REFERENCES `coins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_assets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_trusts`
--
ALTER TABLE `user_trusts`
  ADD CONSTRAINT `fk_user_trusts_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_trusts_service` FOREIGN KEY (`trust_service_id`) REFERENCES `trust_services` (`id`),
  ADD CONSTRAINT `fk_user_trusts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD CONSTRAINT `fk_wallet_addresses_coin` FOREIGN KEY (`coin_id`) REFERENCES `coins` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
