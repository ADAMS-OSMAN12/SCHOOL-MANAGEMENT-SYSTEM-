<?php
/**
 * Add Class Page
 * Sakam M/A JHS School Management System
 * 
 * Form to add new class
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Add Class';

// Get teachers for dropdown
$teachers = getAllTeachers();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $className = sanitizeInput($_POST['class_name'] ?? '');
        $classLevel = (int)$_POST['class_level'] ?? 0;
        $teacherId = $_POST['teacher_id'] ?? '';
        $capacity = (int)$_POST['capacity'] ?? 40;
        
        // Validation
        $errors = [];
        
        if (empty($className)) {
            $errors[] = 'Class name is required.';
        } elseif (recordExists('classes', 'class_name = ?', [$className])) {
            $errors[] = 'Class name already exists.';
        }
        
        if ($classLevel < 1 || $classLevel > 12) {
            $errors[] = 'Class level must be between 1 and 12.';
        }
        
        if (empty($errors)) {
            // Insert class
            $data = [
                'class_name' => $className,
                'class_level' => $classLevel,
                'teacher_id' => $teacherId ?: null,
                'capacity' => $capacity,
                'status' => 'Active'
            ];
            
            $insertId = insert('classes', $data);
            
            if ($insertId) {
                // Log activity
                logActivity(getUserId(), 'Add Class', "Added class: $className");
                
                // Set success message and redirect
                setMessage('Class added successfully!', 'success');
                redirect('classes.php');
            } else {
                $error = 'Failed to add class. Please try again.';
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
    <h1><i class="fas fa-plus"></i> Add Class</h1>
    <a href="classes.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Classes
    </a>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<!-- Add Class Form -->
<div class="card">
    <div class="card-header">
        <h3>Class Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="class_name">Class Name *</label>
                    <input type="text" id="class_name" name="class_name" class="form-control" 
                           placeholder="e.g., Primary 1, JHS 1" required>
                </div>
                
                <div class="form-group">
                    <label for="class_level">Class Level *</label>
                    <input type="number" id="class_level" name="class_level" class="form-control" 
                           placeholder="e.g., 1" min="1" max="12" required>
                    <small>1 = Primary 1, 7 = JHS 1, etc.</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="teacher_id">Class Teacher</label>
                    <select id="teacher_id" name="teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>">
                            <?php echo escapeHTML($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" 
                           value="40" min="1" max="100">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Class
                </button>
                <button type="reset" class="btn btn-outline">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>