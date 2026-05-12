<?php
/**
 * Classes List Page
 * Sakam M/A JHS School Management System
 * 
 * Display and manage class records
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Classes';

// Get all classes with teacher info
$classes = fetchAll("
    SELECT c.*, t.first_name as teacher_first_name, t.last_name as teacher_last_name,
           (SELECT COUNT(*) FROM students WHERE class_id = c.id AND status = 'Active') as student_count
    FROM classes c
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY c.class_level, c.class_name
");

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-door-open"></i> Classes</h1>
    <a href="add_class.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Class
    </a>
</div>

<!-- Classes Grid -->
<div class="card">
    <div class="card-header">
        <h3>All Classes (<?php echo count($classes); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($classes)): ?>
        <div class="empty-state">
            <i class="fas fa-door-open"></i>
            <h3>No classes yet</h3>
            <p>Add your first class to get started.</p>
            <a href="add_class.php" class="btn btn-primary mt-2">Add Class</a>
        </div>
        <?php else: ?>
        <div class="grid-3">
            <?php foreach ($classes as $class): ?>
            <div class="grid-card">
                <div class="grid-card-header">
                    <h4><?php echo escapeHTML($class['class_name']); ?></h4>
<span class="badge badge-success">Active</span>
                </div>
                <div class="grid-card-body">
                    <div class="grid-item">
                        <i class="fas fa-layer-group"></i>
                        <span>Level: <?php echo $class['class_level']; ?></span>
                    </div>
                    <div class="grid-item">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students: <?php echo $class['student_count']; ?></span>
                    </div>
                    <div class="grid-item">
                        <i class="fas fa-user-tie"></i>
                        <span>Class Teacher: <?php echo $class['teacher_first_name'] ? escapeHTML($class['teacher_first_name'] . ' ' . $class['teacher_last_name']) : 'Not Assigned'; ?></span>
                    </div>
                </div>
                <div class="grid-card-footer">
                    <a href="edit_class.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button onclick="deleteRecord(<?php echo $class['id']; ?>)" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this class? This action cannot be undone.</p>
            <p class="text-danger">Students in this class will need to be reassigned.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline modal-cancel">Cancel</button>
            <a href="" id="deleteLink" class="btn btn-danger">Delete</a>
        </div>
    </div>
</div>

<script>
function deleteRecord(id) {
    const modal = document.getElementById('deleteModal');
    const deleteLink = document.getElementById('deleteLink');
    
    deleteLink.href = 'delete_class.php?id=' + id + '&token=<?php echo generateCSRFToken(); ?>';
    modal.classList.add('active');
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>