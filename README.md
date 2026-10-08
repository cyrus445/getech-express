# 📦 GETECH EXPRESS — Application Web de Gestion des Livraisons

**Épreuve de Pratique Professionnelle — Licence SIL — Session TD 2026**

---

## 🗂️ Table des matières

1. [Vue d'ensemble du projet](#1-vue-densemble-du-projet)
2. [Arborescence des fichiers](#2-arborescence-des-fichiers)
3. [Installation et configuration](#3-installation-et-configuration)
4. [Explication détaillée de chaque fichier](#4-explication-détaillée-de-chaque-fichier)
   - [database.sql](#41-databasesql)
   - [config.php](#42-configphp)
   - [index.php](#43-indexphp)
   - [header.php](#44-headerphp)
   - [dashboard.php](#45-dashboardphp)
   - [livreurs.php](#46-livreursphp)
   - [ajouter_livreur.php](#47-ajouter_livreurphp)
   - [commandes.php](#48-commandesphp)
   - [ajouter_commande.php](#49-ajouter_commandephp)
   - [modifier_statut.php](#410-modifier_statutphp)
   - [logout.php](#411-logoutphp)
5. [Flux de navigation](#5-flux-de-navigation)
6. [Sécurité implémentée](#6-sécurité-implémentée)
7. [Technologies utilisées](#7-technologies-utilisées)

---

## 1. Vue d'ensemble du projet

GETECH EXPRESS est une application web destinée à digitaliser la gestion des livraisons de colis à domicile au Bénin. L'application permet de :

- **Authentifier** un utilisateur par mot de passe simple
- **Gérer les livreurs** : ajouter, afficher, supprimer
- **Gérer les commandes** : créer, associer un livreur, définir un statut
- **Suivre les livraisons** : filtrer par statut, modifier le statut en temps réel

L'application suit une architecture **MVC légère** en PHP procédural : chaque fichier PHP joue à la fois le rôle de contrôleur (traitement) et de vue (affichage HTML).

---

## 2. Arborescence des fichiers

```
getech_express/
│
├── database.sql          ← Script SQL (création BDD + tables + données test)
├── config.php            ← Connexion PDO à MySQL
├── index.php             ← Page de connexion (authentification)
├── header.php            ← Barre de navigation commune (incluse dans toutes les pages)
├── dashboard.php         ← Tableau de bord (statistiques + dernières commandes)
├── livreurs.php          ← Liste des livreurs
├── ajouter_livreur.php   ← Formulaire d'ajout d'un livreur
├── commandes.php         ← Liste des commandes (avec filtres par statut)
├── ajouter_commande.php  ← Formulaire de création d'une commande
├── modifier_statut.php   ← Modification du statut + livreur d'une commande
└── logout.php            ← Déconnexion (destruction de session)
```

---

## 3. Installation et configuration

### Prérequis

- Serveur PHP >= 7.4 (WAMP, XAMPP, MAMP ou serveur Linux)
- MySQL >= 5.7 ou MariaDB
- Navigateur web moderne

### Étapes

1. **Copier le dossier** `getech_express/` dans le répertoire web :
   - WAMP/XAMPP : `C:/wamp/www/` ou `C:/xampp/htdocs/`
   - Linux : `/var/www/html/`

2. **Créer la base de données** : Ouvrir phpMyAdmin (ou un client MySQL) et importer le fichier `database.sql`. Cela crée :
   - La base `getech_express`
   - Les tables `livreur` et `commande`
   - Quelques données de test

3. **Configurer la connexion** dans `config.php` :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');   // votre utilisateur MySQL
   define('DB_PASS', '');       // votre mot de passe MySQL
   define('DB_NAME', 'getech_express');
   ```

4. **Accéder à l'application** : `http://localhost/getech_express/`

5. **Se connecter** avec le mot de passe : `getech`

---

## 4. Explication détaillée de chaque fichier

---

### 4.1 `database.sql`

**Rôle** : Définit toute la structure de la base de données.

#### Ce que contient ce fichier :

**Création de la base de données :**
```sql
CREATE DATABASE IF NOT EXISTS getech_express CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
On utilise `utf8mb4` (et non `utf8`) car ce jeu de caractères supporte tous les caractères Unicode, y compris les emojis et les caractères accentués français comme `é`, `è`, `ê`.

**Table `livreur` :**
```sql
CREATE TABLE livreur (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nomcomplet VARCHAR(150) NOT NULL,
    telephone  VARCHAR(20)  NOT NULL
);
```
- `id` : clé primaire auto-incrémentée, générée automatiquement par MySQL à chaque insertion.
- `nomcomplet` : VARCHAR(150) car un nom de personne ne dépasse jamais 150 caractères en pratique.
- `telephone` : VARCHAR (et non INT) car un numéro peut commencer par 0 ou contenir des espaces/tirets.

**Table `commande` :**
```sql
CREATE TABLE commande (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    description   TEXT        NOT NULL,
    client        VARCHAR(150) NOT NULL,
    telclient     VARCHAR(20) NOT NULL,
    adresseclient VARCHAR(255) NOT NULL,
    statut        ENUM('en préparation', 'en cours de livraison', 'livré') DEFAULT 'en préparation',
    livreur_id    INT DEFAULT NULL,
    FOREIGN KEY (livreur_id) REFERENCES livreur(id) ON DELETE SET NULL
);
```
- `description` : TEXT car la description peut être longue (plus de 255 caractères).
- `statut` : ENUM garantit que seules les 3 valeurs autorisées peuvent être stockées. Cela évite les erreurs de saisie.
- `livreur_id` : clé étrangère vers la table `livreur`. La contrainte `ON DELETE SET NULL` signifie que si un livreur est supprimé, les commandes qui lui étaient associées ne sont pas supprimées — leur `livreur_id` passe simplement à NULL.

---

### 4.2 `config.php`

**Rôle** : Établit la connexion à MySQL via PDO et fournit l'objet `$pdo` utilisé par tous les autres fichiers.

#### Pourquoi PDO et non `mysqli` ?

PDO (PHP Data Objects) est une abstraction de base de données qui :
- Supporte plusieurs SGBD (MySQL, PostgreSQL, SQLite…)
- Offre les **requêtes préparées** nativement, protégeant contre les injections SQL
- Gère les exceptions de façon propre avec `try/catch`

#### Configuration de PDO :
```php
$pdo = new PDO(
    'mysql:host=localhost;dbname=getech_express;charset=utf8mb4',
    DB_USER, DB_PASS,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lance des exceptions si erreur SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Retourne des tableaux associatifs
        PDO::ATTR_EMULATE_PREPARES   => false,                    // Vraies requêtes préparées MySQL
    ]
);
```

#### Comment ce fichier est utilisé :
Chaque page PHP fonctionnelle commence par :
```php
require_once 'config.php';
```
Cela donne accès à la variable `$pdo` dans toute la page.

---

### 4.3 `index.php`

**Rôle** : Page d'accueil = page de connexion. C'est le **point d'entrée** de l'application.

#### Fonctionnement en 3 étapes :

**Étape 1 — Démarrage de session et vérification :**
```php
session_start();
if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true) {
    header('Location: dashboard.php');
    exit;
}
```
Si l'utilisateur est déjà connecté (session active), on le redirige directement vers le tableau de bord. Il n'a pas besoin de se reconnecter.

**Étape 2 — Vérification du mot de passe (POST) :**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mdp_saisi = trim($_POST['password'] ?? '');
    if ($mdp_saisi === 'getech') {
        $_SESSION['connecte'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $erreur = 'Mot de passe incorrect.';
    }
}
```
- `trim()` supprime les espaces inutiles avant/après la saisie.
- `$_POST['password'] ?? ''` : l'opérateur `??` évite une erreur si la clé n'existe pas.
- En cas de succès, on stocke `$_SESSION['connecte'] = true` et on redirige.

**Étape 3 — Affichage du formulaire :**
Le formulaire HTML est affiché. En cas d'erreur, un message d'alerte Bootstrap s'affiche en rouge au-dessus du formulaire.

#### Design :
- Fond en dégradé sombre (effet professionnel)
- Carte centrée avec ombre portée
- Logo circulaire avec icône de camion Bootstrap Icons

---

### 4.4 `header.php`

**Rôle** : Barre de navigation réutilisable, incluse dans toutes les pages protégées via `require_once 'header.php'`.

#### Pourquoi factoriser la navigation ?

Sans ce fichier, il faudrait copier-coller le code HTML de la navbar dans chaque page. Si on veut modifier un lien, on devrait modifier 6 fichiers. Avec `header.php`, on ne modifie qu'un seul endroit.

#### Mise en évidence du lien actif :

```php
class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
```

`basename($_SERVER['PHP_SELF'])` retourne le nom du fichier PHP en cours d'exécution (ex: `dashboard.php`). On compare ce nom avec chaque lien de la navbar pour ajouter la classe `active` qui souligne le lien courant.

#### Navigation incluse :

| Lien | Page cible |
|------|-----------|
| 🏠 Tableau de bord | `dashboard.php` |
| 👥 Livreurs | `livreurs.php` |
| 📦 Commandes | `commandes.php` |
| 🔓 Déconnexion | `logout.php` |

---

### 4.5 `dashboard.php`

**Rôle** : Tableau de bord principal. Première page visible après la connexion.

#### Protection de page (pattern répété dans tous les fichiers protégés) :
```php
session_start();
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: index.php');
    exit;
}
```
Ce bloc de 4 lignes est **le garde-fou de sécurité**. Si quelqu'un essaie d'accéder à `dashboard.php` directement dans l'URL sans être connecté, il est immédiatement redirigé vers la page de connexion.

#### Requêtes SQL utilisées :

**Comptage des livreurs :**
```sql
SELECT COUNT(*) AS total FROM livreur
```

**Commandes par statut :**
```sql
SELECT statut, COUNT(*) AS total FROM commande GROUP BY statut
```
`GROUP BY statut` regroupe les lignes ayant le même statut et `COUNT(*)` compte combien il y en a dans chaque groupe.

**5 dernières commandes :**
```sql
SELECT c.id, c.client, c.statut, l.nomcomplet AS livreur
FROM commande c
LEFT JOIN livreur l ON c.livreur_id = l.id
ORDER BY c.id DESC LIMIT 5
```
`LEFT JOIN` récupère le nom du livreur même si `livreur_id` est NULL (commande non affectée). `ORDER BY c.id DESC` trie du plus récent au plus ancien.

#### Affichage :
- 5 cartes de statistiques (livreurs, commandes totales, par statut)
- Boutons d'actions rapides
- Tableau des 5 dernières commandes avec badges colorés

---

### 4.6 `livreurs.php`

**Rôle** : Affiche la liste complète des livreurs avec le nombre de commandes associées à chacun.

#### Requête SQL principale :
```sql
SELECT l.id, l.nomcomplet, l.telephone, COUNT(c.id) AS nb_commandes
FROM livreur l
LEFT JOIN commande c ON c.livreur_id = l.id
GROUP BY l.id
ORDER BY l.nomcomplet ASC
```
Cette requête combine les tables `livreur` et `commande` pour afficher en une seule fois le livreur ET combien de commandes il a traitées. `GROUP BY l.id` est obligatoire quand on utilise `COUNT()` avec un JOIN.

#### Fonctionnalité de suppression :
```php
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $id_del = (int) $_GET['supprimer'];
    $stmt = $pdo->prepare('DELETE FROM livreur WHERE id = ?');
    $stmt->execute([$id_del]);
    header('Location: livreurs.php?msg=supprime');
}
```
On vérifie `is_numeric()` avant d'utiliser l'ID pour éviter toute injection. La suppression déclenche automatiquement `ON DELETE SET NULL` sur les commandes liées (défini dans la BDD).

#### Messages flash :
Le paramètre `?msg=ajoute` ou `?msg=supprime` dans l'URL est utilisé pour afficher une alerte Bootstrap après une action. C'est la technique du **PRG (Post/Redirect/Get)** qui évite la double soumission de formulaire.

---

### 4.7 `ajouter_livreur.php`

**Rôle** : Formulaire HTML + traitement PHP pour ajouter un nouveau livreur.

#### Validation côté serveur :

```php
if (empty($nomcomplet)) {
    $erreurs[] = 'Le nom complet est obligatoire.';
} elseif (strlen($nomcomplet) < 3) {
    $erreurs[] = 'Le nom complet doit contenir au moins 3 caractères.';
}

if (!preg_match('/^[0-9\s\+\-]{6,20}$/', $telephone)) {
    $erreurs[] = 'Numéro de téléphone invalide.';
}
```

**Pourquoi valider côté serveur ET non seulement côté client ?**
La validation HTML5 (`required`, `minlength`) peut être contournée facilement (en désactivant JavaScript ou en envoyant une requête HTTP directe). La validation PHP côté serveur est donc indispensable.

#### Insertion sécurisée :
```php
$stmt = $pdo->prepare('INSERT INTO livreur (nomcomplet, telephone) VALUES (?, ?)');
$stmt->execute([$nomcomplet, $telephone]);
```
Les `?` sont des **marqueurs de position**. PDO remplace automatiquement chaque `?` par la valeur correspondante, en l'**échappant** pour prévenir les injections SQL. C'est la bonne pratique fondamentale.

#### Réaffichage des valeurs après erreur :
```html
value="<?= htmlspecialchars($form['nomcomplet']) ?>"
```
`htmlspecialchars()` convertit les caractères spéciaux HTML (`<`, `>`, `"`, `&`) en entités HTML pour éviter les failles XSS (Cross-Site Scripting).

---

### 4.8 `commandes.php`

**Rôle** : Affiche toutes les commandes avec possibilité de filtrer par statut.

#### Filtre dynamique par statut :

```php
$filtre = $_GET['statut'] ?? '';
if ($filtre && in_array($filtre, $statuts_valides)) {
    $stmt = $pdo->prepare("SELECT ... WHERE c.statut = ? ORDER BY c.id DESC");
    $stmt->execute([$filtre]);
} else {
    $stmt = $pdo->query("SELECT ... ORDER BY c.id DESC");
}
```

On valide que le filtre fait partie des valeurs autorisées (`in_array`) avant de l'utiliser en base de données. Cela évite d'accepter n'importe quelle valeur dans la requête SQL.

#### Boutons de filtre dynamiques :
```php
class="btn btn-sm <?= $filtre === 'en préparation' ? 'btn-warning' : 'btn-outline-warning' ?>"
```
Le bouton du filtre actif est rempli (plein), les autres sont vides (outline). C'est un retour visuel important pour l'utilisateur.

#### Affichage des badges de statut :
```php
$badge = match($cmd['statut']) {
    'en préparation'        => 'badge-prep',
    'en cours de livraison' => 'badge-cours',
    'livré'                 => 'badge-livre',
    default                 => 'bg-secondary text-white',
};
```
`match()` est une syntaxe PHP 8+ plus concise et stricte que `switch`. Chaque statut a sa propre couleur (jaune, bleu, vert).

---

### 4.9 `ajouter_commande.php`

**Rôle** : Formulaire de création d'une commande avec association d'un livreur.

#### Chargement des livreurs pour la liste déroulante :
```php
$stmt_liv = $pdo->query('SELECT id, nomcomplet FROM livreur ORDER BY nomcomplet ASC');
$livreurs = $stmt_liv->fetchAll();
```
Ces livreurs sont ensuite affichés dans un `<select>` :
```html
<select name="livreur_id">
    <option value="">-- Aucun --</option>
    <?php foreach ($livreurs as $liv): ?>
        <option value="<?= $liv['id'] ?>"><?= htmlspecialchars($liv['nomcomplet']) ?></option>
    <?php endforeach; ?>
</select>
```

#### Gestion du livreur NULL :
```php
$livreur_id = !empty($form['livreur_id']) && is_numeric($form['livreur_id'])
              ? (int) $form['livreur_id']
              : null;
```
Si l'utilisateur ne choisit pas de livreur, `livreur_id` vaut `null` en PHP, ce qui sera stocké comme `NULL` en SQL. La commande existe mais n'est pas encore affectée.

#### Insertion :
```php
$stmt = $pdo->prepare("INSERT INTO commande (description, client, telclient, adresseclient, statut, livreur_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$description, $client, $telclient, $adresseclient, $statut, $livreur_id]);
```
6 paramètres, 6 `?`. PDO les gère tous de façon sécurisée.

---

### 4.10 `modifier_statut.php`

**Rôle** : Permet de modifier le statut d'une commande existante et de réaffecter un livreur.

#### Récupération sécurisée de l'ID :
```php
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: commandes.php');
    exit;
}
```
On **caste** la valeur en `(int)` pour être sûr de travailler avec un entier. Si l'ID est invalide ou absent, on redirige.

#### Chargement de la commande existante :
```php
$stmt = $pdo->prepare("SELECT c.*, l.nomcomplet AS livreur_nom FROM commande c LEFT JOIN livreur l ON c.livreur_id = l.id WHERE c.id = ?");
$stmt->execute([$id]);
$commande = $stmt->fetch();
if (!$commande) {
    header('Location: commandes.php');
    exit;
}
```
Si la commande n'existe pas en base (ID inexistant ou URL manipulée), on redirige. C'est une défense contre la manipulation d'URL.

#### Requête UPDATE :
```php
$stmt = $pdo->prepare("UPDATE commande SET statut = ?, livreur_id = ? WHERE id = ?");
$stmt->execute([$nouveau_statut, $livreur_id, $id]);
```
`UPDATE` modifie uniquement les champs `statut` et `livreur_id`. Les autres informations (client, adresse, description) restent inchangées.

#### Affichage de la fiche commande :
Avant le formulaire de modification, une carte Bootstrap affiche en lecture seule toutes les informations de la commande (client, adresse, description). L'utilisateur peut ainsi vérifier qu'il modifie la bonne commande.

---

### 4.11 `logout.php`

**Rôle** : Déconnecte l'utilisateur proprement et le redirige vers la page de connexion.

#### Processus de déconnexion complet :
```php
session_start();        // 1. On ouvre la session pour y accéder
$_SESSION = [];         // 2. On efface toutes les variables de session
// 3. On supprime le cookie de session du navigateur
setcookie(session_name(), '', time() - 42000, ...);
session_destroy();      // 4. On détruit la session côté serveur
header('Location: index.php');  // 5. On redirige
```

Ces 4 étapes sont importantes :
- Étape 2 seule ne suffit pas (la session existe encore en mémoire)
- Étape 3 supprime le cookie `PHPSESSID` du navigateur
- Étape 4 supprime le fichier de session sur le serveur
- Sans toutes ces étapes, la session pourrait être réutilisée

---

## 5. Flux de navigation

```
[Utilisateur] 
    │
    ▼
index.php ──(mot de passe incorrect)──► message d'erreur (reste sur index.php)
    │
    (mot de passe : getech)
    │
    ▼
dashboard.php ◄──────────────────────────────────────┐
    │                                                  │
    ├──► livreurs.php ──► ajouter_livreur.php ─────────┤
    │                                                  │
    ├──► commandes.php ──► ajouter_commande.php ───────┤
    │         │                                        │
    │         └──► modifier_statut.php ────────────────┘
    │
    └──► logout.php ──► index.php
```

---

## 6. Sécurité implémentée

| Menace | Contre-mesure |
|--------|--------------|
| Accès non autorisé | Vérification `$_SESSION['connecte']` en haut de chaque page protégée |
| Injection SQL | Requêtes préparées PDO avec marqueurs `?` partout |
| XSS (Cross-Site Scripting) | `htmlspecialchars()` sur toutes les données affichées |
| Manipulation d'URL | Vérification `is_numeric()` + `(int)` cast sur tous les IDs |
| Double soumission de formulaire | Pattern PRG (Post/Redirect/Get) après chaque action |
| Session résiduelle | Déconnexion complète en 4 étapes dans `logout.php` |

---

## 7. Technologies utilisées

| Technologie | Version | Usage |
|-------------|---------|-------|
| PHP | 7.4+ | Traitement serveur, sessions, requêtes SQL |
| MySQL | 5.7+ | Stockage des données |
| PDO | (intégré PHP) | Connexion sécurisée à MySQL |
| HTML5 | — | Structure des pages |
| CSS3 | — | Styles personnalisés |
| Bootstrap | 5.3 | Mise en forme responsive, composants UI |
| Bootstrap Icons | 1.10.5 | Icônes vectorielles |

---

*Application développée dans le cadre de l'Épreuve de Pratique Professionnelle — Licence SIL — Session TD 2026*
*GETECH EXPRESS — Cotonou, Bénin*
