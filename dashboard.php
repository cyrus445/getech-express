<?php
// ============================================================
// FICHIER : dashboard.php
// Rôle     : Tableau de bord principal après connexion
//            Affiche des statistiques globales
// ============================================================

session_start();

// Protection : si non connecté, rediriger vers index.php
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// --- Statistiques globales ---

// Nombre total de livreurs
$stmt = $pdo->query('SELECT COUNT(*) AS total FROM livreur');
$nb_livreurs = $stmt->fetch()['total'];

// Nombre total de commandes
$stmt = $pdo->query('SELECT COUNT(*) AS total FROM commande');
$nb_commandes = $stmt->fetch()['total'];

// Commandes par statut
$stmt = $pdo->query("
    SELECT statut, COUNT(*) AS total
    FROM commande
    GROUP BY statut
");
$stats_statut = $stmt->fetchAll();

// Transformer en tableau associatif pour accès facile
$par_statut = [
    'en préparation'       => 0,
    'en cours de livraison' => 0,
    'livré'                => 0,
];
foreach ($stats_statut as $row) {
    $par_statut[$row['statut']] = $row['total'];
}

// 5 dernières commandes
$stmt = $pdo->query("
    SELECT c.id, c.client, c.statut, c.adresseclient,
           l.nomcomplet AS livreur
    FROM commande c
    LEFT JOIN livreur l ON c.livreur_id = l.id
    ORDER BY c.id DESC
    LIMIT 5
");
$dernieres_commandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GETECH EXPRESS – Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .stat-card { border: none; border-radius: 14px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-icon { font-size: 2.5rem; opacity: 0.85; }
        .badge-statut-prep  { background-color: #ffc107; color: #000; }
        .badge-statut-cours { background-color: #0d6efd; color: #fff; }
        .badge-statut-livre { background-color: #198754; color: #fff; }
        .page-title { font-weight: 700; color: #1a1a2e; }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>

<div class="container-fluid py-4 px-4">
    <h2 class="page-title mb-4">
        <i class="bi bi-speedometer2 me-2 text-danger"></i> Tableau de bord
    </h2>

    <!-- Cartes statistiques -->
    <div class="row g-4 mb-4">
        <!-- Total livreurs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#1a1a2e,#e63946); color:white;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-white-50 small text-uppercase fw-bold">Livreurs</p>
                        <h2 class="mb-0 fw-bold"><?= $nb_livreurs ?></h2>
                    </div>
                    <i class="bi bi-people-fill stat-icon"></i>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="livreurs.php" class="text-white text-decoration-none small">
                        Voir tous <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total commandes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#0f3460,#0d6efd); color:white;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-white-50 small text-uppercase fw-bold">Commandes</p>
                        <h2 class="mb-0 fw-bold"><?= $nb_commandes ?></h2>
                    </div>
                    <i class="bi bi-box-seam-fill stat-icon"></i>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="commandes.php" class="text-white text-decoration-none small">
                        Voir toutes <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- En préparation -->
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card stat-card shadow-sm h-100 border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning stat-icon"></i>
                    <h3 class="fw-bold mt-2"><?= $par_statut['en préparation'] ?></h3>
                    <p class="text-muted small mb-0">En préparation</p>
                </div>
            </div>
        </div>

        <!-- En cours de livraison -->
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card stat-card shadow-sm h-100 border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-truck text-primary stat-icon"></i>
                    <h3 class="fw-bold mt-2"><?= $par_statut['en cours de livraison'] ?></h3>
                    <p class="text-muted small mb-0">En livraison</p>
                </div>
            </div>
        </div>

        <!-- Livré -->
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card stat-card shadow-sm h-100 border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill text-success stat-icon"></i>
                    <h3 class="fw-bold mt-2"><?= $par_statut['livré'] ?></h3>
                    <p class="text-muted small mb-0">Livrées</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Raccourcis d'actions rapides -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h5 class="text-muted fw-semibold">Actions rapides</h5>
        </div>
        <div class="col-auto">
            <a href="ajouter_livreur.php" class="btn btn-outline-danger">
                <i class="bi bi-person-plus-fill me-1"></i> Ajouter un livreur
            </a>
        </div>
        <div class="col-auto">
            <a href="ajouter_commande.php" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle-fill me-1"></i> Nouvelle commande
            </a>
        </div>
        <div class="col-auto">
            <a href="commandes.php" class="btn btn-outline-success">
                <i class="bi bi-list-ul me-1"></i> Suivi des commandes
            </a>
        </div>
    </div>

    <!-- Tableau des dernières commandes -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-0 pt-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-danger"></i>5 dernières commandes</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Client</th>
                            <th>Adresse</th>
                            <th>Livreur affecté</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dernieres_commandes)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Aucune commande enregistrée
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dernieres_commandes as $cmd): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $cmd['id'] ?></td>
                                    <td><?= htmlspecialchars($cmd['client']) ?></td>
                                    <td><?= htmlspecialchars($cmd['adresseclient']) ?></td>
                                    <td>
                                        <?php if ($cmd['livreur']): ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($cmd['livreur']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Non affecté</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge = match($cmd['statut']) {
                                            'en préparation'        => 'badge-statut-prep',
                                            'en cours de livraison' => 'badge-statut-cours',
                                            'livré'                 => 'badge-statut-livre',
                                            default                 => 'bg-secondary',
                                        };
                                        ?>
                                        <span class="badge <?= $badge ?>">
                                            <?= htmlspecialchars($cmd['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="modifier_statut.php?id=<?= $cmd['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-fill"></i> Modifier
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
