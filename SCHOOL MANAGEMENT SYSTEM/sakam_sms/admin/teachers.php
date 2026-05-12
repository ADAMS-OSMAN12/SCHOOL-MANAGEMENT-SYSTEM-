<?php
/**
 * Teachers List Page
 * Sakam M/A JHS School Management System
 * 
 * Display and manage teacher records
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Teachers';

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($statusFilter) {
    $where[] = "t.status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $where[] = "(t.first_name LIKE ? OR t.last_name LIKE ? OR t.staff_id LIKE ? OR t.email LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$totalTeachers = fetchOne("SELECT COUNT(*) as total FROM teachers t $whereClause", $params);
$totalCount = $totalTeachers['total'] ?? 0;

// Pagination
$perPage = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalPages = ceil($totalCount / $perPage);
$offset = ($page - 1) * $perPage;

// Get teachers
$teachers = fetchAll("
    SELECT t.*, u.username
    FROM teachers t
    LEFT JOIN users u ON u.teacher_id = t.id
    $whereClause
    ORDER BY t.last_name, t.first_name
    LIMIT $perPage OFFSET $offset
", $params);

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-chalkboard-teacher"></i> Teachers</h1>
    <a href="add_teacher.php" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add Teacher
    </a>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-group">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
            
            <input type="text" name="search" class="form-control" placeholder="Search teachers..." value="<?php echo escapeHTML($search); ?>">
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            
            <a href="teachers.php" class="btn btn-outline">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- Teachers Table -->
<div class="card">
    <div class="card-header">
        <h3>All Teachers (<?php echo number_format($totalCount); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($teachers)): ?>
        <div class="empty-state">
            <i class="fas fa-chalkboard-teacher"></i>
            <h3>No teachers found</h3>
            <p>Try adjusting your filters or add a new teacher.</p>
            <a href="add_teacher.php" class="btn btn-primary mt-2">Add Teacher</a>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                    <tr>
                        <td><?php echo escapeHTML($teacher['staff_id']); ?></td>
                        <td>
                            <strong><?php echo escapeHTML($teacher['first_name'] . ' ' . $teacher['last_name']); ?></strong>
                        </td>
                        <td><?php echo $teacher['gender']; ?></td>
                        <td><?php echo escapeHTML($teacher['email']); ?></td>
                        <td><?php echo escapeHTML($teacher['contact']); ?></td>
                        <td>
    <?php 
    if (!empty($teacher['subject_id'])) {
        $subject = fetchOne("SELECT subject_name FROM subjects WHERE id = ?", [$teacher['subject_id']]);
        echo escapeHTML($subject['subject_name'] ?? 'N/A');
    } else {
        echo 'N/A';
    }
    ?>
</td>
                        <td>
                            <span class="badge badge-<?php echo $teacher['status'] === 'Active' ? 'success' : 'warning'; ?>">
                                <?php echo $teacher['status']; ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="teacher_profile.php?id=<?php echo $teacher['id']; ?>" class="view-btn" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteRecord(<?php echo $teacher['id']; ?>, 'teacher')" class="delete-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo $search; ?>">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo $search; ?>" 
               class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo $search; ?>">
                Next <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
            <p>Are you sure you want to delete this teacher? This action cannot be undone.</p>
            <p class="text-danger">This will also remove the teacher's login account.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline modal-cancel">Cancel</button>
            <a href="" id="deleteLink" class="btn btn-danger">Delete</a>
        </div>
    </div>
</div>

<script>
function deleteRecord(id, type) {
    const modal = document.getElementById('deleteModal');
    const deleteLink = document.getElementById('deleteLink');
    
    deleteLink.href = 'delete_teacher.php?id=' + id + '&token=<?php echo generateCSRFToken(); ?>';
    modal.classList.add('active');
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>