-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 08, 2024 at 03:02 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `AuctionDB`
--

-- --------------------------------------------------------

--
-- Table structure for table `Auction`
--

CREATE TABLE `Auction` (
  `AuctionID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `ItemName` varchar(255) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `CategoryID` int(11) NOT NULL,
  `ReservePrice` float DEFAULT NULL,
  `StartPrice` float DEFAULT NULL,
  `EndDate` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Auction`
--

INSERT INTO `Auction` (`AuctionID`, `UserID`, `ItemName`, `Description`, `CategoryID`, `ReservePrice`, `StartPrice`, `EndDate`) VALUES
(1, 2, 'test-1', 'qwdqn woqidn', 3, 0, 20, '2024-11-21T02:58'),
(2, 2, 'test-2', '.', 2, 0, 10, '2024-11-19T20:07'),
(3, 2, 'test-3', '.', 1, 0, 5, '2024-11-11T20:08'),
(4, 2, 'test 4', 'apfjapfm', 1, 2000, 1000, '2024-11-23T13:59');

-- --------------------------------------------------------

--
-- Table structure for table `Bid`
--

CREATE TABLE `Bid` (
  `BidID` int(11) NOT NULL,
  `AuctionID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `BidAmount` float DEFAULT NULL,
  `BidLength` int(11) DEFAULT NULL,
  `BidTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Bid`
--

INSERT INTO `Bid` (`BidID`, `AuctionID`, `UserID`, `BidAmount`, `BidLength`, `BidTime`) VALUES
(1, 3, 1, 10, 0, '2024-11-05 00:15:19'),
(2, 3, 1, 11, 0, '2024-11-05 00:15:19'),
(3, 3, 1, 12, 0, '2024-11-05 00:15:19'),
(4, 3, 1, 14, NULL, '2024-11-05 00:16:51'),
(5, 3, 1, 20, NULL, '2024-11-05 00:16:57'),
(6, 2, 1, 11, NULL, '2024-11-08 13:57:30'),
(7, 2, 1, 15, NULL, '2024-11-08 13:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `Category`
--

CREATE TABLE `Category` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Category`
--

INSERT INTO `Category` (`CategoryID`, `CategoryName`) VALUES
(1, 'Electronics'),
(2, 'Furniture'),
(3, 'Clothing'),
(4, 'Books'),
(5, 'Toys');

-- --------------------------------------------------------

--
-- Table structure for table `Notification`
--

CREATE TABLE `Notification` (
  `NotificationID` int(11) NOT NULL,
  `AuctionID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `NotificationTime` datetime DEFAULT NULL,
  `NotificationType` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Role` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`UserID`, `Name`, `Email`, `Password`, `Role`) VALUES
(1, 'User', 'roham@gmail.com', '$2y$10$dUDrdojrLX/sApW7agOo0.8uWTNeIq54/TmoshpyWJ8cDaHKoOhxq', 'buyer'),
(2, 'User', 'rohamseller@gmail.com', '$2y$10$6.VT881PwQ0FazxiTQN8PuZg2Krbdc6/zc0wuE2/LpG83pP.zIaB6', 'seller');

-- --------------------------------------------------------

--
-- Table structure for table `WatchList`
--

CREATE TABLE `WatchList` (
  `WatchID` int(11) NOT NULL,
  `AuctionID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `WatchList`
--

INSERT INTO `WatchList` (`WatchID`, `AuctionID`, `UserID`) VALUES
(4, 3, 1),
(5, 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Auction`
--
ALTER TABLE `Auction`
  ADD PRIMARY KEY (`AuctionID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `CategoryID` (`CategoryID`);

--
-- Indexes for table `Bid`
--
ALTER TABLE `Bid`
  ADD PRIMARY KEY (`BidID`),
  ADD KEY `AuctionID` (`AuctionID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `Category`
--
ALTER TABLE `Category`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `Notification`
--
ALTER TABLE `Notification`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `AuctionID` (`AuctionID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `WatchList`
--
ALTER TABLE `WatchList`
  ADD PRIMARY KEY (`WatchID`),
  ADD KEY `AuctionID` (`AuctionID`),
  ADD KEY `UserID` (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Auction`
--
ALTER TABLE `Auction`
  MODIFY `AuctionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Bid`
--
ALTER TABLE `Bid`
  MODIFY `BidID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Category`
--
ALTER TABLE `Category`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Notification`
--
ALTER TABLE `Notification`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `WatchList`
--
ALTER TABLE `WatchList`
  MODIFY `WatchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Auction`
--
ALTER TABLE `Auction`
  ADD CONSTRAINT `auction_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`),
  ADD CONSTRAINT `auction_ibfk_2` FOREIGN KEY (`CategoryID`) REFERENCES `Category` (`CategoryID`);

--
-- Constraints for table `Bid`
--
ALTER TABLE `Bid`
  ADD CONSTRAINT `bid_ibfk_1` FOREIGN KEY (`AuctionID`) REFERENCES `Auction` (`AuctionID`),
  ADD CONSTRAINT `bid_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`);

--
-- Constraints for table `Notification`
--
ALTER TABLE `Notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`AuctionID`) REFERENCES `Auction` (`AuctionID`),
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`);

--
-- Constraints for table `WatchList`
--
ALTER TABLE `WatchList`
  ADD CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`AuctionID`) REFERENCES `Auction` (`AuctionID`),
  ADD CONSTRAINT `watchlist_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
