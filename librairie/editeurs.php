<?php
/**
 * Liste des éditeurs, avec le nombre de livres publiés.
 */
require_once 'includes/init.php';

$editeurs = Editeur::trouverTous();

$titrePage = 'Éditeurs';
$pageActive = 'editeurs.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1>Éditeurs</h1>
    <a class="bouton" href="editeur-form.php">Ajouter un éditeur</a>
</div>

<?php if (empty($editeurs)) : ?>
    <div class="vide">
        <p>Aucun éditeur pour l'instant.</p>
        <a class="bouton" href="editeur-form.php">Ajouter un éditeur</a>
    </div>
<?php else : ?>
    <div class="tableau-conteneur">
        <table class="tableau">
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Ville</th>
                    <th scope="col">Site Web</th>
                    <th scope="col" class="nombre">Livres</th>
                    <th scope="col"><span class="visuellement-cache">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($editeurs as $editeur) : ?>
                    <tr>
                        <td><strong><?= e($editeur->getNom()) ?></strong></td>
                        <td><?= e($editeur->getVille() ?? '') ?></td>
                        <td>
                            <?php if ($editeur->getSiteWeb() !== null) : ?>
                                <a href="<?= e($editeur->getSiteWeb()) ?>" target="_blank" rel="noopener">Visiter le site</a>
                            <?php endif; ?>
                        </td>
                        <td class="nombre"><?= $editeur->compterLivres() ?></td>
                        <td class="actions">
                            <a href="editeur-form.php?id=<?= $editeur->getId() ?>">Modifier</a>
                            <?= boutonSupprimer('editeur-supprimer.php', $editeur->getId(), 'Supprimer ' . $editeur->getNom() . '? Ses livres resteront au catalogue, sans éditeur.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require 'includes/pied.php'; ?>
