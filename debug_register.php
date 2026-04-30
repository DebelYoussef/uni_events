<?php
// Debug script to check registration issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

echo "=== Registration Debug Test ===\n\n";

// Test 1: Check database connection
echo "1. Database Connection: ";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✓ Connected\n\n";
} catch (Exception $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n\n";
    exit;
}

// Test 2: Check users table exists
echo "2. Users Table: ";
$stmt = $pdo->query("SHOW TABLES LIKE 'users'");
if ($stmt->fetch()) {
    echo "✓ Exists\n\n";
} else {
    echo "✗ Missing\n\n";
    exit;
}

// Test 3: Simulate a registration
echo "3. Testing User Creation:\n";
$test_email = 'test_' . time() . '@example.com';
$test_name = 'Test User ' . time();
$test_password = 'TestPass@123';
$test_student_id = 'STU' . time();
$test_role = 'student';

echo "   - Email: $test_email\n";
echo "   - Name: $test_name\n";
echo "   - Student ID: $test_student_id\n";
echo "   - Role: $test_role\n\n";

$result = create_user($pdo, $test_name, $test_email, $test_password, $test_role, $test_student_id);

if ($result['success']) {
    echo "   ✓ User Created Successfully!\n";
    echo "   - User ID: " . $result['user_id'] . "\n\n";
    
    // Verify in database
    echo "4. Verifying in Database:\n";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$test_email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "   ✓ User Found in Database\n";
        echo "   - Name: " . $user['name'] . "\n";
        echo "   - Email: " . $user['email'] . "\n";
        echo "   - Role: " . $user['role'] . "\n";
        echo "   - Student ID: " . $user['student_id'] . "\n";
        echo "   - Is Approved: " . $user['is_approved'] . "\n";
    } else {
        echo "   ✗ User NOT found in database!\n";
    }
} else {
    echo "   ✗ User Creation Failed\n";
    echo "   - Message: " . $result['message'] . "\n";
    if (isset($result['feedback'])) {
        echo "   - Feedback: " . implode(', ', $result['feedback']) . "\n";
    }
}

echo "\n=== End Debug Test ===\n";
?>
