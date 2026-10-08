<?php
// ============================================================
// FICHIER : modifier_statut.php
// Rôle     : Modifier le statut d'une commande et son livreur
// ============================================================

session_start();
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Récupération de l'ID depuis l'URL
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: commandes.php');
    exit;
}

// Chargement de la commande existante
$stmt = $pdo->prepare("
    SELECT c.*, l.nomcomplet AS livreur_nom
    FROM commande c
    LEFT JOIN livreur l ON c.livreur_id = l.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$commande = $stmt->fetch();

// Si la commande n'existe pas
if (!$commande) {
    header('Location: commandes.php');
    exit;
}

// Chargement des livreurs pour la liste déroulante
$stmt_liv = $pdo->query('SELECT id, nomcomplet FROM livreur ORDER BY nomcomplet ASC');
$livreurs = $stmt_liv->fetchAll();

$erreurs = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nouveau_statut = trim($_POST['statut']     ?? '');
    $nouveau_liv_id = trim($_POST['livreur_id'] ?? '');

    $statuts_valides = ['en préparation', 'en cours de livraison', 'livré'];

    if (!in_array($nouveau_statut, $statuts_valides)) {
        $erreurs[] = 'Statut invalide. Choisissez parmi les options proposées.';
    }

    $livreur_id = !empty($nouveau_liv_id) && is_numeric($nouveau_liv_id)
                  ? (int) $nouveau_liv_id
                  : null;

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("
            UPDATE commande
            SET statut = ?, livreur_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$nouveau_statut, $livreur_id, $id]);

        header('Location: commandes.php?msg=modifie');
        exit;
    }
}

// Badges d'affichage du statut actuel
$badge_map = [
    'en préparation'        => ['class' => 'bg-warning text-dark', 'icon' => 'bi-hourglass-split'],
    'en cours de livraison' => ['class' => 'bg-primary text-white', 'icon' => 'bi-truck'],
    'livré'                 => ['class' => 'bg-success text-white', 'icon' => 'bi-check-circle-fill'],
];
$badge = $badge_map[$commande['statut']] ?? ['class' => 'bg-secondary text-white', 'icon' => 'bi-question'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GETECH EXPRESS – Modifier la commande #<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .form-card { max-width: 620px; margin: 0 auto; }
        .form-control:focus, .form-select:focus {
            border-color: #e63946;
            box-shadow: 0 0 0 0.2rem rgba(230,57,70,.2);
        }
        .btn-save { background: linear-gradient(135deg,#e63946,#f4a261); border:none; color:white; font-weight:600; }
        .btn-save:hover { opacity:0.9; color:white; }
        .info-label { font-size: 0.78rem; text-transform: uppercase; color: #6c757d; font-weight: 700; }
        .info-value { font-size: 0.95rem; font-weight: 500; }
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
                <li class="breadcrumb-item"><a href="commandes.php">Commandes</a></li>
                <li class="breadcrumb-item active">Modifier #<?= $id ?></li>
            </ol>
        </nav>

        <!-- Fiche récapitulatif de la commande -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-text-fill me-2"></i>Commande #<?= $id ?></h5>
                <span class="badge <?= $badge['class'] ?> px-3 py-2">
                    <i class="bi <?= $badge['icon'] ?> me-1"></i><?= htmlspecialchars($commande['statut']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="info-label mb-1">Client</p>
                        <p class="info-value mb-0"><?= htmlspecialchars($commande['client']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="info-label mb-1">Téléphone</p>
                        <p class="info-value mb-0"><?= htmlspecialchars($commande['telclient']) ?></p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">Adresse</p>
                        <p class="info-value mb-0"><?= htmlspecialchars($commande['adresseclient']) ?></p>
                    </div>
                    <div class="col-12">
                        <p class="info-label mb-1">Description du colis</p>
                        <p class="info-value mb-0"><?= htmlspecialchars($commande['description']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-pencil-fill me-2"></i>Mettre à jour le statut</h5>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($erreurs as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="modifier_statut.php?id=<?= $id ?>">

                    <!-- Statut -->
                    <div class="mb-4">
                        <label for="statut" class="form-label fw-semibold">
                            <i class="bi bi-flag me-1 text-danger"></i> Nouveau statut <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg" id="statut" name="statut" required>
                            <option value="en préparation"
                                <?= $commande['statut'] === 'en préparation' ? 'selected' : '' ?>>
                                ⏳ En préparation
                            </option>
                            <option value="en cours de livraison"
                                <?= $commande['statut'] === 'en cours de livraison' ? 'selected' : '' ?>>
                                🚚 En cours de livraison
                            </option>
                            <option value="livré"
                                <?= $commande['statut'] === 'livré' ? 'selected' : '' ?>>
                                ✅ Livré
                            </option>
                        </select>
                    </div>

                    <!-- Livreur -->
                    <div class="mb-4">
                        <label for="livreur_id" class="form-label fw-semibold">
                            <i class="bi bi-person-check me-1 text-danger"></i> Livreur affecté
                        </label>
                        <select class="form-select form-select-lg" id="livreur_id" name="livreur_id">
                            <option value="">-- Aucun livreur --</option>
                            <?php foreach ($livreurs as $liv): ?>
                                <option value="<?= $liv['id'] ?>"
                                    <?= $commande['livreur_id'] == $liv['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($liv['nomcomplet']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($livreurs)): ?>
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Aucun livreur enregistré. <a href="ajouter_livreur.php">Ajoutez-en un.</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-save btn-lg flex-fill">
                            <i class="bi bi-floppy-fill me-1"></i> Enregistrer les modifications
                        </button>
                        <a href="commandes.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript : confirmation visuelle du changement de statut -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mise à jour visuelle en temps réel de l'icône selon le statut sélectionné
    document.getElementById('statut').addEventListener('change', function() {
        const val = this.value;
        const icons = {
            'en préparation':        '⏳',
            'en cours de livraison': '🚚',
            'livré':                 '✅',
        };
        // Mise à jour de l'option sélectionnée avec emoji
        this.options[this.selectedIndex].textContent = (icons[val] || '') + ' ' + val;
    });
</script>
</body>
</html>
