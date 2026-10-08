<?php
// ============================================================
// FICHIER : header.php
// Rôle     : Barre de navigation commune à toutes les pages
//            Incluse dans chaque page avec require_once
// ============================================================

// Vérification de session : toute page qui inclut ce header
// doit avoir démarré la session et vérifié l'authentification
?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1a1a2e, #e63946);">
    <div class="container-fluid">
        <!-- Logo + nom de l'entreprise -->
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-truck-front-fill me-2"></i>GETECH EXPRESS
        </a>

        <!-- Bouton responsive (mobile) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Liens de navigation -->
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
                       href="dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['livreurs.php', 'ajouter_livreur.php']) ? 'active' : '' ?>"
                       href="livreurs.php">
                        <i class="bi bi-people-fill me-1"></i> Livreurs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['commandes.php', 'ajouter_commande.php', 'modifier_statut.php']) ? 'active' : '' ?>"
                       href="commandes.php">
                        <i class="bi bi-box-seam-fill me-1"></i> Commandes
                    </a>
                </li>
            </ul>

            <!-- Bouton de déconnexion -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
