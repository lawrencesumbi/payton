<?php
// Keeping your existing logic intact
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "payton");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ADDED 'phone' to the SELECT
$stmt = $conn->prepare("SELECT fullname, email, phone, profile_pic, password FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

$fullname = $userData['fullname'] ?? "Unknown User";
$email    = $userData['email'] ?? "No email";
$phone    = $userData['phone'] ?? ""; // Added phone variable
$profile  = $userData['profile_pic'] ?? "";
$profilePath = !empty($profile) && file_exists($profile) ? $profile : "profile/default.jpg";

// --- ADDED PHILIPPINES PHONE VALIDATION LOGIC ---
$is_valid_ph = preg_match('/^(09|\+639)\d{9}$/', $phone);

// Calculate Progress (Updated to include Phone)
$progress = 10; 

if (!empty($fullname)) $progress += 20;
if (!empty($email))    $progress += 20;
if ($is_valid_ph)      $progress += 20; // 20% for Valid PH Phone
if (!empty($profile))  $progress += 15;
if (!empty($userData['password'])) $progress += 15; 

$progress = min($progress, 100);

// UPDATE PERSONAL INFO
if (isset($_POST['save_profile'])) {
    $newFullname = trim($_POST['fullname']);
    $newEmail    = trim($_POST['email']);
    $newPhone    = trim($_POST['phone']); // Capture Phone

    // Validation for PH Phone
    if (!preg_match('/^(09|\+639)\d{9}$/', $newPhone)) {
         $_SESSION['msg'] = ["type"=>"error", "text"=>"Please enter a valid PH phone number"];
    } elseif ($newFullname && $newEmail) {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $newFullname, $newEmail, $newPhone, $uid);
        $stmt->execute();
        $_SESSION['msg'] = ["type"=>"success", "text"=>"Personal information updated!"];
        echo "<script>window.location.href = 'spender.php?page=my_account';</script>";
        exit();
    }
}

// UPDATE PASSWORD (Original logic)
if (isset($_POST['update_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    if (!$userData || !password_verify($oldPassword, $userData['password'])) {
        $_SESSION['msg'] = ["type"=>"error", "text"=>"Old password is incorrect"];
    } else {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newHash, $uid);
        $stmt->execute();
        $_SESSION['msg'] = ["type"=>"success", "text"=>"Password updated!"];
        echo "<script>window.location.href = 'login.php';</script>";
        exit();
    }
}

// UPDATE PHOTO (Original logic)
if (isset($_POST['update_photo']) && !empty($_FILES['profile_pic']['name'])) {
    $file = $_FILES['profile_pic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed)) {
        $_SESSION['msg'] = ["type"=>"error", "text"=>"Only JPG and PNG allowed"];
    } else {
        $uploadDir = "profile/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $newName = time() . "_" . uniqid() . "." . $ext;
        $uploadPath = $uploadDir . $newName;
        if (!empty($profile) && file_exists($profile)) unlink($profile);
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->bind_param("si", $uploadPath, $uid);
            $stmt->execute();
            $_SESSION['msg'] = ["type"=>"success", "text"=>"Profile photo updated!"];
            echo "<script>window.location.href = 'spender.php?page=my_account';</script>";
            exit();
        }
    }
}

// REMOVE PHOTO (Original logic)
if (isset($_POST['remove_photo'])) {
    if (!empty($profile) && file_exists($profile)) unlink($profile); 
    $stmt = $conn->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $_SESSION['msg'] = ["type"=>"success", "text"=>"Profile photo removed"];
    echo "<script>window.location.href = 'spender.php?page=my_account';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Account Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #7f00d4;
            --primary-hover: #943acf;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --input-bg: #ffffff;
            --radius: 16px;
            --shadow: rgba(0,0,0,0.05);
            --success: #10b981;
            --danger: #ef4444;
        }

        [data-theme="dark"] {
            --bg: #12141a;
            --bg-card: #191c24;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #2a2e39;
            --input-bg: #1f232d;
            --shadow: rgba(0,0,0,0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text-main); transition: background 0.3s ease;}
        
        html, body { height: 100%; -ms-overflow-style: none; scrollbar-width: none; }
        html::-webkit-scrollbar, body::-webkit-scrollbar { display: none; width: 0 !important; height: 0 !important; }

        .acc-container { width: 100%;}
        .acc-header { margin-bottom: 24px; }
        .acc-layout { display: grid; grid-template-columns: 1fr 320px; gap: 24px;}
        
        .acc-card { 
            background: var(--bg-card); border-radius: var(--radius); padding: 18px; 
            border: 1px solid var(--border); box-shadow: 0 4px 6px -1px var(--shadow);
            margin-bottom: 10px; transition: background 0.3s ease;
        }

        .acc-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        .profile-section { display: flex; align-items: center; gap: 24px; padding-bottom: 20px; }
        .profile-pic-container { position: relative; width: 100px; height: 100px; }
        .profile-pic-container img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid var(--bg-card); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        
        .profile-info-meta h4 { font-size: 18px; font-weight: 700; }
        .profile-info-meta p { color: var(--text-muted); font-size: 13px; margin-top: 4px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main); }
        .form-group input { 
            width: 100%; padding: 12px 16px; border-radius: 10px; border: 1px solid var(--border); 
            background: var(--input-bg); color: var(--text-main); transition: all 0.2s; font-size: 14px; 
        }
        .form-group input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(127, 0, 212, 0.1); }

        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; border: none;}
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-main); }

        .progress-card { text-align: center; position: sticky; top: 20px; }
        .circular-progress { 
            width: 120px; height: 120px; border-radius: 50%; margin: 20px auto;
            background: radial-gradient(closest-side, var(--bg-card) 79%, transparent 80% 100%),
                        conic-gradient(var(--primary) <?= (int)$progress ?>%, var(--border) 0);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 800; color: var(--text-main);
        }

        .progress-items { text-align: left; margin-top: 20px; }
        .p-item { display: flex; align-items: center; justify-content: space-between; font-size: 13px; margin-bottom: 12px; }
        .status-icon.done { color: var(--success); }
        .status-icon.missing { color: var(--danger); }

        /* MODERN NOTIFICATION TOAST */
        #notif-toast {
            position: fixed; top: 20px; right: 20px; 
            padding: 16px 24px; border-radius: 12px;
            background: var(--bg-card); color: var(--text-main);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: flex; align-items: center; gap: 12px;
            z-index: 9999; border-left: 6px solid var(--primary);
            transform: translateX(150%); transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        #notif-toast.show { transform: translateX(0); }
        #notif-toast i { font-size: 20px; }

        @media (max-width: 850px) { .acc-layout { grid-template-columns: 1fr; } .form-grid { grid-template-columns: 1fr; } .profile-section { flex-direction: column; text-align: center; } }
    </style>
</head>
<body>

<div id="notif-toast">
    <i id="notif-icon"></i>
    <span id="notif-msg"></span>
</div>

<div class="acc-container">
    <div class="acc-layout">
        <main>
            <div class="acc-card">
                <form method="post" enctype="multipart/form-data">
                    <div class="profile-section">
                        <div class="profile-pic-container">
                            <img id="profilePreview" src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">
                        </div>
                        <div class="profile-info-meta">
                            <h4>Profile Picture</h4>
                            <p>PNG or JPG. Recommended size 800x800px.</p>
                            <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                                    <input type="file" name="profile_pic" id="profilePicInput" hidden accept=".jpg,.jpeg,.png">
                                    <button type="button" class="btn btn-outline" onclick="document.getElementById('profilePicInput').click()">
                                        <i class="fa-solid fa-camera"></i> Change Photo
                                    </button>
                                    <button type="submit" name="update_photo" class="btn btn-primary">Save</button>

                                <?php if (!empty($profile)): ?>
                                <button type="submit" name="remove_photo" class="btn btn-outline" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.3);">
                                    <i class="fa-solid fa-trash-can"></i> Remove
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="acc-card">
                <h3><i class="fa-solid fa-user-gear"></i> Personal Information</h3>
                <form method="post">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" value="<?= htmlspecialchars($fullname) ?>" placeholder="Your Name">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="name@company.com">
                        </div>
                        <div class="form-group">
                            <label>Phone Number (Philippines)</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="+639xxxxxxxxx or 09xxxxxxxxx">
                        </div>
                    </div>
                    <button type="submit" name="save_profile" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </form>
            </div>

            <div class="acc-card">
                <h3><i class="fa-solid fa-shield-halved"></i> Security</h3>
                <form method="post">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="old_password" placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" placeholder="••••••••">
                        </div>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-primary">
                        <i class="fa-solid fa-lock"></i> Update Password
                    </button>
                </form>
            </div>
        </main>

        <aside>
            <div class="acc-card progress-card">
                <h3>Profile Completion</h3>
                <div class="circular-progress"><?= (int)$progress ?>%</div>
                
                <div class="progress-items">
                    <div class="p-item">
                        <span>Account Setup</span> 
                        <i class="fa-solid fa-circle-check status-icon done"></i>
                    </div>
                    <div class="p-item">
                        <span>Profile Image</span> 
                        <i class="fa-solid <?= !empty($profile) ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i>
                    </div>
                    <div class="p-item">
                        <span>Personal Info</span> 
                        <i class="fa-solid <?= (!empty($fullname) && !empty($email)) ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i>
                    </div>
                    <div class="p-item">
                        <span>PH Phone Number</span> 
                        <i class="fa-solid <?= $is_valid_ph ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i>
                    </div>
                    <div class="p-item">
                        <span>Security Set</span> 
                        <i class="fa-solid <?= !empty($userData['password']) ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i>
                    </div>
                </div>
            </div>
            <div class="acc-card" style="margin-top: 20px;">
                <h3 style="font-size: 14px; color: #00bcd4;">
                    <i class="fa-solid fa-lightbulb"></i> Security Tip
                </h3>
                <p style="font-size: 12px; color: var(--text-muted-local); line-height: 1.6;">
                    Use a unique password for Payton to keep your financial data safe. Enabling 2FA is coming soon!
                </p>
                <p style="font-size: 12px; color: var(--text-muted-local); line-height: 1.6; margin-top: 20px; margin-bottom: 20px;">
                    Always click <b>Logout</b> when you're finished to clear your session tokens.
                </p>
            </div>
        </aside>
    </div>
</div>

<script>
    // PROFILE PHOTO PREVIEW
    document.getElementById('profilePicInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('profilePreview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // TOAST LOGIC
    function showNotif(text, type) {
        const toast = document.getElementById('notif-toast');
        const icon = document.getElementById('notif-icon');
        const msg = document.getElementById('notif-msg');

        msg.innerText = text;
        toast.style.borderLeftColor = (type === 'success') ? '#10b981' : '#ef4444';
        icon.className = (type === 'success') ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';
        icon.style.color = (type === 'success') ? '#10b981' : '#ef4444';

        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }

    <?php if (isset($_SESSION['msg'])): ?>
        showNotif("<?= $_SESSION['msg']['text'] ?>", "<?= $_SESSION['msg']['type'] ?>");
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>
</script>
</body>
</html>