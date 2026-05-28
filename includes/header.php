<?php
/**
 * Storefront header — includes <head>, navbar, and flash messages.
 * Pages may set $page_title, $meta_description, $meta_keywords before including.
 */
require_once __DIR__ . '/functions.php';
include __DIR__ . '/head.php';
include __DIR__ . '/navbar.php';
?>
<?php if ($m = flash('success')): ?><div class="flash-msg success"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash('error')):   ?><div class="flash-msg error"><?=   e($m) ?></div><?php endif; ?>
