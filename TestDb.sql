DROP DATABASE IF EXISTS AuctionDB;
CREATE DATABASE AuctionDB;
USE AuctionDB;

CREATE TABLE `Category` (
    CategoryID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    CategoryName varchar(255)
);

CREATE TABLE `Users` (  
    UserID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name varchar(255),
    Email varchar(255),
    Password varchar(255),
    Role varchar(255)
);

CREATE TABLE `Auction` (
    AuctionID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    UserID int,
    ItemName varchar(255),
    Description varchar(255),
    CategoryID int NOT NULL,
    ReservePrice float,
    StartPrice float,
    EndDate varchar(255),
    FOREIGN KEY (UserID) REFERENCES Users(UserID),  
    FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID)
);

CREATE TABLE `Bid` (
    BidID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    AuctionID int NOT NULL,
    UserID int NOT NULL,
    BidAmount float,
    BidLength int,
    FOREIGN KEY (AuctionID) REFERENCES Auction(AuctionID),  
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE `WatchList` (
    WatchID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    AuctionID int NOT NULL,
    UserID int NOT NULL,
    FOREIGN KEY (AuctionID) REFERENCES Auction(AuctionID),  
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE `Notification` (
    NotificationID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    AuctionID int NOT NULL,
    UserID int NOT NULL,
    NotificationTime DATETIME DEFAULT CURRENT_TIMESTAMP,
    NotificationType varchar(255),
    Message TEXT,
    IsRead BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (AuctionID) REFERENCES Auction(AuctionID),  
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

CREATE TABLE `NotificationPreferences` (
    UserID INT PRIMARY KEY,
    OutbidNotify BOOLEAN DEFAULT TRUE,
    AuctionEndNotify BOOLEAN DEFAULT TRUE,
    EmailNotifications BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Insert default preferences for existing users
INSERT INTO NotificationPreferences (UserID)
SELECT UserID FROM Users;

-- Insert sample categories
INSERT INTO Category (CategoryName) VALUES ('Electronics'), ('Furniture'), ('Clothing'), ('Books'), ('Toys');
