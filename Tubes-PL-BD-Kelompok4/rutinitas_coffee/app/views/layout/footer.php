</main>
<?php if (Auth::check()): ?>
  </div>
</div>
<?php endif; ?>
<script>
// Pembuka/penutup jendela mengambang: data-open="id", data-close, atau klik di luar jendela
document.addEventListener('click', function (e) {
  const buka = e.target.closest('[data-open]');
  if (buka) { const d = document.getElementById(buka.dataset.open); if (d) d.showModal(); return; }
  if (e.target.closest('[data-close]')) { const d = e.target.closest('dialog'); if (d) d.close(); return; }
  if (e.target.tagName === 'DIALOG') e.target.close();
});
</script>
</body>
</html>
