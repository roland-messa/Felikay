<?php

function display_alert()
{
  if (!isset($_GET['msg'])) return;

  $full_name = isset($_SESSION['user_nom']) ? $_SESSION['user_nom'] : 'Cher client';
  $prenom = explode(' ', trim($full_name))[0];

  $type = $_GET['msg'];
  $config = [
    'success'               => ['bg' => 'bg-green-50', 'border' => 'border-green-100', 'text' => 'text-green-700', 'icon' => '✓', 'label' => 'Opération réussie !'],
    'success_order'         => ['bg' => 'bg-black', 'border' => 'border-stone-800', 'text' => 'text-white', 'icon' => '✨', 'label' => "Merci $prenom ! Votre commande Felikay est confirmée."],
    'success_registration'  => ['bg' => 'bg-black', 'border' => 'border-stone-800', 'text' => 'text-white', 'icon' => '✉', 'label' => "Bienvenue ! Votre compte a été créé. Vous pouvez maintenant vous connecter."],
    'deleted'               => ['bg' => 'bg-stone-800', 'border' => 'border-stone-700', 'text' => 'text-white', 'icon' => 'ℹ', 'label' => 'Élément supprimé.'],
    'error'                 => ['bg' => 'bg-red-50', 'border' => 'border-red-100', 'text' => 'text-red-700', 'icon' => '✕', 'label' => 'Une erreur est survenue.'],
    'error_login'           => ['bg' => 'bg-red-50', 'border' => 'border-red-100', 'text' => 'text-red-700', 'icon' => '✕', 'label' => 'Email ou mot de passe incorrect.'],
    'success_contact'       => ['bg' => 'bg-black', 'border' => 'border-stone-800', 'text' => 'text-white', 'icon' => '✉', 'label' => 'Merci. Votre message a été transmis à la Maison Felikay.'],
    'error_email_exists'    => ['bg' => 'bg-red-50', 'border' => 'border-red-100', 'text' => 'text-red-700', 'icon' => '✕', 'label' => 'Cette adresse email possède déjà un compte Felikay.']
  ];

  if (!isset($config[$type])) return;

  $c = $config[$type];
  echo "
    <div id='notification' class='max-w-[1400px] mx-auto px-6 mb-8 animate-fadeIn'>
        <div class='{$c['bg']} border {$c['border']} {$c['text']} px-6 py-4 flex justify-between items-center shadow-sm'>
            <span class='text-[11px] uppercase tracking-[0.2em] font-bold'>
                {$c['icon']} {$c['label']}
            </span>
            <button onclick=\"this.parentElement.parentElement.remove()\" class='hover:scale-110 transition'>✕</button>
        </div>
    </div>
    <script>setTimeout(() => { document.getElementById('notification')?.remove(); }, 6000);</script>";
}

function isAdmin()
{
  if (session_status() === PHP_SESSION_NONE) session_start();

  if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
  }
}


function formatPrice($price)
{
  return number_format($price, 2, '.', ' ') . ' $';
}


function getProductBadge($p)
{
  $cat = strtolower($p['cat_nom'] ?? '');
  $genre = strtolower($p['genre'] ?? '');
  $age = strtolower($p['tranche_age'] ?? '');

  if (stripos($cat, 'chaussure') !== false) {
    return 'Chaussures' . (!empty($genre) ? ' • ' . ucfirst($genre) : '');
  }

  if (stripos($cat, 'enfant') !== false) {
    return (!empty($age) && $age !== 'adulte') ? 'Vêtements • ' . ucfirst($age) : 'Vêtements • Enfant';
  }

  if (in_array($cat, ['homme', 'femme'])) {
    return 'Vêtements • ' . ucfirst($cat);
  }

  return !empty($cat) ? ucfirst($cat) : 'Sans catégorie';
}


function uploadImage($file, $folder = '../../assets/img/produits/')
{
  if (!isset($file) || $file['error'] !== 0) {
    return false;
  }

  // 2. Extensions autorisées
  $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
  $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

  if (!in_array($file_extension, $allowed_extensions)) {
    return false;
  }

  // 3. Création du dossier s'il n'existe pas
  if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
  }

  $new_name = 'produit_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
  $target_path = $folder . $new_name;

  // 5. Déplacement du fichier temporaire vers le dossier final
  if (move_uploaded_file($file['tmp_name'], $target_path)) {
    return "assets/img/produits/" . $new_name;
  }

  return false;
}
