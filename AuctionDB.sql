-- Create and use the AuctionDB database
DROP DATABASE IF EXISTS AuctionDB;
CREATE DATABASE AuctionDB;
USE AuctionDB;

-- Table for users
CREATE TABLE `Users` (
  `UserID` INT AUTO_INCREMENT PRIMARY KEY,
  `Username` VARCHAR(255) NOT NULL,
  `Email` VARCHAR(255) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `Role` ENUM('buyer', 'seller', 'admin') NOT NULL,
  `blacklisted` BOOLEAN NOT NULL DEFAULT 0
);

-- Table for categories
CREATE TABLE `Category` (
  `CategoryID` INT AUTO_INCREMENT PRIMARY KEY,
  `CategoryName` VARCHAR(255) NOT NULL
);

-- Table for auctions
CREATE TABLE `Auction` (
  `AuctionID` INT AUTO_INCREMENT PRIMARY KEY,
  `UserID` INT,
  `ItemName` VARCHAR(255) NOT NULL,
  `Description` VARCHAR(255) NOT NULL,
  `CategoryID` INT,
  `ReservePrice` DECIMAL(10, 2),
  `StartPrice` DECIMAL(10, 2),
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
  `BidAmount` DECIMAL(10, 2),
  `BidTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for notifications
CREATE TABLE `Notification` (
  `NotificationID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT,
  `UserID` INT,
  `NotificationTime` DATETIME DEFAULT CURRENT_TIMESTAMP,
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

-- Table for seller reviews and ratings
CREATE TABLE `SellerReviews` (
  `ReviewID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT NOT NULL,
  `BuyerID` INT NOT NULL,
  `SellerID` INT NOT NULL,
  `Rating` INT CHECK (`Rating` BETWEEN 1 AND 5),
  `ReviewText` TEXT,
  `ReviewDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`BuyerID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`SellerID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for admin actions log
CREATE TABLE `AdminActions` (
  `ActionID` INT AUTO_INCREMENT PRIMARY KEY,
  `AdminID` INT NOT NULL,
  `ActionType` VARCHAR(255) NOT NULL,
  `ActionDescription` TEXT,
  `ActionDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`AdminID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Sample data for Users table
INSERT INTO `Users` (`Username`, `Email`, `Password`, `Role`, `blacklisted`) VALUES
('AdminUser', 'admin@example.com', '$2y$10$scAVQZPVzAI7BFYWXSkxt.CO9.FzAmB57X0AGd2IetiV8CW3cmsPm', 'admin', 0),
('User1', 'user1@example.com', 'hashed_password_1', 'buyer', 0),
('User2', 'user2@example.com', 'hashed_password_2', 'seller', 0),
('User3', 'user3@example.com', 'hashed_password_3', 'buyer', 0),
('User4', 'user4@example.com', 'hashed_password_4', 'buyer', 0),
('User5', 'user5@example.com', 'hashed_password_5', 'buyer', 0),
('User6', 'user6@example.com', 'hashed_password_6', 'seller', 0),
('User7', 'user7@example.com', 'hashed_password_7', 'buyer', 0);

-- Sample data for Category table
INSERT INTO `Category` (`CategoryName`) VALUES
('Electronics'),
('Furniture'),
('Clothing'),
('Books'),
('Toys'),
('Sports'),
('Other');

-- Sample data for Auction table
INSERT INTO `Auction` (`UserID`, `ItemName`, `Description`, `CategoryID`, `ReservePrice`, `StartPrice`, `EndDate`) VALUES
(2, 'Laptop', 'A high-performance laptop', 1, 500, 300, '2024-12-01 12:00:00'),
(2, 'Sofa', 'Comfortable 3-seater sofa', 2, 200, 150, '2024-11-20 18:00:00'),
(6, 'Mountain Bike', 'A bike perfect for mountain trails', 6, 300, 150, '2024-11-25 17:00:00'),
(6, 'Dining Table', 'Modern wooden dining table', 2, 250, 200, '2024-11-30 16:00:00'),
(2, 'Smartphone', 'Latest model smartphone', 1, 600, 350, '2024-12-05 14:00:00'),
(6, 'Novel Set', 'Collection of famous novels', 4, 50, 25, '2024-11-27 20:00:00'),
(2, 'Gaming Console', 'Play the latest games', 1, 400, 300, '2024-12-03 19:00:00'),
(2, 'Office Chair', 'Ergonomic office chair', 2, 150, 100, '2024-11-28 18:00:00'),
(6, 'Winter Jacket', 'Warm and stylish winter jacket', 3, 80, 50, '2024-11-29 12:00:00'),
(6, 'Tennis Racket', 'High-quality tennis racket', 6, 100, 70, '2024-11-26 15:00:00');

-- Sample data for Bid table (AdminUser is removed, only other users bid)
INSERT INTO `Bid` (`AuctionID`, `UserID`, `BidAmount`) VALUES
(1, 3, 360),
(1, 4, 370),
(2, 5, 170),
(3, 3, 170),
(3, 5, 180),
(3, 7, 200),
(4, 1, 210),
(4, 5, 220),
(5, 4, 400),
(5, 7, 420),
(6, 3, 30),
(6, 7, 40),
(6, 5, 50),
(7, 5, 320),
(8, 4, 120),
(8, 3, 130),
(9, 7, 60),
(10, 3, 80),
(10, 4, 90);

-- Sample data for WatchList table
INSERT INTO `WatchList` (`AuctionID`, `UserID`) VALUES
(1, 1),
(2, 1),
(3, 3),
(4, 1),
(5, 4),
(6, 3),
(7, 5),
(8, 4),
(9, 7),
(10, 3);

-- Sample data for UserViews table
INSERT INTO `UserViews` (`UserID`, `AuctionID`) VALUES
(1, 1),
(1, 2),
(3, 3),
(4, 5),
(5, 7),
(3, 6),
(7, 9),
(4, 8),
(3, 10),
(1, 4);

-- Sample data for SellerReviews table (AdminUser is not involved)
INSERT INTO `SellerReviews` (`AuctionID`, `BuyerID`, `SellerID`, `Rating`, `ReviewText`) VALUES
(1, 3, 2, 5, 'Excellent seller, very professional!'),
(2, 1, 2, 4, 'Good experience overall.'),
(3, 3, 6, 5, 'Great product, highly recommend!'),
(4, 1, 6, 4, 'Item as described, good seller.'),
(5, 4, 2, 5, 'Fast shipping, very happy.'),
(6, 3, 6, 3, 'Okay experience.'),
(7, 5, 2, 4, 'Product was good, but packaging could be better.'),
(8, 4, 2, 5, 'Very comfortable chair, great seller!'),
(9, 7, 6, 4, 'Nice jacket, as advertised.'),
(10, 3, 6, 5, 'Amazing quality, very satisfied.');

COMMIT;
