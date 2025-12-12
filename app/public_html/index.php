<?php
/**
 * Embun Slowbar - Root Redirect
 * Automatically redirects to the main application
 */

// Determine protocol
if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
} else {
    $uri = 'http://';
}

$uri .= $_SERVER['HTTP_HOST'];
header('Location: ' . $uri . '/embun/');
exit;
