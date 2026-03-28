<?php
session_start();
require_once "db.php";
include "log_helper.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];


/* ADD PERSON */
if(isset($_POST['add_person'])){
    $name = trim($_POST['person_name']);

    if(!empty($name)){
        $stmt = $conn->prepare("INSERT INTO people (user_id, name) VALUES (?, ?)");
        
        if($stmt->execute([$user_id, $name])){
            $_SESSION['success_msg'] = "Person added successfully.";
        } else {
            $_SESSION['error_msg'] = "Error adding person.";
        }
    } else {
        $_SESSION['error_msg'] = "Please enter a name.";
    }

    $logAction = $_SESSION['fullname'] . " Added a Person: $name";
    addLog($conn, $user_id, $logAction);
}


/* DELETE PERSON */
if(isset($_POST['delete_person'])){
    $person_id = $_POST['person_id'];

    $stmt = $conn->prepare("DELETE FROM people WHERE id = ? AND user_id = ?");
    $stmt->execute([$person_id, $user_id]);

    $_SESSION['success_msg'] = "Person removed.";

    $logAction = $_SESSION['fullname'] . " Deleted a Person: $name";
    addLog($conn, $user_id, $logAction);
}


// ✅ Redirect back to main page
header("Location: http://localhost/payton/spender.php?page=people");
exit();