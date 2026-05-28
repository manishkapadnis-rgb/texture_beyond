<?php
require_once 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $type = $_POST['type'] ?? 'contact';
    db_exec("INSERT INTO enquiries (type,name,email,phone,company,subject,message) VALUES (?,?,?,?,?,?,?)",
      [$type,$_POST['name'],$_POST['email'],$_POST['phone']??'',$_POST['company']??'',$_POST['subject']??'',$_POST['message']??'']);
    send_admin_notification("New $type enquiry from {$_POST['name']}", "Email: {$_POST['email']}\nPhone: ".($_POST['phone']??'')."\n\n".($_POST['message']??''));
    flash('success','Thank you. We will respond shortly.');
    redirect(url('contact.php#sent'));
}
$s = setting();
$page_title = 'Contact | ' . SITE_NAME;
include 'includes/header.php';
?>
<section class="pt-40 pb-section-gap px-margin-mobile md:px-margin-desktop bg-surface">
  <div class="max-w-container-max mx-auto">
    <div class="text-center mb-16">
      <span class="font-label-caps text-label-caps text-secondary mb-4 block">Reach Out</span>
      <h1 class="font-display-md text-headline-lg md:text-display-md">Get In Touch</h1>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
      <div class="gsap-fade space-y-8">
        <div>
          <h3 class="font-display-md text-headline-md mb-6">Studio</h3>
          <div class="space-y-4 font-body-md">
            <div><div class="font-label-caps text-label-caps text-secondary mb-1">Address</div><?= e($s['address']) ?></div>
            <div><div class="font-label-caps text-label-caps text-secondary mb-1">Email</div><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></div>
            <div><div class="font-label-caps text-label-caps text-secondary mb-1">Phone</div><?= e($s['phone']) ?></div>
          </div>
        </div>
      </div>
      <div class="gsap-fade">
        <div class="flex gap-4 mb-8 font-label-caps text-label-caps">
          <button onclick="showForm('contact')" id="tab-contact" class="tab-btn pb-2 border-b-2 border-primary">General</button>
          <button onclick="showForm('bulk')" id="tab-bulk" class="tab-btn pb-2 border-b-2 border-transparent text-on-surface-variant">Bulk Inquiry</button>
          <button onclick="showForm('corporate')" id="tab-corporate" class="tab-btn pb-2 border-b-2 border-transparent text-on-surface-variant">Corporate Gifting</button>
        </div>
        <form id="form-contact" method="post" class="space-y-6 form-pane">
          <input type="hidden" name="type" value="contact"/>
          <input class="input-underline" name="name" required placeholder="Name"/>
          <input class="input-underline" name="email" type="email" required placeholder="Email"/>
          <input class="input-underline" name="phone" placeholder="Phone"/>
          <input class="input-underline" name="subject" placeholder="Subject"/>
          <textarea class="input-underline" name="message" rows="4" required placeholder="Message"></textarea>
          <button class="btn-primary">Send Message</button>
        </form>
        <form id="form-bulk" method="post" class="space-y-6 form-pane hidden">
          <input type="hidden" name="type" value="bulk"/>
          <input class="input-underline" name="name" required placeholder="Name"/>
          <input class="input-underline" name="email" type="email" required placeholder="Email"/>
          <input class="input-underline" name="phone" required placeholder="Phone"/>
          <input class="input-underline" name="company" placeholder="Company / Project"/>
          <textarea class="input-underline" name="message" rows="4" required placeholder="Quantities, dimensions, timeline"></textarea>
          <button class="btn-primary">Submit Bulk Inquiry</button>
        </form>
        <form id="form-corporate" method="post" class="space-y-6 form-pane hidden">
          <input type="hidden" name="type" value="corporate"/>
          <input class="input-underline" name="name" required placeholder="Name"/>
          <input class="input-underline" name="email" type="email" required placeholder="Email"/>
          <input class="input-underline" name="phone" required placeholder="Phone"/>
          <input class="input-underline" name="company" required placeholder="Company"/>
          <textarea class="input-underline" name="message" rows="4" required placeholder="Gifting brief — occasion, quantity, budget"></textarea>
          <button class="btn-primary">Request Proposal</button>
        </form>
      </div>
    </div>
  </div>
</section>
<script>
function showForm(t){
  ['contact','bulk','corporate'].forEach(k=>{
    document.getElementById('form-'+k).classList.toggle('hidden', k!==t);
    document.getElementById('tab-'+k).classList.toggle('border-primary', k===t);
    document.getElementById('tab-'+k).classList.toggle('border-transparent', k!==t);
    document.getElementById('tab-'+k).classList.toggle('text-on-surface-variant', k!==t);
  });
}
</script>
<?php include 'includes/footer.php'; ?>
