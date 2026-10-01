<?php
/**
 * Formulaire d'ajout et de modification d'une catégorie.
 */
require_once 'includes/init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$categorie = $id !== null ? Categorie::trouverParId($id) : new Categorie();

if ($categorie === null) {
    ajouterMessage('erreur', 'Cette catégorie n\'existe pas ou a été supprimée.');
    rediriger('categories.php');
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!jetonCsrfValide()) {
        $erreurs['general'] = 'Le formulaire a expiré. Envoyez-le de nouveau.';
    } else {
        $estAjout = $categorie->estNouveau();
        $categorie->hydrater($_POST);
        $erreurs = $categorie->valider();

        if (empty($erreurs)) {
            $categorie->sauvegarder();
            ajouterMessage('succes', 'La catégorie ' . $categorie->getNom() . ' a été ' . ($estAjout ? 'ajoutée.' : 'modifiée.'));
            rediriger('categories.php');
        }
    }
}

$titrePage = $categorie->estNouveau() ? 'Ajouter une catégorie' : 'Modifier ' . $categorie->getNom();
$pageActive = 'categories.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1><?= $categorie->estNouveau() ? 'Ajouter une catégorie' : 'Modifier la catégorie' ?></h1>
</div>

<?php if (isset($erreurs['general'])) : ?>
    <p class="message message--erreur"><?= e($erreurs['general']) ?></p>
<?php endif; ?>

<form class="formulaire formulaire--etroit" method="post" novalidate>
    <?= champCsrf() ?>

    <div class="champ">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" maxlength="60" required
               value="<?= e($categorie->getNom()) ?>"<?= attributsErreur($erreurs, 'nom') ?>>
        <?= erreurChamp($erreurs, 'nom') ?>
    </div>

    <div class="champ">
        <label for="description">Description <span class="facultatif">(facultatif)</span></label>
        <textarea id="description" name="description" rows="3" maxlength="255"<?= attributsErreur($erreurs, 'description') ?>><?= e($categorie->getDescription() ?? '') ?></textarea>
        <?= erreurChamp($erreurs, 'description') ?>
    </div>

    <div class="formulaire__actions">
        <button type="submit" class="bouton"><?= $categorie->estNouveau() ? 'Ajouter la catégorie' : 'Enregistrer les modifications' ?></button>
        <a href="categories.php">Annuler</a>
    </div>
</form>

<?php require 'includes/pied.php'; ?>
