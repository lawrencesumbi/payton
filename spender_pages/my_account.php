<?php
/* ==========================
   DATABASE CONNECTION
========================== */
$host = "localhost";
$user = "root";
$pass = "";
$db   = "payton";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

/* ==========================
   CHECK LOGIN SESSION
========================== */
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$uid = $_SESSION['user_id'];

/* ==========================
   FETCH USER DATA
========================== */
$sql = "SELECT fullname, email, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$userData = $res->fetch_assoc();

$fullname = $userData['fullname'] ?? "Unknown User";
$email    = $userData['email'] ?? "No email";
$profile  = $userData['profile_pic'] ?? "";

/* ==========================
   PROFILE IMAGE DEFAULT
========================== */
$profilePath = "uploads/default.png";
if (!empty($profile) && file_exists("uploads/" . $profile)) {
  $profilePath = "uploads/" . $profile;
}

/* ==========================
   SIMPLE PROFILE COMPLETION
========================== */
$progress = 0;
if (!empty($fullname)) $progress += 20;
if (!empty($email)) $progress += 20;
if (!empty($profile)) $progress += 20;
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

    /* PAGE PADDING */
    .acc-page{
      width: 100%;
      padding: 25px;
    }

    /* MAIN WRAPPER */
    .acc-wrapper{
      margin-top: 20px;
      background: #f9fafb;
      border-radius: 18px;
      padding: 15px;
      border: 1px solid #eef1f6;
      box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
      max-width: 100%;
      width: 100%;
    }

    /* MAIN 2 COLUMN LAYOUT */
    .acc-layout{
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 18px;
      align-items: start;
      width: 100%;
    }

    /* LEFT SECTION */
    .acc-main{
      
      display: flex;
      flex-direction: column;
      gap: 16px;
      width: 100%;
    }

    .acc-card{
      background: #fff;
      border-radius: 18px;
      padding: 18px;
      box-shadow: 0 18px 45px rgba(0,0,0,0.06);
      border: 1px solid rgba(0,0,0,0.05);
    }

    .acc-card h3{
      font-size: 16px;
      font-weight: 900;
      margin-bottom: 12px;
      color: #111;
    }

    /* Profile Header Card */
    .profile-head{
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
    }

    .profile-left{
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .profile-pic{
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: #ffe7a3;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 20px;
      color: #222;
      overflow: hidden;
    }

    .profile-pic img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .profile-meta h3{
      font-size: 14px;
      font-weight: 900;
      margin-bottom: 4px;
    }

    .profile-meta p{
      font-size: 12px;
      color: #777;
      line-height: 1.4;
    }

    .btn-upload{
      border: 1px solid #e5e7eb;
      background: #fff;
      padding: 10px 14px;
      border-radius: 14px;
      font-weight: 900;
      cursor: pointer;
      transition: 0.2s;
    }

    .btn-upload:hover{
      background: #f6f7fb;
    }

    /* FORM */
    .acc-form{
      margin-top: 14px;
      display: grid;
      gap: 14px;
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
      color: #333;
      margin-bottom: 6px;
    }

    .acc-form input,
    .acc-form textarea{
      width: 100%;
      padding: 12px 14px;
      border-radius: 14px;
      border: 1px solid #ddd;
      outline: none;
      font-size: 13px;
      transition: 0.2s;
    }

    .acc-form input:focus,
    .acc-form textarea:focus{
      border-color: #2563eb;
      box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
    }

    /* PASSWORD BOX */
    .pass-box{
      margin-top: 6px;
      padding: 14px;
      border-radius: 16px;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
    }

    .pass-title{
      font-size: 14px;
      font-weight: 900;
      margin-bottom: 12px;
      color: #111;
    }

    /* Save Button */
    .btn-save-changes{
      padding: 12px 18px;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      font-weight: 900;
      font-size: 14px;
      color: #fff;
      background: #2563eb;
      transition: 0.2s;
      width: fit-content;
    }

    .btn-save-changes:hover{
      background: #1d4ed8;
      transform: translateY(-2px);
    }

    /* RIGHT PANEL */
    .acc-right{
      display: flex;
      flex-direction: column;
      gap: 16px;
      width: 100%;
      position: sticky;
      top: 20px;
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
            <div class="profile-left">

              <div class="profile-pic">
                <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">
              </div>

              <div class="profile-meta">
                <h3>Upload new photo</h3>
                <p>At least 800×800 px recommended.<br>JPG or PNG is allowed</p>
              </div>

            </div>

            <button class="btn-upload" type="button">Upload new photo</button>
          </div>

          <!-- Personal info card -->
          <div class="acc-card">
            <h3>Personal Info</h3>

            <form method="post" action="" enctype="multipart/form-data">
              <div class="acc-form">

                <!-- Row: Fullname + Email -->
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
                  Save changes
                </button>

              </div>
            </form>
          </div>

    <div class="acc-card">
        <!-- Password Container -->
                
                  <h4 class="pass-title">Change Password</h4>

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

                  <button class="btn-save-changes" type="submit" name="save_profile">
                  Update Password
                </button>
                
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
