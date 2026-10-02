USE ledgerflow;
UPDATE users SET password='$2y$12$L5975K08E23i7GLgiA2eMOIqwbPVKelJh0r.WQCeZpM9qPlaDzvA2', status='active' WHERE email='admin@example.com';
