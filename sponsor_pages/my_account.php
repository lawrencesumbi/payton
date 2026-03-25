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

$stmt = $conn->prepare("SELECT fullname, email, profile_pic, password FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

$fullname = $userData['fullname'] ?? "Unknown User";
$email    = $userData['email'] ?? "No email";
$profile  = $userData['profile_pic'] ?? "";
$profilePath = !empty($profile) && file_exists($profile) ? $profile : "profile/default.jpg";

// Calculate Progress
$progress = 10; 
if (!empty($fullname)) $progress += 25;
if (!empty($email))    $progress += 25;
if (!empty($profile))  $progress += 20;
if (!empty($userData['password'])) $progress += 20;
$progress = min($progress, 100);

// UPDATE PERSONAL INFO
if (isset($_POST['save_profile'])) {
    $newFullname = trim($_POST['fullname']);
    $newEmail    = trim($_POST['email']);
    if ($newFullname && $newEmail) {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newFullname, $newEmail, $uid);
        $stmt->execute();
        echo "<script>alert('Personal information updated successfully'); window.location.href = 'spender.php?page=my_account';</script>";
    }
}

// UPDATE PASSWORD
if (isset($_POST['update_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    if (!$userData || !password_verify($oldPassword, $userData['password'])) {
        echo "<script>alert('Old password is incorrect');</script>";
    } else {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newHash, $uid);
        $stmt->execute();
        echo "<script>alert('Password updated successfully'); window.location.href = 'login.php';</script>";
    }
}

// UPDATE PHOTO
if (isset($_POST['update_photo']) && !empty($_FILES['profile_pic']['name'])) {
    $file = $_FILES['profile_pic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed)) {
        echo "<script>alert('Only JPG and PNG files are allowed');</script>";
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
            echo "<script>alert('Profile photo updated successfully'); window.location.href = 'spender.php?page=my_account';</script>";
        }
    }
}

// REMOVE PHOTO
if (isset($_POST['remove_photo'])) {
    if (!empty($profile) && file_exists($profile)) {
        unlink($profile);
    }
    $stmt = $conn->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    echo "<script>alert('Profile photo removed'); window.location.href = 'spender.php?page=my_account';</script>";
}
?>

<style>
    /* Use the existing variables from sponsor.php/spender.php */
    :root {
        --primary: #8f2cd1;;
        --primary-hover: #943acf;
        --bg-card-local: var(--bg-card, #ffffff);
        --text-main-local: var(--text-main, #111827);
        --text-muted-local: var(--text-muted, #6b7280);
        --border-local: var(--border-color, #eeeeee);
        --input-bg-local: var(--hover-bg, #ffffff);
    }

    /* Local overrides for dark mode if variables aren't defined globally */
    [data-theme="dark"] {
        --bg-card-local: #191c24;
        --text-main-local: #f8fafc;
        --text-muted-local: #94a3b8;
        --border-local: #2a2e39;
        --input-bg-local: #242833;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    
    /* Ensure the container itself is transparent to show the main app background */
    .acc-container { width: 100%; margin: 0 auto; background: transparent; color: var(--text-main-local); }
    
    .acc-header h1 { font-size: 24px; font-weight: 800; color: var(--text-main-local); margin-bottom: 24px; }

    .acc-layout { display: grid; grid-template-columns: 1fr 320px; gap: 24px; }
    
    .acc-card { 
        background: var(--bg-card-local); 
        border-radius: 16px; 
        padding: 18px; 
        border: 1px solid var(--border-local);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .acc-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--text-main-local); }

    .profile-section { display: flex; align-items: center; gap: 24px; padding-bottom: 20px;}
    .profile-pic-container { position: relative; width: 100px; height: 100px; }
    .profile-pic-container img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid var(--bg-card-local); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    
    .profile-info-meta h4 { font-size: 18px; font-weight: 700; color: var(--text-main-local); }
    .profile-info-meta p { color: var(--text-muted-local); font-size: 13px; margin-top: 4px; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main-local); }
    .form-group input { 
        width: 100%; padding: 12px 16px; border-radius: 10px; border: 1px solid var(--border-local); 
        background: var(--input-bg-local); color: var(--text-main-local); transition: all 0.2s; font-size: 14px; 
    }
    .form-group input:focus { border-color: var(--primary); outline: none; }

    .btn { 
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; 
        border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; 
        transition: all 0.2s; border: none;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-outline { background: var(--input-bg-local); border: 1px solid var(--border-local); color: var(--text-main-local); }
    .btn-outline:hover { background: var(--border-local); }

    .progress-card { text-align: center; position: sticky; top: 20px; }
    .circular-progress { 
        width: 150px; height: 150px; border-radius: 50%; margin: 20px auto;
        /* Center of progress bar matches card background */
        background: radial-gradient(closest-side, var(--bg-card-local) 79%, transparent 80% 100%),
                    conic-gradient(var(--primary) <?= (int)$progress ?>%, var(--border-local) 0);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 800; color: var(--text-main-local);
    }

    .p-item { display: flex; align-items: center; justify-content: space-between; font-size: 13px; margin-bottom: 12px; color: var(--text-muted-local); }
    .p-item i { color: #10b981; margin-right: 8px; }

    @media (max-width: 850px) {
        .acc-layout { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
        .profile-section { flex-direction: column; text-align: center; }
    }

    
    
</style>

<div class="acc-container">
    <div class="acc-layout">
        <main>
            <div class="acc-card">
                <div class="profile-section">
                    <div class="profile-pic-container">
                        <img id="profilePreview" src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">
                    </div>
                    <div class="profile-info-meta">
                        <h4>Profile Picture</h4>
                        <p>PNG or JPG. Recommended size 800x800px.</p>
                        <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                            <form method="post" enctype="multipart/form-data" id="photoForm" style="display: contents;">
                                <input type="file" name="profile_pic" id="profilePicInput" hidden accept=".jpg,.jpeg,.png">
                                <button type="button" class="btn btn-outline" onclick="document.getElementById('profilePicInput').click()">
                                    <i class="fa-solid fa-camera"></i> Change Photo
                                </button>
                                <button type="submit" name="update_photo" class="btn btn-primary">Save</button>
                            </form>

                            <?php if (!empty($profile)): ?>
                            <form method="post" style="display: contents;" onsubmit="return confirm('Are you sure you want to remove your profile picture?');">
                                <button type="submit" name="remove_photo" class="btn btn-outline" style="color: #dc2626; border-color: #fca5a5;">
                                    <i class="fa-solid fa-trash-can"></i> Remove
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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
                        <span><i class="fa-solid <?= $progress >= 10 ? 'fa-circle-check' : 'fa-circle' ?>"></i> Account Setup</span> 
                        <b>10%</b>
                    </div>
                    <div class="p-item">
                        <span><i class="fa-solid <?= !empty($profile) ? 'fa-circle-check' : 'fa-circle' ?>"></i> Profile Image</span> 
                        <b>20%</b>
                    </div>
                    <div class="p-item">
                        <span><i class="fa-solid <?= (!empty($fullname) && !empty($email)) ? 'fa-circle-check' : 'fa-circle' ?>"></i> Personal Info</span> 
                        <b>50%</b>
                    </div>
                    <div class="p-item">
                        <span><i class="fa-solid <?= !empty($userData['password']) ? 'fa-circle-check' : 'fa-circle' ?>"></i> Security Set</span> 
                        <b>20%</b>
                    </div>
                </div>
            </div>
            <div class="acc-card" style="margin-top: 20px;">
                <h3 style="font-size: 14px; color: var(--accent-cyan);">
                    <i class="fa-solid fa-lightbulb"></i> Security Tip
                </h3>
                <p style="font-size: 12px; color: var(--text-muted-local); line-height: 1.6;">
                    Use a unique password for Payton to keep your financial data safe. Enabling 2FA is coming soon!
                </p>
                <p style="font-size: 12px; color: var(--text-muted-local); line-height: 1.6; margin-top: 20px; margin-bottom: 20px;">
                    Always click <b>Logout</b> when you're finished to clear your session tokens from the browser.
            </div>
        </aside>
    </div>
</div>

<script>
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
</script>