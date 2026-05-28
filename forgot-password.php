<?php
require_once 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $e = trim($_POST['email']);
    $token = bin2hex(random_bytes(16));
    $exp = date('Y-m-d H:i:s', time()+3600);
    $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
    $stmt->bind_param('sss', $token, $exp, $e); $stmt->execute();
    flash('success', 'If the email exists, a reset link has been sent.');
    redirect(url('login.php'));
}
$page_title = 'Forgot Password | Texture & Beyond';
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface min-h-[70vh]">
  <div class="max-w-md mx-auto gsap-fade">
    <div class="text-center mb-12">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Reset Password</span>
      <h1 class="font-display-md text-headline-lg">Forgot Password</h1>
      <p class="font-body-md text-on-surface-variant mt-4">Enter your email and we'll send reset instructions.</p>
    </div>
    <form method="post" class="space-y-8">
      <input class="input-underline" name="email" type="email" required placeholder="Email Address"/>
      <button class="btn-primary w-full">Send Reset Link</button>
    </form>
    <p class="text-center mt-10 font-body-md text-on-surface-variant"><a href="<?= url('login.php') ?>" class="gold-underline">← Back to Sign In</a></p>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
