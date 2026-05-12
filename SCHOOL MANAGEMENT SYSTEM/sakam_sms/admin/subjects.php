<?php
/**
 * Subjects List Page
 * Sakam M/A JHS School Management System
 * 
 * Display and manage subject records
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Subjects';

// Get all subjects with teacher info
$subjects = fetchAll("
    SELECT s.*, t.first_name as teacher_first_name, t.last_name as teacher_last_name,
           c.class_name
    FROM subjects s
    LEFT JOIN teachers t ON s.teacher_id = t.id
    LEFT JOIN classes c ON s.class_id = c.id
    ORDER BY s.subject_name
");

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-book"></i> Subjects</h1>
    <a href="add_subject.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Subject
    </a>
</div>

<!-- Subjects Table -->
<div class="card">
    <div class="card-header">
        <h3>All Subjects (<?php echo count($subjects); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($subjects)): ?>
        <div class="empty-state">
            <i class="fas fa-book"></i>
            <h3>No subjects yet</h3>
            <p>Add your first subject to get started.</p>
            <a href="add_subject.php" class="btn btn-primary mt-2">Add Subject</a>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Pass Mark</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo escapeHTML($subject['subject_code']); ?></td>
                        <td><strong><?php echo escapeHTML($subject['subject_name']); ?></strong></td>
                        <td><?php echo $subject['class_id'] ? escapeHTML($subject['class_name']) : 'All Classes'; ?></td>
                        <td><?php echo $subject['teacher_first_name'] ? escapeHTML($subject['teacher_first_name'] . ' ' . $subject['teacher_last_name']) : 'Not Assigned'; ?></td>
                        <td>-</td>
                        <td>
                            <span class="badge badge-success">Active</span>
                        </td>
                        <td class="table-actions">
                            <a href="edit_subject.php?id=<?php echo $subject['id']; ?>" class="edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteRecord(<?php echo $subject['id']; ?>)" class="delete-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
            <p>Are you sure you want to delete this subject? This action cannot be undone.</p>
            <p class="text-danger">All results for this subject will also be deleted.</p>
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
    
    deleteLink.href = 'delete_subject.php?id=' + id + '&token=<?php echo generateCSRFToken(); ?>';
    modal.classList.add('active');
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>