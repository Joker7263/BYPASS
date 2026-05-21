<?php
// ==============================================
// Bread TV APK Manager - Direct Download Link
// APK is hosted elsewhere (Google Drive, MediaFire, etc.)
// Admin controls the download link & display keys
// ==============================================

// Admin credentials (CHANGE THESE!)
define('ADMIN_USERNAME', 'Bread');
define('ADMIN_PASSWORD', 'Breadpro231');

// File paths
$data_file = 'apk_data.json';

// Initialize data file
if (!file_exists($data_file)) {
    $default_data = [
        'current_version' => [
            'version' => '8.0',
            'download_link' => '',  // ← direct download link here
            'file_size' => '9.07 MB',
            'whats_new' => "- General performance improvements and bug fixes.\n- Thanks for using Bread TV!",
            'download_count' => 0
        ],
        'versions' => [],
        'display_keys' => [],
        'settings' => [
            'app_name' => 'Bread TV',
            'app_tagline' => 'TV, Reimagined',
            'contact_email' => 'support@breadtv.com',
            'contact_website' => '',
            'primary_color' => '#e67e22',
            'secondary_color' => '#f39c12',
            'keys_title' => '📋 Activation Keys'
        ]
    ];
    file_put_contents($data_file, json_encode($default_data, JSON_PRETTY_PRINT));
}

session_start();
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

function loadData() {
    global $data_file;
    if (!file_exists($data_file)) return [];
    return json_decode(file_get_contents($data_file), true) ?: [];
}

function saveData($data) {
    global $data_file;
    file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
}

// Handle admin login
if (isset($_POST['login'])) {
    if ($_POST['username'] === ADMIN_USERNAME && $_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle add display key
if ($admin_logged_in && isset($_POST['add_key'])) {
    $data = loadData();
    $new_key = trim($_POST['new_key']);
    if (!empty($new_key)) {
        $data['display_keys'][] = [
            'key' => $new_key,
            'added_at' => date('Y-m-d H:i:s')
        ];
        saveData($data);
        $success = "Key added successfully!";
    } else {
        $error = "Please enter a key!";
    }
}

// Handle delete key
if ($admin_logged_in && isset($_GET['delete_key']) && isset($_GET['key_index'])) {
    $data = loadData();
    $index = (int)$_GET['key_index'];
    if (isset($data['display_keys'][$index])) {
        array_splice($data['display_keys'], $index, 1);
        saveData($data);
        $success = "Key deleted!";
    }
}

// Handle edit key
if ($admin_logged_in && isset($_POST['edit_key']) && isset($_POST['key_index']) && isset($_POST['edit_value'])) {
    $data = loadData();
    $index = (int)$_POST['key_index'];
    $new_value = trim($_POST['edit_value']);
    if (isset($data['display_keys'][$index]) && !empty($new_value)) {
        $data['display_keys'][$index]['key'] = $new_value;
        saveData($data);
        $success = "Key updated!";
    } else {
        $error = "Invalid key value!";
    }
}

// Handle update APK info (link-based)
if ($admin_logged_in && isset($_POST['update_apk'])) {
    $data = loadData();
    
    // Save current version to history before updating
    if (!empty($data['current_version']['download_link']) && !empty($data['current_version']['version'])) {
        array_unshift($data['versions'], [
            'version' => $data['current_version']['version'],
            'download_link' => $data['current_version']['download_link'],
            'file_size' => $data['current_version']['file_size'],
            'whats_new' => $data['current_version']['whats_new'],
            'archived_at' => date('Y-m-d H:i:s')
        ]);
        $data['versions'] = array_slice($data['versions'], 0, 10);
    }
    
    $data['current_version'] = [
        'version' => $_POST['version'] ?? '1.0',
        'download_link' => $_POST['download_link'] ?? '',
        'file_size' => $_POST['file_size'] ?? '',
        'whats_new' => $_POST['whats_new'] ?? '',
        'download_count' => $data['current_version']['download_count'] ?? 0
    ];
    saveData($data);
    $success = "APK info updated! Download link saved.";
}

// Handle download count increment (when user clicks download)
if (isset($_GET['download'])) {
    $data = loadData();
    $download_link = $data['current_version']['download_link'];
    
    if (!empty($download_link)) {
        $data['current_version']['download_count']++;
        saveData($data);
        
        // Redirect to the actual download link
        header('Location: ' . $download_link);
        exit;
    } else {
        $error_msg = "No download link available.";
    }
}

// Handle delete current version
if ($admin_logged_in && isset($_GET['delete_current'])) {
    $data = loadData();
    $data['current_version'] = [
        'version' => '',
        'download_link' => '',
        'file_size' => '',
        'whats_new' => '',
        'download_count' => 0
    ];
    saveData($data);
    $success = "Current version removed.";
}

// Handle delete archived version
if ($admin_logged_in && isset($_GET['delete_version']) && isset($_GET['version_index'])) {
    $data = loadData();
    $index = (int)$_GET['version_index'];
    if (isset($data['versions'][$index])) {
        array_splice($data['versions'], $index, 1);
        saveData($data);
        $success = "Version deleted!";
    }
}

// Update settings
if ($admin_logged_in && isset($_POST['update_settings'])) {
    $data = loadData();
    $data['settings']['app_name'] = $_POST['app_name'] ?? 'Bread TV';
    $data['settings']['app_tagline'] = $_POST['app_tagline'] ?? '';
    $data['settings']['contact_email'] = $_POST['contact_email'] ?? '';
    $data['settings']['contact_website'] = $_POST['contact_website'] ?? '';
    $data['settings']['primary_color'] = $_POST['primary_color'] ?? '#e67e22';
    $data['settings']['secondary_color'] = $_POST['secondary_color'] ?? '#f39c12';
    $data['settings']['keys_title'] = $_POST['keys_title'] ?? '📋 Activation Keys';
    saveData($data);
    $success = "Settings updated!";
}

// Load data
$data = loadData();
$current = $data['current_version'];
$versions = $data['versions'];
$display_keys = $data['display_keys'] ?? [];
$settings = $data['settings'];
$primary = $settings['primary_color'];
$secondary = $settings['secondary_color'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo htmlspecialchars($settings['app_name']); ?> - Download APK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, <?php echo $primary; ?>15 0%, <?php echo $secondary; ?>15 100%);
            min-height: 100vh;
        }
        :root {
            --primary: <?php echo $primary; ?>;
            --secondary: <?php echo $secondary; ?>;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 2rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        .btn-primary:hover { transform: scale(1.02); cursor: pointer; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
        .hero { text-align: center; padding: 2rem 1rem; }
        .hero h1 {
            font-size: 3.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .download-card { text-align: center; }
        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 1.3rem;
            font-weight: bold;
            padding: 1rem 2rem;
            border-radius: 3rem;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }
        .download-btn:hover { transform: scale(1.02); }
        
        .keys-section {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9ff;
            border-radius: 1.5rem;
        }
        .key-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .key-text {
            font-family: monospace;
            font-size: 1rem;
            font-weight: 600;
            background: #f3f4f6;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            word-break: break-all;
            flex: 1;
        }
        .copy-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .copy-btn:hover { opacity: 0.9; }
        .copy-btn.copied { background: #10b981; }
        
        .version-item, .key-admin-item {
            background: #f8f9ff;
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
        }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-info { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            font-family: 'Inter', sans-serif;
        }
        .empty-keys {
            text-align: center;
            padding: 1.5rem;
            color: #999;
            font-style: italic;
        }
        .link-preview {
            background: #f0fdf4;
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            word-break: break-all;
            color: #166534;
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .key-item { flex-direction: column; text-align: center; }
            .copy-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (!$admin_logged_in && isset($_GET['admin'])): ?>
        <!-- Admin Login -->
        <div class="card" style="max-width: 450px; margin: 80px auto;">
            <div style="text-align: center;">
                <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary);"></i>
                <h2>Admin Access</h2>
            </div>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary" style="width:100%">Login</button>
            </form>
            <p style="margin-top: 1rem; text-align: center;"><a href="<?php echo $_SERVER['PHP_SELF']; ?>">← Back to Download</a></p>
        </div>
    <?php elseif ($admin_logged_in): ?>
        <!-- ========== ADMIN PANEL ========== -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <h2><i class="fas fa-crown" style="color: var(--primary);"></i> Admin Control Panel</h2>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline"><i class="fas fa-eye"></i> View Public</a>
                    <a href="?logout=1" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- ===== MANAGE DOWNLOAD LINK (APK hosted elsewhere) ===== -->
        <div class="card">
            <h2><i class="fas fa-link" style="color: var(--primary);"></i> APK Download Link</h2>
            <p style="color: #666; margin-bottom: 1rem;">Paste the direct download link from Google Drive, MediaFire, or any file hosting.</p>
            
            <form method="POST">
                <div class="form-group">
                    <label>Version Number</label>
                    <input type="text" name="version" value="<?php echo htmlspecialchars($current['version']); ?>" required placeholder="e.g., 8.0">
                </div>
                <div class="form-group">
                    <label>Direct Download Link</label>
                    <input type="url" name="download_link" value="<?php echo htmlspecialchars($current['download_link']); ?>" placeholder="https://drive.google.com/... or https://example.com/app.apk" style="width:100%">
                    <?php if (!empty($current['download_link'])): ?>
                        <div class="link-preview" style="margin-top: 0.5rem;">
                            <i class="fas fa-check-circle"></i> Current link: <a href="<?php echo $current['download_link']; ?>" target="_blank"><?php echo substr($current['download_link'], 0, 50); ?>...</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>File Size (display only)</label>
                    <input type="text" name="file_size" value="<?php echo htmlspecialchars($current['file_size']); ?>" placeholder="e.g., 50.2 MB">
                </div>
                <div class="form-group">
                    <label>What's New</label>
                    <textarea name="whats_new" rows="3"><?php echo htmlspecialchars($current['whats_new']); ?></textarea>
                </div>
                <button type="submit" name="update_apk" class="btn btn-primary"><i class="fas fa-save"></i> Save Download Link</button>
                <?php if (!empty($current['download_link'])): ?>
                    <a href="?delete_current=1" class="btn btn-danger" onclick="return confirm('Remove current version?')" style="margin-left: 0.5rem;"><i class="fas fa-trash"></i> Remove</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- ===== MANAGE DISPLAY KEYS ===== -->
        <div class="card">
            <h2><i class="fas fa-key" style="color: var(--primary);"></i> Manage Display Keys</h2>
            <p style="color: #666; margin-bottom: 1rem;">Add keys/info here. These will appear on the public page with a COPY button.</p>
            
            <form method="POST" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <input type="text" name="new_key" placeholder="Enter key or info to display (e.g., ABC123-XYZ)" style="flex: 1; padding: 0.75rem; border-radius: 1rem; border: 2px solid #e5e7eb;">
                <button type="submit" name="add_key" class="btn btn-primary"><i class="fas fa-plus"></i> Add Key</button>
            </form>
            
            <h3>Current Display Keys (<?php echo count($display_keys); ?> keys)</h3>
            <?php if (empty($display_keys)): ?>
                <div class="empty-keys">
                    <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p>No keys to display. Add a key above.</p>
                </div>
            <?php else: ?>
                <?php foreach ($display_keys as $index => $item): ?>
                    <div class="key-admin-item">
                        <div style="flex: 1;">
                            <code style="background: #e5e7eb; padding: 0.25rem 0.75rem; border-radius: 0.5rem;"><?php echo htmlspecialchars($item['key']); ?></code>
                            <div><small style="color: #999;">Added: <?php echo $item['added_at']; ?></small></div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <form method="POST" style="display: inline-flex; gap: 0.25rem;">
                                <input type="hidden" name="key_index" value="<?php echo $index; ?>">
                                <input type="text" name="edit_value" value="<?php echo htmlspecialchars($item['key']); ?>" style="padding: 0.4rem; border-radius: 0.5rem; border: 1px solid #ccc; width: 150px;">
                                <button type="submit" name="edit_key" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Edit</button>
                            </form>
                            <a href="?delete_key=1&key_index=<?php echo $index; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this key?')"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Preview -->
            <?php if (!empty($display_keys)): ?>
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed #ccc;">
                <h4><i class="fas fa-eye"></i> Preview (how users will see):</h4>
                <div class="keys-section">
                    <?php foreach ($display_keys as $item): ?>
                        <div class="key-item">
                            <span class="key-text"><?php echo htmlspecialchars($item['key']); ?></span>
                            <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($item['key']); ?>', this)">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== VERSION HISTORY ===== -->
        <?php if (!empty($versions)): ?>
        <div class="card">
            <h2><i class="fas fa-history"></i> Previous Versions (Archived)</h2>
            <?php foreach ($versions as $index => $version): ?>
                <div class="version-item">
                    <div>
                        <strong>v<?php echo htmlspecialchars($version['version']); ?></strong><br>
                        <small><?php echo $version['file_size']; ?> • <?php echo $version['archived_at']; ?></small><br>
                        <small class="link-preview" style="font-size: 0.7rem;">Link: <?php echo substr($version['download_link'], 0, 60); ?>...</small>
                    </div>
                    <a href="?delete_version=1&version_index=<?php echo $index; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this archived version?')"><i class="fas fa-trash"></i> Delete</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ===== SETTINGS ===== -->
        <div class="card">
            <h2><i class="fas fa-sliders-h"></i> Appearance & Settings</h2>
            <form method="POST">
                <div class="form-group">
                    <label>App Name</label>
                    <input type="text" name="app_name" value="<?php echo htmlspecialchars($settings['app_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="app_tagline" value="<?php echo htmlspecialchars($settings['app_tagline']); ?>">
                </div>
                <div class="form-group">
                    <label>Keys Section Title</label>
                    <input type="text" name="keys_title" value="<?php echo htmlspecialchars($settings['keys_title']); ?>">
                </div>
                <div class="form-group">
                    <label>Primary Color</label>
                    <input type="color" name="primary_color" value="<?php echo $primary; ?>">
                </div>
                <div class="form-group">
                    <label>Secondary Color</label>
                    <input type="color" name="secondary_color" value="<?php echo $secondary; ?>">
                </div>
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
                </div>
                <div class="form-group">
                    <label>Contact Website (optional)</label>
                    <input type="text" name="contact_website" value="<?php echo htmlspecialchars($settings['contact_website']); ?>">
                </div>
                <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
            </form>
        </div>

    <?php else: ?>
        <!-- ========== PUBLIC DOWNLOAD PAGE ========== -->
        <div class="hero">
            <h1><?php echo htmlspecialchars($settings['app_name']); ?></h1>
            <p class="tagline"><?php echo htmlspecialchars($settings['app_tagline']); ?></p>
        </div>

        <div class="download-card card">
            <?php if (!empty($current['download_link'])): ?>
                
                <!-- Download Button (redirects to external link) -->
                <a href="?download=1" class="download-btn">
                    <i class="fas fa-download"></i> Download APK v<?php echo htmlspecialchars($current['version']); ?>
                </a>
                <div style="margin-top: 0.5rem; color: #666;">
                    <?php echo htmlspecialchars($current['file_size']); ?>
                </div>

                <!-- DISPLAY KEYS SECTION WITH COPY BUTTONS -->
                <?php if (!empty($display_keys)): ?>
                    <div class="keys-section">
                        <h3 style="margin-bottom: 1rem;">
                            <i class="fas fa-key" style="color: var(--primary);"></i> 
                            <?php echo htmlspecialchars($settings['keys_title']); ?>
                        </h3>
                        <?php foreach ($display_keys as $item): ?>
                            <div class="key-item">
                                <span class="key-text"><?php echo htmlspecialchars($item['key']); ?></span>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($item['key']); ?>', this)">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        <?php endforeach; ?>
                        <p style="font-size: 0.75rem; color: #666; margin-top: 0.75rem;">
                            <i class="fas fa-mouse-pointer"></i> Click "Copy" button to copy each key
                        </p>
                    </div>
                <?php endif; ?>

                <!-- What's New Section -->
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #eee;">
                    <div style="background: #f8f9ff; border-radius: 1rem; padding: 1rem; text-align: left;">
                        <strong><i class="fas fa-star" style="color: var(--primary);"></i> What's New:</strong>
                        <p style="margin-top: 0.5rem;"><?php echo nl2br(htmlspecialchars($current['whats_new'])); ?></p>
                    </div>
                    <div style="display: flex; justify-content: center; gap: 1.5rem; margin-top: 1rem; color: #666;">
                        <span><i class="fas fa-download"></i> <?php echo number_format($current['download_count']); ?> downloads</span>
                        <span><i class="fas fa-android"></i> Android APK</span>
                    </div>
                </div>
            <?php else: ?>
                <div style="padding: 2rem;">
                    <i class="fas fa-clock" style="font-size: 3rem; color: #ccc;"></i>
                    <p style="margin-top: 1rem; color: #888;">APK coming soon. Stay tuned!</p>
                </div>
            <?php endif; ?>

            <!-- Contact Section -->
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee;">
                <h3><i class="fas fa-envelope"></i> CONTACT US</h3>
                <p><?php echo htmlspecialchars($settings['contact_email']); ?></p>
                <?php if (!empty($settings['contact_website'])): ?>
                    <p><?php echo htmlspecialchars($settings['contact_website']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <footer style="text-align: center; margin-top: 2rem;">
            <a href="?admin=1" style="color: #999; text-decoration: none;">
                <i class="fas fa-lock"></i> Admin Login
            </a>
        </footer>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(text, buttonElement) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHTML = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fas fa-check"></i> Copied!';
        buttonElement.classList.add('copied');
        setTimeout(() => {
            buttonElement.innerHTML = originalHTML;
            buttonElement.classList.remove('copied');
        }, 2000);
    }).catch(err => {
        alert('Failed to copy: ' + err);
    });
}
</script>
</body>
</html>