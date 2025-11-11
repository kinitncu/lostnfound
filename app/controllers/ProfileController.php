<?php
class ProfileController {
    public static function index(): void {
        require_login();

        $user = User::findById(current_user_id());
        $error = null;
        $success = get_flash('success');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'update_profile') {
                verify_csrf();

                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');

                if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address.';
                }

                // Common name fields
                $first_name  = trim($_POST['first_name'] ?? '') ?: null;
                $middle_name = trim($_POST['middle_name'] ?? '') ?: null;
                $last_name   = trim($_POST['last_name'] ?? '') ?: null;

                // Role-based fields (user_type is fixed; we don’t change it here)
                $department = null; $year_level = null; $section = null; $position_title = null;
                $role = $user['user_type'] ?? '';

                if ($role === 'student') {
                    $department  = trim($_POST['department'] ?? '') ?: null;
                    $year_level  = trim($_POST['year_level'] ?? '') ?: null;
                    $section     = trim($_POST['section'] ?? '') ?: null;
                } elseif ($role === 'staff') {
                    $position_title = trim($_POST['position_title'] ?? '') ?: null;
                }

                if (!$error) {
                    try {
                        User::updateProfileDetails(current_user_id(), [
                            'email' => $email !== '' ? $email : null,
                            'phone' => $phone !== '' ? $phone : null,
                            'first_name' => $first_name,
                            'middle_name' => $middle_name,
                            'last_name' => $last_name,
                            'department' => $department,
                            'year_level' => $year_level,
                            'section' => $section,
                            'position_title' => $position_title
                        ]);
                        session_regenerate_id(true);
                        set_flash('success', 'Profile updated.');
                        redirect('index.php?r=profile');
                    } catch (Throwable $e) {
                        // Most common cause: missing columns in users table
                        $error = 'Profile update failed. Please ensure the users table has the extended columns (user_type, first_name, middle_name, last_name, department, year_level, section, position_title). Details: ' . $e->getMessage();
                    }
                }
            } elseif ($action === 'change_password') {
                verify_csrf();
                $current = $_POST['current_password'] ?? '';
                $new = $_POST['new_password'] ?? '';
                $confirm = $_POST['new_password_confirm'] ?? '';

                $user = User::findById(current_user_id());
                if (empty($user['password_hash']) || !password_verify($current, $user['password_hash'])) {
                    $error = 'Your current password is incorrect.';
                } elseif (strlen($new) < 8) {
                    $error = 'New password must be at least 8 characters.';
                } elseif ($new !== $confirm) {
                    $error = 'New passwords do not match.';
                } else {
                    try {
                        User::updatePasswordHash(current_user_id(), password_hash($new, PASSWORD_DEFAULT));
                        session_regenerate_id(true);
                        set_flash('success', 'Password changed successfully.');
                        redirect('index.php?r=profile');
                    } catch (Throwable $e) {
                        $error = 'Could not update password. ' . $e->getMessage();
                    }
                }
            }
        }

        // Refresh
        $user = User::findById(current_user_id());
        render('profile/index', compact('user','error','success'));
    }
}