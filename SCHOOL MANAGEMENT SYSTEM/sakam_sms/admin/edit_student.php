<?php
/**
 * Edit Student Page
 * Sakam M/A JHS School Management System
 * 
 * Form to edit existing student
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Edit Student';

// Get student ID
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get student data
$student = fetchOne("SELECT * FROM students WHERE id = ?", [$studentId]);

if (!$student) {
    setMessage('Student not found.', 'error');
    redirect('students.php');
}

// Get classes for dropdown
$classes = getAllClasses();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $studentIdNum = sanitizeInput($_POST['student_id'] ?? '');
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['date_of_birth'] ?? '';
        $classId = $_POST['class_id'] ?? '';
        $admissionDate = $_POST['admission_date'] ?? '';
        $parentName = sanitizeInput($_POST['parent_name'] ?? '');
        $parentContact = sanitizeInput($_POST['parent_contact'] ?? '');
        $parentEmail = sanitizeInput($_POST['parent_email'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        
        // Validation
        $errors = [];
        
        if (empty($studentIdNum)) {
            $errors[] = 'Student ID is required.';
        } elseif ($studentIdNum !== $student['student_id'] && recordExists('students', 'student_id = ? AND id != ?', [$studentIdNum, $studentId])) {
            $errors[] = 'Student ID already exists.';
        }
        
        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }
        
        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }
        
        if (empty($gender)) {
            $errors[] = 'Gender is required.';
        }
        
        if (empty($dob)) {
            $errors[] = 'Date of birth is required.';
        }
        
        if (empty($classId)) {
            $errors[] = 'Class is required.';
        }
        
        if (empty($admissionDate)) {
            $errors[] = 'Admission date is required.';
        }
        
        if (!empty($parentEmail) && !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid parent email address.';
        }
        
        if (empty($errors)) {
            // Update student
            $data = [
                'student_id' => $studentIdNum,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
                'date_of_birth' => $dob,
                'class_id' => $classId,
                'admission_date' => $admissionDate,
                'parent_name' => $parentName,
                'parent_contact' => $parentContact,
                'parent_email' => $parentEmail,
                'address' => $address,
                'status' => $status
            ];
            
            $result = update('students', $data, "id = $studentId");
            
            if ($result) {
                // Log activity
                logActivity(getUserId(), 'Update Student', "Updated student: $firstName $lastName");
                
                // Set success message and redirect
                setMessage('Student updated successfully!', 'success');
                redirect('students.php');
            } else {
                $error = 'Failed to update student. Please try again.';
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
    <h1><i class="fas fa-user-edit"></i> Edit Student</h1>
    <a href="students.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<!-- Edit Student Form -->
<div class="card">
    <div class="card-header">
        <h3>Student Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="student_id">Student ID *</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" 
                           value="<?php echo escapeHTML($student['student_id']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="class_id">Class *</label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $student['class_id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($class['class_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           value="<?php echo escapeHTML($student['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           value="<?php echo escapeHTML($student['last_name']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" 
                           value="<?php echo $student['date_of_birth']; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="admission_date">Admission Date *</label>
                    <input type="date" id="admission_date" name="admission_date" class="form-control" 
                           value="<?php echo $student['admission_date']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Active" <?php echo $student['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $student['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="Suspended" <?php echo $student['status'] === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                        <option value="Graduated" <?php echo $student['status'] === 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="parent_name">Parent/Guardian Name</label>
                    <input type="text" id="parent_name" name="parent_name" class="form-control" 
                           value="<?php echo escapeHTML($student['parent_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="parent_contact">Parent Contact</label>
                    <input type="tel" id="parent_contact" name="parent_contact" class="form-control" 
                           value="<?php echo escapeHTML($student['parent_contact']); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="parent_email">Parent Email</label>
                    <input type="email" id="parent_email" name="parent_email" class="form-control" 
                           value="<?php echo escapeHTML($student['parent_email']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" 
                          rows="3"><?php echo escapeHTML($student['address']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Student
                </button>
                <a href="students.php" class="btn btn-outline">
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