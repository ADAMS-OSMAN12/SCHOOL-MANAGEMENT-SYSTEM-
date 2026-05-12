<?php
/**
 * Add Result Page
 * Sakam M/A JHS School Management System
 * 
 * Form to add new student result
 */

// Require teacher access
require_once '../includes/functions.php';
requireTeacher();

// Page title
$pageTitle = 'Add Result';

// Get filter values
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

// Get classes for dropdown
$classes = getAllClasses();

// Get students for dropdown (if class selected)
$students = [];
if ($classId > 0) {
    $students = fetchAll("SELECT id, first_name, last_name, student_id FROM students WHERE class_id = ? AND status = 'Active' ORDER BY last_name, first_name", [$classId]);
}

// Get subjects for dropdown (if class selected)
$subjects = [];
if ($classId > 0) {
    $subjects = fetchAll("SELECT id, subject_name, subject_code FROM subjects WHERE class_id = ? OR class_id IS NULL ORDER BY subject_name", [$classId]);
}

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
        $examDate = sanitizeInput($_POST['exam_date'] ?? date('Y-m-d'));
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
        
        // Check if result already exists for this student/subject/term
        if ($studentId > 0 && $subjectId > 0 && !empty($term)) {
            $existing = fetchOne("SELECT id FROM results WHERE student_id = ? AND subject_id = ? AND term = ?", 
                [$studentId, $subjectId, $term]);
            if ($existing) {
                $errors[] = 'A result already exists for this student, subject, and term. Please edit the existing result instead.';
            }
        }
        
        if (empty($errors)) {
            // Insert result
            $data = [
                'student_id' => $studentId,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'term' => $term,
                'ca_score' => $caScore,
                'exam_score' => $examScore,
                'exam_date' => $examDate,
                'comments' => $comments,
                'entered_by' => getUserId(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = insert('results', $data);
            
            if ($result) {
                // Log activity
                $student = fetchOne("SELECT first_name, last_name FROM students WHERE id = ?", [$studentId]);
                $subject = fetchOne("SELECT subject_name FROM subjects WHERE id = ?", [$subjectId]);
                logActivity(getUserId(), 'Add Result', "Added result for {$student['first_name']} {$student['last_name']} in {$subject['subject_name']}");
                
                // Set success message and redirect
                setMessage('Result added successfully!', 'success');
                redirect('results.php');
            } else {
                $error = 'Failed to add result. Please try again.';
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
    <h1><i class="fas fa-plus-circle"></i> Add Result</h1>
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

<!-- Add Result Form -->
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
                    <select id="class_id" name="class_id" class="form-control" required onchange="loadStudentsAndSubjects(this.value)">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $classId == $class['id'] ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $student['id']; ?>" <?php echo $studentId == $student['id'] ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $subject['id']; ?>">
                            <?php echo escapeHTML($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="term">Term *</label>
                    <select id="term" name="term" class="form-control" required>
                        <option value="">Select Term</option>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ca_score">CA Score (out of 30) *</label>
                    <input type="number" id="ca_score" name="ca_score" class="form-control" 
                           min="0" max="30" step="0.5" value="0" required>
                </div>
                
                <div class="form-group">
                    <label for="exam_score">Exam Score (out of 70) *</label>
                    <input type="number" id="exam_score" name="exam_score" class="form-control" 
                           min="0" max="70" step="0.5" value="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="exam_date">Exam Date *</label>
                    <input type="date" id="exam_date" name="exam_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="comments">Comments</label>
                    <input type="text" id="comments" name="comments" class="form-control" 
                           placeholder="Optional comments">
                </div>
            </div>
            
            <!-- Score Preview -->
            <div class="score-preview">
                <h4>Score Preview</h4>
                <div class="preview-grid">
                    <div class="preview-item">
                        <span class="label">CA Score:</span>
                        <span class="value" id="ca-preview">0</span>
                    </div>
                    <div class="preview-item">
                        <span class="label">Exam Score:</span>
                        <span class="value" id="exam-preview">0</span>
                    </div>
                    <div class="preview-item">
                        <span class="label">Total:</span>
                        <span class="value" id="total-preview">0</span>
                    </div>
                    <div class="preview-item">
                        <span class="label">Grade:</span>
                        <span class="value badge" id="grade-preview">-</span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Result
                </button>
                <a href="results.php" class="btn btn-outline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function loadStudentsAndSubjects(classId) {
    if (!classId) {
        document.getElementById('student_id').innerHTML = '<option value="">Select Student</option>';
        document.getElementById('subject_id').innerHTML = '<option value="">Select Subject</option>';
        return;
    }
    
    // Load students
    fetch('ajax_load_students.php?class_id=' + classId)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Student</option>';
            data.forEach(student => {
                options += '<option value="' + student.id + '">' + student.first_name + ' ' + student.last_name + ' (' + student.student_id + ')</option>';
            });
            document.getElementById('student_id').innerHTML = options;
        });
    
    // Load subjects
    fetch('ajax_load_subjects.php?class_id=' + classId)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Subject</option>';
            data.forEach(subject => {
                options += '<option value="' + subject.id + '">' + subject.subject_name + ' (' + subject.subject_code + ')</option>';
            });
            document.getElementById('subject_id').innerHTML = options;
        });
}

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