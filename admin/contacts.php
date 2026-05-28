<?php
$page_title = 'Contacts';
require_once __DIR__ . '/includes/header.php';
$id = (int)($_GET['id'] ?? 0);
if (($_GET['action'] ?? '')==='delete' && $id){ $conn->query("DELETE FROM contacts WHERE id=$id"); flash('success','Deleted.'); redirect(admin_url('contacts.php')); }
$rows = $conn->query("SELECT * FROM contacts ORDER BY id DESC");
?>
<h1 class="serif text-4xl mb-8">Contact Messages</h1>
<table><thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th></th></tr></thead><tbody>
<?php while ($r=$rows->fetch_assoc()): ?>
<tr><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['subject']) ?></td><td class="max-w-md"><?= nl2br(e($r['message'])) ?></td><td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
<td><a class="btn-ghost" onclick="return confirm('Delete?')" href="?action=delete&id=<?= $r['id'] ?>">Delete</a></td></tr>
<?php endwhile; ?>
</tbody></table>
<?php include __DIR__ . '/includes/footer.php'; ?>
