<?php
$page_title = 'Users';
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
if (($_GET['action'] ?? '')==='delete' && $id){ $conn->query("DELETE FROM users WHERE id=$id AND role='user'"); flash('success','Deleted.'); redirect(admin_url('users.php')); }
$rows = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<h1 class="serif text-4xl mb-8">Users</h1>
<table><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th></th></tr></thead><tbody>
<?php while ($r=$rows->fetch_assoc()): ?>
<tr><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['role']) ?></td><td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
<td><?php if ($r['role']==='user'): ?><a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a><?php endif; ?></td></tr>
<?php endwhile; ?>
</tbody></table>
<?php include __DIR__ . '/includes/footer.php'; ?>
