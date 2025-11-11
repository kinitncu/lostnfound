<?php
class AdminUsersController {
    public static function index(): void {
        require_admin();
        $users = User::listAll(1000, 0);
        render('admin/users', compact('users'));
    }

    public static function store(): void {
        require_admin(); verify_csrf();

        $school_id = trim($_POST['school_id'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $user_type = $_POST['user_type'] ?? null;
        if ($user_type !== null && !in_array($user_type, ['student','staff'], true)) $user_type = null;

        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $student_no = trim($_POST['student_no'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $year_level = trim($_POST['year_level'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $position_title = trim($_POST['position_title'] ?? '');
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;

        $password_plain = $_POST['password'] ?? '';
        $password_hash = null;
        $errors = [];

        if (!preg_match('/^\d{10}$/', $school_id)) $errors[] = 'School ID must be 10 digits.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
        if ($password_plain !== '' && strlen($password_plain) < 8) $errors[] = 'Password must be at least 8 characters.';
        if (User::findBySchoolId($school_id)) $errors[] = 'A user with this School ID already exists.';

        if (!empty($errors)) {
            set_flash('error', implode(' ', $errors));
            redirect('index.php?r=admin/users');
        }

        if ($password_plain !== '') $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

        $id = User::createByAdmin([
            'school_id' => $school_id,
            'password_hash' => $password_hash,
            'email' => ($email !== '' ? $email : null),
            'phone' => ($phone !== '' ? $phone : null),
            'is_admin' => $is_admin,
            'user_type' => $user_type,
            'student_no' => ($student_no !== '' ? $student_no : null),
            'first_name' => ($first_name !== '' ? $first_name : null),
            'middle_name' => ($middle_name !== '' ? $middle_name : null),
            'last_name' => ($last_name !== '' ? $last_name : null),
            'department' => ($department !== '' ? $department : null),
            'year_level' => ($year_level !== '' ? $year_level : null),
            'section' => ($section !== '' ? $section : null),
            'position_title' => ($position_title !== '' ? $position_title : null),
        ]);

        AuditLog::record(current_user_id(), 'user_create', 'user', $id, ['school_id'=>$school_id,'user_type'=>$user_type,'is_admin'=>$is_admin]);
        set_flash('success', 'User created successfully.');
        redirect('index.php?r=admin/users');
    }

    public static function delete(): void {
        require_admin(); verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { set_flash('error','Invalid user.'); redirect('index.php?r=admin/users'); }

        if ($id === current_user_id()) {
            set_flash('error', 'You cannot delete your own account.');
            redirect('index.php?r=admin/users');
        }

        $u = User::findById($id);
        if ($u) {
            // delete avatar directory
            $dir = uploads_path('avatars/'.$id);
            if (is_dir($dir)) {
                foreach (glob($dir.'/*') as $f) @unlink($f);
                @rmdir($dir);
            }
        }

        User::deleteByAdmin($id);
        AuditLog::record(current_user_id(), 'user_delete', 'user', $id, ['school_id'=>$u['school_id'] ?? null]);
        set_flash('success', 'User deleted.');
        redirect('index.php?r=admin/users');
    }
}