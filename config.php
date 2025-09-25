<?php 
// Database configuration    
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientID = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$googleOauthScope = $_ENV['GOOGLE_OAUTH_SCOPE'];
$redirectUri = $_ENV['REDIRECT_URI'];
 
// Start session 
if(!session_id()) session_start(); 
 
// Google OAuth URL 
$googleOauthURL = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode($googleOauthScope) . '&redirect_uri=' . $redirectUri . '&response_type=code&client_id=' . $clientID . '&access_type=online'; 
 
?>