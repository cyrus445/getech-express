<?php
// ============================================================
// FICHIER : ajouter_commande.php
// Rôle     : Formulaire de création d'une nouvelle commande
//            avec affectation d'un livreur et choix du statut
// ============================================================

session_start();
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Chargement des livreurs disponibles pour la liste déroulante
$stmt_liv = $pdo->query('SELECT id, nomcomplet FROM livreur ORDER BY nomcomplet ASC');
$livreurs = $stmt_liv->fetchAll();

$erreurs    = [];
$form       = [
    'description'   => '',
    'client'        => '',
    'telclient'     => '',
    'adresseclient' => '',
    'statut'        => 'en préparation',
    'livreur_id'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage
    $form['description']   = trim($_POST['description']   ?? '');
    $form['client']        = trim($_POST['client']        ?? '');
    $form['telclient']     = trim($_POST['telclient']     ?? '');
    $form['adresseclient'] = trim($_POST['adresseclient'] ?? '');
    $form['statut']        = trim($_POST['statut']        ?? 'en préparation');
    $form['livreur_id']    = trim($_POST['livreur_id']    ?? '');

    // Validation
    if (empty($form['description'])) {
        $erreurs[] = 'La description du colis est obligatoire.';
    }
    if (empty($form['client'])) {
        $erreurs[] = 'Le nom du client est obligatoire.';
    }
    if (empty($form['telclient'])) {
        $erreurs[] = 'Le téléphone du client est obligatoire.';
    } elseif (!preg_match('/^[0-9\s\+\-]{6,20}$/', $form['telclient'])) {
        $erreurs[] = 'Numéro de téléphone client invalide.';
    }
    if (empty($form['adresseclient'])) {
        $erreurs[] = "L'adresse de livraison est obligatoire.";
    }

    $statuts_valides = ['en préparation', 'en cours de livraison', 'livré'];
    if (!in_array($form['statut'], $statuts_valides)) {
        $erreurs[] = 'Statut invalide.';
    }

    // livreur_id peut être vide (NULL)
    $livreur_id = !empty($form['livreur_id']) && is_numeric($form['livreur_id'])
                  ? (int) $form['livreur_id']
                  : null;

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("
            INSERT INTO commande (description, client, telclient, adresseclient, statut, livreur_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form['description'],
            $form['client'],
            $form['telclient'],
            $form['adresseclient'],
            $form['statut'],
            $livreur_id,
        ]);

        header('Location: commandes.php?msg=ajoute');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GETECH EXPRESS – Nouvelle commande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .form-card { max-width: 680px; margin: 0 auto; }
        .form-control:focus, .form-select:focus {
            border-color: #e63946;
            box-shadow: 0 0 0 0.2rem rgba(230,57,70,.2);
        }
        .btn-save { background: linear-gradient(135deg,#e63946,#f4a261); border:none; color:white; font-weight:600; }
        .btn-save:hover { opacity:0.9; color:white; }
        .section-title { font-size: 0.85rem; text-transform: uppercase; font-weight: 700;
                         color: #6c757d; letter-spacing: 1px; margin-bottom: 1rem; }
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
                <li class="breadcrumb-item active">Nouvelle commande</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-danger text-white rounded-top-3">
                <h5 class="mb-0"><i class="bi bi-plus-circle-fill me-2"></i>Créer une nouvelle commande</h5>
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

                <form method="POST" action="ajouter_commande.php" novalidate>

                    <!-- Section : Informations du colis -->
                    <p class="section-title"><i class="bi bi-box-seam me-1"></i> Informations du colis</p>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea
                            class="form-control"
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Ex : Colis alimentaire 5kg, documents administratifs..."
                            required
                        ><?= htmlspecialchars($form['description']) ?></textarea>
                    </div>

                    <!-- Section : Informations client -->
                    <hr class="my-4">
                    <p class="section-title"><i class="bi bi-person me-1"></i> Informations du client</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="client" class="form-label fw-semibold">
                                Nom du client <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="client" name="client"
                                   placeholder="Ex : Jean Dupont"
                                   value="<?= htmlspecialchars($form['client']) ?>"
                                   required maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label for="telclient" class="form-label fw-semibold">
                                Téléphone <span class="text-danger">*</span>
                            </label>
                            <input type="tel" class="form-control" id="telclient" name="telclient"
                                   placeholder="Ex : 96111111"
                                   value="<?= htmlspecialchars($form['telclient']) ?>"
                                   required maxlength="20">
                        </div>
                        <div class="col-12">
                            <label for="adresseclient" class="form-label fw-semibold">
                                Adresse de livraison <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="adresseclient" name="adresseclient"
                                   placeholder="Ex : Cadjehoun, Cotonou"
                                   value="<?= htmlspecialchars($form['adresseclient']) ?>"
                                   required maxlength="255">
                        </div>
                    </div>

                    <!-- Section : Affectation et statut -->
                    <hr class="my-4">
                    <p class="section-title"><i class="bi bi-gear me-1"></i> Affectation & Statut</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="livreur_id" class="form-label fw-semibold">
                                <i class="bi bi-person-check me-1"></i> Livreur affecté
                            </label>
                            <select class="form-select" id="livreur_id" name="livreur_id">
                                <option value="">-- Aucun (à affecter plus tard) --</option>
                                <?php foreach ($livreurs as $liv): ?>
                                    <option value="<?= $liv['id'] ?>"
                                        <?= $form['livreur_id'] == $liv['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($liv['nomcomplet']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($livreurs)): ?>
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Aucun livreur disponible.
                                    <a href="ajouter_livreur.php">Ajoutez-en un d'abord.</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="statut" class="form-label fw-semibold">
                                <i class="bi bi-flag me-1"></i> Statut initial <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="en préparation"        <?= $form['statut'] === 'en préparation'        ? 'selected' : '' ?>>⏳ En préparation</option>
                                <option value="en cours de livraison" <?= $form['statut'] === 'en cours de livraison' ? 'selected' : '' ?>>🚚 En cours de livraison</option>
                                <option value="livré"                 <?= $form['statut'] === 'livré'                 ? 'selected' : '' ?>>✅ Livré</option>
                            </select>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-save btn-lg flex-fill">
                            <i class="bi bi-floppy-fill me-1"></i> Enregistrer la commande
                        </button>
                        <a href="commandes.php" class="btn btn-outline-secondary btn-lg">
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
