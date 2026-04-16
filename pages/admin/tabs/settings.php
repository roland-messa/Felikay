<?php
// On récupère les réglages depuis la DB (votre bloc actuel est correct)
$config = $pdo->query("SELECT cle, valeur FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$siteName = $config['site_name'] ?? 'Felikay';
$contactEmail = $config['contact_email'] ?? 'contact@felikay.com';
$currency = $config['currency'] ?? 'USD';
$maintenance = $config['maintenance_mode'] ?? '0';
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

  <div class="bg-white p-8 rounded-3xl border border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
    <h4 class="font-serif italic text-xl mb-6">Informations Boutique</h4>
    <form action="../assets/actions/update_settings.php" method="POST" class="space-y-4">
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Nom du Site</label>
        <input type="text" name="site_name" value="<?php echo htmlspecialchars($siteName); ?>" class="w-full p-3 bg-slate-50 rounded-xl border border-black text-sm outline-none">
      </div>
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Email de contact</label>
        <input type="email" name="contact_email" value="<?php echo htmlspecialchars($contactEmail); ?>" class="w-full p-3 bg-slate-50 rounded-xl border border-black text-sm outline-none">
      </div>
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Devise</label>
        <select name="currency" class="w-full p-3 bg-slate-50 rounded-xl border border-black text-sm outline-none">
          <option value="USD" <?php echo $currency == 'USD' ? 'selected' : ''; ?>>Dollar ($)</option>
          <option value="EUR" <?php echo $currency == 'EUR' ? 'selected' : ''; ?>>Euro (€)</option>
          <option value="CDF" <?php echo $currency == 'CDF' ? 'selected' : ''; ?>>Franc Congolais (FC)</option>
        </select>
      </div>
      <button type="submit" class="bg-black text-white px-6 py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-zinc-800 transition">Enregistrer les infos</button>
    </form>
  </div>

  <div class="bg-white p-8 rounded-3xl border border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
    <h4 class="font-serif italic text-xl mb-6">Sécurité du Compte</h4>
    <form action="../../assets/actions/update_settings.php" method="POST" class="space-y-4">
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Nouveau mot de passe</label>
        <input type="password" name="new_pass" placeholder="••••••••" class="w-full p-3 bg-slate-50 rounded-xl border border-black text-sm outline-none">
      </div>
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Confirmer le mot de passe</label>
        <input type="password" name="confirm_pass" placeholder="••••••••" class="w-full p-3 bg-slate-50 rounded-xl border border-black text-sm outline-none">
      </div>
      <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl">
        <p class="text-[10px] text-orange-600 font-bold uppercase italic">Note :</p>
        <p class="text-[11px] text-orange-700">La déconnexion sera automatique après le changement.</p>
      </div>
      <button type="submit" class="bg-black text-white px-6 py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-zinc-800 transition">Mettre à jour l'accès</button>
    </form>
  </div>

  <div class="lg:col-span-2 bg-slate-900 p-8 rounded-3xl border border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] text-white">
    <form action="../assets/actions/update_settings.php" method="POST" class="flex flex-col md:flex-row justify-between items-center gap-6">
      <div>
        <h4 class="font-serif italic text-xl">Mode Maintenance</h4>
        <p class="text-slate-400 text-xs">Si activé, les clients verront une page "Bientôt de retour" au lieu du catalogue.</p>
      </div>

      <div class="flex items-center gap-4">
        <span class="<?php echo $maintenance == '0' ? 'text-red-500' : 'text-slate-500'; ?> text-[10px] font-bold uppercase">Désactivé</span>

        <input type="hidden" name="maintenance_mode" value="<?php echo $maintenance == '1' ? '0' : '1'; ?>">
        <button type="submit" class="w-14 h-8 <?php echo $maintenance == '1' ? 'bg-green-500' : 'bg-slate-700'; ?> rounded-full relative p-1 transition-all">
          <div class="w-6 h-6 bg-white rounded-full shadow-md transition-transform <?php echo $maintenance == '1' ? 'translate-x-6' : 'translate-x-0'; ?>"></div>
        </button>

        <span class="<?php echo $maintenance == '1' ? 'text-green-500' : 'text-slate-500'; ?> text-[10px] font-bold uppercase">Activé</span>
      </div>
    </form>
  </div>

</div>