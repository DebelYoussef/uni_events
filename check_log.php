<?php
$log_file = 'c:\xampp\htdocs\unievents\debug_register.log';
if (file_exists($log_file)) {
    echo file_get_contents($log_file);
} else {
    echo "No log file found yet. Try registering in your browser first.";
}
?>