<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "payton";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$uid = $_SESSION['user_id'];

$sql = "SELECT fullname, email, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$userData = $res->fetch_assoc();

$fullname = $userData['fullname'] ?? "Unknown User";
$email    = $userData['email'] ?? "No email";
$profile  = $userData['profile_pic'] ?? "";

$profilePath = "uploads/default.png";
if (!empty($profile) && file_exists("uploads/" . $profile)) {
  $profilePath = "uploads/" . $profile;
}

$progress = 0;
if (!empty($fullname)) $progress += 20;
if (!empty($email)) $progress += 20;
if (!empty($profile)) $progress += 20;

/* ==========================
   UPDATE PERSONAL INFO
========================== */
if (isset($_POST['save_profile'])) {

  $newFullname = trim($_POST['fullname']);
  $newEmail    = trim($_POST['email']);
  $uid         = $_SESSION['user_id'];

  if (!empty($newFullname) && !empty($newEmail)) {

    $sql = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $newFullname, $newEmail, $uid);
    $stmt->execute();

    // Optional success message
    echo "<script>
      alert('Personal information updated successfully');
      window.location.href = 'login.php';
    </script>";
  }
}

/* ==========================
   UPDATE PASSWORD
========================== */
if (isset($_POST['update_password'])) {

  $oldPassword = $_POST['old_password'];
  $newPassword = $_POST['new_password'];
  $uid         = $_SESSION['user_id'];

  // Get current password from DB
  $sql = "SELECT password FROM users WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  // Verify old password
  if (!$user || !password_verify($oldPassword, $user['password'])) {

    echo "<script>alert('Old password is incorrect');</script>";

  } else {

    // Hash new password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->bind_param("si", $newHash, $uid);
    $update->execute();

    echo "<script>
      alert('Password updated successfully');
      window.location.href = 'login.php';
    </script>";
  }
}


/* ==========================
   UPDATE PROFILE PHOTO
========================== */
if (isset($_POST['update_photo']) && !empty($_FILES['profile_pic']['name'])) {

    $uid = $_SESSION['user_id'];

    $fileName = $_FILES['profile_pic']['name'];
    $tmpName  = $_FILES['profile_pic']['tmp_name'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg','jpeg','png'];
    if (!in_array($fileExt, $allowed)) {
        echo "<script>alert('Only JPG and PNG files are allowed');</script>";
        return;
    }

    // Create folder if it doesn't exist
    $uploadDir = "profile/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $newName = time() . "_" . uniqid() . "." . $fileExt;
    $uploadPath = $uploadDir . $newName;

    // Get old profile_pic path
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();

    // Delete old photo if exists
    if (!empty($old['profile_pic']) && file_exists($old['profile_pic'])) {
        unlink($old['profile_pic']);
    }

    // Move uploaded file
    if (move_uploaded_file($tmpName, $uploadPath)) {

        // Store only the **path** in the database
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $uploadPath, $uid);
        $stmt->execute();

        echo "<script>
            alert('Profile photo updated successfully');
            window.location.href = 'login.php';
        </script>";

    } else {
        echo "<script>alert('Failed to upload file. Check folder permissions.');</script>";
    }
}



/* ==========================
   DISPLAY PROFILE PHOTO
========================== */
/* ===== FETCH USER DATA ===== */
$sql = "SELECT fullname, email, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$userData = $res->fetch_assoc();

// Set profile path, default if empty
$profilePath = !empty($userData['profile_pic']) && file_exists($userData['profile_pic'])
    ? $userData['profile_pic']
    : "profile/default.png";

$fullname = $userData['fullname'] ?? "Unknown User";
$email    = $userData['email'] ?? "No email";
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account Settings</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* =========================
       GLOBAL
    ========================= */
    *{
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body{
      background: #f5f7fb;
    }

    /* PAGE PADDING */
    .acc-page{
      
      width: 100%;
      
    }

    /* MAIN WRAPPER */
    .acc-wrapper{

      
      
      border-radius: 22px;
      
      
      
      width: 100%;
    }

    /* MAIN 2 COLUMN LAYOUT */
    .acc-layout{
      
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 20px;
      align-items: start;
      width: 100%;
    }

    /* LEFT SECTION */
    .acc-main{
        
      display: flex;
      flex-direction: column;
      gap: 18px;
      width: 100%;
      
    }

    /* RIGHT SECTION */
    .acc-right{
      display: flex;
      flex-direction: column;
      gap: 16px;
      width: 100%;
      position: sticky;
      top: 25px;
      
    }

    /* CARD */
    .acc-card{
      background: #fff;
      border-radius: 20px;
      padding: 22px;
      box-shadow: 0 18px 45px rgba(0,0,0,0.06);
      border: 1px solid rgba(0,0,0,0.05);
    }

    .acc-card h3{
      font-size: 15px;
      font-weight: 900;
      margin-bottom: 14px;
      color: #111;
    }

    /* =========================
       PROFILE HEADER
    ========================= */
    .profile-head{
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
    }

    .profile-left{
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .profile-pic{
      width: 92px;
      height: 92px;
      border-radius: 50%;
      background: #e5e7eb;
      overflow: hidden;
      border: 3px solid #fff;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      flex-shrink: 0;
    }

    .profile-pic img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .profile-meta h4{
      font-size: 15px;
      font-weight: 900;
      margin-bottom: 6px;
      color: #111;
    }

    .profile-meta p{
      font-size: 13px;
      color: #6b7280;
      line-height: 1.4;
    }

    .btn-upload{
      border: 1px solid #e5e7eb;
      background: #9225eb;
      padding: 12px 18px;
      border-radius: 14px;
      font-weight: 900;
      cursor: pointer;
      transition: 0.2s;
      color: #fff;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
    }

    .btn-upload:hover{
      background: #be86eb;
      transform: translateY(-2px);
    }

    /* =========================
       FORM
    ========================= */
    .acc-form{
      display: grid;
      gap: 16px;
      margin-top: 4px;
    }

    .acc-row{
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .acc-form label{
      display: block;
      font-size: 12px;
      font-weight: 900;
      color: #374151;
      margin-bottom: 6px;
    }

    .acc-form input{
      width: 100%;
      padding: 13px 14px;
      border-radius: 14px;
      border: 1px solid #e5e7eb;
      outline: none;
      font-size: 13px;
      transition: 0.2s;
      background: #f9fafb;
    }

    .acc-form input:focus{
      border-color: #2563eb;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
    }

    /* BUTTON */
    .btn-save-changes{
      padding: 12px 18px;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      font-weight: 900;
      font-size: 14px;
      color: #fff;
      background: #9225eb;
      transition: 0.2s;
      width: fit-content;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-save-changes:hover{
      background: #b358dd;
      transform: translateY(-2px);
    }

    /* =========================
       PROGRESS CIRCLE
    ========================= */
    .progress-circle{
      width: 160px;
      height: 160px;
      border-radius: 50%;
      margin: 0 auto 18px;
      background: conic-gradient(#7c3aed <?= (int)$progress ?>%, #ebe5eb  0);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .progress-circle span{
      width: 115px;
      height: 115px;
      background: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: 900;
      color: #111;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .progress-list{
      display: grid;
      gap: 12px;
      font-size: 13px;
      color: #444;
    }

    .progress-item{
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .done{
      color: #7b0c85;
      font-weight: 900;
    }

    /* RESPONSIVE */
    @media(max-width: 980px){
      .acc-layout{
        grid-template-columns: 1fr;
      }

      .acc-right{
        position: relative;
        top: 0;
      }

      .acc-row{
        grid-template-columns: 1fr;
      }

      .profile-head{
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>

<body>

<div class="acc-page">
  <div class="acc-wrapper">
    <div class="acc-layout">

      <!-- LEFT -->
      <section class="acc-main">

        <!-- Profile photo card -->
<div class="acc-card profile-head">
  <form method="post" enctype="multipart/form-data">

    <div class="profile-left">
      <div class="profile-pic">
    <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">
</div>

      <div class="profile-meta">
        <h4>Upload new photo</h4>
        <p>At least 800×800 px recommended.<br>JPG or PNG is allowed</p>
      </div>
    </div>

    <!-- Hidden file input -->
    <input type="file" name="profile_pic" id="profilePicInput" hidden accept=".jpg,.jpeg,.png">

    <button class="btn-upload" type="button"
      onclick="document.getElementById('profilePicInput').click()">
      <i class="fa-solid fa-cloud-arrow-up"></i>
      Upload Photo
    </button>

    <button class="btn-save-changes" type="submit" name="update_photo">
      Save Photo
    </button>

  </form>
</div>


        <!-- Personal info card -->
        <div class="acc-card">
          <h3>Personal Info</h3>

          <form method="post" action="" enctype="multipart/form-data">
            <div class="acc-form">

              <div class="acc-row">
                <div>
                  <label>Full Name</label>
                  <input type="text" name="fullname" value="<?= htmlspecialchars($fullname) ?>">
                </div>

                <div>
                  <label>Email</label>
                  <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                </div>
              </div>

              <button class="btn-save-changes" type="submit" name="save_profile">
                <i class="fa-solid fa-floppy-disk"></i>
                Save changes
              </button>

            </div>
          </form>
        </div>

        <!-- Change password card -->
        <div class="acc-card">
          <h3>Change Password</h3>

          <form method="post" action="">
            <div class="acc-form">

              <div class="acc-row">
                <div>
                  <label>Old Password</label>
                  <input type="password" name="old_password" placeholder="Enter old password">
                </div>

                <div>
                  <label>New Password</label>
                  <input type="password" name="new_password" placeholder="Enter new password">
                </div>
              </div>

              <button class="btn-save-changes" type="submit" name="update_password">
                <i class="fa-solid fa-key"></i>
                Update Password
              </button>

            </div>
          </form>
        </div>

      </section>

      <!-- RIGHT -->
      <aside class="acc-right">
        <div class="acc-card progress-card">
          <h3>Complete your profile</h3>

          <div class="progress-circle">
            <span><?= (int)$progress ?>%</span>
          </div>

          <div class="progress-list">
            <div class="progress-item">
              <span><i class="fa-solid fa-check done"></i> Setup account</span>
              <b class="done">10%</b>
            </div>

            <div class="progress-item">
              <span><i class="fa-solid fa-check done"></i> Upload your photo</span>
              <b class="done">5%</b>
            </div>

            <div class="progress-item">
              <span><i class="fa-solid fa-check done"></i> Personal info</span>
              <b class="done">10%</b>
            </div>
          </div>

        </div>
      </aside>

    </div>
  </div>
</div>

</body>
</html>