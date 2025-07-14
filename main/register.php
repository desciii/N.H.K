<?php
require_once 'db.php';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $success = true;
            } else {
                $errors[] = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link rel="icon" href="../assets/logo.png" type="image/png">
    <style>
      body {
        background-color: #edede9;
        font-family: "Inter", sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
      }
      #main {
        background-color: #e3d5ca;
        padding: 30px;
        border-radius: 12px;
        border: 2px solid #000;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 250px;
        text-align: center;
      }
      h1, h2 {
        margin-bottom: 20px;
        color: #333;
      }
      form {
        display: flex;
        flex-direction: column;
        gap: 12px;
      }
      input[type="text"],
      input[type="email"],
      input[type="password"] {
        padding: 10px;
        font-size: 16px;
        background-color: lightcyan;
        border: 1px solid #ccc;
        border-radius: 6px;
      }
      input:focus {
        outline: none;
        border-color: #888;
      }
      button {
        padding: 10px;
        font-size: 16px;
        background-color: #d5bdaf;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        width: 100px;
        align-items: center;
        text-align: center;
        justify-content: center;
        margin: 0 auto;
        transition: background-color 0.2s ease;
      }
      button:hover {
        background-color: #c7afa1;
      }
      .login-link {
        margin-top: 15px;
        font-size: 14px;
      }
      .login-link a {
        color: #333;
        text-decoration: underline;
      }
      .message {
        margin-top: 10px;
        color: red;
        font-size: 14px;
      }
      .success {
        color: green;
      }
    </style>
  </head>
  <body>
    <div id="main">
      <h1>Welcome to N.H.K</h1>
      <h2>Create Account</h2>

      <?php if (!empty($errors)): ?>
        <div class="message"> 
          <?php foreach ($errors as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php elseif ($success): ?>
        <div class="message success">
          Registration successful! <a href="login.php">Login here</a>.
        </div>
      <?php endif; ?>

      <?php if (!$success): ?>
        <form method="POST">
          <input type="text" name="username" placeholder="Username" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <button type="submit">Register</button>
        </form>
      <?php endif; ?>

      <div class="login-link">
        Already have an account? <a href="login.php">Log in</a>
      </div>
    </div>
  </body>
</html>
