<?php
/**
 * Accueil : vitrine des derniers livres, portrait de l'inventaire et livres à réapprovisionner.
 */
require_once 'includes/init.php';

$livresRecents = Livre::trouverRecents(6);
$livresStockFaible = Livre::trouverStockFaible();

$nombreLivres = Livre::compter();
$nombreAuteurs = Auteur::compter();
$valeurInventaire = Livre::valeurInventaire();

$titrePage = 'Accueil';
$pageActive = 'index.php';
require 'includes/entete.php';
?>

<section class="vitrine">
    <div class="vitrine__texte">
        <p class="vitrine__surtitre">Librairie de l'étrange</p>
        <h1>Nemesis</h1>
        <p>Classiques gothiques, fantastique et horreur d'ici. Gérez le catalogue, les auteurs, les éditeurs et les catégories.</p>
        <p class="vitrine__chiffres">
            <?= $nombreLivres ?> livres de <?= $nombreAuteurs ?> auteurs,
            pour un inventaire de <?= formatPrix($valeurInventaire) ?>.
        </p>
        <a class="bouton" href="livres.php">Parcourir le catalogue</a>
    </div>

    <?php if (empty($livresRecents)) : ?>
        <div class="vide">
            <p>Les rayons sont vides. Ajoutez un premier livre pour les garnir.</p>
            <a class="bouton" href="livre-form.php">Ajouter un livre</a>
        </div>
    <?php else : ?>
        <ul class="tablette" aria-label="Derniers livres ajoutés">
            <?php foreach ($livresRecents as $livre) : ?>
                <li>
                    <a href="livre-fiche.php?id=<?= $livre->getId() ?>" title="<?= e($livre->getTitre()) ?>">
                        <?= couvertureHtml($livre) ?>
                        <span class="visuellement-cache"><?= e($livre->getTitre()) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section class="section">
    <h2>À réapprovisionner</h2>

    <?php if (empty($livresStockFaible)) : ?>
        <p class="texte-discret">Tous les livres ont plus de <?= Livre::SEUIL_STOCK_FAIBLE ?> exemplaires en stock.</p>
    <?php else : ?>
        <div class="tableau-conteneur">
            <table class="tableau">
                <thead>
                    <tr>
                        <th scope="col">Titre</th>
                        <th scope="col">Auteur</th>
                        <th scope="col" class="nombre">En stock</th>
                        <th scope="col"><span class="visuellement-cache">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livresStockFaible as $livre) : ?>
                        <tr>
                            <td><a href="livre-fiche.php?id=<?= $livre->getId() ?>"><?= e($livre->getTitre()) ?></a></td>
                            <td><?= e($livre->getAuteurNom()) ?></td>
                            <td class="nombre">
                                <span class="pastille <?= $livre->getQuantiteStock() === 0 ? 'pastille--rupture' : 'pastille--faible' ?>">
                                    <?= $livre->getQuantiteStock() ?>
                                </span>
                            </td>
                            <td class="actions"><a href="livre-form.php?id=<?= $livre->getId() ?>">Modifier le stock</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require 'includes/pied.php'; ?>
