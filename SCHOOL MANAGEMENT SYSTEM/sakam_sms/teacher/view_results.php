<?php
/**
 * View Results Page
 * Sakam M/A JHS School Management System
 * 
 * View detailed student results and report card
 */

// Require teacher access
require_once '../includes/functions.php';
requireTeacher();

// Page title
$pageTitle = 'View Results';

// Get filter values
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$term = isset($_GET['term']) ? $_GET['term'] : '';

// Get classes for filter
$classes = getAllClasses();

// Get students for filter (if class selected)
$students = [];
if ($classId > 0) {
    $students = fetchAll("SELECT id, first_name, last_name, student_id FROM students WHERE class_id = ? AND status = 'Active' ORDER BY last_name, first_name", [$classId]);
}

// Get results if student is selected
$results = [];
$student = null;
if ($studentId > 0) {
    $student = fetchOne("SELECT s.*, c.class_name FROM students s JOIN classes c ON s.class_id = c.id WHERE s.id = ?", [$studentId]);
    
    if ($student) {
        $query = "SELECT r.*, sub.subject_name, sub.subject_code, sub.pass_mark 
                  FROM results r 
                  JOIN subjects sub ON r.subject_id = sub.id 
                  WHERE r.student_id = ?";
        
        $params = [$studentId];
        
        if (!empty($term)) {
            $query .= " AND r.term = ?";
            $params[] = $term;
        }
        
        $query .= " ORDER BY sub.subject_name";
        
        $results = fetchAll($query, $params);
    }
}

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-file-alt"></i> View Results</h1>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Select Student</h3>
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
                    <select id="student_id" name="student_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Select Student</option>
                        <?php foreach ($students as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $studentId == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['student_id'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="term">Term</label>
                    <select id="term" name="term" class="form-control" onchange="this.form.submit()">
                        <option value="">All Terms</option>
                        <option value="Term 1" <?php echo $term === 'Term 1' ? 'selected' : ''; ?>>Term 1</option>
                        <option value="Term 2" <?php echo $term === 'Term 2' ? 'selected' : ''; ?>>Term 2</option>
                        <option value="Term 3" <?php echo $term === 'Term 3' ? 'selected' : ''; ?>>Term 3</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Report -->
<?php if ($student && !empty($results)): ?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-graduation-cap"></i> Report Card - <?php echo escapeHTML($student['first_name'] . ' ' . $student['last_name']); ?></h3>
        <button onclick="window.print()" class="btn btn-outline">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
    <div class="card-body">
        <!-- Student Info -->
        <div class="report-header">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Student ID:</span>
                    <span class="value"><?php echo escapeHTML($student['student_id']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo escapeHTML($student['first_name'] . ' ' . $student['last_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Class:</span>
                    <span class="value"><?php echo escapeHTML($student['class_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Gender:</span>
                    <span class="value"><?php echo escapeHTML($student['gender']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Results Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>CA (30)</th>
                        <th>Exam (70)</th>
                        <th>Total (100)</th>
                        <th>Pass Mark</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalScore = 0;
                    $subjectCount = 0;
                    $passedCount = 0;
                    
                    foreach ($results as $result): 
                        $total = $result['ca_score'] + $result['exam_score'];
                        $totalScore += $total;
                        $subjectCount++;
                        if ($total >= $result['pass_mark']) {
                            $passedCount++;
                        }
                        
                        $grade = getGrade($total);
                        $gradeColor = getGradeColor($grade);
                        $status = $total >= $result['pass_mark'] ? 'Pass' : 'Fail';
                    ?>
                    <tr>
                        <td><strong><?php echo escapeHTML($result['subject_name']); ?></strong></td>
                        <td><?php echo escapeHTML($result['subject_code']); ?></td>
                        <td><?php echo $result['ca_score']; ?></td>
                        <td><?php echo $result['exam_score']; ?></td>
                        <td><strong><?php echo $total; ?></strong></td>
                        <td><?php echo $result['pass_mark']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $gradeColor; ?>">
                                <?php echo $grade; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $status === 'Pass' ? 'success' : 'danger'; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="4"><strong>Summary</strong></td>
                        <td><strong><?php echo $totalScore; ?></strong></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Summary Statistics -->
        <div class="report-summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Total Subjects:</span>
                    <span class="value"><?php echo $subjectCount; ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Passed:</span>
                    <span class="value success"><?php echo $passedCount; ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Failed:</span>
                    <span class="value danger"><?php echo $subjectCount - $passedCount; ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Average Score:</span>
                    <span class="value"><?php echo $subjectCount > 0 ? round($totalScore / $subjectCount, 1) : 0; ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Pass Rate:</span>
                    <span class="value"><?php echo $subjectCount > 0 ? round(($passedCount / $subjectCount) * 100, 1) : 0; ?>%</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($studentId > 0): ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <p>No results found for this student in the selected term.</p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <p>Select a student to view their results.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Helper functions
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

// Include footer
include '../includes/footer.php';
?>