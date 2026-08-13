<?php
/**
 * Barangay Officials CRUD Manager - Barangay Zone 12-A
 */
$active_tab = 'officials';
$admin_title = 'Manage Officials';

require_once 'includes/admin_header.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error = '';

// Check actions
if ($action === 'delete' && $id) {
    // Delete action
    try {
        // Fetch official photo to delete from server if it exists
        $stmt = $pdo->prepare("SELECT photo FROM officials WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $photo = $stmt->fetchColumn();
        
        if ($photo && file_exists('../' . $photo)) {
            unlink('../' . $photo);
        }
        
        $stmt = $pdo->prepare("DELETE FROM officials WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = "Official record deleted successfully.";
        $action = 'list';
    } catch (PDOException $e) {
        $error = "Failed to delete official record: " . $e->getMessage();
        $action = 'list';
    }
}

// Processing Add or Edit POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $position = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_SPECIAL_CHARS));
    $term_start = filter_input(INPUT_POST, 'term_start', FILTER_VALIDATE_INT);
    $term_end = filter_input(INPUT_POST, 'term_end', FILTER_VALIDATE_INT);
    $order_display = filter_input(INPUT_POST, 'order_display', FILTER_VALIDATE_INT) ?? 0;
    
    // Validations
    if (empty($name) || empty($position) || !$term_start || !$term_end) {
        $error = "Name, Position, Term Start, and Term End are required fields and must contain valid values.";
    } else {
        // Handle Photo Upload
        $photo_path = isset($_POST['existing_photo']) ? $_POST['existing_photo'] : null;
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_name = $_FILES['photo']['name'];
            $file_size = $_FILES['photo']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (!in_array($file_ext, $allowed_exts)) {
                $error = "Invalid file extension. Allowed types: " . implode(', ', $allowed_exts);
            } elseif ($file_size > 2 * 1024 * 1024) { // 2MB limit
                $error = "File size must not exceed 2MB.";
            } else {
                // Ensure uploads directory exists
                if (!file_exists('../uploads')) {
                    mkdir('../uploads', 0777, true);
                }
                
                // Delete existing photo if editing and new one uploaded
                if ($action === 'edit' && $photo_path && file_exists('../' . $photo_path)) {
                    unlink('../' . $photo_path);
                }
                
                $new_filename = 'official_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $photo_path = 'uploads/' . $new_filename;
                
                if (!move_uploaded_file($file_tmp, '../' . $photo_path)) {
                    $error = "Failed to save uploaded image.";
                    $photo_path = isset($_POST['existing_photo']) ? $_POST['existing_photo'] : null;
                }
            }
        }
        
        // Save to Database if no errors
        if (empty($error)) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO officials (name, position, term_start, term_end, photo, order_display) VALUES (:name, :position, :term_start, :term_end, :photo, :order_display)");
                    $stmt->execute([
                        'name' => $name,
                        'position' => $position,
                        'term_start' => $term_start,
                        'term_end' => $term_end,
                        'photo' => $photo_path,
                        'order_display' => $order_display
                    ]);
                    $message = "New official added successfully.";
                } else {
                    $stmt = $pdo->prepare("UPDATE officials SET name = :name, position = :position, term_start = :term_start, term_end = :term_end, photo = :photo, order_display = :order_display WHERE id = :id");
                    $stmt->execute([
                        'name' => $name,
                        'position' => $position,
                        'term_start' => $term_start,
                        'term_end' => $term_end,
                        'photo' => $photo_path,
                        'order_display' => $order_display,
                        'id' => $id
                    ]);
                    $message = "Official record updated successfully.";
                }
                $action = 'list';
            } catch (PDOException $e) {
                $error = "Database execution failed: " . $e->getMessage();
            }
        }
    }
}

// Fetch official data if editing
$official_data = null;
if ($action === 'edit' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM officials WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $official_data = $stmt->fetch();
        if (!$official_data) {
            $error = "Official record not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Failed to fetch record: " . $e->getMessage();
        $action = 'list';
    }
}
?>

<!-- Action Status Alerts -->
<?php if ($message): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Form View (Add/Edit) -->
    <div style="margin-bottom: 1.5rem;">
        <a href="manage_officials.php" class="btn-action btn-edit">← Back to List</a>
    </div>
    
    <div class="form-card">
        <h3 class="form-title"><?php echo $action === 'add' ? 'Add New Official' : 'Edit Official Details'; ?></h3>
        
        <form action="manage_officials.php?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST" enctype="multipart/form-data">
            <?php if ($action === 'edit' && $official_data['photo']): ?>
                <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($official_data['photo']); ?>">
            <?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group form-grid-full">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Juan Dela Cruz" value="<?php echo htmlspecialchars($official_data['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group form-grid-full">
                    <label for="position" class="form-label">Barangay Position</label>
                    <input type="text" name="position" id="position" class="form-control" placeholder="e.g. Barangay Captain / Barangay Kagawad" value="<?php echo htmlspecialchars($official_data['position'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="term_start" class="form-label">Term Start Year</label>
                    <input type="number" name="term_start" id="term_start" class="form-control" placeholder="2023" value="<?php echo htmlspecialchars($official_data['term_start'] ?? '2023'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="term_end" class="form-label">Term End Year</label>
                    <input type="number" name="term_end" id="term_end" class="form-control" placeholder="2026" value="<?php echo htmlspecialchars($official_data['term_end'] ?? '2026'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="order_display" class="form-label">Display Priority Order (Lowest first)</label>
                    <input type="number" name="order_display" id="order_display" class="form-control" placeholder="1" value="<?php echo htmlspecialchars($official_data['order_display'] ?? '0'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="photo" class="form-label">Official Photo (Upload image)</label>
                    <input type="file" name="photo" id="photo" class="form-control">
                    <?php if ($action === 'edit' && $official_data['photo']): ?>
                        <div style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--color-text-muted);">
                            Current image exists: <a href="../<?php echo $official_data['photo']; ?>" target="_blank">View Photo</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="submit" class="btn-action btn-add" style="margin-top: 1.5rem; font-size: 0.95rem; width: 100%; justify-content: center; border: none;">
                Save Official Record
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- List View -->
    <div class="table-card">
        <div class="table-header">
            <h3 style="color: var(--color-primary);">Officials List</h3>
            <a href="manage_officials.php?action=add" class="btn-action btn-add">+ Add New Official</a>
        </div>
        
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM officials ORDER BY order_display ASC");
            $officials_list = $stmt->fetchAll();
        } catch (PDOException $e) {
            $officials_list = [];
        }
        ?>
        
        <?php if (!empty($officials_list)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Term Period</th>
                        <th>Sort Order</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($officials_list as $row): ?>
                        <tr>
                            <td>
                                <?php if ($row['photo']): ?>
                                    <img src="../<?php echo htmlspecialchars($row['photo']); ?>" alt="Profile" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-accent);">
                                <?php else: ?>
                                    <div style="width: 45px; height: 45px; border-radius: 50%; background-color: var(--color-border); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="bi bi-person-fill" style="color: var(--color-text-muted);"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td style="font-weight: 500; color: var(--color-primary);"><?php echo htmlspecialchars($row['position']); ?></td>
                            <td><?php echo htmlspecialchars($row['term_start']); ?> - <?php echo htmlspecialchars($row['term_end']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_display']); ?></td>
                            <td style="text-align: right;">
                                <a href="manage_officials.php?action=edit&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                                <a href="manage_officials.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this official record?');"><i class="bi bi-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 3rem; text-align: center;" class="text-muted">
                No official listings in the database yet. Click "+ Add New Official" to begin.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
require_once 'includes/admin_footer.php';
?>
