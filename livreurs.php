<?php
// ============================================================
// FICHIER : livreurs.php
// Rôle     : Affiche la liste de tous les livreurs enregistrés
// ============================================================

session_start();
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Gestion de la suppression (optionnel bonus)
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $id_del = (int) $_GET['supprimer'];
    // On libère d'abord les commandes liées (la FK est ON DELETE SET NULL)
    $stmt = $pdo->prepare('DELETE FROM livreur WHERE id = ?');
    $stmt->execute([$id_del]);
    header('Location: livreurs.php?msg=supprime');
    exit;
}

// Message flash après suppression
$msg = $_GET['msg'] ?? '';

// Récupération de tous les livreurs avec comptage des commandes associées
$stmt = $pdo->query("
    SELECT l.id, l.nomcomplet, l.telephone,
           COUNT(c.id) AS nb_commandes
    FROM livreur l
    LEFT JOIN commande c ON c.livreur_id = l.id
    GROUP BY l.id
    ORDER BY l.nomcomplet ASC
");
$livreurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GETECH EXPRESS – Liste des livreurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .page-title { font-weight: 700; color: #1a1a2e; }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>

<div class="container-fluid py-4 px-4">

    <!-- En-tête de page -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="page-title mb-0">
            <i class="bi bi-people-fill me-2 text-danger"></i> Gestion des Livreurs
        </h2>
        <a href="ajouter_livreur.php" class="btn btn-danger">
            <i class="bi bi-person-plus-fill me-1"></i> Ajouter un livreur
        </a>
    </div>

    <!-- Message de confirmation -->
    <?php if ($msg === 'ajoute'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Livreur ajouté avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($msg === 'supprime'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-trash-fill me-2"></i> Livreur supprimé avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tableau des livreurs -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col"><i class="bi bi-person-fill me-1"></i> Nom complet</th>
                            <th scope="col"><i class="bi bi-telephone-fill me-1"></i> Téléphone</th>
                            <th scope="col"><i class="bi bi-box-seam me-1"></i> Commandes traitées</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($livreurs)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Aucun livreur enregistré. <a href="ajouter_livreur.php">Ajoutez le premier !</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($livreurs as $i => $liv): ?>
                                <tr>
                                    <td class="text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center"
                                                 style="width:36px;height:36px;font-size:0.9rem;font-weight:700;flex-shrink:0;">
                                                <?= strtoupper(substr($liv['nomcomplet'], 0, 1)) ?>
                                            </div>
                                            <span class="fw-semibold"><?= htmlspecialchars($liv['nomcomplet']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="tel:<?= htmlspecialchars($liv['telephone']) ?>" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($liv['telephone']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill"><?= $liv['nb_commandes'] ?> commande(s)</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="livreurs.php?supprimer=<?= $liv['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer ce livreur ?')">
                                            <i class="bi bi-trash3-fill"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small">
            Total : <strong><?= count($livreurs) ?></strong> livreur(s)
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
