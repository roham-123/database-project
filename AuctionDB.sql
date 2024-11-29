-- Create and use the AuctionDB database
DROP DATABASE IF EXISTS AuctionDB; 
CREATE DATABASE AuctionDB; 
USE AuctionDB;

-- table for users (admin user shouldn't be considered a buyer or seller, just for management purposes)
CREATE TABLE `Users` (
  `UserID` INT AUTO_INCREMENT PRIMARY KEY,
  `Username` VARCHAR(255) NOT NULL,
  `Email` VARCHAR(255) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `Role` ENUM('buyer', 'seller', 'admin') NOT NULL,
  `blacklisted` BOOLEAN NOT NULL DEFAULT 0
);

-- table for categories (like different kinds of stuff we have in auctions)
CREATE TABLE `Category` (
  `CategoryID` INT AUTO_INCREMENT PRIMARY KEY,
  `CategoryName` VARCHAR(255) NOT NULL
);

-- table for auctions (where sellers list their items for auction)
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

-- table for bids (only buyers can bid, admin isn't allowed here)
CREATE TABLE `Bid` (
  `BidID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT,
  `UserID` INT,
  `BidAmount` DECIMAL(10, 2),
  `BidTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- table for notifications (used for sending notifications to users)
CREATE TABLE `Notification` (
  `NotificationID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT,
  `UserID` INT,
  `NotificationTime` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `NotificationType` VARCHAR(255),
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- table for watchlist (users can keep track of auctions they're interested in)
CREATE TABLE `WatchList` (
  `WatchID` INT AUTO_INCREMENT PRIMARY KEY,
  `AuctionID` INT,
  `UserID` INT,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- table for user views (tracking how many times a user views a particular auction)
CREATE TABLE `UserViews` (
  `UserViewID` INT AUTO_INCREMENT PRIMARY KEY,
  `UserID` INT,
  `AuctionID` INT,
  UNIQUE KEY `user_auction_unique` (`UserID`, `AuctionID`),
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE
);

-- table for seller reviews and ratings (for buyers to rate sellers, admin not involved)
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

-- table for admin actions log (track admin actions like deleting users or managing auctions)
CREATE TABLE `AdminActions` (
  `ActionID` INT AUTO_INCREMENT PRIMARY KEY,
  `AdminID` INT NOT NULL,
  `ActionType` VARCHAR(255) NOT NULL,
  `ActionDescription` TEXT,
  `ActionDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`AdminID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- sample data for Users table (Admin is UserID 1, only managing stuff here)
INSERT INTO `Users` (`Username`, `Email`, `Password`, `Role`, `blacklisted`) VALUES
('AdminUser', 'admin@example.com', '$2y$10$scAVQZPVzAI7BFYWXSkxt.CO9.FzAmB57X0AGd2IetiV8CW3cmsPm', 'admin', 0),
('User1', 'user1@example.com', 'hashed_password_1', 'buyer', 0),
('User2', 'user2@example.com', 'hashed_password_2', 'seller', 0),
('User3', 'user3@example.com', 'hashed_password_3', 'buyer', 0),
('User4', 'user4@example.com', 'hashed_password_4', 'buyer', 0),
('User5', 'user5@example.com', 'hashed_password_5', 'buyer', 0),
('User6', 'user6@example.com', 'hashed_password_6', 'seller', 0),
('User7', 'user7@example.com', 'hashed_password_7', 'buyer', 0);

-- sample data for Category table (adding different kinds of categories, nothing fancy)
INSERT INTO `Category` (`CategoryName`) VALUES
('Electronics'),
('Furniture'),
('Clothing'),
('Books'),
('Toys'),
('Sports'),
('Other');

-- sample data for Auction table (adding auctions, all images are in 'uploads' folder)
INSERT INTO `Auction` (`UserID`, `ItemName`, `Description`, `CategoryID`, `ReservePrice`, `StartPrice`, `EndDate`, `Views`, `Image`) VALUES
(2, 'Laptop', 'A high-performance laptop', 1, 500, 300, '2024-12-04 12:00:00', 15, 'uploads/laptop.jpg'),
(2, 'Sofa', 'Comfortable 3-seater sofa', 2, 200, 150, '2024-12-20 18:00:00', 20, 'uploads/sofa.jpg'),
(6, 'Mountain Bike', 'A bike perfect for mountain trails', 6, 300, 150, '2024-12-25 17:00:00', 18, 'uploads/bike.jpg'),
(6, 'Dining Table', 'Modern wooden dining table', 2, 250, 200, '2024-12-30 16:00:00', 25, 'uploads/dining_table.jpg'),
(2, 'Smartphone', 'Latest model smartphone', 1, 600, 350, '2024-12-05 14:00:00', 30, 'uploads/smartphone.jpg'),
(6, 'Novel Set', 'Collection of famous novels', 4, 50, 25, '2024-12-27 20:00:00', 10, 'uploads/novel_set.jpg'),
(2, 'Gaming Console', 'Play the latest games', 1, 400, 300, '2024-12-03 19:00:00', 22, 'uploads/gaming_console.jpg'),
(2, 'Office Chair', 'Ergonomic office chair', 2, 150, 100, '2024-12-28 18:00:00', 17, 'uploads/office_chair.jpg'),
(6, 'Winter Jacket', 'Warm and stylish winter jacket', 3, 80, 50, '2024-12-29 12:00:00', 19, 'uploads/winter_jacket.jpg'),
(6, 'Tennis Racket', 'High-quality tennis racket', 6, 100, 70, '2024-12-26 15:00:00', 16, 'uploads/tennis.jpg'),
(2, 'Electric Guitar', 'High-quality electric guitar', 1, 300, 200, '2024-12-04 17:00:00', 21, 'uploads/guitar.jpg'),
(6, 'Office Desk', 'Wooden office desk', 2, 150, 100, '2024-12-02 18:00:00', 23, 'uploads/desk.jpg');

-- sample data for Bid table (admin isn't allowed to bid, only buyers can)
INSERT INTO `Bid` (`AuctionID`, `UserID`, `BidAmount`) VALUES
(1, 3, 360),
(1, 4, 370),
(2, 5, 170),
(3, 3, 170),
(3, 5, 180),
(3, 7, 200),
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
(10, 4, 90),
(11, 4, 250),
(11, 7, 270),
(12, 3, 150),
(12, 5, 180);

-- sample data for WatchList table (admin isn't allowed a watchlist)
INSERT INTO `WatchList` (`AuctionID`, `UserID`) VALUES
(3, 3),
(4, 5);

-- sample data for UserViews table (tracking who viewed what auctions, admin views removed)
INSERT INTO `UserViews` (`UserID`, `AuctionID`) VALUES
(3, 3),
(4, 5),
(5, 7),
(3, 6),
(7, 9),
(4, 8),
(3, 10),
(5, 11),
(7, 12);

-- sample data for SellerReviews table (only buyers can leave reviews, no admin reviews)
INSERT INTO `SellerReviews` (`AuctionID`, `BuyerID`, `SellerID`, `Rating`, `ReviewText`) VALUES
(1, 3, 2, 5, 'Excellent seller, very professional!'),
(2, 4, 2, 4, 'Good experience overall.'),
(3, 3, 6, 5, 'Great product, highly recommend!'),
(4, 5, 6, 4, 'Item as described, good seller.'),
(5, 4, 2, 5, 'Fast shipping, very happy.'),
(6, 3, 6, 3, 'Okay experience.'),
(7, 5, 2, 4, 'Product was good, but packaging could be better.'),
(8, 4, 2, 5, 'Very comfortable chair, great seller!'),
(9, 7, 6, 4, 'Nice jacket, as advertised.'),
(10, 3, 6, 5, 'Amazing quality, very satisfied.'),
(11, 4, 2, 5, 'Amazing sound quality, loved the guitar.'),
(12, 5, 6, 4, 'Desk was sturdy, decent quality.');

COMMIT;
