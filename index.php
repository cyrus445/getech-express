<?php
// ============================================================
// FICHIER : index.php
// Rôle     : Page de connexion (authentification par mot de passe)
// ============================================================

session_start();

// Si déjà connecté, redirection vers le tableau de bord
if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true) {
    header('Location: dashboard.php');
    exit;
}

$erreur = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mdp_saisi = trim($_POST['password'] ?? '');

    // Mot de passe défini dans l'énoncé : getech
    if ($mdp_saisi === 'getech') {
        $_SESSION['connecte'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $erreur = 'Mot de passe incorrect. Veuillez réessayer.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GETECH EXPRESS – Connexion</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }
        .logo-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #e63946, #f4a261);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        .btn-login {
            background: linear-gradient(135deg, #e63946, #f4a261);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 8px;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; color: white; }
        .form-control:focus { border-color: #e63946; box-shadow: 0 0 0 0.2rem rgba(230,57,70,0.25); }
        .brand-name { color: #e63946; font-weight: 800; letter-spacing: 1px; }
        .brand-sub  { color: #6c757d; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="logo-circle">
                <i class="bi bi-truck"></i>
            </div>
            <h2 class="brand-name">GETECH EXPRESS</h2>
            <p class="brand-sub">Système de gestion des livraisons</p>
        </div>

        <?php if ($erreur): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">
                    <i class="bi bi-lock-fill me-1"></i> Mot de passe
                </label>
                <input
                    type="password"
                    class="form-control form-control-lg"
                    id="password"
                    name="password"
                    placeholder="Entrez votre mot de passe"
                    required
                    autofocus
                >
            </div>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-login btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
                </button>
            </div>
        </form>

        <p class="text-center text-muted mt-3 mb-0" style="font-size:0.8rem;">
            &copy; 2026 GETECH EXPRESS – Cotonou, Bénin
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
