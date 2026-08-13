<?php
/**
 * Barangay News CRUD Manager - Barangay Zone 12-A
 */
$active_tab = 'news';
$admin_title = 'Manage News & Announcements';

require_once 'includes/admin_header.php';

// Helper function to decode images JSON or single string path
function parse_news_images($image_data) {
    if (empty($image_data)) return [];
    $decoded = json_decode($image_data, true);
    if (is_array($decoded)) return array_values($decoded);
    return [$image_data];
}

// Ensure database column can store multiple image JSON paths
try {
    $pdo->exec("ALTER TABLE news MODIFY COLUMN image TEXT DEFAULT NULL");
} catch (PDOException $e) {
    // Column already modified or table doesn't exist yet
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error = '';

// Check actions
if ($action === 'delete' && $id) {
    // Delete action
    try {
        // Fetch news image(s) to delete from server if they exist
        $stmt = $pdo->prepare("SELECT image FROM news WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $raw_image = $stmt->fetchColumn();
        $images_to_delete = parse_news_images($raw_image);
        
        foreach ($images_to_delete as $img_path) {
            if ($img_path && file_exists('../' . $img_path)) {
                @unlink('../' . $img_path);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = "News article deleted successfully.";
        $action = 'list';
    } catch (PDOException $e) {
        $error = "Failed to delete news article: " . $e->getMessage();
        $action = 'list';
    }
}

// Processing Add or Edit POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS));
    $content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS));
    $date_posted = trim(filter_input(INPUT_POST, 'date_posted', FILTER_SANITIZE_SPECIAL_CHARS));
    $author = trim(filter_input(INPUT_POST, 'author', FILTER_SANITIZE_SPECIAL_CHARS));
    
    // Validations
    if (empty($title) || empty($content) || empty($date_posted) || empty($author)) {
        $error = "Title, Content, Date Posted, and Author are required fields.";
    } else {
        // Keep existing images that were not removed
        $existing_images = isset($_POST['existing_images']) && is_array($_POST['existing_images']) ? $_POST['existing_images'] : [];
        $deleted_images = isset($_POST['deleted_images']) && is_array($_POST['deleted_images']) ? $_POST['deleted_images'] : [];
        
        // Remove deleted files from server disk
        foreach ($deleted_images as $del_img) {
            if ($del_img && file_exists('../' . $del_img)) {
                @unlink('../' . $del_img);
            }
        }
        
        $final_images = array_values(array_diff($existing_images, $deleted_images));
        
        // Handle Multiple Image Uploads (name="images[]")
        $uploaded_files = isset($_FILES['images']) ? $_FILES['images'] : (isset($_FILES['image']) ? $_FILES['image'] : null);
        
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
                    $file_tmp = is_array($uploaded_files['tmp_name']) ? $uploaded_files['tmp_name'][$i] : $uploaded_files['tmp_name'];
                    $file_name = is_array($uploaded_files['name']) ? $uploaded_files['name'][$i] : $uploaded_files['name'];
                    $file_size = is_array($uploaded_files['size']) ? $uploaded_files['size'][$i] : $uploaded_files['size'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    if (!in_array($file_ext, $allowed_exts)) {
                        $error = "Invalid file type: '$file_name'. Allowed: " . implode(', ', $allowed_exts);
                        break;
                    } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
                        $error = "File '$file_name' exceeds 5MB limit.";
                        break;
                    } else {
                        $new_filename = 'news_' . time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $file_ext;
                        $image_path = 'uploads/' . $new_filename;
                        
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
                    $stmt = $pdo->prepare("INSERT INTO news (title, content, image, date_posted, author) VALUES (:title, :content, :image, :date_posted, :author)");
                    $stmt->execute([
                        'title' => $title,
                        'content' => $content,
                        'image' => $image_json,
                        'date_posted' => $date_posted,
                        'author' => $author
                    ]);
                    $message = "News article published successfully.";
                } else {
                    $stmt = $pdo->prepare("UPDATE news SET title = :title, content = :content, image = :image, date_posted = :date_posted, author = :author WHERE id = :id");
                    $stmt->execute([
                        'title' => $title,
                        'content' => $content,
                        'image' => $image_json,
                        'date_posted' => $date_posted,
                        'author' => $author,
                        'id' => $id
                    ]);
                    $message = "News article updated successfully.";
                }
                $action = 'list';
            } catch (PDOException $e) {
                $error = "Database execution failed: " . $e->getMessage();
            }
        }
    }
}

// Fetch news data if editing
$news_data = null;
if ($action === 'edit' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $news_data = $stmt->fetch();
        if (!$news_data) {
            $error = "News article not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Failed to fetch article: " . $e->getMessage();
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
        <a href="manage_news.php" class="btn-action btn-edit">← Back to List</a>
    </div>
    
    <div class="form-card">
        <h3 class="form-title"><?php echo $action === 'add' ? 'Publish News Announcement' : 'Edit News Details'; ?></h3>
        
        <form action="manage_news.php?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" method="POST" enctype="multipart/form-data">
            
            <div class="form-grid">
                <div class="form-group form-grid-full">
                    <label for="title" class="form-label">Article Title</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Barangay Hall Expansion Project Launched" value="<?php echo htmlspecialchars($news_data['title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group form-grid-full">
                    <label for="content" class="form-label">Article Body Content</label>
                    <textarea name="content" id="content" class="form-control" placeholder="Type news article contents here..." style="min-height: 200px;" required><?php echo htmlspecialchars($news_data['content'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="date_posted" class="form-label">Publication Date</label>
                    <input type="date" name="date_posted" id="date_posted" class="form-control" value="<?php echo htmlspecialchars($news_data['date_posted'] ?? date('Y-m-d')); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="author" class="form-label">Author / Department</label>
                    <input type="text" name="author" id="author" class="form-control" placeholder="e.g. Barangay Zone 12-A Secretariat" value="<?php echo htmlspecialchars($news_data['author'] ?? 'Barangay Zone 12-A'); ?>" required>
                </div>
                
                <div class="form-group form-grid-full">
                    <label for="images" class="form-label">Article Images (Upload multiple images)</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                    <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.35rem;">
                        💡 You can hold <kbd style="background: #e2e8f0; padding: 2px 5px; border-radius: 3px;">Ctrl</kbd> or <kbd style="background: #e2e8f0; padding: 2px 5px; border-radius: 3px;">Cmd</kbd> to select multiple image files at once.
                    </div>
                    
                    <?php 
                    $existing_imgs = $news_data ? parse_news_images($news_data['image']) : [];
                    if (!empty($existing_imgs)): 
                    ?>
                        <div style="margin-top: 1.25rem;">
                            <label class="form-label">Currently Attached Images (<?php echo count($existing_imgs); ?>):</label>
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
                Publish Announcement
            </button>
        </form>

        <script>
        function removeImageCard(idx, imgPath) {
            const card = document.getElementById('img-card-' + idx);
            if (card) {
                card.remove();
            }
            const deletedDiv = document.getElementById('deleted-inputs');
            if (deletedDiv) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'deleted_images[]';
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
            <h3 style="color: var(--color-primary);">News & Announcements List</h3>
            <a href="manage_news.php?action=add" class="btn-action btn-add">+ Publish News</a>
        </div>
        
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM news ORDER BY date_posted DESC");
            $news_list = $stmt->fetchAll();
        } catch (PDOException $e) {
            $news_list = [];
        }
        ?>
        
        <?php if (!empty($news_list)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Images</th>
                        <th>Publication Date</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news_list as $row): ?>
                        <tr>
                            <td>
                                <?php 
                                $imgs = parse_news_images($row['image']);
                                if (!empty($imgs)): 
                                ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <img src="../<?php echo htmlspecialchars($imgs[0]); ?>" alt="Thumbnail" style="width: 60px; height: 40px; border-radius: 4px; object-fit: cover;">
                                        <?php if (count($imgs) > 1): ?>
                                            <span class="badge" style="background-color: #dbeafe; color: #1e40af; font-size: 0.7rem; padding: 0.2rem 0.4rem;"><?php echo count($imgs); ?> photos</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="width: 60px; height: 40px; border-radius: 4px; background-color: var(--color-border); display: flex; align-items: center; justify-content: center; font-size: 0.85rem;" class="text-muted">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--color-text-muted);">
                                <?php echo date("M d, Y", strtotime($row['date_posted'])); ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['author']); ?></td>
                            <td style="text-align: right;">
                                <a href="manage_news.php?action=edit&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                                <a href="manage_news.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this news article? This will permanently remove its image(s) from the server.');"><i class="bi bi-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 3rem; text-align: center;" class="text-muted">
                No news announcements have been published yet. Click "+ Publish News" to begin.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
require_once 'includes/admin_footer.php';
?>
