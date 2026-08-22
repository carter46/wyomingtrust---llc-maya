-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 21, 2026 at 12:43 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u502532383_wyoming`
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
(1, 'admin@wyomingtrust.com', '$2y$10$ZFWgpxuHCITGa10XAuPBrOumSCkkMY3pZI0/ANGnw1gAgy5wAKYRm', '2026-01-19 01:18:36', '2026-01-19 22:31:13');

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
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coins`
--

INSERT INTO `coins` (`id`, `coin_key`, `display_name`, `symbol`, `default_balance`, `logo`, `is_default`, `created_at`) VALUES
(1, 'bitcoin', 'Bitcoin', 'BTC', 0.00000000, 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png', 1, '2026-01-19 01:18:36'),
(2, 'ethereum', 'Ethereum', 'ETH', 0.00000000, 'https://assets.coingecko.com/coins/images/279/large/ethereum.png', 1, '2026-01-19 01:18:36'),
(3, 'tether', 'Tether', 'USDT', 0.00000000, 'https://assets.coingecko.com/coins/images/325/large/Tether.png', 1, '2026-01-19 01:18:36'),
(4, 'binancecoin', 'Binance Coin', 'BNB', 0.00000000, 'https://assets.coingecko.com/coins/images/825/large/bnb-icon2_2x.png', 1, '2026-01-19 01:18:36'),
(5, 'solana', 'Solana', 'SOL', 0.00000000, 'https://assets.coingecko.com/coins/images/4128/large/solana.png', 1, '2026-01-19 01:18:36'),
(6, 'ripple', 'Ripple', 'XRP', 0.00000000, 'https://assets.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png', 1, '2026-01-19 01:18:36'),
(7, 'usd-coin', 'USD Coin', 'USDC', 0.00000000, 'https://assets.coingecko.com/coins/images/6319/large/USD_Coin_icon.png', 1, '2026-01-19 01:18:36'),
(8, 'cardano', 'Cardano', 'ADA', 0.00000000, 'https://assets.coingecko.com/coins/images/975/large/cardano.png', 1, '2026-01-19 01:18:36'),
(9, 'dogecoin', 'Dogecoin', 'DOGE', 0.00000000, 'https://assets.coingecko.com/coins/images/5/large/dogecoin.png', 1, '2026-01-19 01:18:36'),
(10, 'tron', 'TRON', 'TRX', 0.00000000, 'https://assets.coingecko.com/coins/images/1094/large/tron-logo.png', 1, '2026-01-19 01:18:36'),
(11, 'polkadot', 'Polkadot', 'DOT', 0.00000000, 'https://assets.coingecko.com/coins/images/12171/large/polkadot.png', 1, '2026-01-19 01:18:36'),
(12, 'polygon', 'Polygon', 'MATIC', 0.00000000, 'https://assets.coingecko.com/coins/images/4713/large/polygon.png', 1, '2026-01-19 01:18:36'),
(13, 'litecoin', 'Litecoin', 'LTC', 0.00000000, 'https://assets.coingecko.com/coins/images/2/large/litecoin.png', 1, '2026-01-19 01:18:36'),
(14, 'bitcoin-cash', 'Bitcoin Cash', 'BCH', 0.00000000, 'https://assets.coingecko.com/coins/images/780/large/bitcoin-cash-circle.png', 1, '2026-01-19 01:18:36'),
(15, 'avalanche-2', 'Avalanche', 'AVAX', 0.00000000, 'https://assets.coingecko.com/coins/images/12559/large/coin-round-red.png', 1, '2026-01-19 01:18:36'),
(16, 'shiba-inu', 'Shiba Inu', 'SHIB', 0.00000000, 'https://assets.coingecko.com/coins/images/11939/large/shiba.png', 1, '2026-01-19 01:18:36'),
(17, 'chainlink', 'Chainlink', 'LINK', 0.00000000, 'https://assets.coingecko.com/coins/images/877/large/chainlink-new-logo.png', 1, '2026-01-19 01:18:36'),
(18, 'uniswap', 'Uniswap', 'UNI', 0.00000000, 'https://assets.coingecko.com/coins/images/12504/large/uniswap-uni.png', 1, '2026-01-19 01:18:36'),
(19, 'stellar', 'Stellar', 'XLM', 0.00000000, 'https://assets.coingecko.com/coins/images/100/large/Stellar_symbol_black.png', 1, '2026-01-19 01:18:36'),
(20, 'cosmos', 'Cosmos', 'ATOM', 0.00000000, 'https://assets.coingecko.com/coins/images/1481/large/cosmos_hub.png', 1, '2026-01-19 01:18:36'),
(21, 'internet-computer', 'Internet Computer', 'ICP', 0.00000000, 'https://assets.coingecko.com/coins/images/14495/large/Internet_Computer_logo.png', 1, '2026-01-19 01:18:36'),
(22, 'optimism', 'Optimism', 'OP', 0.00000000, 'https://assets.coingecko.com/coins/images/25244/large/Optimism.png', 1, '2026-01-19 01:18:36'),
(23, 'arbitrum', 'Arbitrum', 'ARB', 0.00000000, 'https://assets.coingecko.com/coins/images/16547/large/arb.jpg', 1, '2026-01-19 01:18:36'),
(24, 'aptos', 'Aptos', 'APT', 0.00000000, 'https://assets.coingecko.com/coins/images/26455/large/aptos_round.png', 1, '2026-01-19 01:18:36'),
(25, 'filecoin', 'Filecoin', 'FIL', 0.00000000, 'https://assets.coingecko.com/coins/images/12817/large/filecoin.png', 1, '2026-01-19 01:18:36'),
(26, 'hedera-hashgraph', 'Hedera', 'HBAR', 0.00000000, 'https://assets.coingecko.com/coins/images/3688/large/hbar.png', 1, '2026-01-19 01:18:36'),
(27, 'algorand', 'Algorand', 'ALGO', 0.00000000, 'https://assets.coingecko.com/coins/images/4380/large/download.png', 1, '2026-01-19 01:18:36'),
(28, 'vechain', 'VeChain', 'VET', 0.00000000, 'https://assets.coingecko.com/coins/images/1167/large/VET_Token_Icon.png', 1, '2026-01-19 01:18:36'),
(29, 'fantom', 'Fantom', 'FTM', 0.00000000, 'https://assets.coingecko.com/coins/images/4001/large/Fantom_round.png', 1, '2026-01-19 01:18:36'),
(30, 'monero', 'Monero', 'XMR', 0.00000000, 'https://assets.coingecko.com/coins/images/69/large/monero_logo.png', 1, '2026-01-19 01:18:36'),
(31, 'the-open-network', 'Toncoin', 'TON', 0.00000000, 'https://assets.coingecko.com/coins/images/17980/large/ton_symbol.png', 1, '2026-01-19 01:18:36');

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

--
-- Dumping data for table `linked_wallets`
--

INSERT INTO `linked_wallets` (`id`, `user_id`, `wallet_type`, `wallet_name`, `encrypted_data`, `encryption_method`, `created_at`, `updated_at`) VALUES
(2, 6, 'metamask', 'MetaMask', 'sBY6rdKGVlhYG7pTfys87k9YY2ZjZXNkamtMbnFqc0MwUzVmQ3gwV3FrRDYzVlZPajY2VkJ6SUlib0Fia0l2VkN4aS9YMVlmUXhLL2RETUlXelI2SzBOVmpsS2Z1NEdKSm1ZdHF3PT0=', 'aes-256-cbc', '2026-01-21 00:34:14', '2026-01-21 00:34:14');

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
(1, 'crypto', 'Bitcoin', 1, '{\"coin_name\":\"Bitcoin\",\"network_type\":\"Bitcoin\",\"wallet_address\":\"0xb308d62953fc5ED11FAf7B47d671422dE28519d9\"}', '2026-01-19 01:18:36', '2026-01-20 00:53:34'),
(2, 'crypto', 'Ethereum', 1, '{\"coin_name\":\"Ethereum\",\"network_type\":\"Ethereum\",\"wallet_address\":\"bc1qalwxcycakh32lrz45vesqg46ushlmv0t6trct0\"}', '2026-01-19 01:18:36', '2026-01-20 00:54:01'),
(6, 'bank_transfer', 'simon bliss Bank Transfer', 1, '{\"bank_name\":\"simon bliss\",\"account_name\":\"wellsfarg\",\"account_number\":\"35535553563\",\"routing_number\":\"wrwr\",\"swift_code\":\"ABNGNGLAXXX\",\"additional_details\":\"\"}', '2026-01-20 00:54:28', '2026-01-20 00:54:28');

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
(1, 'WyomingTrust', 'Secure Your Digital Legacy', NULL, NULL, 1, 1, NULL, '2026-01-19 01:18:36');

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

-- --------------------------------------------------------

--
-- Table structure for table `trust_services`
--

CREATE TABLE `trust_services` (
  `id` int(10) UNSIGNED NOT NULL,
  `service_key` varchar(191) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_free` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trust_services`
--

INSERT INTO `trust_services` (`id`, `service_key`, `service_name`, `description`, `price`, `is_free`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'irrevocable_trust', 'Irrevocable Trust Service', 'Permanent trust structure for asset protection', 0.00, 1, 1, '2026-01-19 01:18:36', '2026-01-19 01:18:36'),
(2, 'revocable_living_trust', 'Revocable Living Trust', 'Flexible trust that can be modified or revoked', 0.00, 1, 1, '2026-01-19 01:18:36', '2026-01-19 01:18:36'),
(3, 'crypto_asset_trust', 'Crypto Asset Trust Service', 'Specialized trust for cryptocurrency and digital assets', 399.00, 0, 1, '2026-01-19 01:18:36', '2026-01-19 01:18:36'),
(4, 'smart_contract_trust', 'Smart Contract Trust Service', 'Blockchain-based trust using smart contracts', 499.00, 0, 1, '2026-01-19 01:18:36', '2026-01-19 01:18:36');

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
(6, 'carter tech', 'mr.carter.tech07@gmail.com', '$2y$10$N7k9CAZBcdfeF0CiQkljieSB14Z.nAmbfG6cFgQDdAc7hXdvOnjNW', 1, NULL, '2026-01-20 13:28:48', NULL, NULL, '2026-01-20 13:28:47', '2026-01-20 13:29:01');

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
(156, 6, 1, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(157, 6, 2, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(158, 6, 3, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(159, 6, 4, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(160, 6, 5, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(161, 6, 6, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(162, 6, 7, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(163, 6, 8, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(164, 6, 9, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(165, 6, 10, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(166, 6, 11, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(167, 6, 12, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(168, 6, 13, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(169, 6, 14, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(170, 6, 15, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(171, 6, 16, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(172, 6, 17, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(173, 6, 18, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(174, 6, 19, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(175, 6, 20, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(176, 6, 21, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(177, 6, 22, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(178, 6, 23, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(179, 6, 24, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(180, 6, 25, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(181, 6, 26, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(182, 6, 27, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(183, 6, 28, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(184, 6, 29, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(185, 6, 30, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48'),
(186, 6, 31, 0.00000000, '2026-01-20 13:28:48', '2026-01-20 13:28:48');

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
(2, 6, 2, NULL, 'active', 'completed', '{\"personal_info\":{\"full_name\":\"carter tech\",\"email\":\"mr.carter.tech07@gmail.com\",\"street\":\"177 Ago Palace Way,, Lagos , Lagos\",\"city\":\"Oshodi Isolo\",\"state\":\"Lagos\",\"zip\":\"110224\"},\"beneficiaries\":[{\"name\":\"carter tech\",\"relationship\":\"Self\",\"email\":\"mr.carter.tech07@gmail.com\",\"allocation\":60,\"wallet_address\":\"cbbcbddfg\",\"is_myself\":true},{\"name\":\"billy\",\"relationship\":\"Parent\",\"email\":\"billyfredrickgibbons@gmail.com\",\"allocation\":40,\"wallet_address\":\"\",\"is_myself\":false}],\"payment_info\":{\"type\":\"free\",\"amount\":0}}', '2026-01-20 13:30:07', '2026-01-20 13:30:07');

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
  ADD UNIQUE KEY `service_key` (`service_key`),
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
-- Indexes for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wallet_addresses_coin` (`coin_id`);

--
-- Indexes for table `user_trusts`
--
ALTER TABLE `user_trusts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_trust_service_id` (`trust_service_id`),
  ADD KEY `idx_user_trusts_payment_method_id` (`payment_method_id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pricing_plans`
--
ALTER TABLE `pricing_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trust_services`
--
ALTER TABLE `trust_services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_assets`
--
ALTER TABLE `user_assets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_trusts`
--
ALTER TABLE `user_trusts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD CONSTRAINT `fk_wallet_addresses_coin` FOREIGN KEY (`coin_id`) REFERENCES `coins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_trusts`
--
ALTER TABLE `user_trusts`
  ADD CONSTRAINT `fk_user_trusts_service` FOREIGN KEY (`trust_service_id`) REFERENCES `trust_services` (`id`),
  ADD CONSTRAINT `fk_user_trusts_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_trusts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
