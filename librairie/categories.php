<?php
/**
 * Liste des catégories, avec le nombre de livres de chacune.
 */
require_once 'includes/init.php';

$categories = Categorie::trouverTous();

$titrePage = 'Catégories';
$pageActive = 'categories.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1>Catégories</h1>
    <a class="bouton" href="categorie-form.php">Ajouter une catégorie</a>
</div>

<?php if (empty($categories)) : ?>
    <div class="vide">
        <p>Aucune catégorie pour l'instant.</p>
        <a class="bouton" href="categorie-form.php">Ajouter une catégorie</a>
    </div>
<?php else : ?>
    <div class="tableau-conteneur">
        <table class="tableau">
            <thead>
                <tr>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Description</th>
                    <th scope="col" class="nombre">Livres</th>
                    <th scope="col"><span class="visuellement-cache">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $categorie) : ?>
                    <?php $nombreLivres = $categorie->compterLivres(); ?>
                    <tr>
                        <td><strong><?= e($categorie->getNom()) ?></strong></td>
                        <td><?= e($categorie->getDescription() ?? '') ?></td>
                        <td class="nombre">
                            <?php if ($nombreLivres > 0) : ?>
                                <a href="livres.php?categorie=<?= $categorie->getId() ?>"><?= $nombreLivres ?></a>
                            <?php else : ?>
                                0
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="categorie-form.php?id=<?= $categorie->getId() ?>">Modifier</a>
                            <?= boutonSupprimer('categorie-supprimer.php', $categorie->getId(), 'Supprimer la catégorie ' . $categorie->getNom() . '? Les livres resteront au catalogue.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require 'includes/pied.php'; ?>
