<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Get teams user created or is a member of
$stmt = $pdo->prepare("
  SELECT DISTINCT t.id, t.name
  FROM teams t
  LEFT JOIN team_members tm ON t.id = tm.team_id
  WHERE t.created_by = ? OR tm.user_id = ?
");
$stmt->execute([$user_id, $user_id]);
$teams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sidebar</title>
  <style>
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 220px;
      height: 100%;
      background-color: #d6ccc2;
      padding: 20px;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      overflow-y: auto;
      margin-top: 60px;
    }

    .sidebar h3 {
      margin-top: 0;
      font-size: 18px;
      color: #333;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar li {
      margin: 10px 0;
    }

    .sidebar a {
      color: #333;
      text-decoration: none;
      transition: color 0.2s ease;
    }

    .sidebar a:hover {
      color: #4caf50;
    }

    .dashboard {
      margin-left: 240px;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h3>Your Teams</h3>
  <ul>
    <?php foreach ($teams as $team): ?>
      <li>
        <a href="view_team.php?id=<?= $team['id'] ?>">
          <?= htmlspecialchars($team['name']) ?>
        </a>
      </li>
    <?php endforeach; ?>
    <?php if (empty($teams)): ?>
      <li>No teams found</li>
    <?php endif; ?>
  </ul>
</div>

</body>
</html>
