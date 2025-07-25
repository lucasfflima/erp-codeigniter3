<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login com Google</h2>

    <?php if (!$this->session->userdata('usuario_logado')): ?>
        <a href="<?= site_url('auth/google_login') ?>">
            <img src="https://developers.google.com/identity/images/btn_google_signin_light_normal_web.png" alt="Login com Google">
        </a>
    <?php else: ?>
        <p>Você já está logado como <?= $this->session->userdata('usuario_logado')['nome'] ?>.</p>
        <a href="<?= site_url('dashboard') ?>">Ir para Dashboard</a>
    <?php endif; ?>
</body>
</html>