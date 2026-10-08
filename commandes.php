<?php
// ============================================================
// FICHIER : commandes.php
// Rôle     : Liste de toutes les commandes avec leur statut
//            et la possibilité de modifier le statut
// ============================================================

session_start();
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Messages flash passés en GET
$msg = $_GET['msg'] ?? '';

// Filtre optionnel par statut
$filtre = $_GET['statut'] ?? '';
$statuts_valides = ['en préparation', 'en cours de livraison', 'livré'];

// Construction de la requête avec filtre éventuel
if ($filtre && in_array($filtre, $statuts_valides)) {
    $stmt = $pdo->prepare("
        SELECT c.*, l.nomcomplet AS livreur_nom
        FROM commande c
        LEFT JOIN livreur l ON c.livreur_id = l.id
        WHERE c.statut = ?
        ORDER BY c.id DESC
    ");
    $stmt->execute([$filtre]);
} else {
    $stmt = $pdo->query("
        SELECT c.*, l.nomcomplet AS livreur_nom
        FROM commande c
        LEFT JOIN livreur l ON c.livreur_id = l.id
        ORDER BY c.id DESC
    ");
}
$commandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GETECH EXPRESS – Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .page-title { font-weight: 700; color: #1a1a2e; }
        .badge-prep  { background-color: #ffc107; color:#000; }
        .badge-cours { background-color: #0d6efd; color:#fff; }
        .badge-livre { background-color: #198754; color:#fff; }
        .filter-btn.active-filter { font-weight: 700; }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>

<div class="container-fluid py-4 px-4">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="page-title mb-0">
            <i class="bi bi-box-seam-fill me-2 text-danger"></i> Gestion des Commandes
        </h2>
        <a href="ajouter_commande.php" class="btn btn-danger">
            <i class="bi bi-plus-circle-fill me-1"></i> Nouvelle commande
        </a>
    </div>

    <!-- Messages flash -->
    <?php if ($msg === 'ajoute'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i> Commande créée avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($msg === 'modifie'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-pencil-fill me-2"></i> Statut mis à jour avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtres par statut -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="commandes.php"
           class="btn btn-sm <?= !$filtre ? 'btn-dark' : 'btn-outline-dark' ?>">
            Toutes
        </a>
        <a href="commandes.php?statut=en+pr%C3%A9paration"
           class="btn btn-sm <?= $filtre === 'en préparation' ? 'btn-warning' : 'btn-outline-warning' ?>">
            <i class="bi bi-hourglass-split me-1"></i> En préparation
        </a>
        <a href="commandes.php?statut=en+cours+de+livraison"
           class="btn btn-sm <?= $filtre === 'en cours de livraison' ? 'btn-primary' : 'btn-outline-primary' ?>">
            <i class="bi bi-truck me-1"></i> En cours de livraison
        </a>
        <a href="commandes.php?statut=livr%C3%A9"
           class="btn btn-sm <?= $filtre === 'livré' ? 'btn-success' : 'btn-outline-success' ?>">
            <i class="bi bi-check-circle-fill me-1"></i> Livré
        </a>
    </div>

    <!-- Tableau des commandes -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Livreur</th>
                            <th>Statut</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($commandes)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Aucune commande <?= $filtre ? 'avec ce statut' : 'enregistrée' ?>.
                                    <?php if (!$filtre): ?>
                                        <a href="ajouter_commande.php">Créez la première !</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($commandes as $cmd): ?>
                                <tr>
                                    <td class="fw-bold text-danger">#<?= $cmd['id'] ?></td>
                                    <td style="max-width:180px;">
                                        <span class="text-truncate d-inline-block" style="max-width:180px;"
                                              title="<?= htmlspecialchars($cmd['description']) ?>">
                                            <?= htmlspecialchars($cmd['description']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($cmd['client']) ?></td>
                                    <td><?= htmlspecialchars($cmd['telclient']) ?></td>
                                    <td><?= htmlspecialchars($cmd['adresseclient']) ?></td>
                                    <td>
                                        <?php if ($cmd['livreur_nom']): ?>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($cmd['livreur_nom']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic small">Non affecté</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge = match($cmd['statut']) {
                                            'en préparation'        => 'badge-prep',
                                            'en cours de livraison' => 'badge-cours',
                                            'livré'                 => 'badge-livre',
                                            default                 => 'bg-secondary text-white',
                                        };
                                        ?>
                                        <span class="badge <?= $badge ?> px-3 py-2">
                                            <?= htmlspecialchars($cmd['statut']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="modifier_statut.php?id=<?= $cmd['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-fill me-1"></i>Modifier
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
            <?= count($commandes) ?> commande(s) affichée(s)
            <?= $filtre ? '— Filtre actif : <strong>' . htmlspecialchars($filtre) . '</strong>' : '' ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
