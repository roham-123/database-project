-- Create and use the AuctionDB database
DROP DATABASE IF EXISTS AuctionDB;
CREATE DATABASE AuctionDB;
USE AuctionDB;

-- Table for users
CREATE TABLE `Users` (
  `UserID` INT AUTO_INCREMENT PRIMARY KEY,
  `Username` VARCHAR(255) NOT NULL,
  `Email` VARCHAR(255),
  `Password` VARCHAR(255),
  `Role` VARCHAR(255)
);

-- Table for categories
CREATE TABLE `Category` (
  `CategoryID` INT AUTO_INCREMENT PRIMARY KEY,
  `CategoryName` VARCHAR(255)
);

-- Table for auctions
CREATE TABLE `Auction` (
  `AuctionID` INT AUTO_INCREMENT PRIMARY KEY,
  `UserID` INT,
  `ItemName` VARCHAR(255),
  `Description` VARCHAR(255),
  `CategoryID` INT,
  `ReservePrice` FLOAT,
  `StartPrice` FLOAT,
  `EndDate` DATETIME,
  `Views` INT DEFAULT 0,
  `Image` VARCHAR(255),
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`CategoryID`) REFERENCES `Category`(`CategoryID`) ON DELETE CASCADE
);

-- Table for bids
CREATE TABLE `Bid` (
  `BidID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT,
  `UserID` INT,
  `BidAmount` FLOAT,
  `BidTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for notifications
CREATE TABLE `Notification` (
  `NotificationID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT,
  `UserID` INT,
  `NotificationTime` DATETIME,
  `NotificationType` VARCHAR(255),
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for watchlist
CREATE TABLE `WatchList` (
  `WatchID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT,
  `UserID` INT,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for user views (to track auction views)
CREATE TABLE `UserViews` (
  `UserViewID` INT AUTO_INCREMENT PRIMARY KEY,
  `UserID` INT,
  `AuctionID` INT,
  UNIQUE KEY `user_auction_unique` (`UserID`, `AuctionID`),
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE
);

-- Sample data for Users table
INSERT INTO `Users` (`Username`, `Email`, `Password`, `Role`) VALUES
('User1', 'user1@example.com', 'password1', 'buyer'),
('User2', 'user2@example.com', 'password2', 'seller');

-- Sample data for Category table
INSERT INTO `Category` (`CategoryName`) VALUES
('Electronics'),
('Furniture'),
('Clothing'),
('Books'),
('Toys');

-- Sample data for Auction table
INSERT INTO `Auction` (`UserID`, `ItemName`, `Description`, `CategoryID`, `ReservePrice`, `StartPrice`, `EndDate`) VALUES
(2, 'Laptop', 'A high-performance laptop', 1, 500, 300, '2024-12-01 12:00:00'),
(2, 'Sofa', 'Comfortable 3-seater sofa', 2, 200, 150, '2024-11-20 18:00:00');

-- Sample data for Bid table
INSERT INTO `Bid` (`AuctionID`, `UserID`, `BidAmount`) VALUES
(1, 1, 350),
(2, 1, 160);

-- Sample data for WatchList table
INSERT INTO `WatchList` (`AuctionID`, `UserID`) VALUES
(1, 1),
(2, 1);

-- Sample data for UserViews table
INSERT INTO `UserViews` (`UserID`, `AuctionID`) VALUES
(1, 1),
(1, 2);

COMMIT;
