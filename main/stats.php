<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Total tasks assigned to the user
$total = $pdo->prepare("
  SELECT COUNT(*) 
  FROM tasks t
  JOIN task_assignments ta ON t.id = ta.task_id
  WHERE ta.user_id = ?
");
$total->execute([$user_id]);
$totalTasks = $total->fetchColumn();

// Upcoming tasks
$upcoming = $pdo->prepare("
  SELECT COUNT(*) 
  FROM tasks t
  JOIN task_assignments ta ON t.id = ta.task_id
  WHERE ta.user_id = ? AND t.due_date >= CURDATE()
");
$upcoming->execute([$user_id]);
$upcomingTasks = $upcoming->fetchColumn();

// Completed tasks
$completed = $pdo->prepare("
  SELECT COUNT(*) 
  FROM tasks t
  JOIN task_assignments ta ON t.id = ta.task_id
  WHERE ta.user_id = ? AND t.status = 'done'
");
$completed->execute([$user_id]);
$completedTasks = $completed->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link rel="icon" href="../assets/logo.png" type="image/png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    a {
      text-decoration: none;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #edede9;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    nav {
      background-color: #d6ccc2;
      padding: 15px 30px;
      font-size: 16px;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 30px;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 100;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }


    .layout {
      display: flex;
      margin-top: 60px; /* height of the nav */
      flex: 1;
    }

    .sidebar {  
      width: 220px;
      background-color: #d6ccc2;
      padding: 20px;
      height: calc(100vh - 60px);
      box-shadow: 2px 0 5px rgba(0,0,0,0.05);
      overflow-y: auto; 
      position: sticky;
      top: 60px;
      flex-shrink: 0;
    }

    .sidebar h3 {
      font-size: 18px;
      margin-bottom: 18px !important;
      font-weight: bold;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar li {
      margin: 15px 0 !important;
    }

    .sidebar a {
      text-decoration: none;
      color: #333;
      transition: color 0.2s;
    }

    .sidebar a:hover {
      color: #4caf50;
    }

    /* Main Dashboard */
    .dashboard {
      flex: 1;
      padding: 30px;
      width: 100%;
    }

    .welcome {
      background-color: #e3d5ca;
      padding: 20px;
      border-radius: 10px;
      font-size: 18px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
    }

    .cards {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 20px;
      width: 100%;
    }

    @media (max-width: 768px) {
      .cards {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 1024px) and (min-width: 769px) {
      .cards {
        grid-template-columns: 1fr 1fr;
      }
    }

    .card {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
      min-height: 120px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .card h3 {
      font-size: 18px;
      margin-bottom: 8px;
    }

    .card p {
      font-size: 15px;
      color: #555;
    }

    .floating-add-button {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background-color: lightslategray;
      color: white;
      border: none;
      padding: 14px 16px;
      border-radius: 50%;
      font-size: 20px;
      cursor: pointer;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
      transition: background-color 0.2s ease;
    }

    .floating-add-button:hover {
      background-color: #c0392b;
    }

    /* Modal */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .modal-content {
      background-color: #fff;
      padding: 30px;
      border-radius: 12px;
      max-width: 450px;
      width: 90%;
      position: relative;
      animation: slideDown 0.3s ease-out;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    @keyframes slideDown {
      from {
        transform: translateY(-30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .close-btn {
      position: absolute;
      top: 12px;
      right: 16px;
      font-size: 24px;
      color: #888;
      cursor: pointer;
    }

    .close-btn:hover {
      color: #000;
    }

    .modal-content label {
      display: block;
      font-weight: 500;
      margin-bottom: 6px;
      text-align: left;
    }

    .modal-content input,
    .modal-content textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      margin-bottom: 16px;
    }

    .modal-content input:focus,
    .modal-content textarea:focus {
      border-color: #4caf50;
      outline: none;
    }

    .modal-content button[type="submit"] {
      background-color: #4caf50;
      color: white;
      padding: 10px 20px;
      border: none;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .modal-content button[type="submit"]:hover {
      background-color: #43a047;
    }
    .card-link {
      text-decoration: none;
      color: inherit;
      transition: box-shadow 0.2s;
      display: block;
    }
    .card-link:hover .card {
      box-shadow: 0 8px 24px rgba(76,175,80,0.15);
      background: #f3f7f3;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <!-- Top Navigation Bar -->
  <?php include __DIR__ . '/../views/nav.php'; ?>

  <!-- Layout container (sidebar + main content) -->
  <div class="layout">

    <!-- Sidebar (Teams list) -->
    <?php include __DIR__ . '/../views/teams.php'; ?>

    <!-- Main Dashboard -->
    <div class="dashboard">
      <div class="welcome">
        <h2>Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Student') ?>!</h2>
        <p>Here's what's going on with your tasks today.</p>
      </div>
      <div class="cards">
        <a href="tasks.php" class="card-link">
          <div class="card">
            <h3>Total Tasks</h3>
            <p><?= $totalTasks ?> tasks created</p>
          </div>
        </a>
        <a href="tasks.php#upcoming" class="card-link">
          <div class="card">
            <h3>Upcoming Deadlines</h3>
            <p><?= $upcomingTasks ?> due this week</p>
          </div>
        </a>
        <a href="dashboard.php" class="card-link">
          <div class="card">
            <h3>Completed</h3>
            <p><?= $completedTasks ?> tasks done</p>
          </div>
        </a>
      </div>
      <!-- Floating Button to Trigger Modal -->
      <button class="floating-add-button" onclick="openModal()" title="Create Group">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>

  <!-- Create Group Modal -->
  <div id="groupModal" class="modal-overlay">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">&times;</span>
      <h2>Create a Group</h2>
      <form action="create_group.php" method="POST">
        <label for="groupName">Group Name:</label>
        <input type="text" id="groupName" name="name" required>

        <label for="groupDescription">Description:</label>
        <textarea id="groupDescription" name="description" rows="4" required></textarea>

        <button type="submit">Create Group</button>
      </form>
    </div>
  </div>

  <!-- Modal Script -->
  <script>
    function openModal() {
      document.getElementById("groupModal").style.display = "flex";
    }

    function closeModal() {
      document.getElementById("groupModal").style.display = "none";
    }

    window.onclick = function (e) {
      const modal = document.getElementById("groupModal");
      if (e.target === modal) {
        closeModal();
      }
    };
  </script>

</body>
</html>