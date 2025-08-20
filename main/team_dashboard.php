<?php
// filepath: c:\xampp\htdocs\PHP\nhk\N.H.K\main\team_dashboard.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Teams the user created
$stmt = $pdo->prepare("
  SELECT t.id, t.name, t.description, t.created_at
  FROM teams t
  WHERE t.created_by = ?
");
$stmt->execute([$user_id]);
$created_teams = $stmt->fetchAll();

// Teams the user is a member of (but not creator)
$stmt = $pdo->prepare("
  SELECT t.id, t.name, t.description, t.created_at
  FROM teams t
  JOIN team_members tm ON t.id = tm.team_id
  WHERE tm.user_id = ? AND t.created_by != ?
");
$stmt->execute([$user_id, $user_id]);
$member_teams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Teams</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #edede9;
      color: #333;
    }
    nav {
    background-color: #e3d5ca;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      }
      * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
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
        padding: 6px 0px;
        border-radius: 6px;
        transition: background-color 0.2s ease, color 0.2s ease;
      }

      .nav-links a:hover {
        background-color: #d5c2b3;
        color: #000;
      }
    .layout {
      display: flex;
      margin-left: 20px;
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
      text-decoration: none;
    }
    .sidebar h3 {
      font-weight: bold;
      padding-bottom: 5px;
    }
    .main {
      flex: 1;
      padding: 30px;
      overflow-y: auto;
      height: calc(100vh - 60px);
      margin-left: 200px;
    }
    .team-list {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      margin-bottom: 30px;
    }
    .team-list h2 {
      font-size: 20px;
      margin-bottom: 15px;
    }
    .team-item {
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      margin-bottom: 10px;
      background-color: #f9f9f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .team-item a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
    }
    .team-item a:hover {
      color: #4caf50;
    }
    /* Modal styles */
    #editTeamModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0,0,0,0.3);
      z-index: 999;
      align-items: center;
      justify-content: center;
      transition: background-color 0.2s ease;
    }
    #editTeamModal.show {
      display: flex;
    }
    .edit-team-container {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.07);
      padding: 32px 28px;
      max-width: 500px;
      width: 90%;
    }
    /* NAV fix */
    nav a {
    color: #333;
    text-decoration: none !important;
    }

    nav a:hover {
    color: #4caf50;
    }

/* Modal Overlay and Content Transitions - matches view_team.php */
#editTeamModal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 100vw;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

#editTeamModal.show {
  display: flex;
  justify-content: center;
  align-items: center;
}

.edit-team-container {
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
</style>
</head>
<body>
  <?php include __DIR__ . '/../views/nav.php'; ?>
  <div class="layout">
    <div class="sidebar">
      <?php include __DIR__ . '/../views/teams.php'; ?>
    </div>
    <div class="main">
      <!-- Teams You Created -->
      <div class="team-list">
        <h2>Teams You Created</h2>
        <?php if (count($created_teams)): ?>
          <?php foreach ($created_teams as $team): ?>
            <div class="team-item">
              <div>
                <a href="view_team.php?id=<?= $team['id'] ?>">
                  <?= htmlspecialchars($team['name']) ?>
                </a>
                <span style="font-size:13px;color:#888;">
                  Created <?= date('F j, Y', strtotime($team['created_at'])) ?>
                </span>
              </div>
              <button class="btn btn-sm btn-primary" style="margin-left:10px;"
                onclick="openEditTeamModal(<?= $team['id'] ?>, '<?= htmlspecialchars(addslashes($team['name'])) ?>', '<?= htmlspecialchars(addslashes($team['description'])) ?>')">
                <i class="fas fa-edit"></i> Edit
              </button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>You haven't created any teams yet.</p>
        <?php endif; ?>
      </div>
      <!-- Teams You're In -->
      <div class="team-list">
        <h2>Teams You're In</h2>
        <?php if (count($member_teams)): ?>
          <?php foreach ($member_teams as $team): ?>
            <div class="team-item">
              <div>
                <a href="view_team.php?id=<?= $team['id'] ?>">
                  <?= htmlspecialchars($team['name']) ?>
                </a>
                <span style="font-size:13px;color:#888;">
                  Joined <?= date('F j, Y', strtotime($team['created_at'])) ?>
                </span>
              </div>
              <form method="POST" action="leave_team.php" style="display:inline;">
                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to leave this team?');">
                  <i class="fas fa-sign-out-alt"></i> Leave
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>You're not a member of any other teams.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Edit Team Modal Overlay -->
  <div id="editTeamModal">
    <div class="edit-team-container">
      <h3>Edit Team</h2>
      <form id="editTeamForm">
        <input type="hidden" name="team_id" id="edit_team_id">
        <div class="mb-3">
          <label for="edit_team_name" class="form-label">Team Name</label>
          <input type="text" class="form-control" id="edit_team_name" name="name" required>
        </div>
        <div class="mb-3">
          <label for="edit_team_description" class="form-label">Description (optional)</label>
          <textarea class="form-control" id="edit_team_description" name="description" rows="3"></textarea>
        </div>
        <div id="editTeamError" class="alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="closeEditTeamModal()">Cancel</button>
      </form>
    </div>
  </div>
  <script>
    function openEditTeamModal(teamId, name, description) {
      document.getElementById('edit_team_id').value = teamId;
      document.getElementById('edit_team_name').value = name;
      document.getElementById('edit_team_description').value = description || '';
      document.getElementById('editTeamError').style.display = 'none';
      document.getElementById('editTeamModal').classList.add('show');
    }

    function closeEditTeamModal() {
      document.getElementById('editTeamModal').classList.remove('show');
    }

    // Optional: close modal when clicking outside the modal content
    document.getElementById('editTeamModal').addEventListener('click', function(e) {
      if (e.target === this) closeEditTeamModal();
    });

    // Handle form submit
    document.getElementById('editTeamForm').onsubmit = function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('edit_team.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          closeEditTeamModal();
          location.reload(); // Refresh to show updated team info
        } else {
          document.getElementById('editTeamError').innerText = data.error || "Failed to update team.";
          document.getElementById('editTeamError').style.display = 'block';
        }
      })
      .catch(err => {
        document.getElementById('editTeamError').innerText = "Something went wrong.";
        document.getElementById('editTeamError').style.display = 'block';
      });
    };
  </script>
</body>
</html>