<?php
/**
 * Edit Result Page
 * Sakam M/A JHS School Management System
 * 
 * Form to edit existing student result
 */

// Require teacher access
require_once '../includes/functions.php';
requireTeacher();

// Page title
$pageTitle = 'Edit Result';

// Get result ID
$resultId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get result data
$result = fetchOne("SELECT * FROM results WHERE id = ?", [$resultId]);

if (!$result) {
    setMessage('Result not found.', 'error');
    redirect('results.php');
}

// Get classes for dropdown
$classes = getAllClasses();

// Get students for dropdown
$students = fetchAll("SELECT id, first_name, last_name, student_id FROM students WHERE class_id = ? AND status = 'Active' ORDER BY last_name, first_name", [$result['class_id']]);

// Get subjects for dropdown
$subjects = fetchAll("SELECT id, subject_name, subject_code FROM subjects WHERE class_id = ? OR class_id IS NULL ORDER BY subject_name", [$result['class_id']]);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $studentId = (int)($_POST['student_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $term = sanitizeInput($_POST['term'] ?? '');
        $caScore = (float)($_POST['ca_score'] ?? 0);
        $examScore = (float)($_POST['exam_score'] ?? 0);
        $examDate = sanitizeInput($_POST['exam_date'] ?? '');
        $comments = sanitizeInput($_POST['comments'] ?? '');
        
        // Validation
        $errors = [];
        
        if ($studentId <= 0) {
            $errors[] = 'Please select a student.';
        }
        
        if ($classId <= 0) {
            $errors[] = 'Please select a class.';
        }
        
        if ($subjectId <= 0) {
            $errors[] = 'Please select a subject.';
        }
        
        if (empty($term)) {
            $errors[] = 'Please select a term.';
        }
        
        if ($caScore < 0 || $caScore > 30) {
            $errors[] = 'CA Score must be between 0 and 30.';
        }
        
        if ($examScore < 0 || $examScore > 70) {
            $errors[] = 'Exam Score must be between 0 and 70.';
        }
        
        if (empty($examDate)) {
            $errors[] = 'Exam date is required.';
        }
        
        // Check if result already exists for this student/subject/term (excluding current)
        if ($studentId > 0 && $subjectId > 0 && !empty($term)) {
            $existing = fetchOne("SELECT id FROM results WHERE student_id = ? AND subject_id = ? AND term = ? AND id != ?", 
                [$studentId, $subjectId, $term, $resultId]);
            if ($existing) {
                $errors[] = 'A result already exists for this student, subject, and term.';
            }
        }
        
        if (empty($errors)) {
            // Update result
            $data = [
                'student_id' => $studentId,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'term' => $term,
                'ca_score' => $caScore,
                'exam_score' => $examScore,
                'exam_date' => $examDate,
                'comments' => $comments
            ];
            
            $updateResult = update('results', $data, "id = $resultId");
            
            if ($updateResult) {
                // Log activity
                $student = fetchOne("SELECT first_name, last_name FROM students WHERE id = ?", [$studentId]);
                $subject = fetchOne("SELECT subject_name FROM subjects WHERE id = ?", [$subjectId]);
                logActivity(getUserId(), 'Update Result', "Updated result for {$student['first_name']} {$student['last_name']} in {$subject['subject_name']}");
                
                // Set success message and redirect
                setMessage('Result updated successfully!', 'success');
                redirect('results.php');
            } else {
                $error = 'Failed to update result. Please try again.';
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-edit"></i> Edit Result</h1>
    <a href="results.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Results
    </a>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<!-- Edit Result Form -->
<div class="card">
    <div class="card-header">
        <h3>Result Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Class *</label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $result['class_id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($class['class_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="student_id">Student *</label>
                    <select id="student_id" name="student_id" class="form-control" required>
                        <option value="">Select Student</option>
                        <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" <?php echo $student['id'] == $result['student_id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['student_id'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="subject_id">Subject *</label>
                    <select id="subject_id" name="subject_id" class="form-control" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo $subject['id'] == $result['subject_id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="term">Term *</label>
                    <select id="term" name="term" class="form-control" required>
                        <option value="">Select Term</option>
                        <option value="Term 1" <?php echo $result['term'] === 'Term 1' ? 'selected' : ''; ?>>Term 1</option>
                        <option value="Term 2" <?php echo $result['term'] === 'Term 2' ? 'selected' : ''; ?>>Term 2</option>
                        <option value="Term 3" <?php echo $result['term'] === 'Term 3' ? 'selected' : ''; ?>>Term 3</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ca_score">CA Score (out of 30) *</label>
                    <input type="number" id="ca_score" name="ca_score" class="form-control" 
                           min="0" max="30" step="0.5" value="<?php echo $result['ca_score']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="exam_score">Exam Score (out of 70) *</label>
                    <input type="number" id="exam_score" name="exam_score" class="form-control" 
                           min="0" max="70" step="0.5" value="<?php echo $result['exam_score']; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="exam_date">Exam Date *</label>
                    <input type="date" id="exam_date" name="exam_date" class="form-control" 
                           value="<?php echo $result['exam_date']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="comments">Comments</label>
                    <input type="text" id="comments" name="comments" class="form-control" 
                           value="<?php echo escapeHTML($result['comments']); ?>" placeholder="Optional comments">
                </div>
            </div>
            
            <!-- Score Preview -->
            <div class="score-preview">
                <h4>Score Preview</h4>
                <div class="preview-grid">
                    <div class="preview-item">
                        <span class="label">CA Score:</span>
                        <span class="value" id="ca-preview"><?php echo $result['ca_score']; ?></span>
                    </div>
                    <div class="preview-item">
                        <span class="label">Exam Score:</span>
                        <span class="value" id="exam-preview"><?php echo $result['exam_score']; ?></span>
                    </div>
                    <div class="preview-item">
                        <span class="label">Total:</span>
                        <span class="value" id="total-preview"><?php echo $result['ca_score'] + $result['exam_score']; ?></span>
                    </div>
                    <div class="preview-item">
                        <span class="label">Grade:</span>
                        <span class="value badge" id="grade-preview"><?php 
                            $total = $result['ca_score'] + $result['exam_score'];
                            if ($total >= 90) echo 'A1';
                            elseif ($total >= 80) echo 'B2';
                            elseif ($total >= 70) echo 'B3';
                            elseif ($total >= 60) echo 'C4';
                            elseif ($total >= 50) echo 'C5';
                            elseif ($total >= 40) echo 'C6';
                            elseif ($total >= 35) echo 'D7';
                            elseif ($total >= 30) echo 'E8';
                            else echo 'F9';
                        ?></span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Result
                </button>
                <a href="results.php" class="btn btn-outline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Score preview
document.getElementById('ca_score').addEventListener('input', updatePreview);
document.getElementById('exam_score').addEventListener('input', updatePreview);

function updatePreview() {
    const ca = parseFloat(document.getElementById('ca_score').value) || 0;
    const exam = parseFloat(document.getElementById('exam_score').value) || 0;
    const total = ca + exam;
    
    document.getElementById('ca-preview').textContent = ca;
    document.getElementById('exam-preview').textContent = exam;
    document.getElementById('total-preview').textContent = total;
    
    let grade = '-';
    let color = 'secondary';
    
    if (total >= 90) { grade = 'A1'; color = 'success'; }
    else if (total >= 80) { grade = 'B2'; color = 'success'; }
    else if (total >= 70) { grade = 'B3'; color = 'success'; }
    else if (total >= 60) { grade = 'C4'; color = 'warning'; }
    else if (total >= 50) { grade = 'C5'; color = 'warning'; }
    else if (total >= 40) { grade = 'C6'; color = 'warning'; }
    else if (total >= 35) { grade = 'D7'; color = 'danger'; }
    else if (total >= 30) { grade = 'E8'; color = 'danger'; }
    else { grade = 'F9'; color = 'danger'; }
    
    const gradeEl = document.getElementById('grade-preview');
    gradeEl.textContent = grade;
    gradeEl.className = 'value badge badge-' + color;
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>