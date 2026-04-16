<?php
// Empêche JavaScript d'accéder aux cookies (Protection XSS)
ini_set('session.cookie_httponly', 1);
// Force l'utilisation des cookies uniquement (pas d'ID de session dans l'URL)
ini_set('session.use_only_cookies', 1);
// Désactive l'accès via HTTP si tu passes en HTTPS plus tard
ini_set('session.cookie_secure', 0); // Mets à 1 quand tu seras en ligne (HTTPS)
// Empêche l'envoi du cookie sur des sites tiers (Protection CSRF)
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
