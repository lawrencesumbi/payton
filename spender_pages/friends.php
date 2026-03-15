<?php
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];
$message = "";

/* =========================================
SEND FRIEND REQUEST
========================================= */
if(isset($_POST['add_friend'])){

    $friend_email = trim($_POST['friend_email']);

    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email=? AND role='spender'");
    $stmt->execute([$friend_email]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$friend){
        $message = "No spender found with that email.";
    } else {

        if($friend['id'] == $user_id){
            $message = "You cannot add yourself.";
        } else {

            $check = $conn->prepare("
                SELECT * FROM spender_friends
                WHERE (requester_id=? AND addressee_id=?)
                OR (requester_id=? AND addressee_id=?)
            ");
            $check->execute([$user_id,$friend['id'],$friend['id'],$user_id]);

            if($check->rowCount() > 0){
                $message = "Friend request already exists.";
            } else {

                $stmt = $conn->prepare("
                    INSERT INTO spender_friends (requester_id, addressee_id)
                    VALUES (?,?)
                ");
                $stmt->execute([$user_id,$friend['id']]);

                $message = "Friend request sent.";
            }
        }
    }
}

/* =========================================
ACCEPT FRIEND REQUEST
========================================= */
if(isset($_POST['accept_request'])){

    $request_id = $_POST['request_id'];

    $stmt = $conn->prepare("
        UPDATE spender_friends
        SET status='accepted'
        WHERE id=? AND addressee_id=?
    ");
    $stmt->execute([$request_id,$user_id]);

    $message = "Friend request accepted.";
}

/* =========================================
DECLINE REQUEST
========================================= */
if(isset($_POST['decline_request'])){

    $request_id = $_POST['request_id'];

    $stmt = $conn->prepare("
        DELETE FROM spender_friends
        WHERE id=? AND addressee_id=?
    ");
    $stmt->execute([$request_id,$user_id]);

    $message = "Friend request declined.";
}

/* =========================================
REMOVE FRIEND
========================================= */
if(isset($_POST['remove_friend'])){

    $friend_id = $_POST['friend_id'];

    $stmt = $conn->prepare("
        DELETE FROM spender_friends
        WHERE 
        (requester_id=? AND addressee_id=?)
        OR
        (requester_id=? AND addressee_id=?)
    ");
    $stmt->execute([$user_id,$friend_id,$friend_id,$user_id]);

    $message = "Friend removed.";
}

/* =========================================
FETCH FRIEND LIST
========================================= */
$stmt = $conn->prepare("
SELECT u.id, u.fullname, u.email
FROM users u
JOIN spender_friends f
ON (u.id = f.requester_id OR u.id = f.addressee_id)
WHERE (f.requester_id=? OR f.addressee_id=?)
AND f.status='accepted'
AND u.id != ?
ORDER BY u.fullname
");
$stmt->execute([$user_id,$user_id,$user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
FETCH PENDING REQUESTS
========================================= */
$stmt = $conn->prepare("
SELECT f.id, u.fullname, u.email
FROM spender_friends f
JOIN users u ON f.requester_id = u.id
WHERE f.addressee_id=? AND f.status='pending'
");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>

<title>Friends</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>

body{
font-family:Inter;
background:#f9fafb;
margin:0;
}

.container{
max-width:900px;
margin:50px auto;
padding:20px;
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.btn{
padding:10px 18px;
border:none;
border-radius:8px;
cursor:pointer;
font-weight:500;
}

.btn-primary{
background:#6f42c1;
color:white;
}

.btn-danger{
background:#fef2f2;
color:#dc2626;
}

.btn-success{
background:#22c55e;
color:white;
}

.card{
background:white;
border-radius:12px;
border:1px solid #e5e7eb;
margin-bottom:20px;
}

table{
width:100%;
border-collapse:collapse;
}

th{
padding:12px;
text-align:left;
background:#fafafa;
font-size:12px;
color:#6b7280;
}

td{
padding:14px;
border-top:1px solid #eee;
}

.alert{
padding:12px;
background:#ecfdf5;
border-radius:8px;
margin-bottom:20px;
}

.modal{
display:none;
position:fixed;
top:0;
left:0;
width:100vw;
height:100vh;
background:rgba(0,0,0,0.6);
z-index:9999;
justify-content:center;
align-items:center;
}

.modal-card{
background:white;
padding:30px;
border-radius:12px;
width:350px;
box-shadow:0 20px 25px rgba(0,0,0,0.2);
}

.input-box{
width:100%;
padding:10px;
margin:15px 0;
border:1px solid #ddd;
border-radius:8px;
}

</style>
</head>

<body>

<div class="container">

<div class="header">
<h2>Friends</h2>

<button class="btn btn-primary" onclick="openModal()">+ Add Friend</button>
</div>

<?php if(!empty($message)): ?>

<div class="alert">
<?php echo htmlspecialchars($message); ?>
</div>

<?php endif; ?>


<!-- FRIEND REQUESTS -->

<?php if(!empty($requests)): ?>

<div class="card">

<table>

<thead>
<tr>
<th>Friend Requests</th>
<th>Email</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($requests as $r): ?>

<tr>

<td><?php echo htmlspecialchars($r['fullname']); ?></td>

<td><?php echo htmlspecialchars($r['email']); ?></td>

<td>

<form method="POST" style="display:inline;">
<input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
<button name="accept_request" class="btn btn-success">Accept</button>
</form>

<form method="POST" style="display:inline;">
<input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
<button name="decline_request" class="btn btn-danger">Decline</button>
</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>


<!-- FRIEND LIST -->

<div class="card">

<table>

<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php if(!empty($friends)): ?>

<?php foreach($friends as $f): ?>

<tr>

<td><?php echo htmlspecialchars($f['fullname']); ?></td>

<td><?php echo htmlspecialchars($f['email']); ?></td>

<td>

<form method="POST">

<input type="hidden" name="friend_id" value="<?php echo $f['id']; ?>">

<button name="remove_friend" class="btn btn-danger">

Remove

</button>

</form>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="3" style="text-align:center;color:gray">

No friends yet.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>


<!-- ADD FRIEND MODAL -->

<div id="modal" class="modal">

<div class="modal-card">

<h3>Add Friend</h3>

<form method="POST">

<input type="email" name="friend_email" placeholder="Enter friend email" class="input-box" required>

<button name="add_friend" class="btn btn-primary">

Send Request

</button>

<button type="button" onclick="closeModal()" class="btn">

Cancel

</button>

</form>

</div>

</div>


<script>

function openModal(){
document.getElementById("modal").style.display="flex";
}

function closeModal(){
document.getElementById("modal").style.display="none";
}

</script>

</body>
</html>