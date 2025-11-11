<?php
class AuthController {
    public static function login(): void {
        $error = null; $old = ['school_id' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $school_id = trim($_POST['school_id'] ?? '');
            $password  = $_POST['password'] ?? '';
            $old['school_id'] = $school_id;

            if (!preg_match('/^\d{10}$/', $school_id)) {
                $error = 'Please enter a valid 10-digit School ID.';
            } else {
                $user = User::findBySchoolId($school_id);
                if (!$user) {
                    $error = 'Account not found.';
                } else if (empty($user['password_hash'])) {
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['must_set_password'] = true;
                    session_regenerate_id(true);
                    redirect('index.php?r=auth/setup_password');
                } else if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = (int)$user['id'];
                    unset($_SESSION['must_set_password']);
                    session_regenerate_id(true);
                    redirect('index.php');
                } else {
                    $error = 'Invalid credentials.';
                }
            }
        }
        render('auth/login', ['error' => $error, 'old' => $old]);
    }

    public static function setupPassword(): void {
        require_login();
        if (empty($_SESSION['must_set_password'])) { redirect('index.php'); }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $pass = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';
            if (strlen($pass) < 8) $error = 'Password must be at least 8 characters.';
            elseif ($pass !== $confirm) $error = 'Passwords do not match.';
            else {
                User::updatePasswordHash(current_user_id(), password_hash($pass, PASSWORD_DEFAULT));
                unset($_SESSION['must_set_password']);
                session_regenerate_id(true);
                set_flash('success', 'Password set successfully. Welcome!');
                redirect('index.php?r=profile');
            }
        }
        render('auth/setup_password', ['error' => $error]);
    }

    public static function logout(): void {
        verify_csrf(); // POST-only
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
          $p = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        redirect('index.php?r=auth/login');
    }
}