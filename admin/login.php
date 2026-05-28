<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_admin()) redirect(admin_url('index.php'));
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $e = trim($_POST['email']); $p = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
    $stmt->bind_param('s', $e); $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    if ($u && password_verify($p, $u['password'])){
        $_SESSION['admin_id'] = $u['id'];
        redirect(admin_url('index.php'));
    } else flash('error','Invalid credentials.');
}
?>
<!DOCTYPE html><html><head>
<meta charset="utf-8"/><title>Admin Login | Texture & Beyond</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda&family=Hanken+Grotesk:wght@300;600&display=swap" rel="stylesheet"/>
<style>body{font-family:'Hanken Grotesk';background:#fff8f3}.serif{font-family:'Bodoni Moda'}</style>
</head><body class="min-h-screen flex items-center justify-center p-6">
<div class="w-full max-w-md bg-white p-12 shadow-lg">
  <h1 class="serif text-[36px] text-center mb-2">Admin</h1>
  <p class="text-center text-[#444] mb-10">Texture &amp; Beyond</p>
  <?php if ($m = flash('error')): ?><div class="bg-red-50 text-red-700 p-3 mb-6 text-sm"><?= e($m) ?></div><?php endif; ?>
  <form method="post" class="space-y-6">
    <input name="email" type="email" required placeholder="Email" class="w-full border-b border-black py-3 bg-transparent outline-none"/>
    <input name="password" type="password" required placeholder="Password" class="w-full border-b border-black py-3 bg-transparent outline-none"/>
    <button class="w-full bg-black text-white py-4 text-[12px] font-semibold tracking-[.15em] uppercase">Sign In</button>
  </form>
  <p class="text-[11px] tracking-widest uppercase text-center mt-8 text-gray-500">Default: admin@texturebeyond.com / admin123</p>
</div></body></html>
