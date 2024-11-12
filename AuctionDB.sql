-- Create and use the AuctionDB database
DROP DATABASE IF EXISTS AuctionDB;
CREATE DATABASE AuctionDB;
USE AuctionDB;

-- Table for users
CREATE TABLE `Users` (
  `UserID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each user
  `Username` VARCHAR(255) NOT NULL,        -- Username for the user
  `Email` VARCHAR(255),                    -- Email address of the user
  `Password` VARCHAR(255),                 -- Password for user authentication
  `Role` VARCHAR(255)                      -- Role of the user (e.g., buyer, seller)
);

-- Table for categories
CREATE TABLE `Category` (
  `CategoryID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each category
  `CategoryName` VARCHAR(255)                  -- Name of the category (e.g., Electronics, Furniture)
);

-- Table for auctions
CREATE TABLE `Auction` (
  `AuctionID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each auction
  `UserID` INT,                               -- ID of the seller who created the auction
  `ItemName` VARCHAR(255),                    -- Name of the item being auctioned
  `Description` VARCHAR(255),                 -- Description of the auctioned item
  `CategoryID` INT,                           -- ID of the category for the item
  `ReservePrice` FLOAT,                       -- Minimum price the seller wants to accept
  `StartPrice` FLOAT,                         -- Starting price of the auction
  `EndDate` DATETIME,                         -- Date and time when the auction ends
  `Views` INT DEFAULT 0,                      -- Number of views for this auction
  `Image` VARCHAR(255),                       -- Image file path or URL for the item
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`CategoryID`) REFERENCES `Category`(`CategoryID`) ON DELETE CASCADE
);

-- Table for bids
CREATE TABLE `Bid` (
  `BidID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each bid
  `AuctionID` INT,                       -- ID of the auction this bid is for
  `UserID` INT,                          -- ID of the user who placed the bid
  `BidAmount` FLOAT,                     -- Amount of the bid
  `BidTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the bid was placed
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for notifications
CREATE TABLE `Notification` (
  `NotificationID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each notification
  `AuctionID` INT,                                 -- ID of the auction this notification is related to
  `UserID` INT,                                    -- ID of the user who receives the notification
  `NotificationTime` DATETIME,                     -- Date and time of the notification
  `NotificationType` VARCHAR(255),                 -- Type of notification (e.g., outbid, auction won)
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for watchlist
CREATE TABLE `WatchList` (
  `WatchID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each watchlist entry
  `AuctionID` INT,                          -- ID of the auction being watched
  `UserID` INT,                             -- ID of the user watching the auction
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
);

-- Table for user views (to track auction views)
CREATE TABLE `UserViews` (
  `UserViewID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each view entry
  `UserID` INT,                                -- ID of the user who viewed the auction
  `AuctionID` INT,                             -- ID of the auction being viewed
  UNIQUE KEY `user_auction_unique` (`UserID`, `AuctionID`), -- Ensures each user can only view an auction once
  FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE
);

-- Table for seller reviews and ratings
CREATE TABLE `SellerReviews` (
  `ReviewID` INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each review
  `AuctionID` INT NOT NULL,                  -- ID of the auction related to the review
  `BuyerID` INT NOT NULL,                    -- ID of the user (buyer) who left the review
  `SellerID` INT NOT NULL,                   -- ID of the user (seller) receiving the review
  `Rating` INT CHECK (Rating BETWEEN 1 AND 5), -- Rating out of 5 stars (1 to 5)
  `ReviewText` TEXT,                         -- Optional text review
  `ReviewDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date and time of the review
  FOREIGN KEY (`AuctionID`) REFERENCES `Auction`(`AuctionID`) ON DELETE CASCADE,
  FOREIGN KEY (`BuyerID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`SellerID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE
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

-- Sample data for SellerReviews table
INSERT INTO `SellerReviews` (`AuctionID`, `BuyerID`, `SellerID`, `Rating`, `ReviewText`) VALUES
(1, 1, 2, 5, 'Excellent seller, very professional!'),
(2, 1, 2, 4, 'Good experience overall.');

COMMIT;
