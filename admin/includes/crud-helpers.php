<?php
/**
 * Tiny CRUD scaffolder used by lightweight admin pages.
 * Provides upload_image() helper.
 */
function upload_image($field, $subdir, $existing = null){
    if (empty($_FILES[$field]['name'])) return $existing;
    $fn = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES[$field]['name']);
    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $fn);
    return $fn;
}
function admin_upload_url($subdir, $file){
    return $file ? UPLOAD_URL . '/' . $subdir . '/' . $file : '';
}
