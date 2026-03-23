<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle='Settings';
$success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&verifyCsrfToken($_POST['csrf_token']??'')) {
    $settings=['site_name','site_tagline','contact_email','contact_phone','contact_address','payu_merchant_key','payu_salt','payu_mode','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_name','smtp_from_email','razorpay_key','razorpay_secret'];
    foreach ($settings as $key) {
        if (isset($_POST[$key])) {
            $val=sanitize($_POST[$key]);
            $exists=$pdo->prepare("SELECT id FROM settings WHERE setting_key=?");
            $exists->execute([$key]);
            if ($exists->fetch()) { $pdo->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([$val,$key]); }
            else { $pdo->prepare("INSERT INTO settings (setting_key,setting_value) VALUES (?,?)")->execute([$key,$val]); }
        }
    }
    $success='Settings saved.';
}

// Load all settings
$allSettings=[];
$rows=$pdo->query("SELECT setting_key,setting_value FROM settings")->fetchAll();
foreach ($rows as $r) $allSettings[$r['setting_key']]=$r['setting_value'];
function setting($key,$default='') { global $allSettings; return htmlspecialchars($allSettings[$key]??$default,ENT_QUOTES); }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head><body>
<div class="admin-layout">
<?php require_once '../includes/admin-sidebar.php'; ?>
<div class="admin-main">
<?php require_once '../includes/admin-topbar.php'; ?>
<div class="admin-content">
<?php if ($success): ?><div class="alert alert-success"><?= xss($success) ?></div><?php endif; ?>
<div class="page-header">
    <div class="page-header-left"><div class="page-title">Platform Settings</div></div>
</div>

<form method="POST">
<?= csrfField() ?>
<div class="tabs">
    <button type="button" class="tab-btn active" onclick="switchTab('general',this)">General</button>
    <button type="button" class="tab-btn" onclick="switchTab('payment',this)">Payment</button>
    <button type="button" class="tab-btn" onclick="switchTab('email',this)">Email (SMTP)</button>
</div>

<div id="tab-general" class="tab-content active">
<div class="card">
<div class="card-header"><div class="card-title">General Settings</div></div>
<div class="card-body">
    <div class="form-grid form-grid-2">
        <div class="form-group"><label>Site Name</label><input type="text" name="site_name" value="<?= setting('site_name','LexLearnAI') ?>"></div>
        <div class="form-group"><label>Tagline</label><input type="text" name="site_tagline" value="<?= setting('site_tagline','Legal AI Education for the Next Generation of Lawyers') ?>"></div>
        <div class="form-group"><label>Contact Email</label><input type="email" name="contact_email" value="<?= setting('contact_email') ?>"></div>
        <div class="form-group"><label>Contact Phone</label><input type="text" name="contact_phone" value="<?= setting('contact_phone') ?>"></div>
        <div class="form-group full"><label>Address</label><textarea name="contact_address" rows="2"><?= setting('contact_address') ?></textarea></div>
    </div>
</div>
</div>
</div>

<div id="tab-payment" class="tab-content">
<div class="card">
<div class="card-header"><div class="card-title">PayU Integration</div></div>
<div class="card-body">
    <div class="alert alert-warning">⚠️ Keep these credentials secret. Never share your salt key.</div>
    <div class="form-grid form-grid-2">
        <div class="form-group"><label>PayU Merchant Key</label><input type="text" name="payu_merchant_key" value="<?= setting('payu_merchant_key') ?>" placeholder="Your PayU Key"></div>
        <div class="form-group"><label>PayU Salt</label><input type="password" name="payu_salt" value="<?= setting('payu_salt') ?>" placeholder="Your PayU Salt"></div>
        <div class="form-group full"><label>Mode</label><select name="payu_mode"><option value="test" <?= setting('payu_mode')!=='live'?'selected':'' ?>>Test Mode</option><option value="live" <?= setting('payu_mode')==='live'?'selected':'' ?>>Live Mode</option></select></div>
    </div>
</div>
</div>
</div>

<div id="tab-email" class="tab-content">
<div class="card">
<div class="card-header"><div class="card-title">SMTP Email Settings</div></div>
<div class="card-body">
    <div class="form-grid form-grid-2">
        <div class="form-group"><label>SMTP Host</label><input type="text" name="smtp_host" value="<?= setting('smtp_host','smtp.gmail.com') ?>"></div>
        <div class="form-group"><label>SMTP Port</label><input type="number" name="smtp_port" value="<?= setting('smtp_port','587') ?>"></div>
        <div class="form-group"><label>SMTP Username</label><input type="text" name="smtp_user" value="<?= setting('smtp_user') ?>"></div>
        <div class="form-group"><label>SMTP Password</label><input type="password" name="smtp_pass" value="<?= setting('smtp_pass') ?>" placeholder="App password"></div>
        <div class="form-group"><label>From Name</label><input type="text" name="smtp_from_name" value="<?= setting('smtp_from_name','LexLearnAI') ?>"></div>
        <div class="form-group"><label>From Email</label><input type="email" name="smtp_from_email" value="<?= setting('smtp_from_email') ?>"></div>
    </div>
</div>
</div>
</div>

<div style="margin-top:20px;"><button type="submit" class="btn btn-primary">💾 Save Settings</button></div>
</form>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
function switchTab(name,btn){
    document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
