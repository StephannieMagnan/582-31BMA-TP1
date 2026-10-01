<?php
/**
 * Formulaire d'ajout et de modification d'un auteur.
 */
require_once 'includes/init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$auteur = $id !== null ? Auteur::trouverParId($id) : new Auteur();

if ($auteur === null) {
    ajouterMessage('erreur', 'Cet auteur n\'existe pas ou a été supprimé.');
    rediriger('auteurs.php');
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!jetonCsrfValide()) {
        $erreurs['general'] = 'Le formulaire a expiré. Envoyez-le de nouveau.';
    } else {
        $estAjout = $auteur->estNouveau();
        $auteur->hydrater($_POST);
        $erreurs = $auteur->valider();

        if (empty($erreurs)) {
            $auteur->sauvegarder();
            ajouterMessage('succes', $auteur->getNomComplet() . ' a été ' . ($estAjout ? 'ajouté.' : 'modifié.'));
            rediriger('auteurs.php');
        }
    }
}

$titrePage = $auteur->estNouveau() ? 'Ajouter un auteur' : 'Modifier ' . $auteur->getNomComplet();
$pageActive = 'auteurs.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1><?= $auteur->estNouveau() ? 'Ajouter un auteur' : 'Modifier l\'auteur' ?></h1>
</div>

<?php if (isset($erreurs['general'])) : ?>
    <p class="message message--erreur"><?= e($erreurs['general']) ?></p>
<?php endif; ?>

<form class="formulaire formulaire--etroit" method="post" novalidate>
    <?= champCsrf() ?>

    <div class="rangee">
        <div class="champ">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" maxlength="80" required
                   value="<?= e($auteur->getPrenom()) ?>"<?= attributsErreur($erreurs, 'prenom') ?>>
            <?= erreurChamp($erreurs, 'prenom') ?>
        </div>

        <div class="champ">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" maxlength="80" required
                   value="<?= e($auteur->getNom()) ?>"<?= attributsErreur($erreurs, 'nom') ?>>
            <?= erreurChamp($erreurs, 'nom') ?>
        </div>
    </div>

    <div class="champ champ--court">
        <label for="annee_naissance">Année de naissance <span class="facultatif">(facultatif)</span></label>
        <input type="number" id="annee_naissance" name="annee_naissance" min="1000" max="<?= date('Y') ?>"
               value="<?= e($auteur->getAnneeNaissance()) ?>"<?= attributsErreur($erreurs, 'annee_naissance') ?>>
        <?= erreurChamp($erreurs, 'annee_naissance') ?>
    </div>

    <div class="champ">
        <label for="biographie">Biographie <span class="facultatif">(facultatif)</span></label>
        <textarea id="biographie" name="biographie" rows="4"><?= e($auteur->getBiographie()) ?></textarea>
    </div>

    <div class="formulaire__actions">
        <button type="submit" class="bouton"><?= $auteur->estNouveau() ? 'Ajouter l\'auteur' : 'Enregistrer les modifications' ?></button>
        <a href="auteurs.php">Annuler</a>
    </div>
</form>

<?php require 'includes/pied.php'; ?>
