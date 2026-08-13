<?php
/**
 * Events & Programs Admin Manager - Barangay Zone 12-A
 */
$active_tab  = 'events';
$admin_title = 'Manage Events & Programs';

require_once 'includes/admin_header.php';

// Image helper
function parse_event_images($d) {
    if (empty($d)) return [];
    $a = json_decode($d, true);
    return is_array($a) ? array_values($a) : [$d];
}

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `event_date` DATE NOT NULL,
        `event_time` TIME DEFAULT NULL,
        `location` VARCHAR(255) DEFAULT NULL,
        `category` VARCHAR(100) DEFAULT 'General',
        `image` TEXT DEFAULT NULL,
        `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {}

$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error   = '';

// DELETE
if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT image FROM events WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $raw = $stmt->fetchColumn();
        foreach (parse_event_images($raw) as $img) {
            if ($img && file_exists('../' . $img)) @unlink('../' . $img);
        }
        $pdo->prepare("DELETE FROM events WHERE id = :id")->execute(['id' => $id]);
        $message = "Event deleted.";
        $action  = 'list';
    } catch (PDOException $e) {
        $error  = "Delete failed: " . $e->getMessage();
        $action = 'list';
    }
}

// ADD / EDIT POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add','edit'])) {
    $title      = trim(filter_input(INPUT_POST, 'title',      FILTER_SANITIZE_SPECIAL_CHARS));
    $description= trim(filter_input(INPUT_POST, 'description',FILTER_SANITIZE_SPECIAL_CHARS));
    $event_date = trim(filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_SPECIAL_CHARS));
    $event_time = trim(filter_input(INPUT_POST, 'event_time', FILTER_SANITIZE_SPECIAL_CHARS));
    $location   = trim(filter_input(INPUT_POST, 'location',   FILTER_SANITIZE_SPECIAL_CHARS));
    $category   = trim(filter_input(INPUT_POST, 'category',   FILTER_SANITIZE_SPECIAL_CHARS));

    if (empty($title) || empty($event_date)) {
        $error = "Event title and date are required.";
    } else {
        $existing_images = $_POST['existing_images'] ?? [];
        $deleted_images  = $_POST['deleted_images']  ?? [];
        foreach ($deleted_images as $di) { if ($di && file_exists('../'.$di)) @unlink('../'.$di); }
        $final_images = array_values(array_diff($existing_images, $deleted_images));

        if (!empty($_FILES['images']['name'][0])) {
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!file_exists('../uploads')) mkdir('../uploads', 0777, true);
            foreach ($_FILES['images']['name'] as $i => $fname) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed) || $_FILES['images']['size'][$i] > 5*1024*1024) continue;
                $new  = 'event_' . time() . '_' . rand(1000,9999) . '_' . $i . '.' . $ext;
                $path = 'uploads/' . $new;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], '../'.$path)) $final_images[] = $path;
            }
        }

        $image_json = !empty($final_images) ? json_encode(array_values($final_images)) : null;

        if (empty($error)) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, category, image) VALUES (:title,:desc,:date,:time,:loc,:cat,:img)");
                    $stmt->execute(['title'=>$title,'desc'=>$description?:null,'date'=>$event_date,'time'=>$event_time?:null,'loc'=>$location?:null,'cat'=>$category?:'General','img'=>$image_json]);
                    $message = "Event created.";
                } else {
                    $stmt = $pdo->prepare("UPDATE events SET title=:title, description=:desc, event_date=:date, event_time=:time, location=:loc, category=:cat, image=:img WHERE id=:id");
                    $stmt->execute(['title'=>$title,'desc'=>$description?:null,'date'=>$event_date,'time'=>$event_time?:null,'loc'=>$location?:null,'cat'=>$category?:'General','img'=>$image_json,'id'=>$id]);
                    $message = "Event updated.";
                }
                $action = 'list';
            } catch (PDOException $e) { $error = "DB error: " . $e->getMessage(); }
        }
    }
}

// Fetch for edit
$ev_data = null;
if ($action === 'edit' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $ev_data = $stmt->fetch();
        if (!$ev_data) { $error = "Event not found."; $action = 'list'; }
    } catch (PDOException $e) { $error = "Fetch failed."; $action = 'list'; }
}

$categories = ['General','Health','Education','Livelihood','SK / Youth','Clean-up / Environment','Relief Distribution','Sports','Cultural','Seminar / Training'];
?>

<?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div style="margin-bottom:1.5rem;"><a href="manage_events.php" class="btn-action btn-edit">← Back to List</a></div>

    <div class="form-card">
        <h3 class="form-title"><?php echo $action === 'add' ? 'Add New Event / Program' : 'Edit Event'; ?></h3>
        <form action="manage_events.php?action=<?php echo $action; ?><?php echo $id ? '&id='.$id : ''; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group form-grid-full">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Free Medical Mission" value="<?php echo htmlspecialchars($ev_data['title'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-control" value="<?php echo htmlspecialchars($ev_data['event_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Time <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></label>
                    <input type="time" name="event_time" class="form-control" value="<?php echo htmlspecialchars($ev_data['event_time'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($ev_data['category'] ?? 'General') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Location <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></label>
                    <input type="text" name="location" class="form-control" placeholder="e.g. Barangay Hall, Zone 12-A" value="<?php echo htmlspecialchars($ev_data['location'] ?? ''); ?>">
                </div>
                <div class="form-group form-grid-full">
                    <label class="form-label">Description <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></label>
                    <textarea name="description" class="form-control" style="min-height:100px;" placeholder="Brief description of the event..."><?php echo htmlspecialchars($ev_data['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group form-grid-full">
                    <label class="form-label">Event Photos <span style="font-weight:400;color:var(--color-text-muted)">(optional, multiple)</span></label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <div style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.3rem;">
                        💡 Hold <kbd style="background:#e2e8f0;padding:2px 5px;border-radius:3px;">Ctrl</kbd> to select multiple photos. Max 5MB each.
                    </div>

                    <?php $existing_imgs = $ev_data ? parse_event_images($ev_data['image']) : []; ?>
                    <?php if (!empty($existing_imgs)): ?>
                        <div style="margin-top:1.25rem;">
                            <label class="form-label">Current Photos (<?php echo count($existing_imgs); ?>):</label>
                            <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-top:0.5rem;" id="existing-images-container">
                                <?php foreach ($existing_imgs as $idx => $img): ?>
                                    <div id="img-card-<?php echo $idx; ?>" style="position:relative;width:110px;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:5px;background:#fff;text-align:center;box-shadow:var(--shadow-sm);">
                                        <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($img); ?>">
                                        <img src="../<?php echo htmlspecialchars($img); ?>" style="width:100%;height:75px;object-fit:cover;border-radius:4px;">
                                        <button type="button" onclick="removeImg(<?php echo $idx; ?>,'<?php echo htmlspecialchars($img); ?>')" style="margin-top:5px;background:#fee2e2;color:#ef4444;border:1px solid #fecaca;border-radius:4px;padding:3px 6px;font-size:0.75rem;font-weight:600;cursor:pointer;width:100%;"><i class="bi bi-trash"></i> Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div id="deleted-inputs"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn-action btn-add" style="margin-top:1.5rem;font-size:0.95rem;width:100%;justify-content:center;border:none;">
                Save Event
            </button>
        </form>
        <script>
        function removeImg(idx, path) {
            const c = document.getElementById('img-card-'+idx); if(c) c.remove();
            const d = document.getElementById('deleted-inputs');
            if(d) { const i=document.createElement('input');i.type='hidden';i.name='deleted_images[]';i.value=path;d.appendChild(i); }
        }
        </script>
    </div>

<?php else: ?>
    <!-- LIST VIEW -->
    <div class="table-card">
        <div class="table-header">
            <h3 style="color:var(--color-primary);">Events & Programs List</h3>
            <a href="manage_events.php?action=add" class="btn-action btn-add">+ Add Event</a>
        </div>
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
            $list = $stmt->fetchAll();
        } catch (PDOException $e) { $list = []; }
        $today = date('Y-m-d');
        ?>
        <?php if (!empty($list)): ?>
            <table class="admin-table">
                <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Location</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($list as $row): ?>
                    <tr>
                        <td style="font-size:0.85rem;white-space:nowrap;"><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                        <td><span class="badge" style="background:#dbeafe;color:#1e40af;"><?php echo htmlspecialchars($row['category']); ?></span></td>
                        <td style="font-size:0.85rem;"><?php echo htmlspecialchars($row['location'] ?? '—'); ?></td>
                        <td>
                            <?php if ($row['event_date'] >= $today): ?>
                                <span class="badge" style="background:#d1fae5;color:#059669;">Upcoming</span>
                            <?php else: ?>
                                <span class="badge" style="background:#e2e8f0;color:#64748b;">Past</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <a href="manage_events.php?action=edit&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a href="manage_events.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this event?');"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding:3rem;text-align:center;" class="text-muted">No events yet. Click "+ Add Event" to create one.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
