<?php
/**
 * Fiche d'un livre : toutes ses informations, avec son auteur, son éditeur et ses catégories.
 */
require_once 'includes/init.php';

$livre = Livre::trouverParId((int) ($_GET['id'] ?? 0));

if ($livre === null) {
    ajouterMessage('erreur', 'Ce livre n\'existe pas ou a été supprimé.');
    rediriger('livres.php');
}

$auteur = $livre->getAuteur();
$editeur = $livre->getEditeur();

$titrePage = $livre->getTitre();
$pageActive = 'livres.php';
require 'includes/entete.php';
?>

<p class="fil"><a href="livres.php">Catalogue</a> / <?= e($livre->getTitre()) ?></p>

<article class="fiche">
    <?= couvertureHtml($livre, true) ?>

    <div class="fiche__details">
        <h1><?= e($livre->getTitre()) ?></h1>
        <p class="fiche__auteur">
            par <a href="livres.php?auteur=<?= $auteur->getId() ?>"><?= e($auteur->getNomComplet()) ?></a>
        </p>

        <?php if (!empty($livre->getCategories())) : ?>
            <ul class="etiquettes" aria-label="Catégories">
                <?php foreach ($livre->getCategories() as $categorie) : ?>
                    <li><a href="livres.php?categorie=<?= $categorie->getId() ?>"><?= e($categorie->getNom()) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($livre->getResume() !== null) : ?>
            <p class="fiche__resume"><?= nl2br(e($livre->getResume())) ?></p>
        <?php endif; ?>

        <dl class="fiche__donnees">
            <div>
                <dt>Prix</dt>
                <dd><?= formatPrix($livre->getPrix()) ?></dd>
            </div>
            <div>
                <dt>En stock</dt>
                <dd>
                    <?= $livre->getQuantiteStock() ?> exemplaire<?= $livre->getQuantiteStock() > 1 ? 's' : '' ?>
                    <?php if ($livre->estEnStockFaible()) : ?>
                        <span class="pastille <?= $livre->getQuantiteStock() === 0 ? 'pastille--rupture' : 'pastille--faible' ?>">
                            <?= $livre->getQuantiteStock() === 0 ? 'Épuisé' : 'Stock faible' ?>
                        </span>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt>Éditeur</dt>
                <dd>
                    <?= $editeur !== null ? e($editeur->getNom()) . ($editeur->getVille() ? ', ' . e($editeur->getVille()) : '') : 'Non précisé' ?>
                </dd>
            </div>
            <div>
                <dt>Publication</dt>
                <dd><?= $livre->getAnneePublication() ?? 'Non précisée' ?></dd>
            </div>
        </dl>

        <?php if ($auteur->getBiographie() !== null) : ?>
            <section class="fiche__bio">
                <h2>À propos de <?= e($auteur->getNomComplet()) ?></h2>
                <p><?= e($auteur->getBiographie()) ?></p>
            </section>
        <?php endif; ?>

        <div class="fiche__actions">
            <a class="bouton" href="livre-form.php?id=<?= $livre->getId() ?>">Modifier le livre</a>
            <?= boutonSupprimer('livre-supprimer.php', $livre->getId(), 'Supprimer « ' . $livre->getTitre() . ' » du catalogue?') ?>
        </div>
    </div>
</article>

<?php require 'includes/pied.php'; ?>
