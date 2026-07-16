<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IEP Admin — Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Manrope', sans-serif; background: linear-gradient(135deg, #0a1f5c 0%, #1a56db 100%); min-height: 100vh; display: grid; place-items: center; }
    .login-card { background: #fff; border-radius: 18px; padding: 48px; width: 100%; max-width: 400px; box-shadow: 0 24px 60px rgba(0,0,0,.25); }
    .login-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; }
    .logo-icon { width: 48px; height: 48px; background: #1a56db; border-radius: 12px; display: grid; place-items: center; color: #fff; font-size: 1.3rem; }
    .logo-text span { display: block; font-size: 1rem; font-weight: 800; color: #0a1f5c; letter-spacing: 1px; }
    .logo-text small { font-size: 0.75rem; color: #6b7280; }
    h2 { font-size: 1.4rem; font-weight: 700; color: #0a1f5c; margin-bottom: 6px; }
    p { font-size: 0.875rem; color: #6b7280; margin-bottom: 28px; }
    .form-group { position: relative; margin-bottom: 16px; }
    .form-group input { width: 100%; padding: 12px 14px 12px 40px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; font-family: 'Manrope', sans-serif; transition: all .2s; }
    .form-group input:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.1); }
    .form-group i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; }
    .btn-login { width: 100%; background: #1a56db; color: #fff; padding: 13px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; font-family: 'Manrope', sans-serif; transition: all .2s; margin-top: 8px; }
    .btn-login:hover { background: #1344b8; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(26,86,219,.4); }
    .error-msg { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 8px; padding: 10px 14px; font-size: 0.875rem; margin-bottom: 16px; display: none; }
    .back-link { text-align: center; margin-top: 20px; }
    .back-link a { font-size: 0.85rem; color: #1a56db; font-weight: 600; }
  </style>
</head>
<body>
<?php
session_start();
require_once '../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = sanitize($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username && $password) {
    try {
      $db   = getDB();
      $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
      $stmt->execute([$username]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['iep_admin'] = $user['id'];
        $_SESSION['iep_admin_name'] = $user['full_name'];
        header('Location: dashboard.php');
        exit;
      } else {
        $error = 'Invalid username or password.';
      }
    } catch (PDOException $e) {
      $error = 'Database error. Please try again.';
    }
  } else {
    $error = 'Please enter both username and password.';
  }
}
?>
<div class="login-card">
  <div class="login-logo">
    <div class="logo-icon"><i class="fa-solid fa-gear"></i></div>
    <div class="logo-text">
      <span>INTEGRATED</span>
      <small>ENGINEERS POINT — Admin</small>
    </div>
  </div>
  <h2>Welcome Back</h2>
  <p>Sign in to manage enquiries and feedback</p>
  <?php if ($error): ?>
    <div class="error-msg" style="display:block"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <input type="text" name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      <i class="fa-solid fa-user"></i>
    </div>
    <div class="form-group">
      <input type="password" name="password" placeholder="Password" required>
      <i class="fa-solid fa-lock"></i>
    </div>
    <button type="submit" class="btn-login">Sign In <i class="fa-solid fa-arrow-right"></i></button>
  </form>
  <div class="back-link"><a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Back to Website</a></div>
</div>
</body>
</html>
