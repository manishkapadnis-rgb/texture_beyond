<?php
require_once 'includes/functions.php';
if (is_logged_in()) redirect(url('my-account.php'));
$mode = $_GET['mode'] ?? 'login';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (($_POST['mode'] ?? '') === 'register'){
        $n=trim($_POST['name']); $e=trim($_POST['email']); $p=$_POST['password'];
        $stmt=$conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param('s',$e); $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()){ flash('error','Email already registered.'); }
        else {
            $h = password_hash($p, PASSWORD_DEFAULT);
            $st = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $st->bind_param('sss',$n,$e,$h); $st->execute();
            $_SESSION['user_id'] = $conn->insert_id;
            flash('success', 'Welcome to Texture & Beyond.');
            redirect(url('my-account.php'));
        }
    } else {
        $e=trim($_POST['email']); $p=$_POST['password'];
        $stmt=$conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param('s',$e); $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();
        if ($u && password_verify($p, $u['password'])){
            $_SESSION['user_id'] = $u['id'];
            flash('success','Welcome back.');
            redirect(url('my-account.php'));
        } else flash('error','Invalid credentials.');
    }
}
$page_title = 'Login | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-md mx-auto gsap-fade">
    <div class="text-center mb-12">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block"><?= $mode==='register'?'Join Us':'Welcome Back' ?></span>
      <h1 class="font-display-md text-headline-lg"><?= $mode==='register'?'Create Account':'Sign In' ?></h1>
    </div>
    <form method="post" class="space-y-8">
      <input type="hidden" name="mode" value="<?= e($mode) ?>"/>
      <?php if ($mode === 'register'): ?>
        <input class="input-underline" name="name" required placeholder="Full Name"/>
      <?php endif; ?>
      <input class="input-underline" name="email" type="email" required placeholder="Email Address"/>
      <input class="input-underline" name="password" type="password" required placeholder="Password"/>
      <button class="btn-primary w-full"><?= $mode==='register'?'Create Account':'Sign In' ?></button>
    </form>
    <div class="text-center mt-10 font-body-md text-on-surface-variant">
      <?php if ($mode === 'register'): ?>
        Already a member? <a href="?mode=login" class="text-primary gold-underline">Sign In</a>
      <?php else: ?>
        New here? <a href="?mode=register" class="text-primary gold-underline">Create Account</a> · <a href="<?= url('forgot-password.php') ?>" class="text-primary gold-underline">Forgot Password</a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
