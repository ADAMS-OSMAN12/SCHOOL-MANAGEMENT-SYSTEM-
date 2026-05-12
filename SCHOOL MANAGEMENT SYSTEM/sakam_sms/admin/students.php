<?php
/**
 * Students List Page
 * Sakam M/A JHS School Management System
 * 
 * Display and manage student records
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Students';

// Get filter parameters
$classFilter = $_GET['class'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($classFilter) {
    $where[] = "s.class_id = ?";
    $params[] = $classFilter;
}

if ($statusFilter) {
    $where[] = "s.status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR s.parent_contact LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$totalStudents = fetchOne("SELECT COUNT(*) as total FROM students s $whereClause", $params);
$totalCount = $totalStudents['total'] ?? 0;

// Pagination
$perPage = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalPages = ceil($totalCount / $perPage);
$offset = ($page - 1) * $perPage;

// Get students
$students = fetchAll("
    SELECT s.*, c.class_name 
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    $whereClause
    ORDER BY s.last_name, s.first_name
    LIMIT $perPage OFFSET $offset
", $params);

// Get all classes for filter
$classes = getAllClasses();

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-user-graduate"></i> Students</h1>
    <a href="add_student.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Student
    </a>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-group">
            <select name="class" class="form-control">
                <option value="">All Classes</option>
                <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['id']; ?>" <?php echo $classFilter == $class['id'] ? 'selected' : ''; ?>>
                    <?php echo escapeHTML($class['class_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="Suspended" <?php echo $statusFilter === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                <option value="Graduated" <?php echo $statusFilter === 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
            </select>
            
            <input type="text" name="search" class="form-control" placeholder="Search students..." value="<?php echo escapeHTML($search); ?>">
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            
            <a href="students.php" class="btn btn-outline">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-header">
        <h3>All Students (<?php echo number_format($totalCount); ?>)</h3>
        <button onclick="exportToCSV('studentsTable', 'students')" class="btn btn-sm btn-outline">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($students)): ?>
        <div class="empty-state">
            <i class="fas fa-user-graduate"></i>
            <h3>No students found</h3>
            <p>Try adjusting your filters or add a new student.</p>
            <a href="add_student.php" class="btn btn-primary mt-2">Add Student</a>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>Class</th>
                        <th>Parent Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo escapeHTML($student['student_id']); ?></td>
                        <td>
                            <strong><?php echo escapeHTML($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                        </td>
                        <td><?php echo $student['gender']; ?></td>
                        <td><?php echo formatDate($student['date_of_birth']); ?></td>
                        <td><?php echo escapeHTML($student['class_name']); ?></td>
                        <td><?php echo escapeHTML($student['parent_contact']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $student['status'] === 'Active' ? 'success' : 
                                    ($student['status'] === 'Suspended' ? 'danger' : 
                                    ($student['status'] === 'Graduated' ? 'primary' : 'warning')); 
                            ?>">
                                <?php echo $student['status']; ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="student_profile.php?id=<?php echo $student['id']; ?>" class="view-btn" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteRecord(<?php echo $student['id']; ?>, 'student')" class="delete-btn" title="Delete">
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
            <a href="?page=<?php echo $page - 1; ?>&class=<?php echo $classFilter; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo $search; ?>">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&class=<?php echo $classFilter; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo $search; ?>" 
               class="<?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>&class=<?php echo $classFilter; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo $search; ?>">
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
            <p>Are you sure you want to delete this student? This action cannot be undone.</p>
            <p class="text-danger">All related records (results, attendance, fees) will also be deleted.</p>
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
    
    deleteLink.href = 'delete_' + type + '.php?id=' + id;
    modal.classList.add('active');
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>