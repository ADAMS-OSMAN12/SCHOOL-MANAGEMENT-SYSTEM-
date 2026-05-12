<?php
/**
 * Edit Subject Page
 * Sakam M/A JHS School Management System
 * 
 * Form to edit existing subject
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Edit Subject';

// Get subject ID
$subjectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get subject data
$subject = fetchOne("SELECT * FROM subjects WHERE id = ?", [$subjectId]);

if (!$subject) {
    setMessage('Subject not found.', 'error');
    redirect('subjects.php');
}

// Get teachers and classes for dropdowns
$teachers = getAllTeachers();
$classes = getAllClasses();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $subjectCode = sanitizeInput($_POST['subject_code'] ?? '');
        $subjectName = sanitizeInput($_POST['subject_name'] ?? '');
        $classId = $_POST['class_id'] ?? '';
        $teacherId = $_POST['teacher_id'] ?? '';
        $passMark = (int)$_POST['pass_mark'] ?? 40;
        $description = sanitizeInput($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        
        // Validation
        $errors = [];
        
        if (empty($subjectCode)) {
            $errors[] = 'Subject code is required.';
        } elseif ($subjectCode !== $subject['subject_code'] && recordExists('subjects', 'subject_code = ? AND id != ?', [$subjectCode, $subjectId])) {
            $errors[] = 'Subject code already exists.';
        }
        
        if (empty($subjectName)) {
            $errors[] = 'Subject name is required.';
        } elseif ($subjectName !== $subject['subject_name'] && recordExists('subjects', 'subject_name = ? AND id != ?', [$subjectName, $subjectId])) {
            $errors[] = 'Subject name already exists.';
        }
        
        if ($passMark < 0 || $passMark > 100) {
            $errors[] = 'Pass mark must be between 0 and 100.';
        }
        
        if (empty($errors)) {
            // Update subject
            $data = [
                'subject_code' => $subjectCode,
                'subject_name' => $subjectName,
                'class_id' => $classId ?: null,
                'teacher_id' => $teacherId ?: null,
                'pass_mark' => $passMark,
                'description' => $description,
                'status' => $status
            ];
            
            $result = update('subjects', $data, "id = $subjectId");
            
            if ($result) {
                // Log activity
                logActivity(getUserId(), 'Update Subject', "Updated subject: $subjectName");
                
                // Set success message and redirect
                setMessage('Subject updated successfully!', 'success');
                redirect('subjects.php');
            } else {
                $error = 'Failed to update subject. Please try again.';
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
    <h1><i class="fas fa-edit"></i> Edit Subject</h1>
    <a href="subjects.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Subjects
    </a>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<!-- Edit Subject Form -->
<div class="card">
    <div class="card-header">
        <h3>Subject Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="subject_code">Subject Code *</label>
                    <input type="text" id="subject_code" name="subject_code" class="form-control" 
                           value="<?php echo escapeHTML($subject['subject_code']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="subject_name">Subject Name *</label>
                    <input type="text" id="subject_name" name="subject_name" class="form-control" 
                           value="<?php echo escapeHTML($subject['subject_name']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-control">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $subject['class_id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($class['class_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="teacher_id">Teacher</label>
                    <select id="teacher_id" name="teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>" <?php echo $teacher['id'] == $subject['teacher_id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="pass_mark">Pass Mark</label>
                    <input type="number" id="pass_mark" name="pass_mark" class="form-control" 
                           value="<?php echo $subject['pass_mark']; ?>" min="0" max="100">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Active" <?php echo $subject['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $subject['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" 
                          rows="3"><?php echo escapeHTML($subject['description']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Subject
                </button>
                <a href="subjects.php" class="btn btn-outline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>