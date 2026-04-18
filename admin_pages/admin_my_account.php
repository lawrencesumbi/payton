<?php
// Admin Account Settings Module
$userId = $_SESSION['user_id'];

if (isset($_POST['save_profile'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($fullname === '' || $email === '') {
        $message = ['type' => 'error', 'text' => 'Full name and email are required.'];
    } else {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullname, $email, $phone, $userId]);
        $message = ['type' => 'success', 'text' => 'Profile updated successfully.'];
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email'] = $email;
    }
}

if (isset($_POST['update_password'])) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData || !password_verify($oldPassword, $userData['password'])) {
        $message = ['type' => 'error', 'text' => 'Old password is incorrect.'];
    } elseif ($newPassword === '' || $newPassword !== $confirmPassword) {
        $message = ['type' => 'error', 'text' => 'New passwords must match and cannot be empty.'];
    } else {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
        $message = ['type' => 'success', 'text' => 'Password updated successfully.'];
    }
}

if (isset($_POST['update_photo']) && !empty($_FILES['profile_pic']['name'])) {
    $file = $_FILES['profile_pic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['png', 'jpg', 'jpeg'];

    if (!in_array($ext, $allowed)) {
        $message = ['type' => 'error', 'text' => 'Only JPG, JPEG and PNG files are allowed.'];
    } else {
        $uploadDir = 'profile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = time() . '_' . uniqid() . '.' . $ext;
        $uploadPath = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $old = $stmt->fetchColumn();
            if (!empty($old) && file_exists($old)) {
                @unlink($old);
            }
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$uploadPath, $userId]);
            $profilePath = $uploadPath;
            $message = ['type' => 'success', 'text' => 'Profile picture updated successfully.'];
        } else {
            $message = ['type' => 'error', 'text' => 'Unable to upload profile picture.'];
        }
    }
}

if (isset($_POST['remove_photo'])) {
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $old = $stmt->fetchColumn();
    if (!empty($old) && file_exists($old)) {
        @unlink($old);
    }
    $stmt = $conn->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stmt->execute([$userId]);
    $profilePath = 'profile/default.jpg';
    $message = ['type' => 'success', 'text' => 'Profile picture removed.'];
}

$stmt = $conn->prepare("SELECT fullname, email, phone, profile_pic FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$fullname = $userData['fullname'] ?? '';
$email = $userData['email'] ?? '';
$phone = $userData['phone'] ?? '';
$profilePath = (!empty($userData['profile_pic']) && file_exists($userData['profile_pic'])) ? $userData['profile_pic'] : 'profile/default.jpg';

$progress = 10;
if (!empty($fullname)) $progress += 20;
if (!empty($email)) $progress += 20;
if (!empty($phone)) $progress += 20;
if (!empty($userData['profile_pic']) && file_exists($userData['profile_pic'])) $progress += 15;
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$passwordHash = $stmt->fetchColumn();
if (!empty($passwordHash)) $progress += 15;
$progress = min(100, $progress);
?>

<style>
    :root {
        --bg-main: #f8fafc;
        --card-bg: #ffffff;
        --primary: #6366f1;
        --text-dark: #0f172a;
        --text-muted: #64748b;
        --border: #e2e8f0;
        --radius-lg: 24px;
        --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.04);
    }

    [data-theme="dark"] {
        --bg-main: #0f111a;
        --card-bg: #191c24;
        --text-dark: #f8fafc;
        --text-muted: #94a3b8;
        --border: #2a2e39;
    }

    .acc-container { width: 100%; margin: 0 auto; }
    .acc-layout { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }

    .acc-card {
        background: var(--card-bg); 
        border-radius: 20px;
        padding: 24px;
        border: 1px solid var(--border);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .profile-section { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; padding-bottom: 22px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
    .profile-pic-container { width: 108px; height: 108px; border-radius: 50%; overflow: hidden; border: 4px solid var(--bg-main); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .profile-pic-container img { width: 100%; height: 100%; object-fit: cover; }
    .profile-info-meta h4 { font-size: 18px; margin-bottom: 8px; color: var(--text-dark); }
    .profile-info-meta p { margin: 0; color: var(--text-muted); font-size: 13px; }
    .profile-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }

    .acc-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--text-dark); }
    .form-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; margin-bottom: 8px; color: var(--text-dark); font-weight: 600; font-size: 13px; }
    .form-group input { width: 100%; padding: 14px 16px; border-radius: 14px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text-dark); transition: border-color 0.2s ease, box-shadow 0.2s ease; }
    .form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1); }

    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 22px; border-radius: 14px; font-weight: 700; cursor: pointer; transition: transform 0.2s ease, background 0.2s ease; border: none; }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: #4f46e5; transform: translateY(-1px); }
    .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-dark); }
    .btn-outline:hover { background: var(--bg-main); }

    .sidebar-summary { display: grid; gap: 24px; }
    .summary-card { background: var(--card-bg); border-radius: 20px; border: 1px solid var(--border); padding: 24px; }
    .summary-card h3 { font-size: 16px; margin-bottom: 18px; color: var(--text-dark); }
    .summary-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; color: var(--text-muted); }
    .summary-item strong { color: var(--text-dark); }
    .progress-ring { width: 132px; height: 132px; border-radius: 50%; margin: 0 auto 18px; display:flex; align-items:center; justify-content:center; color: var(--text-dark); font-size: 22px; font-weight: 800; background: radial-gradient(closest-side, var(--card-bg) 78%, transparent 79% 100%), conic-gradient(var(--primary) 0 74%, var(--border) 0); }

    @media (max-width: 960px) {
        .acc-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 720px) {
        .form-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="acc-container">
    <div class="acc-layout">
        <main>
            <div class="acc-card">
                <div class="profile-section">
                    <div class="profile-pic-container">
                        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile Picture">
                    </div>
                    <div class="profile-info-meta">
                        <h4>Profile Picture</h4>
                        <p>Upload a square JPG or PNG image for your admin profile. Recommended size 800x800px.</p>
                        <div class="profile-actions">
                            <form method="post" enctype="multipart/form-data" id="photoForm" style="display:inline-flex; gap:10px; flex-wrap:wrap;">
                                <input type="hidden" name="update_photo" value="1">
                                <input type="file" name="profile_pic" id="profilePicInput" accept=".jpg,.jpeg,.png" hidden onchange="document.getElementById('photoForm').submit();">
                                <button type="button" class="btn btn-outline" onclick="document.getElementById('profilePicInput').click();">
                                    <i class="fa-solid fa-camera"></i> Change Photo
                                </button>
                                <button type="submit" class="btn btn-primary">Save Photo</button>
                            </form>
                            <form method="post" style="display:inline-flex; align-items:center;">
                                <button type="submit" name="remove_photo" class="btn btn-outline" style="color:#dc2626; border-color:#f7d3d3;">
                                    <i class="fa-solid fa-trash-can"></i> Remove Photo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="acc-card">
                <h3><i class="fa-solid fa-user"></i> Personal Information</h3>
                <form method="post">
                    <input type="hidden" name="save_profile" value="1">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" value="<?= htmlspecialchars($fullname) ?>" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="name@company.com" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </form>
            </div>

            <div class="acc-card">
                <h3><i class="fa-solid fa-lock"></i> Security</h3>
                <form method="post">
                    <input type="hidden" name="update_password" value="1">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="old_password" placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="••••••••">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </main>

        <aside class="sidebar-summary">
            <div class="summary-card">
                <h3>Admin Summary</h3>
                <div class="summary-item"><span>Full Name</span><strong><?= htmlspecialchars($fullname) ?></strong></div>
                <div class="summary-item"><span>Email</span><strong><?= htmlspecialchars($email) ?></strong></div>
                <div class="summary-item"><span>Phone</span><strong><?= htmlspecialchars($phone ?: 'Not set') ?></strong></div>
                <div class="summary-item"><span>Role</span><strong>Admin</strong></div>
            </div>
            <div class="summary-card" style="text-align:center;">
                <h3>Account Health</h3>
                <div class="progress-ring" style="background: radial-gradient(closest-side, var(--bg-card) 78%, transparent 79% 100%), conic-gradient(var(--accent-purple) 0 <?= (int)$progress ?>%, var(--border-color) 0);"><?= (int)$progress ?>%</div>
                <p style="margin:0; color: var(--text-muted);">Complete your profile by adding a valid phone number and keeping your photo current.</p>
            </div>
        </aside>
    </div>
</div>

<script>
    document.getElementById('profilePicInput').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const allowed = ['image/png', 'image/jpeg'];
        if (!allowed.includes(file.type)) {
            alert('Only JPG, JPEG, PNG files are allowed.');
            this.value = '';
            return;
        }
    });
</script>
