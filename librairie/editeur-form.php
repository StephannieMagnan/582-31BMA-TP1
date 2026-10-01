<?php
/**
 * Formulaire d'ajout et de modification d'un éditeur.
 */
require_once 'includes/init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editeur = $id !== null ? Editeur::trouverParId($id) : new Editeur();

if ($editeur === null) {
    ajouterMessage('erreur', 'Cet éditeur n\'existe pas ou a été supprimé.');
    rediriger('editeurs.php');
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!jetonCsrfValide()) {
        $erreurs['general'] = 'Le formulaire a expiré. Envoyez-le de nouveau.';
    } else {
        $estAjout = $editeur->estNouveau();
        $editeur->hydrater($_POST);
        $erreurs = $editeur->valider();

        if (empty($erreurs)) {
            $editeur->sauvegarder();
            ajouterMessage('succes', $editeur->getNom() . ' a été ' . ($estAjout ? 'ajouté.' : 'modifié.'));
            rediriger('editeurs.php');
        }
    }
}

$titrePage = $editeur->estNouveau() ? 'Ajouter un éditeur' : 'Modifier ' . $editeur->getNom();
$pageActive = 'editeurs.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1><?= $editeur->estNouveau() ? 'Ajouter un éditeur' : 'Modifier l\'éditeur' ?></h1>
</div>

<?php if (isset($erreurs['general'])) : ?>
    <p class="message message--erreur"><?= e($erreurs['general']) ?></p>
<?php endif; ?>

<form class="formulaire formulaire--etroit" method="post" novalidate>
    <?= champCsrf() ?>

    <div class="champ">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" maxlength="100" required
               value="<?= e($editeur->getNom()) ?>"<?= attributsErreur($erreurs, 'nom') ?>>
        <?= erreurChamp($erreurs, 'nom') ?>
    </div>

    <div class="champ">
        <label for="ville">Ville <span class="facultatif">(facultatif)</span></label>
        <input type="text" id="ville" name="ville" maxlength="80"
               value="<?= e($editeur->getVille() ?? '') ?>"<?= attributsErreur($erreurs, 'ville') ?>>
        <?= erreurChamp($erreurs, 'ville') ?>
    </div>

    <div class="champ">
        <label for="site_web">Site Web <span class="facultatif">(facultatif)</span></label>
        <input type="url" id="site_web" name="site_web" maxlength="255" placeholder="https://"
               value="<?= e($editeur->getSiteWeb() ?? '') ?>"<?= attributsErreur($erreurs, 'site_web') ?>>
        <?= erreurChamp($erreurs, 'site_web') ?>
    </div>

    <div class="formulaire__actions">
        <button type="submit" class="bouton"><?= $editeur->estNouveau() ? 'Ajouter l\'éditeur' : 'Enregistrer les modifications' ?></button>
        <a href="editeurs.php">Annuler</a>
    </div>
</form>

<?php require 'includes/pied.php'; ?>
