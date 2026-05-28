<?php
require_once 'includes/functions.php';
unset($_SESSION['user_id']);
flash('success', 'Signed out.');
redirect(url('index.php'));
