<?php
/**
 * Barangay Facilities CRUD Manager - Barangay Zone 12-A
 */
$active_tab = 'facilities';
$admin_title = 'Manage Public Facilities';

require_once 'includes/admin_header.php';

// Helper function to decode images JSON or single string path
function parse_facility_images($image_data) {
    if (empty($image_data)) return [];
    $decoded = json_decode($image_data, true);
    if (is_array($decoded)) return array_values($decoded);
    return [$image_data];
}

// Ensure database column can store multiple image JSON paths
try {
    $pdo->exec("ALTER TABLE facilities MODIFY COLUMN image TEXT DEFAULT NULL");
} catch (PDOException $e) {
    // Column already modified or doesn't need altering
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error = '';

// Delete action
if ($action === 'delete' && $id) {
    try {
        // Fetch facility image(s) to delete from server
        $stmt = $pdo->prepare("SELECT image FROM facilities WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $raw_image = $stmt->fetchColumn();
        $images_to_delete = parse_facility_images($raw_image);

        foreach ($images_to_delete as $img_path) {
            if ($img_path && file_exists('../' . $img_path)) {
                @unlink('../' . $img_path);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM facilities WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = "Facility record deleted successfully.";
        $action = 'list';
    } catch (PDOException $e) {
        $error = "Failed to delete facility: " . $e->getMessage();
        $action = 'list';
    }
}

// Processing Add or Edit POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $name           = trim(filter_input(INPUT_POST, 'name',           FILTER_SANITIZE_SPECIAL_CHARS));
    $type           = trim(filter_input(INPUT_POST, 'type',           FILTER_SANITIZE_SPECIAL_CHARS));
    $address        = trim(filter_input(INPUT_POST, 'address',        FILTER_SANITIZE_SPECIAL_CHARS));
    $contact_number = trim(filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_SPECIAL_CHARS));
    $description    = trim(filter_input(INPUT_POST, 'description',    FILTER_SANITIZE_SPECIAL_CHARS));

    if (empty($name) || empty($type) || empty($address)) {
        $error = "Facility Name, Facility Type, and Physical Address are required fields.";
    } else {
        // Keep existing images that were not removed
        $existing_images = isset($_POST['existing_images']) && is_array($_POST['existing_images']) ? $_POST['existing_images'] : [];
        $deleted_images  = isset($_POST['deleted_images'])  && is_array($_POST['deleted_images'])  ? $_POST['deleted_images']  : [];

        // Remove deleted files from server disk
        foreach ($deleted_images as $del_img) {
            if ($del_img && file_exists('../' . $del_img)) {
                @unlink('../' . $del_img);
            }
        }

        $final_images = array_values(array_diff($existing_images, $deleted_images));

        // Handle Multiple Image Uploads (name="images[]")
        $uploaded_files = isset($_FILES['images']) ? $_FILES['images'] : null;

        if ($uploaded_files && !empty($uploaded_files['name'][0])) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            // Ensure uploads directory exists
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
            }

            $file_count = is_array($uploaded_files['name']) ? count($uploaded_files['name']) : 1;

            for ($i = 0; $i < $file_count; $i++) {
                $err = is_array($uploaded_files['error']) ? $uploaded_files['error'][$i] : $uploaded_files['error'];
                if ($err === UPLOAD_ERR_OK) {
                    $file_tmp  = is_array($uploaded_files['tmp_name']) ? $uploaded_files['tmp_name'][$i] : $uploaded_files['tmp_name'];
                    $file_name = is_array($uploaded_files['name'])     ? $uploaded_files['name'][$i]     : $uploaded_files['name'];
                    $file_size = is_array($uploaded_files['size'])     ? $uploaded_files['size'][$i]     : $uploaded_files['size'];
                    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (!in_array($file_ext, $allowed_exts)) {
                        $error = "Invalid file type: '$file_name'. Allowed: " . implode(', ', $allowed_exts);
                        break;
                    } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
                        $error = "File '$file_name' exceeds 5MB limit.";
                        break;
                    } else {
                        $new_filename = 'facility_' . time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $file_ext;
                        $image_path   = 'uploads/' . $new_filename;

                        if (move_uploaded_file($file_tmp, '../' . $image_path)) {
                            $final_images[] = $image_path;
                        }
                    }
                }
            }
        }

        // Convert image paths to JSON if images exist
        $image_json = !empty($final_images) ? json_encode(array_values($final_images)) : null;

        // Save to Database if no errors
        if (empty($error)) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO facilities (name, type, address, contact_number, description, image) VALUES (:name, :type, :address, :contact_number, :description, :image)");
                    $stmt->execute([
                        'name'           => $name,
                        'type'           => $type,
                        'address'        => $address,
                        'contact_number' => empty($contact_number) ? null : $contact_number,
                        'description'    => empty($description)    ? null : $description,
                        'image'          => $image_json
                    ]);
                    $message = "New facility directory entry created.";
                } else {
                    $stmt = $pdo->prepare("UPDATE facilities SET name = :name, type = :type, address = :address, contact_number = :contact_number, description = :description, image = :image WHERE id = :id");
                    $stmt->execute([
                        'name'           => $name,
                        'type'           => $type,
                        'address'        => $address,
                        'contact_number' => empty($contact_number) ? null : $contact_number,
                        'description'    => empty($description)    ? null : $description,
                        'image'          => $image_json,
                        'id'             => $id
                    ]);
                    $message = "Facility directory record updated.";
                }
                $action = 'list';
            } catch (PDOException $e) {
                $error = "Database execution failed: " . $e->getMessage();
            }
        }
    }
}

// Fetch facility data if editing
$facility_data = null;
if ($action === 'edit' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $facility_data = $stmt->fetch();
        if (!$facility_data) {
            $error = "Facility record not found.";
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
        <a href="manage_facilities.php" class="btn-action btn-edit">← Back to List</a>
    </div>

    <div class="form-card">
        <h3 class="form-title"><?php echo $action === 'add' ? 'Add New Public Facility' : 'Edit Facility Details'; ?></h3>

        <form action="manage_facilities.php?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group form-grid-full">
                    <label for="name" class="form-label">Facility Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Zone 12-A Barangay Health Station" value="<?php echo htmlspecialchars($facility_data['name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="type" class="form-label">Facility Type / Category</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">-- Select Type --</option>
                        <option value="Health"     <?php echo isset($facility_data['type']) && $facility_data['type'] === 'Health'     ? 'selected' : ''; ?>>Health</option>
                        <option value="Utility"    <?php echo isset($facility_data['type']) && $facility_data['type'] === 'Utility'    ? 'selected' : ''; ?>>Utility</option>
                        <option value="Education"  <?php echo isset($facility_data['type']) && $facility_data['type'] === 'Education'  ? 'selected' : ''; ?>>Education</option>
                        <option value="Government" <?php echo isset($facility_data['type']) && $facility_data['type'] === 'Government' ? 'selected' : ''; ?>>Government / Office</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="contact_number" class="form-label">Contact Number (Optional)</label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="e.g. (034) 441-1774" value="<?php echo htmlspecialchars($facility_data['contact_number'] ?? ''); ?>">
                </div>

                <div class="form-group form-grid-full">
                    <label for="address" class="form-label">Physical Address</label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="e.g. Pueblo San Antonio, Brgy. Zone 12-A" value="<?php echo htmlspecialchars($facility_data['address'] ?? ''); ?>" required>
                </div>

                <div class="form-group form-grid-full">
                    <label for="description" class="form-label">Description / Services Offered</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Brief description of the facility and utility services..." style="min-height: 100px;"><?php echo htmlspecialchars($facility_data['description'] ?? ''); ?></textarea>
                </div>

                <!-- Multi-Image Upload -->
                <div class="form-group form-grid-full">
                    <label for="images" class="form-label">Facility Photos (Upload multiple images)</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                    <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.35rem;">
                        💡 You can hold <kbd style="background: #e2e8f0; padding: 2px 5px; border-radius: 3px;">Ctrl</kbd> or <kbd style="background: #e2e8f0; padding: 2px 5px; border-radius: 3px;">Cmd</kbd> to select multiple image files at once. Max 5MB per file.
                    </div>

                    <?php
                    $existing_imgs = $facility_data ? parse_facility_images($facility_data['image']) : [];
                    if (!empty($existing_imgs)):
                    ?>
                        <div style="margin-top: 1.25rem;">
                            <label class="form-label">Currently Attached Photos (<?php echo count($existing_imgs); ?>):</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem;" id="existing-images-container">
                                <?php foreach ($existing_imgs as $idx => $img_path): ?>
                                    <div class="existing-img-card" id="img-card-<?php echo $idx; ?>" style="position: relative; width: 110px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 5px; background: #ffffff; text-align: center; box-shadow: var(--shadow-sm);">
                                        <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($img_path); ?>">
                                        <img src="../<?php echo htmlspecialchars($img_path); ?>" alt="Preview" style="width: 100%; height: 75px; object-fit: cover; border-radius: 4px;">
                                        <button type="button" onclick="removeImageCard(<?php echo $idx; ?>, '<?php echo htmlspecialchars($img_path); ?>')" style="margin-top: 5px; background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; border-radius: 4px; padding: 3px 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; width: 100%;">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div id="deleted-inputs"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn-action btn-add" style="margin-top: 1.5rem; font-size: 0.95rem; width: 100%; justify-content: center; border: none;">
                Save Facility Record
            </button>
        </form>

        <script>
        function removeImageCard(idx, imgPath) {
            const card = document.getElementById('img-card-' + idx);
            if (card) card.remove();
            const deletedDiv = document.getElementById('deleted-inputs');
            if (deletedDiv) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type  = 'hidden';
                hiddenInput.name  = 'deleted_images[]';
                hiddenInput.value = imgPath;
                deletedDiv.appendChild(hiddenInput);
            }
        }
        </script>
    </div>

<?php else: ?>
    <!-- List View -->
    <div class="table-card">
        <div class="table-header">
            <h3 style="color: var(--color-primary);">Facilities Directory List</h3>
            <a href="manage_facilities.php?action=add" class="btn-action btn-add">+ Add New Facility</a>
        </div>

        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM facilities ORDER BY type ASC, name ASC");
            $facilities_list = $stmt->fetchAll();
        } catch (PDOException $e) {
            $facilities_list = [];
        }
        ?>

        <?php if (!empty($facilities_list)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facilities_list as $row): ?>
                        <tr>
                            <td>
                                <?php
                                $f_imgs = parse_facility_images($row['image']);
                                if (!empty($f_imgs)):
                                ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <img src="../<?php echo htmlspecialchars($f_imgs[0]); ?>" alt="Thumbnail" style="width: 60px; height: 40px; border-radius: 4px; object-fit: cover;">
                                        <?php if (count($f_imgs) > 1): ?>
                                            <span class="badge" style="background-color: #dbeafe; color: #1e40af; font-size: 0.7rem; padding: 0.2rem 0.4rem;"><?php echo count($f_imgs); ?> photos</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="width: 60px; height: 40px; border-radius: 4px; background-color: var(--color-border); display: flex; align-items: center; justify-content: center; font-size: 0.75rem;" class="text-muted">No Photo</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td>
                                <?php
                                $badge_style = 'background-color: var(--color-border); color: var(--color-text-dark);';
                                if ($row['type'] === 'Health')     $badge_style = 'background-color: #fee2e2; color: #ef4444;';
                                elseif ($row['type'] === 'Utility')    $badge_style = 'background-color: #fef3c7; color: #d97706;';
                                elseif ($row['type'] === 'Education')  $badge_style = 'background-color: #dbeafe; color: #2563eb;';
                                elseif ($row['type'] === 'Government') $badge_style = 'background-color: #d1fae5; color: #059669;';
                                ?>
                                <span class="badge" style="<?php echo $badge_style; ?>"><?php echo htmlspecialchars($row['type']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_number'] ?? 'N/A'); ?></td>
                            <td style="text-align: right;">
                                <a href="manage_facilities.php?action=edit&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                                <a href="manage_facilities.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this facility? This will permanently remove its photo(s) from the server.');"><i class="bi bi-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 3rem; text-align: center;" class="text-muted">
                No facilities have been registered in the database yet. Click "+ Add New Facility" to begin.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
require_once 'includes/admin_footer.php';
?>
