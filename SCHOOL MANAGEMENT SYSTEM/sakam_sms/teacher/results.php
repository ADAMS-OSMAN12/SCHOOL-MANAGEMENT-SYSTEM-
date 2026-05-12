<?php
/**
 * Results Management Page
 * Sakam M/A JHS School Management System
 * 
 * View and manage student results
 */

// Require teacher access
require_once '../includes/functions.php';
requireTeacher();

// Page title
$pageTitle = 'Results Management';

// Get filter values
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$term = isset($_GET['term']) ? $_GET['term'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$query = "SELECT r.*, s.first_name, s.last_name, s.student_id as sid, sub.subject_name, sub.subject_code, c.class_name 
          FROM results r 
          JOIN students s ON r.student_id = s.id 
          JOIN subjects sub ON r.subject_id = sub.id 
          JOIN classes c ON s.class_id = c.id 
          WHERE 1=1";

$params = [];

if ($classId > 0) {
    $query .= " AND s.class_id = ?";
    $params[] = $classId;
}

if ($studentId > 0) {
    $query .= " AND r.student_id = ?";
    $params[] = $studentId;
}

if (!empty($term)) {
    $query .= " AND r.term = ?";
    $params[] = $term;
}

if (!empty($search)) {
    $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY r.created_at DESC, s.last_name ASC";

// Get results
$results = fetchAll($query, $params);

// Get classes for filter
$classes = getAllClasses();

// Get students for filter (if class selected)
$students = [];
if ($classId > 0) {
    $students = fetchAll("SELECT id, first_name, last_name, student_id FROM students WHERE class_id = ? AND status = 'Active' ORDER BY last_name, first_name", [$classId]);
}

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Results Management</h1>
    <a href="add_result.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Result
    </a>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filter Results</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $classId == $class['id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($class['class_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="student_id">Student</label>
                    <select id="student_id" name="student_id" class="form-control">
                        <option value="">All Students</option>
                        <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" <?php echo $studentId == $student['id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['sid'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="term">Term</label>
                    <select id="term" name="term" class="form-control">
                        <option value="">All Terms</option>
                        <option value="Term 1" <?php echo $term === 'Term 1' ? 'selected' : ''; ?>>Term 1</option>
                        <option value="Term 2" <?php echo $term === 'Term 2' ? 'selected' : ''; ?>>Term 2</option>
                        <option value="Term 3" <?php echo $term === 'Term 3' ? 'selected' : ''; ?>>Term 3</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Search by name or ID" value="<?php echo escapeHTML($search); ?>">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="results.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Results (<?php echo count($results); ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($results)): ?>
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <p>No results found.</p>
            <a href="add_result.php" class="btn btn-primary">Add First Result</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Term</th>
                        <th>CA (30)</th>
                        <th>Exam (70)</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): 
                        $total = $result['ca_score'] + $result['exam_score'];
                        $grade = getGrade($total);
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo escapeHTML($result['first_name'] . ' ' . $result['last_name']); ?></strong>
                            <br><small class="text-muted"><?php echo escapeHTML($result['sid']); ?></small>
                        </td>
                        <td><?php echo escapeHTML($result['class_name']); ?></td>
                        <td>
                            <?php echo escapeHTML($result['subject_name']); ?>
                            <br><small class="text-muted"><?php echo escapeHTML($result['subject_code']); ?></small>
                        </td>
                        <td><?php echo escapeHTML($result['term']); ?></td>
                        <td><?php echo $result['ca_score']; ?></td>
                        <td><?php echo $result['exam_score']; ?></td>
                        <td><strong><?php echo $total; ?></strong></td>
                        <td>
                            <span class="badge badge-<?php echo getGradeColor($grade); ?>">
                                <?php echo $grade; ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($result['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_result.php?id=<?php echo $result['id']; ?>" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_result.php?id=<?php echo $result['id']; ?>&token=<?php echo generateCSRFToken(); ?>" 
                                   class="btn-icon btn-danger" title="Delete" 
                                   onclick="return confirm('Are you sure you want to delete this result?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';

// Helper function for grade
function getGrade($score) {
    if ($score >= 90) return 'A1';
    if ($score >= 80) return 'B2';
    if ($score >= 70) return 'B3';
    if ($score >= 60) return 'C4';
    if ($score >= 50) return 'C5';
    if ($score >= 40) return 'C6';
    if ($score >= 35) return 'D7';
    if ($score >= 30) return 'E8';
    return 'F9';
}

function getGradeColor($grade) {
    if (in_array($grade, ['A1', 'B2', 'B3'])) return 'success';
    if (in_array($grade, ['C4', 'C5', 'C6'])) return 'warning';
    return 'danger';
}
?>