<?php
require_once 'includes/functions.php';
$return = $_REQUEST['return'] ?? '';
if ($return && !preg_match('#^https?://#i', $return)) $return = $return; // relative ok
$safe_return = $return && (strpos($return, url('')) === 0 || strpos($return, '/') === 0) ? $return : url('my-account.php');

if (is_logged_in()) redirect($safe_return);

$mode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $form_mode = $_POST['mode'] ?? 'login';
    if ($form_mode === 'register'){
        $n = trim($_POST['name'] ?? '');
        $e = trim($_POST['email'] ?? '');
        $p = $_POST['password'] ?? '';
        if ($n === '' || $e === '' || strlen($p) < 6) {
            flash('error', 'Please fill all fields. Password must be at least 6 characters.');
        } elseif (!filter_var($e, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
            $stmt->bind_param('s', $e); $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                flash('error', 'Email already registered. Please sign in instead.');
            } else {
                $h = password_hash($p, PASSWORD_DEFAULT);
                $st = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
                $st->bind_param('sss', $n, $e, $h); $st->execute();
                $_SESSION['user_id'] = $conn->insert_id;
                session_regenerate_id(true);
                flash('success', 'Welcome to Texture & Beyond.');
                redirect($safe_return);
            }
        }
        $mode = 'register';
    } else {
        $e = trim($_POST['email'] ?? '');
        $p = $_POST['password'] ?? '';
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param('s', $e); $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();
        if ($u && password_verify($p, $u['password'])) {
            $_SESSION['user_id'] = $u['id'];
            session_regenerate_id(true);
            flash('success', 'Welcome back.');
            redirect($safe_return);
        }
        flash('error', 'Invalid email or password.');
    }
}

$page_title = ($mode === 'register' ? 'Create Account' : 'Sign In') . ' | Texture & Beyond';
include 'includes/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh - 200px);display:flex;align-items:center;justify-content:center;padding:120px 24px 80px;background:linear-gradient(180deg,#fff8f3 0%,#f4f1ec 100%)}
.auth-card{width:100%;max-width:440px;background:#fff;padding:48px 40px;border-radius:14px;box-shadow:0 20px 60px -20px rgba(0,0,0,.18),0 0 0 1px rgba(0,0,0,.04);font-family:'Hanken Grotesk'}
.auth-tabs{display:grid;grid-template-columns:1fr 1fr;background:#f4f1ec;border-radius:999px;padding:4px;margin-bottom:28px}
.auth-tab{padding:10px;text-align:center;font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#7a7570;text-decoration:none;border-radius:999px;transition:all .25s}
.auth-tab.is-active{background:#1a1a1a;color:#fff}
.auth-title{font-family:'Bodoni Moda';font-size:28px;font-weight:500;margin:0 0 6px;color:#1a1a1a;text-align:center}
.auth-sub{font-size:13px;color:#6b6b6b;text-align:center;margin:0 0 24px}
.auth-field{position:relative;margin-bottom:14px}
.auth-field input{width:100%;padding:14px 16px;border:1px solid rgba(0,0,0,.15);border-radius:8px;background:#fff;font-family:inherit;font-size:14px;outline:none;transition:border-color .2s,box-shadow .2s}
.auth-field input:focus{border-color:#1a1a1a;box-shadow:0 0 0 3px rgba(26,26,26,.08)}
.auth-btn{display:block;width:100%;padding:14px;background:#1a1a1a;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;transition:background .25s,transform .2s}
.auth-btn:hover{background:#000;transform:translateY(-1px)}
.auth-foot{text-align:center;font-size:13px;color:#6b6b6b;margin-top:18px}
.auth-foot a{color:#1a1a1a;text-decoration:underline;font-weight:600}
.auth-divider{display:flex;align-items:center;gap:12px;color:#a5a09b;font-size:11px;letter-spacing:.15em;text-transform:uppercase;margin:18px 0}
.auth-divider::before,.auth-divider::after{content:"";flex:1;height:1px;background:rgba(0,0,0,.08)}
.auth-perks{display:flex;justify-content:center;gap:18px;margin-top:18px;font-size:11px;color:#6b6b6b;letter-spacing:.05em}
.auth-perks span{display:inline-flex;align-items:center;gap:4px}
.auth-perks .material-symbols-outlined{font-size:14px;color:#1d7a5c}
</style>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-tabs">
      <a href="?mode=login<?= $return ? '&return=' . urlencode($return) : '' ?>" class="auth-tab<?= $mode==='login'?' is-active':'' ?>">Sign In</a>
      <a href="?mode=register<?= $return ? '&return=' . urlencode($return) : '' ?>" class="auth-tab<?= $mode==='register'?' is-active':'' ?>">Register</a>
    </div>

    <?php if ($mode === 'register'): ?>
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-sub">Join Texture &amp; Beyond to checkout and review.</p>
    <?php else: ?>
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-sub">Sign in to your account to continue.</p>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="mode" value="<?= e($mode) ?>"/>
      <?php if ($return): ?><input type="hidden" name="return" value="<?= e($return) ?>"/><?php endif; ?>
      <?php if ($mode === 'register'): ?>
        <div class="auth-field"><input name="name" required placeholder="Full name" autocomplete="name"/></div>
      <?php endif; ?>
      <div class="auth-field"><input name="email" type="email" required placeholder="Email address" autocomplete="email"/></div>
      <div class="auth-field"><input name="password" type="password" required placeholder="Password" minlength="<?= $mode==='register'?6:1 ?>" autocomplete="<?= $mode==='register'?'new-password':'current-password' ?>"/></div>
      <button class="auth-btn" type="submit"><?= $mode === 'register' ? 'Create Account' : 'Sign In' ?></button>
    </form>

    <div class="auth-foot">
      <?php if ($mode === 'register'): ?>
        Already a member? <a href="?mode=login<?= $return ? '&return=' . urlencode($return) : '' ?>">Sign in</a>
      <?php else: ?>
        New here? <a href="?mode=register<?= $return ? '&return=' . urlencode($return) : '' ?>">Create account</a> · <a href="<?= url('forgot-password.php') ?>">Forgot password?</a>
      <?php endif; ?>
    </div>

    <div class="auth-divider">Your benefits</div>
    <div class="auth-perks">
      <span><span class="material-symbols-outlined">verified</span> Verified reviews</span>
      <span><span class="material-symbols-outlined">local_shipping</span> Order tracking</span>
      <span><span class="material-symbols-outlined">favorite</span> Wishlist</span>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
