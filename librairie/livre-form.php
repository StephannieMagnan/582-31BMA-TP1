<?php
/**
 * Formulaire d'ajout et de modification d'un livre.
 */
require_once 'includes/init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$livre = $id !== null ? Livre::trouverParId($id) : new Livre();

if ($livre === null) {
    ajouterMessage('erreur', 'Ce livre n\'existe pas ou a été supprimé.');
    rediriger('livres.php');
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!jetonCsrfValide()) {
        $erreurs['general'] = 'Le formulaire a expiré. Envoyez-le de nouveau.';
    } else {
        $estAjout = $livre->estNouveau();
        $livre->hydrater($_POST);
        $erreurs = $livre->valider();

        if (empty($erreurs)) {
            $livre->sauvegarder();
            ajouterMessage('succes', '« ' . $livre->getTitre() . ' » a été ' . ($estAjout ? 'ajouté au catalogue.' : 'modifié.'));
            rediriger('livre-fiche.php?id=' . $livre->getId());
        }
    }
}

// Listes pour les clés étrangères et les catégories
$auteurs = Auteur::trouverTous();
$editeurs = Editeur::trouverTous();
$categories = Categorie::trouverTous();

$titrePage = $livre->estNouveau() ? 'Ajouter un livre' : 'Modifier ' . $livre->getTitre();
$pageActive = 'livres.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1><?= $livre->estNouveau() ? 'Ajouter un livre' : 'Modifier le livre' ?></h1>
</div>

<?php if (empty($auteurs)) : ?>
    <div class="vide">
        <p>Un livre doit être lié à un auteur. Ajoutez d'abord un auteur.</p>
        <a class="bouton" href="auteur-form.php">Ajouter un auteur</a>
    </div>
<?php else : ?>

    <?php if (isset($erreurs['general'])) : ?>
        <p class="message message--erreur"><?= e($erreurs['general']) ?></p>
    <?php endif; ?>

    <div class="mise-en-page-formulaire">
        <form class="formulaire" method="post" novalidate>
            <?= champCsrf() ?>

            <div class="champ">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" maxlength="150" required
                       value="<?= e($livre->getTitre()) ?>" data-apercu="titre"<?= attributsErreur($erreurs, 'titre') ?>>
                <?= erreurChamp($erreurs, 'titre') ?>
            </div>

            <div class="rangee">
                <div class="champ">
                    <label for="auteur_id">Auteur</label>
                    <select id="auteur_id" name="auteur_id" required data-apercu="auteur"<?= attributsErreur($erreurs, 'auteur_id') ?>>
                        <option value="">Choisir un auteur</option>
                        <?php foreach ($auteurs as $auteur) : ?>
                            <option value="<?= $auteur->getId() ?>" <?= $auteur->getId() === $livre->getAuteurId() ? 'selected' : '' ?>>
                                <?= e($auteur->getNomComplet()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= erreurChamp($erreurs, 'auteur_id') ?>
                </div>

                <div class="champ">
                    <label for="editeur_id">Éditeur <span class="facultatif">(facultatif)</span></label>
                    <select id="editeur_id" name="editeur_id"<?= attributsErreur($erreurs, 'editeur_id') ?>>
                        <option value="">Aucun éditeur</option>
                        <?php foreach ($editeurs as $editeur) : ?>
                            <option value="<?= $editeur->getId() ?>" <?= $editeur->getId() === $livre->getEditeurId() ? 'selected' : '' ?>>
                                <?= e($editeur->getNom()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= erreurChamp($erreurs, 'editeur_id') ?>
                </div>
            </div>

            <div class="rangee rangee--trois">
                <div class="champ">
                    <label for="annee_publication">Année <span class="facultatif">(facultatif)</span></label>
                    <input type="number" id="annee_publication" name="annee_publication" min="1450" max="<?= date('Y') + 1 ?>"
                           value="<?= e($livre->getAnneePublication()) ?>"<?= attributsErreur($erreurs, 'annee_publication') ?>>
                    <?= erreurChamp($erreurs, 'annee_publication') ?>
                </div>

                <div class="champ">
                    <label for="prix">Prix ($)</label>
                    <input type="number" id="prix" name="prix" min="0" max="9999.99" step="0.01" required
                           value="<?= e(number_format($livre->getPrix(), 2, '.', '')) ?>"<?= attributsErreur($erreurs, 'prix') ?>>
                    <?= erreurChamp($erreurs, 'prix') ?>
                </div>

                <div class="champ">
                    <label for="quantite_stock">En stock</label>
                    <input type="number" id="quantite_stock" name="quantite_stock" min="0" step="1" required
                           value="<?= e($livre->getQuantiteStock()) ?>"<?= attributsErreur($erreurs, 'quantite_stock') ?>>
                    <?= erreurChamp($erreurs, 'quantite_stock') ?>
                </div>
            </div>

            <fieldset class="champ">
                <legend>Catégories</legend>
                <?php if (empty($categories)) : ?>
                    <p class="texte-discret">Aucune catégorie. <a href="categorie-form.php">Ajouter une catégorie</a></p>
                <?php else : ?>
                    <div class="cases">
                        <?php foreach ($categories as $categorie) : ?>
                            <label class="case">
                                <input type="checkbox" name="categorie_ids[]" value="<?= $categorie->getId() ?>"
                                    <?= in_array($categorie->getId(), $livre->getCategorieIds(), true) ? 'checked' : '' ?>>
                                <?= e($categorie->getNom()) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </fieldset>

            <div class="champ">
                <label for="resume">Résumé <span class="facultatif">(facultatif)</span></label>
                <textarea id="resume" name="resume" rows="5"><?= e($livre->getResume()) ?></textarea>
            </div>

            <div class="formulaire__actions">
                <button type="submit" class="bouton"><?= $livre->estNouveau() ? 'Ajouter le livre' : 'Enregistrer les modifications' ?></button>
                <a href="<?= $livre->estNouveau() ? 'livres.php' : 'livre-fiche.php?id=' . $livre->getId() ?>">Annuler</a>
            </div>
        </form>

        <aside class="apercu" aria-label="Aperçu de la couverture">
            <?= couvertureHtml($livre, true) ?>
            <p class="texte-discret">Aperçu de la couverture</p>
        </aside>
    </div>
<?php endif; ?>

<?php require 'includes/pied.php'; ?>