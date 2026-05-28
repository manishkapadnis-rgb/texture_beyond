<?php
require_once 'includes/functions.php';
$items = cart_items();
if (!$items) redirect(url('cart.php'));

$subtotal = cart_subtotal();
$discount = 0; $coupon_code = $_SESSION['coupon'] ?? '';
if ($coupon_code){ $r = apply_coupon($coupon_code); if ($r['ok']) $discount = $r['discount']; }
$s = setting();
$shipping = ($subtotal >= ($s['free_shipping_above'] ?? 0)) ? 0 : (float)$s['shipping_charge'];
$total = max(0, $subtotal - $discount + $shipping);
$u = current_user();
$checkout_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['full_name'] ?? '');
    $em   = trim($_POST['email'] ?? '');
    $ph   = trim($_POST['phone'] ?? '');
    $ad   = trim($_POST['address'] ?? '');
    $ci   = trim($_POST['city'] ?? '');
    $st   = trim($_POST['state'] ?? '');
    $pn   = trim($_POST['pincode'] ?? '');
    $pm   = $_POST['payment_method'] ?? 'COD';
    $no   = $_POST['notes'] ?? '';
    $create_account = !empty($_POST['create_account']);
    $pw   = (string)($_POST['account_password'] ?? '');
    $uid  = $u['id'] ?? null;

    if ($name === '')                              $checkout_error = 'Please enter your full name.';
    elseif (!filter_var($em, FILTER_VALIDATE_EMAIL)) $checkout_error = 'Please enter a valid email.';
    elseif (!preg_match('/^[\d+\-\s()]{7,20}$/', $ph)) $checkout_error = 'Please enter a valid phone number.';
    elseif ($ad === '' || $ci === '' || $st === '' || $pn === '') $checkout_error = 'Please fill in all shipping fields.';

    // Guest checkout: if email already belongs to a registered user and the
    // shopper is not logged in, ask them to sign in (we will never attach an
    // order to an account without authentication). New accounts are created
    // when "Create Account" is checked and auto-logged in.
    if (!$checkout_error && !$uid) {
        $look = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $look->bind_param('s', $em); $look->execute();
        $existing = $look->get_result()->fetch_assoc();
        if ($existing && $create_account) {
            $checkout_error = 'An account with this email already exists. Please sign in instead, or use a different email.';
        } elseif (!$existing && $create_account) {
            if (strlen($pw) < 6) {
                $checkout_error = 'Password must be at least 6 characters.';
            } else {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $ins = $conn->prepare("INSERT INTO users (name,email,phone,password,address,city,state,pincode) VALUES (?,?,?,?,?,?,?,?)");
                $ins->bind_param('ssssssss', $name,$em,$ph,$hash,$ad,$ci,$st,$pn);
                if ($ins->execute()) {
                    $uid = $conn->insert_id;
                    $_SESSION['user_id'] = $uid;
                    flash('success', 'Your account has been created.');
                } else {
                    $checkout_error = 'Could not create account: ' . $conn->error;
                }
            }
        }
        // else: guest checkout — order placed with user_id = NULL
    }

  if (!$checkout_error) {
    $code = 'TB' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = $conn->prepare("INSERT INTO orders (order_code,user_id,full_name,email,phone,address,city,state,pincode,subtotal,discount,coupon_code,shipping,total,payment_method,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('sissssssssddsdss', $code,$uid,$name,$em,$ph,$ad,$ci,$st,$pn,$subtotal,$discount,$coupon_code,$shipping,$total,$pm,$no);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $ins = $conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity,subtotal) VALUES (?,?,?,?,?,?)");
    foreach ($items as $it){
        $ins->bind_param('iisdid', $order_id, $it['id'], $it['name'], $it['effective_price'], $it['qty'], $it['line_total']);
        $ins->execute();
        $conn->query("UPDATE products SET stock=GREATEST(0,stock-{$it['qty']}) WHERE id={$it['id']}");
    }
    if ($coupon_code) $conn->query("UPDATE coupons SET used=used+1 WHERE code='" . $conn->real_escape_string($coupon_code) . "'");
    cart_clear();
    redirect(url('thank-you.php?code=' . $code));
  }
}

$page_title = 'Checkout | Texture & Beyond';
include 'includes/header.php';
?>
<style>
.co-wrap{padding:120px 16px 60px}
@media(min-width:768px){.co-wrap{padding:160px 32px 80px}}
.co-card{background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:20px}
@media(min-width:768px){.co-card{padding:28px}}
.co-section-title{font-size:18px;font-weight:600;margin:0 0 16px;color:#1a1a1a}
.co-grid{display:grid;grid-template-columns:1fr;gap:14px}
@media(min-width:640px){.co-grid{grid-template-columns:1fr 1fr}}
.co-grid .span-2{grid-column:1/-1}
.co-input{width:100%;padding:14px 16px;border:1px solid rgba(0,0,0,.15);border-radius:10px;background:#fff;font-size:14px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s}
.co-input:focus{border-color:#1a1a1a;box-shadow:0 0 0 3px rgba(26,26,26,.06)}
.co-pay{display:flex;flex-direction:column;gap:10px}
.co-pay label{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid rgba(0,0,0,.12);border-radius:10px;cursor:pointer;font-size:14px;transition:border-color .2s,background .2s}
.co-pay label:hover{border-color:#1a1a1a}
.co-pay input[type=radio]{accent-color:#1a1a1a}
.co-pay input[type=radio]:checked + span{font-weight:600}
.co-summary{position:sticky;top:24px}
.co-summary__row{display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px}
.co-summary__row strong{font-weight:600}
.co-summary__total{display:flex;justify-content:space-between;font-size:20px;font-weight:700;padding-top:14px;margin-top:14px;border-top:2px solid #1a1a1a}
.co-cta{display:block;width:100%;background:#1a1a1a;color:#fff;border:none;padding:16px;border-radius:999px;font-size:14px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;cursor:pointer;margin-top:18px;transition:transform .2s,background .25s}
.co-cta:hover{background:#000;transform:translateY(-1px)}
.co-tabs{display:flex;background:#f4f1ec;border-radius:999px;padding:4px;margin-bottom:18px}
.co-tabs button{flex:1;border:none;background:transparent;padding:10px 14px;border-radius:999px;font-size:13px;font-weight:600;cursor:pointer;color:#1a1a1a;transition:all .2s}
.co-tabs button.is-active{background:#1a1a1a;color:#fff}
.co-checkbox{display:flex;align-items:center;gap:10px;font-size:14px;cursor:pointer;margin-bottom:12px}
.co-checkbox input{width:18px;height:18px;accent-color:#1a1a1a}
.co-error{background:#fdecec;color:#a01515;border:1px solid #f0c2c2;padding:12px 14px;border-radius:10px;font-size:14px;margin-bottom:18px}
.co-help{font-size:12.5px;color:#6b6b6b;margin-top:6px}
.co-line{display:flex;gap:12px;align-items:center;font-size:14px;justify-content:space-between;padding:8px 0;border-bottom:1px dashed rgba(0,0,0,.08)}
.co-line:last-child{border-bottom:none}
.co-line__qty{color:#6b6b6b;font-size:12px;margin-left:4px}
@media(max-width:767px){.co-summary{position:static}}
</style>
<section class="bg-surface co-wrap">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-10">
      <span class="font-label-caps text-label-caps text-secondary mb-2 block">Final Step</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">Checkout</h1>
    </div>

    <?php if ($checkout_error): ?>
      <div class="co-error"><?= e($checkout_error) ?></div>
    <?php endif; ?>

    <?php if (!$u): ?>
    <div class="co-card mb-6">
      <div class="co-tabs" role="tablist">
        <button type="button" id="co-tab-guest" class="is-active" role="tab" aria-selected="true">Guest / New customer</button>
        <button type="button" id="co-tab-login" role="tab" aria-selected="false">Returning customer</button>
      </div>
      <div id="co-pane-login" hidden>
        <form method="post" action="<?= e(url('login.php?return=' . urlencode(url('checkout.php')))) ?>" class="co-grid">
          <input type="hidden" name="action" value="login"/>
          <input class="co-input span-2" name="email" type="email" required placeholder="Email"/>
          <input class="co-input span-2" name="password" type="password" required placeholder="Password" autocomplete="current-password"/>
          <button class="co-cta span-2" type="submit">Sign in & continue</button>
          <p class="co-help span-2 text-center"><a class="gold-underline text-secondary" href="<?= e(url('forgot-password.php')) ?>">Forgot your password?</a></p>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <form method="post" id="co-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-10">
      <div class="lg:col-span-2 space-y-6">
        <div class="co-card">
          <h3 class="co-section-title">Contact & Shipping</h3>
          <div class="co-grid">
            <input class="co-input span-2" name="full_name" required placeholder="Full name" value="<?= e($_POST['full_name'] ?? $u['name'] ?? '') ?>" autocomplete="name"/>
            <input class="co-input" name="email" type="email" required placeholder="Email" value="<?= e($_POST['email'] ?? $u['email'] ?? '') ?>" autocomplete="email"/>
            <input class="co-input" name="phone" required placeholder="Phone" value="<?= e($_POST['phone'] ?? $u['phone'] ?? '') ?>" autocomplete="tel" inputmode="tel" pattern="[\d+\-\s()]{7,20}"/>
            <input class="co-input span-2" name="address" required placeholder="Street address" value="<?= e($_POST['address'] ?? $u['address'] ?? '') ?>" autocomplete="street-address"/>
            <input class="co-input" name="city" required placeholder="City" value="<?= e($_POST['city'] ?? $u['city'] ?? '') ?>" autocomplete="address-level2"/>
            <input class="co-input" name="state" required placeholder="State" value="<?= e($_POST['state'] ?? $u['state'] ?? '') ?>" autocomplete="address-level1"/>
            <input class="co-input" name="pincode" required placeholder="Pincode" value="<?= e($_POST['pincode'] ?? $u['pincode'] ?? '') ?>" autocomplete="postal-code" inputmode="numeric"/>
            <textarea class="co-input span-2" name="notes" placeholder="Order notes (optional)" rows="2"><?= e($_POST['notes'] ?? '') ?></textarea>
          </div>
        </div>

        <?php if (!$u): ?>
        <div class="co-card">
          <label class="co-checkbox">
            <input type="checkbox" name="create_account" id="create_account" value="1" <?= !empty($_POST['create_account']) || !isset($_POST['create_account']) ? 'checked' : '' ?>/>
            <span><strong>Create an account</strong> for faster checkout next time</span>
          </label>
          <div id="account-password-wrap">
            <input class="co-input" name="account_password" id="account_password" type="password" placeholder="Choose a password (min 6 characters)" minlength="6" autocomplete="new-password"/>
            <p class="co-help">We'll use your email above as your login. You can skip this and check out as a guest.</p>
          </div>
        </div>
        <script>
          (function(){
            var cb=document.getElementById('create_account'),wrap=document.getElementById('account-password-wrap'),pw=document.getElementById('account_password');
            function sync(){wrap.style.display=cb.checked?'':'none';pw.required=cb.checked;} cb.addEventListener('change',sync); sync();
            var g=document.getElementById('co-tab-guest'),l=document.getElementById('co-tab-login'),pg=document.getElementById('co-form'),pl=document.getElementById('co-pane-login');
            if(g&&l){g.addEventListener('click',function(){g.classList.add('is-active');l.classList.remove('is-active');pg.hidden=false;pl.hidden=true;});l.addEventListener('click',function(){l.classList.add('is-active');g.classList.remove('is-active');pl.hidden=false;pg.hidden=true;});}
          })();
        </script>
        <?php endif; ?>

        <div class="co-card">
          <h3 class="co-section-title">Payment Method</h3>
          <div class="co-pay">
            <label><input type="radio" name="payment_method" value="COD" checked/> <span>Cash on Delivery</span></label>
            <label><input type="radio" name="payment_method" value="RAZORPAY"/> <span>Razorpay — Cards / UPI / Netbanking</span></label>
            <label><input type="radio" name="payment_method" value="BANK"/> <span>Bank Transfer</span></label>
          </div>
        </div>
      </div>

      <div class="co-card co-summary">
        <h3 class="co-section-title">Your Order</h3>
        <div class="mb-3">
          <?php foreach ($items as $it): ?>
          <div class="co-line">
            <span><?= e($it['name']) ?><span class="co-line__qty">× <?= (int)$it['qty'] ?></span></span>
            <strong><?= money($it['line_total']) ?></strong>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="co-summary__row"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
        <?php if ($discount): ?><div class="co-summary__row" style="color:#7a3a3a"><span>Discount</span><strong>−<?= money($discount) ?></strong></div><?php endif; ?>
        <div class="co-summary__row"><span>Shipping</span><strong><?= $shipping ? money($shipping) : 'Free' ?></strong></div>
        <div class="co-summary__total"><span>Total</span><span><?= money($total) ?></span></div>
        <button class="co-cta" type="submit">Place Order</button>
        <p class="co-help" style="text-align:center;margin-top:10px">🔒 Secure checkout • Your data is encrypted</p>
      </div>
    </form>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
