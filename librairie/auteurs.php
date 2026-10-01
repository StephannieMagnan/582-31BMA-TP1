<?php
/**
 * Liste des auteurs, avec le nombre de livres de chacun.
 */
require_once 'includes/init.php';

$auteurs = Auteur::trouverTous();

$titrePage = 'Auteurs';
$pageActive = 'auteurs.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1>Auteurs</h1>
    <a class="bouton" href="auteur-form.php">Ajouter un auteur</a>
</div>

<?php if (empty($auteurs)) : ?>
    <div class="vide">
        <p>Aucun auteur pour l'instant.</p>
        <a class="bouton" href="auteur-form.php">Ajouter un auteur</a>
    </div>
<?php else : ?>
    <div class="tableau-conteneur">
        <table class="tableau">
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col" class="nombre">Naissance</th>
                    <th scope="col" class="nombre">Livres</th>
                    <th scope="col"><span class="visuellement-cache">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($auteurs as $auteur) : ?>
                    <?php $nombreLivres = $auteur->compterLivres(); ?>
                    <tr>
                        <td>
                            <strong><?= e($auteur->getNom()) ?></strong>, <?= e($auteur->getPrenom()) ?>
                            <?php if ($auteur->getBiographie() !== null) : ?>
                                <span class="tableau__note"><?= e($auteur->getBiographie()) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="nombre"><?= $auteur->getAnneeNaissance() ?? '' ?></td>
                        <td class="nombre">
                            <?php if ($nombreLivres > 0) : ?>
                                <a href="livres.php?auteur=<?= $auteur->getId() ?>"><?= $nombreLivres ?></a>
                            <?php else : ?>
                                0
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="auteur-form.php?id=<?= $auteur->getId() ?>">Modifier</a>
                            <?= boutonSupprimer('auteur-supprimer.php', $auteur->getId(), 'Supprimer ' . $auteur->getNomComplet() . '?') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require 'includes/pied.php'; ?>
