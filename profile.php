<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'db.php';
$user_id = $_SESSION['user_id'];

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $profile_pic = $user['profile_pic'];

    // Validate bio
    if (strlen($bio) > 500) {
        $errors[] = "Bio must be less than 500 characters.";
    }

    // Handle file upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate
        if ($file['size'] > $max_size) {
            $errors[] = "Image must be less than 2MB.";
        } elseif (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Only JPG, JPEG, and PNG images are allowed.";
        } elseif (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $errors[] = "Invalid file extension.";
        } else {
            // Generate unique filename
            $new_name = "uploads/" . time() . "_" . uniqid() . "." . $ext;
            if (move_uploaded_file($file['tmp_name'], $new_name)) {
                // Delete old image if exists and not default
                if ($user['profile_pic'] && $user['profile_pic'] !== 'uploads/default-avatar.png' && file_exists($user['profile_pic'])) {
                    @unlink($user['profile_pic']);
                }
                $profile_pic = $new_name;
            } else {
                $errors[] = "Failed to upload image. Check folder permissions.";
            }
        }
    }

    // Update if no errors
    if (empty($errors)) {
        $update = $pdo->prepare("UPDATE users SET bio = ?, profile_pic = ? WHERE id = ?");
        $update->execute([$bio, $profile_pic, $user_id]);
        $user['bio'] = $bio;
        $user['profile_pic'] = $profile_pic;
        $success = "Profile updated successfully!";
    }
}

// Default avatar
$avatar = $user['profile_pic'] && file_exists($user['profile_pic'])
    ? $user['profile_pic']
    : 'uploads/default-avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .avatar-preview {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .upload-area.dragover {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }
        .file-info {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 8px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>My Profile</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $err): ?>
                                        <li><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Avatar Section -->
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <img src="<?= htmlspecialchars($avatar) ?>" 
                                         id="avatar-preview" 
                                         class="avatar-preview rounded-circle" 
                                         alt="Profile Picture">
                                    <div class="position-absolute bottom-0 end-0">
                                        <label for="profile_pic" class="btn btn-sm btn-primary rounded-circle p-2 shadow-sm" title="Change Photo">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                </div>
                                <h5 class="mt-3 mb-1"><?= htmlspecialchars($user['username']) ?></h5>
                                <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                            </div>

                            <!-- Form Section -->
                            <div class="col-md-8">
                                <form method="POST" enctype="multipart/form-data" id="profileForm">
                                    <input type="file" 
                                           name="profile_pic" 
                                           id="profile_pic" 
                                           accept="image/jpeg,image/jpg,image/png" 
                                           class="d-none">

                                    <div class="mb-3">
                                        <label class="form-label"><i class="fas fa-align-left"></i> Bio</label>
                                        <textarea name="bio" 
                                                  class="form-control" 
                                                  rows="4" 
                                                  placeholder="Tell us about yourself... (max 500 chars)"
                                                  maxlength="500"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                        <div class="form-text text-end">
                                            <span id="bio-count">0</span>/500
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label"><i class="fas fa-image"></i> Profile Picture</label>
                                        <div class="upload-area" id="drop-area">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                            <p class="mb-1">Drop image here or click to select</p>
                                            <small class="text-muted">JPG, PNG &bull; Max 2MB</small>
                                            <div class="file-info" id="file-info"></div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                        <a href="dashboard.php" class="btn btn-secondary px-4">
                                            <i class="fas fa-arrow-left"></i> Back
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Default Avatar (create this file) -->
    <?php if (!file_exists('uploads/default-avatar.png')): ?>
        <script>
            // Create default avatar if missing
            fetch('https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=0d6efd&color=fff&size=180&bold=true')
                .then(r => r.blob())
                .then(blob => {
                    const url = URL.createObjectURL(blob);
                    document.getElementById('avatar-preview').src = url;
                });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bio counter
        const bioTextarea = document.querySelector('[name="bio"]');
        const bioCount = document.getElementById('bio-count');
        bioTextarea.addEventListener('input', () => {
            bioCount.textContent = bioTextarea.value.length;
        });
        bioCount.textContent = bioTextarea.value.length;

        // Image upload preview & drag-drop
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('profile_pic');
        const preview = document.getElementById('avatar-preview');
        const fileInfo = document.getElementById('file-info');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false);
        });

        dropArea.addEventListener('drop', handleDrop, false);
        dropArea.addEventListener('click', () => fileInput.click());

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                handleFiles(fileInput.files);
            }
        });

        function handleFiles(files) {
            const file = files[0];
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }

            fileInfo.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        // Camera button
        document.querySelector('label[for="profile_pic"]').addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });
    </script>
</body>
</html>
