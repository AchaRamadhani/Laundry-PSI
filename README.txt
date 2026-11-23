Patch: arahkan tautan WhatsApp langsung ke WhatsApp Web

Salin folder app dan resources dari ZIP ini ke folder proyek Laravel, gabungkan dengan folder yang sudah ada, dan izinkan overwrite untuk tiga file yang sama.

File yang diubah:
- app/Support/WhatsAppNota.php
- app/Support/WhatsAppContact.php
- resources/views/kontak/index.blade.php

Tautan pesan dan kontak sekarang memakai https://web.whatsapp.com/send agar diarahkan ke WhatsApp Web.