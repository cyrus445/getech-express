<?php
// ============================================================
// FICHIER : ajouter_livreur.php
// Rôle     : Formulaire d'ajout d'un nouveau livreur
// ============================================================

session_start();
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

$erreurs = [];
$nomcomplet = '';
$telephone  = '';

// Traitement du formulaire (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des données
    $nomcomplet = trim($_POST['nomcomplet'] ?? '');
    $telephone  = trim($_POST['telephone']  ?? '');

    // Validation côté serveur
    if (empty($nomcomplet)) {
        $erreurs[] = 'Le nom complet est obligatoire.';
    } elseif (strlen($nomcomplet) < 3) {
        $erreurs[] = 'Le nom complet doit contenir au moins 3 caractères.';
    }

    if (empty($telephone)) {
        $erreurs[] = 'Le numéro de téléphone est obligatoire.';
    } elseif (!preg_match('/^[0-9\s\+\-]{6,20}$/', $telephone)) {
        $erreurs[] = 'Numéro de téléphone invalide (chiffres uniquement, 6 à 20 caractères).';
    }

    // Si aucune erreur : insertion en base
    if (empty($erreurs)) {
        $stmt = $pdo->prepare('INSERT INTO livreur (nomcomplet, telephone) VALUES (?, ?)');
        $stmt->execute([$nomcomplet, $telephone]);

        // Redirection avec message de succès
        header('Location: livreurs.php?msg=ajoute');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GETECH EXPRESS – Ajouter un livreur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .form-card { max-width: 560px; margin: 0 auto; }
        .form-control:focus { border-color: #e63946; box-shadow: 0 0 0 0.2rem rgba(230,57,70,.2); }
        .btn-save { background: linear-gradient(135deg,#e63946,#f4a261); border:none; color:white; font-weight:600; }
        .btn-save:hover { opacity:0.9; color:white; }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>

<div class="container py-5">
    <div class="form-card">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                <li class="breadcrumb-item"><a href="livreurs.php">Livreurs</a></li>
                <li class="breadcrumb-item active">Ajouter</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-danger text-white rounded-top-3">
                <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Ajouter un nouveau livreur</h5>
            </div>
            <div class="card-body p-4">

                <!-- Affichage des erreurs de validation -->
                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($erreurs as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="ajouter_livreur.php" novalidate>

                    <!-- Champ : Nom complet -->
                    <div class="mb-4">
                        <label for="nomcomplet" class="form-label fw-semibold">
                            <i class="bi bi-person-fill me-1 text-danger"></i> Nom complet <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control form-control-lg"
                            id="nomcomplet"
                            name="nomcomplet"
                            placeholder="Ex : Kofi Mensah"
                            value="<?= htmlspecialchars($nomcomplet) ?>"
                            required
                            maxlength="150"
                        >
                        <div class="form-text">Prénom et nom du livreur.</div>
                    </div>

                    <!-- Champ : Téléphone -->
                    <div class="mb-4">
                        <label for="telephone" class="form-label fw-semibold">
                            <i class="bi bi-telephone-fill me-1 text-danger"></i> Téléphone <span class="text-danger">*</span>
                        </label>
                        <input
                            type="tel"
                            class="form-control form-control-lg"
                            id="telephone"
                            name="telephone"
                            placeholder="Ex : 97000001"
                            value="<?= htmlspecialchars($telephone) ?>"
                            required
                            maxlength="20"
                        >
                        <div class="form-text">Numéro de contact du livreur (sans espace requis).</div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-save btn-lg flex-fill">
                            <i class="bi bi-floppy-fill me-1"></i> Enregistrer le livreur
                        </button>
                        <a href="livreurs.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-circle me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
