SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

-- =====================================================
-- Schema
-- =====================================================

CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stocks` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `ticker` VARCHAR(10) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `sector` VARCHAR(100) NOT NULL DEFAULT '',
    `price` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    `dividend_per_share` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
    `dividend_yield` DECIMAL(8,6) NOT NULL DEFAULT 0.000000,
    `ex_dividend_date` DATE DEFAULT NULL,
    `pay_date` DATE DEFAULT NULL,
    `frequency` ENUM('monthly','quarterly','semi-annual','annual') NOT NULL DEFAULT 'quarterly',
    `last_fetched_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ticker` (`ticker`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `holdings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `stock_id` INT NOT NULL,
    `shares` DECIMAL(14,8) NOT NULL DEFAULT 0.00000000,
    `invested` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    `bought_on` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_stock_date` (`user_id`, `stock_id`, `bought_on`),
    KEY `fk_holdings_user` (`user_id`),
    KEY `fk_holdings_stock` (`stock_id`),
    CONSTRAINT `fk_holdings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_holdings_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `transactions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `stock_id` INT NOT NULL,
    `type` ENUM('buy','sell') NOT NULL DEFAULT 'buy',
    `shares` DECIMAL(14,8) NOT NULL DEFAULT 0.00000000,
    `price_per_share` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    `total_amount` DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
    `executed_at` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_transactions_user` (`user_id`),
    KEY `fk_transactions_stock` (`stock_id`),
    CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_transactions_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stock_payment_months` (
    `stock_id` INT NOT NULL,
    `month` TINYINT NOT NULL,
    PRIMARY KEY (`stock_id`, `month`),
    CONSTRAINT `fk_spm_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Seed data: users
-- =====================================================

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`) VALUES
(1, 'admin', 'admin@dividendflow.nl', '$2y$12$9RG45Hq043AqMkHARCYQUO0KbPm10IunaoNYouDzoR423NmAY3yDq', 1),
(2, 'user', 'user@dividendflow.nl', '$2y$12$9RG45Hq043AqMkHARCYQUO0KbPm10IunaoNYouDzoR423NmAY3yDq', 0);

-- =====================================================
-- Seed data: stocks (50 stocks)
-- Insertion order determines auto-increment IDs (1-50)
-- =====================================================

INSERT INTO `stocks` (`id`, `ticker`, `name`, `sector`, `price`, `dividend_per_share`, `dividend_yield`, `ex_dividend_date`, `pay_date`, `frequency`) VALUES
( 1, 'AAPL', 'Apple Inc.',                   'Technology',         272.9500,  1.0300, 0.003800, '2026-05-12', '2026-05-15', 'quarterly'),
( 2, 'O',    'Realty Income Corp.',           'Real Estate',         66.6000,  3.2190, 0.048600, '2026-02-27', '2026-03-13', 'monthly'),
( 3, 'JNJ',  'Johnson & Johnson',            'Healthcare',         243.4700,  5.1400, 0.021400, '2026-02-24', '2026-03-10', 'quarterly'),
( 4, 'CNQ',  'Canadian Natural Resources',    'Energy',              43.0900,  2.3250, 0.040100, '2026-03-23', '2026-04-03', 'quarterly'),
( 5, 'PEP',  'PepsiCo Inc.',                 'Consumer Defensive', 167.5800,  5.6230, 0.034000, '2026-03-06', '2026-03-27', 'quarterly'),
( 6, 'PG',   'Procter & Gamble',             'Consumer Defensive', 163.7500,  4.1770, 0.025800, '2026-04-24', '2026-05-19', 'quarterly'),
( 7, 'LTC',  'LTC Properties Inc.',          'Real Estate',         40.3600,  2.2800, 0.056500, '2026-02-20', '2026-02-27', 'monthly'),
( 8, 'MCD',  'McDonald''s Corp.',            'Consumer Cyclical',  334.5300,  7.1700, 0.022200, '2026-03-03', '2026-03-17', 'quarterly'),
( 9, 'JPM',  'JPMorgan Chase',               'Financial Services', 306.1300,  5.8000, 0.019600, '2026-04-06', '2026-04-30', 'quarterly'),
(10, 'KO',   'Coca-Cola Co.',                'Consumer Defensive',  80.5000,  2.0400, 0.025600, '2026-03-13', '2026-04-01', 'quarterly'),
(11, 'MA',   'Mastercard Inc.',              'Financial Services', 514.7700,  3.1500, 0.006800, '2026-04-09', '2026-05-05', 'quarterly'),
(12, 'IBM',  'IBM Corp.',                    'Technology',         242.0100,  6.7100, 0.028300, '2026-05-08', '2026-06-10', 'quarterly'),
(13, 'CAH',  'Cardinal Health',              'Healthcare',         227.1300,  2.0380, 0.009000, '2026-04-01', '2026-04-15', 'quarterly'),
(14, 'GD',   'General Dynamics',             'Industrials',        350.7200,  6.0000, 0.017100, '2026-04-17', '2026-05-08', 'quarterly'),
(15, 'RY',   'Royal Bank of Canada',         'Financial Services', 169.8300,  4.6900, 0.027600, '2026-04-24', '2026-05-22', 'quarterly'),
(16, 'BMO',  'Bank of Montreal',             'Financial Services', 148.8500,  4.8500, 0.032800, '2026-04-29', '2026-05-27', 'quarterly'),
(17, 'MSFT', 'Microsoft Corp.',              'Technology',         401.7200,  3.4800, 0.009100, '2026-05-21', '2026-06-11', 'quarterly'),
(18, 'BNS',  'Bank of Nova Scotia',          'Financial Services',  76.7700,  4.3600, 0.042000, '2026-04-07', '2026-04-28', 'quarterly'),
(19, 'CB',   'Chubb Limited',                'Financial Services', 337.9200,  3.8200, 0.011500, '2026-03-16', '2026-04-03', 'quarterly'),
(20, 'LOW',  'Lowe''s Companies',            'Consumer Cyclical',  264.3900,  4.7500, 0.018200, '2026-04-17', '2026-05-06', 'quarterly'),
(21, 'SYY',  'Sysco Corp.',                  'Consumer Defensive',  89.1200,  2.1300, 0.024200, '2026-04-01', '2026-04-24', 'quarterly'),
(22, 'ITW',  'Illinois Tool Works',          'Industrials',        290.2800,  6.2200, 0.022200, '2026-03-31', '2026-04-09', 'quarterly'),
(23, 'WMT',  'Walmart Inc.',                 'Consumer Defensive', 124.4200,  0.9400, 0.008000, '2026-03-23', '2026-04-07', 'quarterly'),
(24, 'KMB',  'Kimberly-Clark',               'Consumer Defensive', 110.3800,  5.0400, 0.046400, '2026-03-06', '2026-03-27', 'quarterly'),
(25, 'BLK',  'BlackRock Inc.',               'Financial Services',1090.2700, 20.8400, 0.021000, '2026-03-06', '2026-03-24', 'quarterly'),
(26, 'MAIN', 'Main Street Capital',          'Financial Services',  58.0900,  3.0000, 0.053700, '2026-03-06', '2026-03-13', 'monthly'),
(27, 'ADP',  'Automatic Data Processing',    'Technology',         218.3600,  6.3200, 0.031100, '2026-03-16', '2026-04-01', 'quarterly'),
(28, 'CF',   'CF Industries',                'Basic Materials',     97.1600,  2.0000, 0.020600, '2026-02-13', '2026-02-27', 'quarterly'),
(29, 'ECL',  'Ecolab Inc.',                  'Basic Materials',    306.7600,  2.6800, 0.009000, '2026-03-18', '2026-04-15', 'quarterly'),
(30, 'CVX',  'Chevron Corp.',                'Energy',             184.1600,  6.8400, 0.038700, '2026-02-17', '2026-03-10', 'quarterly'),
(31, 'SLB',  'SLB N.V.',                     'Energy',              51.4900,  1.1400, 0.022900, '2026-02-11', '2026-04-02', 'quarterly'),
(32, 'DUK',  'Duke Energy',                  'Utilities',          129.2300,  4.2200, 0.033000, '2026-02-13', '2026-03-16', 'quarterly'),
(33, 'AGNC', 'AGNC Investment',              'Real Estate',         11.3500,  1.4400, 0.127000, '2026-02-27', '2026-03-10', 'monthly'),
(34, 'ABT',  'Abbott Laboratories',          'Healthcare',         116.2600,  2.4000, 0.022000, '2026-04-15', '2026-05-15', 'quarterly'),
(35, 'AFL',  'Aflac Inc.',                   'Financial Services', 113.9700,  2.3200, 0.021400, '2026-02-18', '2026-03-02', 'quarterly'),
(36, 'PPG',  'PPG Industries',               'Basic Materials',    123.4800,  2.7800, 0.023000, '2026-02-20', '2026-03-12', 'quarterly'),
(37, 'PNR',  'Pentair plc',                  'Industrials',        100.4400,  1.0000, 0.010400, '2026-04-16', '2026-05-01', 'quarterly'),
(38, 'NUE',  'Nucor Corp.',                  'Basic Materials',    175.6100,  2.2100, 0.012800, '2026-03-31', '2026-05-11', 'quarterly'),
(39, 'EMR',  'Emerson Electric',             'Industrials',        152.7200,  2.1380, 0.014200, '2026-02-13', '2026-03-10', 'quarterly'),
(40, 'C',    'Citigroup Inc.',               'Financial Services', 116.1900,  2.3200, 0.020700, '2026-02-02', '2026-02-27', 'quarterly'),
(41, 'TROW', 'T. Rowe Price',               'Financial Services',  96.4600,  5.0800, 0.053900, '2026-03-16', '2026-03-30', 'quarterly'),
(42, 'RGLD', 'Royal Gold Inc.',              'Basic Materials',    294.3800,  0.6000, 0.006600, '2026-04-02', '2026-04-16', 'quarterly'),
(43, 'TD',   'Toronto-Dominion Bank',        'Financial Services',  98.7800,  3.2200, 0.032600, '2026-04-10', '2026-04-30', 'quarterly'),
(44, 'CSCO', 'Cisco Systems',                'Technology',          78.1000,  1.6400, 0.021500, '2026-04-02', '2026-04-22', 'quarterly'),
(45, 'BMY',  'Bristol-Myers Squibb',         'Healthcare',          61.1000,  2.4900, 0.041200, '2026-04-01', '2026-05-01', 'quarterly'),
(46, 'GOOD', 'Gladstone Commercial',         'Real Estate',         12.7600,  1.2000, 0.094000, '2026-03-23', '2026-03-31', 'monthly'),
(47, 'GWW',  'W.W. Grainger',               'Industrials',       1105.5200,  8.8300, 0.008100, '2026-02-09', '2026-03-01', 'quarterly'),
(48, 'ADC',  'Agree Realty',                 'Real Estate',         79.7800,  3.0810, 0.039500, '2026-02-27', '2026-03-13', 'monthly'),
(49, 'ROP',  'Roper Technologies',           'Technology',         352.1500,  3.3850, 0.010300, '2026-04-06', '2026-04-22', 'quarterly'),
(50, 'SHW',  'Sherwin-Williams',             'Basic Materials',    360.5000,  3.1600, 0.008900, '2026-03-02', '2026-03-13', 'quarterly');

-- =====================================================
-- Seed data: stock_payment_months
-- Month values are 1-12 (converted from 0-11 JS indices)
-- =====================================================

INSERT INTO `stock_payment_months` (`stock_id`, `month`) VALUES
-- AAPL (id=1): [1,4,7,10] -> months 2,5,8,11
(1, 2), (1, 5), (1, 8), (1, 11),
-- O (id=2): monthly
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10), (2, 11), (2, 12),
-- JNJ (id=3): [2,5,8,11] -> months 3,6,9,12
(3, 3), (3, 6), (3, 9), (3, 12),
-- CNQ (id=4): [0,3,6,9] -> months 1,4,7,10
(4, 1), (4, 4), (4, 7), (4, 10),
-- PEP (id=5): [0,2,5,8] -> months 1,3,6,9
(5, 1), (5, 3), (5, 6), (5, 9),
-- PG (id=6): [1,4,7,10] -> months 2,5,8,11
(6, 2), (6, 5), (6, 8), (6, 11),
-- LTC (id=7): monthly
(7, 1), (7, 2), (7, 3), (7, 4), (7, 5), (7, 6), (7, 7), (7, 8), (7, 9), (7, 10), (7, 11), (7, 12),
-- MCD (id=8): [2,5,8,11] -> months 3,6,9,12
(8, 3), (8, 6), (8, 9), (8, 12),
-- JPM (id=9): [0,3,6,9] -> months 1,4,7,10
(9, 1), (9, 4), (9, 7), (9, 10),
-- KO (id=10): [3,6,9,11] -> months 4,7,10,12
(10, 4), (10, 7), (10, 10), (10, 12),
-- MA (id=11): [1,4,7,10] -> months 2,5,8,11
(11, 2), (11, 5), (11, 8), (11, 11),
-- IBM (id=12): [2,5,8,11] -> months 3,6,9,12
(12, 3), (12, 6), (12, 9), (12, 12),
-- CAH (id=13): [0,3,6,9] -> months 1,4,7,10
(13, 1), (13, 4), (13, 7), (13, 10),
-- GD (id=14): [1,4,7,10] -> months 2,5,8,11
(14, 2), (14, 5), (14, 8), (14, 11),
-- RY (id=15): [1,4,7,10] -> months 2,5,8,11
(15, 2), (15, 5), (15, 8), (15, 11),
-- BMO (id=16): [1,4,7,10] -> months 2,5,8,11
(16, 2), (16, 5), (16, 8), (16, 11),
-- MSFT (id=17): [2,5,8,11] -> months 3,6,9,12
(17, 3), (17, 6), (17, 9), (17, 12),
-- BNS (id=18): [0,3,6,9] -> months 1,4,7,10
(18, 1), (18, 4), (18, 7), (18, 10),
-- CB (id=19): [0,3,6,9] -> months 1,4,7,10
(19, 1), (19, 4), (19, 7), (19, 10),
-- LOW (id=20): [1,4,7,10] -> months 2,5,8,11
(20, 2), (20, 5), (20, 8), (20, 11),
-- SYY (id=21): [0,3,6,9] -> months 1,4,7,10
(21, 1), (21, 4), (21, 7), (21, 10),
-- ITW (id=22): [0,3,6,9] -> months 1,4,7,10
(22, 1), (22, 4), (22, 7), (22, 10),
-- WMT (id=23): [0,3,6,9] -> months 1,4,7,10
(23, 1), (23, 4), (23, 7), (23, 10),
-- KMB (id=24): [0,3,6,9] -> months 1,4,7,10
(24, 1), (24, 4), (24, 7), (24, 10),
-- BLK (id=25): [2,5,8,11] -> months 3,6,9,12
(25, 3), (25, 6), (25, 9), (25, 12),
-- MAIN (id=26): monthly
(26, 1), (26, 2), (26, 3), (26, 4), (26, 5), (26, 6), (26, 7), (26, 8), (26, 9), (26, 10), (26, 11), (26, 12),
-- ADP (id=27): [0,3,6,9] -> months 1,4,7,10
(27, 1), (27, 4), (27, 7), (27, 10),
-- CF (id=28): [1,4,7,10] -> months 2,5,8,11
(28, 2), (28, 5), (28, 8), (28, 11),
-- ECL (id=29): [0,3,6,9] -> months 1,4,7,10
(29, 1), (29, 4), (29, 7), (29, 10),
-- CVX (id=30): [2,5,8,11] -> months 3,6,9,12
(30, 3), (30, 6), (30, 9), (30, 12),
-- SLB (id=31): [0,3,6,9] -> months 1,4,7,10
(31, 1), (31, 4), (31, 7), (31, 10),
-- DUK (id=32): [2,5,8,11] -> months 3,6,9,12
(32, 3), (32, 6), (32, 9), (32, 12),
-- AGNC (id=33): monthly
(33, 1), (33, 2), (33, 3), (33, 4), (33, 5), (33, 6), (33, 7), (33, 8), (33, 9), (33, 10), (33, 11), (33, 12),
-- ABT (id=34): [1,4,7,10] -> months 2,5,8,11
(34, 2), (34, 5), (34, 8), (34, 11),
-- AFL (id=35): [2,5,8,11] -> months 3,6,9,12
(35, 3), (35, 6), (35, 9), (35, 12),
-- PPG (id=36): [2,5,8,11] -> months 3,6,9,12
(36, 3), (36, 6), (36, 9), (36, 12),
-- PNR (id=37): [1,4,7,10] -> months 2,5,8,11
(37, 2), (37, 5), (37, 8), (37, 11),
-- NUE (id=38): [1,4,7,10] -> months 2,5,8,11
(38, 2), (38, 5), (38, 8), (38, 11),
-- EMR (id=39): [2,5,8,11] -> months 3,6,9,12
(39, 3), (39, 6), (39, 9), (39, 12),
-- C (id=40): [1,4,7,10] -> months 2,5,8,11
(40, 2), (40, 5), (40, 8), (40, 11),
-- TROW (id=41): [2,5,8,11] -> months 3,6,9,12
(41, 3), (41, 6), (41, 9), (41, 12),
-- RGLD (id=42): [0,3,6,9] -> months 1,4,7,10
(42, 1), (42, 4), (42, 7), (42, 10),
-- TD (id=43): [0,3,6,9] -> months 1,4,7,10
(43, 1), (43, 4), (43, 7), (43, 10),
-- CSCO (id=44): [0,3,6,9] -> months 1,4,7,10
(44, 1), (44, 4), (44, 7), (44, 10),
-- BMY (id=45): [1,4,7,10] -> months 2,5,8,11
(45, 2), (45, 5), (45, 8), (45, 11),
-- GOOD (id=46): monthly
(46, 1), (46, 2), (46, 3), (46, 4), (46, 5), (46, 6), (46, 7), (46, 8), (46, 9), (46, 10), (46, 11), (46, 12),
-- GWW (id=47): [2,5,8,11] -> months 3,6,9,12
(47, 3), (47, 6), (47, 9), (47, 12),
-- ADC (id=48): monthly
(48, 1), (48, 2), (48, 3), (48, 4), (48, 5), (48, 6), (48, 7), (48, 8), (48, 9), (48, 10), (48, 11), (48, 12),
-- ROP (id=49): [0,3,6,9] -> months 1,4,7,10
(49, 1), (49, 4), (49, 7), (49, 10),
-- SHW (id=50): [2,5,8,11] -> months 3,6,9,12
(50, 3), (50, 6), (50, 9), (50, 12);

-- =====================================================
-- Seed data: demo holdings for user_id=2
-- =====================================================

INSERT INTO `holdings` (`user_id`, `stock_id`, `shares`, `invested`) VALUES
(2, 17, 0.02731561,  9.82),  -- MSFT
(2,  1, 0.04055063,  9.15),  -- AAPL
(2,  2, 0.16406605,  8.82),  -- O
(2,  3, 0.04521206,  8.92),  -- JNJ
(2,  4, 0.25591965,  8.46),  -- CNQ
(2, 10, 0.13693665,  8.88),  -- KO
(2,  5, 0.06522813,  8.82),  -- PEP
(2, 11, 0.02128540,  9.60),  -- MA
(2,  6, 0.06724974,  8.93),  -- PG
(2,  7, 0.27493372,  8.83),  -- LTC
(2,  8, 0.03281831,  9.04),  -- MCD
(2,  9, 0.03578836,  9.31),  -- JPM
(2, 32, 0.08501074,  9.02),  -- DUK
(2, 12, 0.04478482, 10.18),  -- IBM
(2, 13, 0.04817594,  8.76),  -- CAH
(2, 45, 0.18009031,  8.68),  -- BMY
(2, 14, 0.03167115,  9.38),  -- GD
(2, 15, 0.06487947,  9.19),  -- RY
(2, 16, 0.07431541,  8.90),  -- BMO
(2, 18, 0.14500906,  9.21),  -- BNS
(2, 19, 0.03264423,  8.85),  -- CB
(2, 20, 0.04137579,  9.24),  -- LOW
(2, 21, 0.12251238,  8.79),  -- SYY
(2, 22, 0.03816494,  8.88),  -- ITW
(2, 23, 0.08806654,  8.96),  -- WMT
(2, 24, 0.10001566,  9.09),  -- KMB
(2, 26, 0.18778539,  9.44),  -- MAIN
(2, 25, 0.01004299,  9.40),  -- BLK
(2, 27, 0.04971855,  9.72),  -- ADP
(2, 44, 0.14045906,  9.22),  -- CSCO
(2, 29, 0.03610906,  8.96),  -- ECL
(2, 30, 0.05925070,  8.78),  -- CVX
(2, 40, 0.09503194,  9.11),  -- C
(2, 33, 0.96951800,  9.24),  -- AGNC
(2, 35, 0.09703227,  9.22),  -- AFL
(2, 36, 0.08929211,  8.89),  -- PPG
(2, 34, 0.09458950,  9.33),  -- ABT
(2, 46, 0.86637519,  8.76),  -- GOOD
(2, 38, 0.06237048,  9.13),  -- NUE
(2, 39, 0.07256374,  9.03),  -- EMR
(2, 37, 0.10936317,  9.68),  -- PNR
(2, 41, 0.11356119,  9.53),  -- TROW
(2, 43, 0.11162052,  8.97),  -- TD
(2, 42, 0.03805228,  8.57),  -- RGLD
(2, 50, 0.03059933,  9.11),  -- SHW
(2, 31, 0.21189257,  8.46),  -- SLB
(2, 47, 0.00985944,  9.07),  -- GWW
(2, 48, 0.13790890,  8.94),  -- ADC
(2, 49, 0.03101160,  9.69),  -- ROP
(2, 28, 0.11278779,  9.23);  -- CF
