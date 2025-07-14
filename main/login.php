<?php
session_start();
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "Both fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: stats.php");
            exit;
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
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
      a#register-link {
        text-decoration: none;
      }
      #register-btn {
        margin-top: 10px;
        background-color: #eee;
        border: none;
        padding: 10px;
        width: 90px;
        display: flex;
        text-align: center;
        justify-content: center;
        margin: 0 auto;
        margin-top: 10px;
        cursor: pointer;
      }
      #register-btn:hover {
        background-color: #ddd;
      }
      .message {
        margin-top: 10px;
        color: red;
        font-size: 14px;
      }
    </style>
    <link rel="icon" href="../assets/logo.png" type="image/png">
    <link rel="stylesheet" href="/nhk/N.H.K/css/authorization.css" />
  </head>
  <body>
    <div id="container">
      <div id="main">
        <h1>Welcome to N.H.K</h1>
        <h2>Login</h2>

        <?php if (!empty($errors)): ?>
        <div class="message">
          <?php foreach ($errors as $error): ?>
          <div><?= htmlspecialchars($error) ?></div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div id="form-container">
          <form method="POST">
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <div id="checkbox"><input type="checkbox" /> Remember Me</div>
            <button type="submit">Login</button>
          </form>
          <br />
          or
          <a href="register.php" id="register-link">
            <button type="button" id="register-btn">Register</button>
          </a>
        </div>
      </div>
    </div>
  </body>
</html>
