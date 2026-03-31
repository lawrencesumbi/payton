<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "payton");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT fullname, email, phone, profile_pic, password FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

$fullname = $userData['fullname'] ?? "Unknown User";
$email    = $userData['email'] ?? "No email";
$phone    = $userData['phone'] ?? ""; 
$profile  = $userData['profile_pic'] ?? "";
$profilePath = !empty($profile) && file_exists($profile) ? $profile : "profile/default.jpg";

$is_valid_ph = preg_match('/^(09|\+639)\d{9}$/', $phone);

$progress = 10; 
if (!empty($fullname)) $progress += 20;
if (!empty($email))    $progress += 20;
if ($is_valid_ph)      $progress += 20; 
if (!empty($profile))  $progress += 15;
if (!empty($userData['password'])) $progress += 15; 
$progress = min($progress, 100);

// UPDATE PERSONAL INFO (Stays on Sponsor/Spender page)
if (isset($_POST['save_profile'])) {
    $newFullname = trim($_POST['fullname']);
    $newEmail    = trim($_POST['email']);
    $newPhone    = trim($_POST['phone']); 

    if (!preg_match('/^(09|\+639)\d{9}$/', $newPhone)) {
        $_SESSION['msg'] = ["type"=>"error", "text"=>"Please enter a valid PH phone number"];
    } elseif ($newFullname && $newEmail) {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $newFullname, $newEmail, $newPhone, $uid);
        $stmt->execute();
        $_SESSION['msg'] = ["type"=>"success", "text"=>"Personal information updated!"];
        echo "<script>window.location.href = 'sponsor.php?page=my_account';</script>";
        exit();
    }
}

// UPDATE PASSWORD (Redirects to Login)
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
        $_SESSION['msg'] = ["type"=>"success", "text"=>"Password updated successfully!"];
        echo "<script>window.location.href = 'login.php';</script>";
        exit();
    }
}

// UPDATE PHOTO
if (isset($_POST['update_photo']) && !empty($_FILES['profile_pic']['name'])) {
    $file = $_FILES['profile_pic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
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
            echo "<script>window.location.href = 'sponsor.php?page=my_account';</script>";
            exit();
        }
    }
}

// REMOVE PHOTO
if (isset($_POST['remove_photo'])) {
    if (!empty($profile) && file_exists($profile)) unlink($profile); 
    $stmt = $conn->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $_SESSION['msg'] = ["type"=>"success", "text"=>"Profile photo removed"];
    echo "<script>window.location.href = 'sponsor.php?page=my_account';</script>";
    exit();
}
?>

<style>
    :root {
        --primary: #8f2cd1;
        --primary-hover: #943acf;
        --bg-card-local: var(--bg-card, #ffffff);
        --text-main-local: var(--text-main, #111827);
        --text-muted-local: var(--text-muted, #6b7280);
        --border-local: var(--border-color, #eeeeee);
        --input-bg-local: var(--hover-bg, #ffffff);
    }

    [data-theme="dark"] {
        --bg-card-local: #191c24;
        --text-main-local: #f8fafc;
        --text-muted-local: #94a3b8;
        --border-local: #2a2e39;
        --input-bg-local: #242833;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    
    .acc-container { width: 100%; margin: 0 auto; background: transparent; color: var(--text-main-local); }
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

    .circular-progress { 
        width: 150px; height: 150px; border-radius: 50%; margin: 20px auto;
        background: radial-gradient(closest-side, var(--bg-card-local) 79%, transparent 80% 100%),
                    conic-gradient(var(--primary) <?= (int)$progress ?>%, var(--border-local) 0);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 800; color: var(--text-main-local);
    }

    .p-item { display: flex; align-items: center; justify-content: space-between; font-size: 13px; margin-bottom: 12px; color: var(--text-muted-local); }
    .status-icon.done { color: #10b981; }
    .status-icon.missing { color: #ef4444; }

    /* TOAST STYLES */
    #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
    .toast-box {
        background: var(--bg-card-local); padding: 16px 24px; border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-left: 5px solid var(--primary);
        display: flex; align-items: center; gap: 12px; margin-bottom: 10px;
        animation: slideIn 0.4s forwards;
    }
    @keyframes slideIn { from { transform: translateX(120%); } to { transform: translateX(0); } }

    @media (max-width: 850px) {
        .acc-layout { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
        .profile-section { flex-direction: column; text-align: center; }
    }
</style>

<div id="toast-container"></div>

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
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Phone Number (Philippines)</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="+63xxxxxxxxx or 09xxxxxxxxx">
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
                    <div class="p-item"><span>Account Setup</span> <i class="fa-solid fa-circle-check status-icon done"></i></div>
                    <div class="p-item"><span>Profile Image</span> <i class="fa-solid <?= !empty($profile) ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i></div>
                    <div class="p-item"><span>Personal Info</span> <i class="fa-solid <?= (!empty($fullname) && !empty($email)) ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i></div>
                    <div class="p-item"><span>PH Phone Number</span> <i class="fa-solid <?= $is_valid_ph ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i></div>
                    <div class="p-item"><span>Security Set</span> <i class="fa-solid <?= !empty($userData['password']) ? 'fa-circle-check status-icon done' : 'fa-circle-xmark status-icon missing' ?>"></i></div>
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
    function showToast(msg, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast-box';
        const color = type === 'success' ? '#10b981' : '#ef4444';
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        toast.style.borderLeftColor = color;
        toast.innerHTML = `<i class="fa-solid ${icon}" style="color:${color}"></i><span>${msg}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s';
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }

    // Capture PHP Session Message and display Toast
    <?php if (isset($_SESSION['msg'])): ?>
        showToast("<?= $_SESSION['msg']['text'] ?>", "<?= $_SESSION['msg']['type'] ?>");
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

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