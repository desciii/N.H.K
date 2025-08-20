<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nav</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter&display=swap"
      rel="stylesheet"
    />
<link rel="icon" href="/PHP/nhk/N.H.K/assets/logo.png" type="image/png" />
    <style>
      body {
        font-family: "Inter", sans-serif;
        margin: 0;
        padding: 0;
        background-color: #edede9;
      }

      nav {
        background-color: #e3d5ca;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      }

      .nav-left {
        display: flex;
        align-items: center;
      }

      .nav-left img {
        width: 30px;
        margin-right: 10px;
      }

      .nav-title {
        font-weight: 600;
        font-size: 20px;
        color: #333;
      }

      .nav-links a {
        margin-left: 15px;
        text-decoration: none;
        color: #333;
        font-size: 16px;
        padding: 6px 10px;
        border-radius: 6px;
        transition: background-color 0.2s ease, color 0.2s ease;
      }

      .nav-links a:hover {
        background-color: #d5c2b3;
        color: #000;
      }
    </style>
  </head>
  <body>
    <nav>
    <a href="stats.php" class="flex items-center space-x-2">
      <div class="nav-left flex items-center space-x-2">
        <img src="../assets/logo.png" alt="Logo" class="h-8 w-8" />
        <div class="nav-title text-lg font-bold">N.H.K</div>
      </div>
    </a>
      <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="team_dashboard.php">Teams</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
      </div>
    </nav>
  </body>
</html>
