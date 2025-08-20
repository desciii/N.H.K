<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id'])) {
  die("Team ID is required.");
}

$team_id = $_GET['id'];

// Get team info
$stmt = $pdo->prepare("
  SELECT t.id, t.name, t.description, t.created_at, t.created_by, u.username AS creator
  FROM teams t
  JOIN users u ON t.created_by = u.id
  WHERE t.id = ?
");
$stmt->execute([$team_id]);
$team = $stmt->fetch();

if (!$team) {
  die("Team not found.");
}

$team_name = htmlspecialchars($team['name']);
$team_description = htmlspecialchars($team['description'] ?? 'No description');
$team_creator = htmlspecialchars($team['creator'] ?? 'Unknown');
$team_created_at = !empty($team['created_at']) ? date('F j, Y', strtotime($team['created_at'])) : 'Unknown date';

// Check if current user is the team creator
$is_creator = ($_SESSION['user_id'] == $team['created_by']);

// Get team members
$membersStmt = $pdo->prepare("
  SELECT u.id, u.username, tm.role 
  FROM team_members tm 
  JOIN users u ON tm.user_id = u.id 
  WHERE tm.team_id = ?
");
$membersStmt->execute([$team_id]);
$members = $membersStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($team['name']) ?> - Team</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #edede9;
      color: #333;
    }

    nav {
      background-color: #d6ccc2;
      padding: 15px 30px;
      font-size: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 60px;
      z-index: 100;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .layout {
      display: flex;
      margin-top: 60px;
      height: calc(100vh - 60px);
      overflow: hidden;
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
      margin-bottom: 15px;
      font-weight: bold;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar li {
      margin-bottom: 10px;
    }

    .sidebar a {
      text-decoration: none;
      color: #333;
      transition: color 0.2s;
    }

    .sidebar a:hover {
      color: #4caf50;
    }

    .main {
      flex: 1;
      padding: 30px;
      overflow-y: auto;
      height: calc(100vh - 60px);
      margin-left: 200px;

    }

    .team-header {
      background-color: #e3d5ca;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      margin-top: 10px;
    }

    .team-header h1 {
      font-size: 24px;
      margin-bottom: 10px;
    }

    .team-header p {
      color: #555;
      margin-bottom: 5px;
    }

    .team-members {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .team-members h2 {
      font-size: 20px;
      margin-bottom: 15px;
    }

    .member {
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      margin-bottom: 10px;
      background-color: #f9f9f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .member-info {
      display: flex;
      flex-direction: column;
    }

    .member-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .remove-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 12px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .remove-btn:hover {
      background-color: #c82333;
    }

    .nav-left a {
      text-decoration: none;
      display: flex;
      align-items: center;
      color: #333;
    }

    .nav-left img {
      height: 30px;
      margin-right: 10px;
    }

    .nav-title {
      font-weight: bold;
      font-size: 18px;
    }

    a {
      text-decoration: none;
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
      z-index: 500;
    }

    .floating-add-button:hover {
      background-color: #c0392b;
    }

  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 100vw;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
  }

  .modal-overlay.flex {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    width: 100%;
    max-width: 400px;
    position: relative;
    animation: slideDown 0.25s ease-out;
    margin-top: 10px;
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
    .modal-content select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      margin-bottom: 16px;
    }

    .modal-content input:focus,
    .modal-content select:focus {
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
      margin-top: 10px;
    }

    .modal-content button[type="submit"]:hover {
      background-color: #43a047;
    }

    .modal-button {
      background-color: #4caf50;
      color: white;
      padding: 10px 20px;
      border: none;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.2s ease;
      margin: 5px;
    }

    .modal-button:hover {
      background-color: #43a047;
    }

    .modal-button.delete {
      background-color: #dc3545;
    }

    .modal-button.delete:hover {
      background-color: #c82333;
    }

    #calendar {
      background-color: white;
      border-radius: 6px;
      padding: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      min-height: 360px;
      font-size: 13px;
    }

    .fc {
      font-size: 13px;
    }

    .fc-toolbar-title {
      font-size: 1rem !important;
    }

    .fc-button {
      padding: 2px 6px !important;
      font-size: 12px !important;
    }

    .fc-daygrid-day-number {
      padding: 2px;
      font-size: 12px;
    }

    .button-container {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }
  </style>
</head>
<body>

  <?php include __DIR__ . '/../views/nav.php'; ?>

  <div class="layout">
    <!-- Sidebar -->
    <div class="sidebar">
      <?php include __DIR__ . '/../views/teams.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main">
      <!-- Calendar Section -->
      <div id="calendar"></div>

      <!-- Team Info -->
      <div class="team-header">
        <h1><?= $team_name ?></h1>
        <p><?= $team_description ?></p>
        <p class="text-sm">Created by <strong><?= $team_creator ?></strong> on <?= $team_created_at ?></p>
      </div>

      <!-- Team Members -->
      <div class="team-members">
        <h2>Team Members</h2>
        <?php foreach ($members as $member): ?>
          <div class="member">
            <div class="member-info">
              <span><?= htmlspecialchars($member['username']) ?></span>
              <span style="font-size: 14px; color: #888;"><?= htmlspecialchars($member['role']) ?></span>
            </div>
            <div class="member-actions">
              <?php if ($is_creator && $member['id'] != $_SESSION['user_id']): ?>
                <button class="remove-btn" onclick="removeMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['username']) ?>')">
                  <i class="fas fa-user-minus"></i>  Remove
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Floating Add Button - Only show to creator -->
      <?php if ($is_creator): ?>
        <button class="floating-add-button" onclick="openMemberModal()" title="Add Member">
          <i class="fas fa-user-plus"></i>
        </button>
      <?php endif; ?>
    </div>
  </div>

<!-- Add Member Modal -->
<div id="memberModal" class="modal-overlay">
  <div class="modal-content">
    <span class="close-btn" onclick="closeMemberModal()">&times;</span>
    <h2>Add Team Member</h2>
    <form action="add_member.php" method="POST">
      <input type="hidden" name="team_id" value="<?= $team_id ?>">

      <label for="username">Username</label>
      <input type="text" name="username" id="username" required>

      <label for="role">Role</label>
      <select name="role" id="role" required>
        <option value="member">Member</option>
        <option value="admin">Admin</option>
      </select>

      <button type="submit">Add Member</button>
    </form>
  </div>
</div>

<!-- Add Task Modal -->
<div id="taskModal" class="modal-overlay">
  <div class="modal-content">
    <span class="close-btn" onclick="closeTaskModal()">&times;</span>
    <h2>Add Task</h2>
    <form action="add_task.php" method="POST">
      <input type="hidden" name="team_id" value="<?= $team_id ?>">
      <input type="hidden" name="due_date" id="due_date">

      <label for="title">Task Title</label>
      <input type="text" name="title" id="title" required>

      <label for="description">Description</label>
      <input type="text" name="description" id="description">

      <label for="assigned_users">Assign To</label>
      <select name="assigned_users[]" id="assigned_users" multiple required style="width: 100%;">
        <?php foreach ($members as $member): ?>
          <option value="<?= htmlspecialchars($member['username']) ?>">
            <?= htmlspecialchars($member['username']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Add Task</button>
    </form>
  </div>
</div>

<!-- Edit Task Modal -->
<div id="editTaskModal" class="modal-overlay">
  <div class="modal-content">
    <span class="close-btn" onclick="closeEditTaskModal()">&times;</span>
    <h2>Edit Task</h2>
    <form id="editTaskForm">
      <input type="hidden" name="task_id" id="edit_task_id">
      <input type="hidden" name="team_id" value="<?= $team_id ?>">

      <label for="edit_title">Task Title</label>
      <input type="text" name="title" id="edit_title" required>

      <label for="edit_description">Description</label>
      <input type="text" name="description" id="edit_description">

      <label for="edit_due_date">Due Date</label>
      <input type="date" name="due_date" id="edit_due_date" required>

      <label for="edit_assigned_users">Assign To</label>
      <select name="assigned_users[]" id="edit_assigned_users" multiple required style="width: 100%;">
        <?php foreach ($members as $member): ?>
          <option value="<?= htmlspecialchars($member['username']) ?>">
            <?= htmlspecialchars($member['username']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="edit_status">Status</label>
      <select name="status" id="edit_status" required>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="done">Done</option>
      </select>

      <div class="button-container">
        <button type="button" id="saveTaskBtn" class="modal-button">
          Save Changes
        </button>
        <button type="button" id="deleteTaskBtn" class="modal-button delete">
          Delete Task
        </button>
      </div>
    </form>
  </div>
</div>



<script>
  $(document).ready(function() {
    $('#assigned_users').select2({
      placeholder: "Select team members",
      allowClear: true
    });

    $('#edit_assigned_users').select2({
      placeholder: "Select team members",
      allowClear: true
    });
  });
</script>


<script>
function openMemberModal() {
  document.getElementById("memberModal").classList.add("flex");
}

function closeMemberModal() {
  document.getElementById("memberModal").classList.remove("flex");
}

function openTaskModal() {
  const modal = document.getElementById("taskModal");
  modal.classList.add("flex");
}

function closeTaskModal() {
  document.getElementById("taskModal").classList.remove("flex");
}

function closeEditTaskModal() {
  document.getElementById("editTaskModal").classList.remove("flex");
}

function removeMember(userId, username) {
  if (confirm(`Are you sure you want to remove ${username} from this team?`)) {
    fetch("remove_member.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `team_id=<?= $team_id ?>&user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(`${username} has been removed from the team.`);
        location.reload(); // Refresh the page to update the member list
      } else {
        alert("Error: " + data.error);
      }
    })
    .catch(err => {
      console.error("Remove member error:", err);
      alert("Something went wrong while removing the member.");
    });
  }
}

window.onclick = function (e) {
  const memberModal = document.getElementById("memberModal");
  const taskModal = document.getElementById("taskModal");
  const editTaskModal = document.getElementById("editTaskModal");

  if (e.target === memberModal) closeMemberModal();
  if (e.target === taskModal) closeTaskModal();
  if (e.target === editTaskModal) closeEditTaskModal();
};
</script>

<script>
// Declare calendar variable globally
let calendar;

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    selectable: true,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek'
    },
    select: function (info) {
      const selectedDate = info.startStr;

      // Check if there's already a task on that date
      const hasEvent = calendar.getEvents().some(event =>
        event.startStr === selectedDate
      );

      if (!hasEvent) {
        document.getElementById('due_date').value = selectedDate;
        openTaskModal();
      } else {
        alert("This date already has a task. Click the task to edit it.");
      }
    },
    eventClick: function(info) {
      const task = info.event;

      document.getElementById('edit_task_id').value = task.id;
      document.getElementById('edit_title').value = task.title;
      document.getElementById('edit_description').value = task.extendedProps.description || '';
      document.getElementById('edit_due_date').value = task.startStr;

      $('#edit_assigned_users').val(task.extendedProps.assigned_to || []).trigger('change');
      $('#edit_status').val(task.extendedProps.status || 'pending');

      document.getElementById('editTaskModal').classList.add('flex');
    }, 
    events: "get_tasks.php?team_id=<?= $team_id ?>",
    eventDidMount: function (info) {
      new bootstrap.Tooltip(info.el, {
        title: info.event.title,
        placement: 'top',
        trigger: 'hover',
        container: 'body'
      });
    }
  });

  calendar.render();
});

// Handle Delete Task
document.getElementById("deleteTaskBtn").addEventListener("click", function(e) {
  e.preventDefault(); // Prevent any default form submission
  e.stopPropagation(); // Stop event bubbling
  
  const taskId = document.getElementById("edit_task_id").value;

  if (!taskId) {
    alert("No task selected!");
    return;
  }

  if (confirm("Are you sure you want to delete this task?")) {
    fetch("delete_task.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "task_id=" + taskId
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("Task deleted!");
        closeEditTaskModal();
        if (calendar) {
          calendar.refetchEvents();
        }
      } else {
        alert("Error: " + (data.error || "Failed to delete task"));
      }
    })
    .catch(err => {
      console.error("Delete error:", err);
      alert("Something went wrong while deleting the task.");
    });
  }
});

// Handle Save Task Changes
document.getElementById("saveTaskBtn").addEventListener("click", function(e) {
    e.preventDefault(); // Prevent any default form submission
    
    const form = document.getElementById('editTaskForm');
    const formData = new FormData(form);
    
    fetch("edit_task.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Task updated successfully!");
            closeEditTaskModal();
            if (calendar) {
                calendar.refetchEvents();
            }
        } else {
            alert("Error: " + (data.error || "Failed to update task"));
        }
    })
    .catch(err => {
        console.error("Update error:", err);
        alert("Something went wrong while updating the task.");
    });
});
</script>

</body>
</html>