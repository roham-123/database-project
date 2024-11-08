<?php

function createNotification($conn, $userID, $auctionID, $message, $notificationType) {
    $stmt = $conn->prepare("INSERT INTO Notification (UserID, AuctionID, Message, NotificationType) 
                           VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $userID, $auctionID, $message, $notificationType);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function getUnreadNotifications($conn, $userID) {
    $stmt = $conn->prepare("SELECT n.*, a.ItemName 
                           FROM Notification n
                           JOIN Auction a ON n.AuctionID = a.AuctionID
                           WHERE n.UserID = ? AND n.IsRead = FALSE
                           ORDER BY n.NotificationTime DESC");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $notifications;
}

function markNotificationAsRead($conn, $notificationID, $userID) {
    $stmt = $conn->prepare("UPDATE Notification SET IsRead = TRUE 
                           WHERE NotificationID = ? AND UserID = ?");
    $stmt->bind_param("ii", $notificationID, $userID);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>