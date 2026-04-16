 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
   <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
     <div>
       <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1">Ventes Totales</p>
       <h3 class="text-3xl font-serif"><?php echo number_format($totalVentes, 2); ?> $</h3>
     </div>
     <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-xl">💰</div>
   </div>
   <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
     <div>
       <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1">Articles</p>
       <h3 class="text-3xl font-serif"><?php echo $countArticles; ?></h3>
     </div>
     <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-xl">📦</div>
   </div>
   <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
     <div>
       <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1">Commandes</p>
       <h3 class="text-3xl font-serif"><?php echo $countCommandes; ?></h3>
     </div>
     <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-xl">📜</div>
   </div>

   <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
     <h4 class="text-sm font-bold mb-6 italic font-serif">Activité récente</h4>
     <div class="h-48 bg-slate-50 rounded-2xl border border-dashed border-slate-200 flex items-center justify-center text-slate-400 text-xs italic">
       Graphique des ventes bientôt disponible...
     </div>
   </div>

 </div>