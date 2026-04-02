<?php
// manage_users.php
require_once 'includes/auth.php';
requireAdmin();

global $db;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve' && isset($_POST['user_id'])) {
        $db->update('users', $_POST['user_id'], ['status' => 'approved']);
        header("Location: manage_users.php");
        exit;
    } elseif ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $db->delete('users', $_POST['user_id']);
        header("Location: manage_users.php");
        exit;
    } elseif ($_POST['action'] === 'add_teacher') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            $error = 'Username and password are required.';
        } else {
            $existing = $db->findWhere('users', 'username', $username);
            if (!empty($existing)) {
                $error = 'Username already exists.';
            } else {
                $db->insert('users', [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'teacher',
                    'status' => 'approved' // Admin adding is pre-approved
                ]);
                $success = 'Teacher added successfully.';
            }
        }
    }
}

$users = $db->selectAll('users');
require_once 'includes/header.php';
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 class="card-title">Manage Users</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary" style="background:#e2e8f0; color:#1e293b;">Back to Dashboard</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="margin-top:20px;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success" style="margin-top:20px;"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card" style="margin-top: 20px;">
    <h3 style="margin-bottom: 15px; font-size: 1.2rem; color: var(--text-primary);">Add New Teacher</h3>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add_teacher">
        <div style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 1;">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div style="flex: 1;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Add Teacher</button>
            </div>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php if ($u['role'] === 'admin') continue; ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; background: #e2e8f0;">
                            <?php echo ucfirst($u['role']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['role'] === 'teacher'): ?>
                            <?php if (isset($u['status']) && $u['status'] === 'pending'): ?>
                                <span style="color: var(--danger-color); font-weight: bold;">Pending Approval</span>
                            <?php else: ?>
                                <span style="color: var(--success-color); font-weight: bold;">Approved</span>
                            <?php endif; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['role'] === 'teacher' && isset($u['status']) && $u['status'] === 'pending'): ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.85rem; margin-right: 5px;">Approve</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 0.85rem;">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
