<?php
// Small validation functions: no database or HTML in this file.
function valid_role($role) {
    return in_array($role, ['user', 'faculty', 'coordinator', 'admin'], true);
}
function faculties() {
    return ['School of Computing', 'School of Engineering', 'School of Business', 'Campus Sustainability'];
}
function new_password_error($password) {
    if (strlen($password) < 8 || strlen($password) > 72) return 'Use a password between 8 and 72 characters.';
    if (!preg_match('/[^\p{L}\p{N}\s]/u', $password)) return 'Include at least one special character in the password, for example @, ! or #.';
    return '';
}
function account_error($name, $email, $university_id, $faculty, $password, $confirm, $needs_password = true, $role = 'user') {
    if (!valid_role($role)) return 'Choose a valid account role.';
    if (trim($name) === '' || strlen($name) > 100) return 'Enter a full name of up to 100 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) return 'Enter a valid email address.';
    if (trim($university_id) === '' || strlen($university_id) > 20) return 'Enter a university ID of up to 20 characters.';
    if ($role === 'user' && !str_ends_with(strtolower($email), '@mail.apu.edu.my')) return 'Student emails must end with @mail.apu.edu.my.';
    if ($role === 'user' && !preg_match('/^TP[0-9]{6}$/D', $university_id)) return 'Student IDs must be TP followed by six digits, for example TP090526.';
    if (!in_array($faculty, faculties(), true)) return 'Choose a faculty.';
    if ($needs_password || $password !== '') {
        $problem = new_password_error($password);
        if ($problem !== '') return $problem;
    }
    if ($password !== $confirm) return 'The passwords do not match.';
    return '';
}
function can_review($old_status, $new_status) {
    return $old_status === 'pending' && in_array($new_status, ['approved', 'rejected', 'needs-revision'], true);
}
