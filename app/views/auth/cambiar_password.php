<?php
$titulo = 'Cambiar Contraseña - Hogar Ideal Perú';
// HTML básico ya que no necesitamos el layout completo de admin si estamos obligando al cambio
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $titulo ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="background:var(--bg);display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1rem;">

<div class="form-card" style="width:100%;max-width:450px;">
  <div style="text-align:center;margin-bottom:2rem;">
    <h2 style="font-family:'Playfair Display',serif;color:var(--text);margin-bottom:.5rem">Actualiza tu contraseña</h2>
    <p style="color:var(--text-2);font-size:.9rem">Por seguridad, debes cambiar la contraseña temporal generada por el administrador.</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group" style="margin-bottom:1rem">
      <label>Nueva Contraseña</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-group" style="margin-bottom:2rem">
      <label>Confirmar Contraseña</label>
      <input type="password" name="password_confirm" required>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Guardar y Continuar</button>
  </form>
</div>

</body>
</html>
