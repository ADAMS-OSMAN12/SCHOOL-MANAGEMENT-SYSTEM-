<?php
/**
 * Add Teacher Page
 * Sakam M/A JHS School Management System
 * 
 * Form to add new teacher
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Add Teacher';

// Get subjects for dropdown
$subjects = getAllSubjects();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $staffId = sanitizeInput($_POST['staff_id'] ?? '');
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['date_of_birth'] ?? '';
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subjectId = $_POST['subject_id'] ?? '';
        $qualification = sanitizeInput($_POST['qualification'] ?? '');
        $hireDate = $_POST['hire_date'] ?? '';
        $address = sanitizeInput($_POST['address'] ?? '');
        
        // Create login account
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
// Validation
         $errors = [];

         if (empty($staffId)) {
             $errors[] = 'Staff ID is required.';
         } elseif (recordExists('teachers', 'staff_id = ?', [$staffId])) {
             $errors[] = 'Staff ID already exists.';
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

         if (empty($email)) {
             $errors[] = 'Email is required.';
         } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $errors[] = 'Invalid email address.';
         } elseif (recordExists('users', 'email = ?', [$email])) {
             $errors[] = 'Email already registered.';
         }

         if (empty($phone)) {
             $errors[] = 'Phone number is required.';
         }
        
        if (empty($hireDate)) {
            $errors[] = 'Hire date is required.';
        }
        
        // Login account validation
        if (empty($username)) {
            $errors[] = 'Username is required for login.';
        } elseif (recordExists('users', 'username = ?', [$username])) {
            $errors[] = 'Username already taken.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (empty($errors)) {
            // Start transaction
            global $conn;
            $conn->begin_transaction();
            
            try {
// Create user account
                 $userData = [
                     'username' => $username,
                     'password' => password_hash($password, PASSWORD_DEFAULT),
                     'email' => $email,
                     'role' => 'teacher',
                     'status' => 'Active'
                 ];
                
                $userId = insert('users', $userData);
                
                if (!$userId) {
                    throw new Exception('Failed to create user account.');
                }
                
// Insert teacher
                  $teacherData = [
                      'staff_id' => $staffId,
                      'first_name' => $firstName,
                      'last_name' => $lastName,
                      'gender' => $gender,
                      'date_of_birth' => $dob,
                      'email' => $email,
                      'contact' => $phone,
                      'subject_id' => $subjectId ?: null,
                      'qualification' => $qualification,
                      'hire_date' => $hireDate,
                      'address' => $address,
                      'status' => 'Active'
                  ];
                
                $teacherId = insert('teachers', $teacherData);
                
                if (!$teacherId) {
                    throw new Exception('Failed to add teacher.');
                }
                
                // Update user with teacher reference
                update('users', ['teacher_id' => $teacherId], "id = $userId");
                
                // Commit transaction
                $conn->commit();
                
                // Log activity
                logActivity(getUserId(), 'Add Teacher', "Added teacher: $firstName $lastName");
                
                // Set success message and redirect
                setMessage('Teacher added successfully! Login credentials: Username - ' . escapeHTML($username), 'success');
                redirect('teachers.php');
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
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
    <h1><i class="fas fa-user-plus"></i> Add Teacher</h1>
    <a href="teachers.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Teachers
    </a>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<!-- Add Teacher Form -->
<div class="card">
    <div class="card-header">
        <h3>Teacher Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" data-validate>
<input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

             <div class="form-row">
                 <div class="form-group">
                     <label for="staff_id">Staff ID *</label>
                     <input type="text" id="staff_id" name="staff_id" class="form-control"
                            placeholder="e.g., TCH001" required>
                 </div>

                 <div class="form-group">
                     <label for="subject_id">Subject</label>
                     <select id="subject_id" name="subject_id" class="form-control">
                         <option value="">Select Subject</option>
                         <?php foreach ($subjects as $subject): ?>
                         <option value="<?php echo $subject['id']; ?>">
                             <?php echo escapeHTML($subject['subject_name']); ?>
                         </option>
                         <?php endforeach; ?>
                     </select>
                 </div>
             </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           placeholder="Enter first name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           placeholder="Enter last name" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="teacher@school.edu" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           placeholder="e.g., 0241234567" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="qualification">Qualification</label>
                    <input type="text" id="qualification" name="qualification" class="form-control" 
                           placeholder="e.g., BSc. Education">
                </div>
                
                <div class="form-group">
                    <label for="hire_date">Hire Date *</label>
                    <input type="date" id="hire_date" name="hire_date" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" 
                          placeholder="Enter address" rows="3"></textarea>
            </div>
            
            <hr>
            <h4><i class="fas fa-user-lock"></i> Login Credentials</h4>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter password (min 6 chars)" required minlength="6">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Confirm password" required>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Teacher
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