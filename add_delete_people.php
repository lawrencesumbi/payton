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
    $email = trim($_POST['person_email']); 

    if(!empty($name)){
        $stmt = $conn->prepare("INSERT INTO people (user_id, name, email) VALUES (?, ?, ?)");
        
        if($stmt->execute([$user_id, $name, $email])){
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

/* EDIT PERSON */
if (isset($_POST['edit_person'])) {
    $id = $_POST['person_id'];
    $name = $_POST['person_name'];
    $email = $_POST['person_email'];

    $stmt = $conn->prepare("UPDATE people SET name = ?, email = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$name, $email, $id, $_SESSION['user_id']])) {
        $_SESSION['success_msg'] = "Friend updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to update friend.";
    }

    $logAction = $_SESSION['fullname'] . " Updated a Person: $name";
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
header("Location: spender.php?page=friends");
exit();