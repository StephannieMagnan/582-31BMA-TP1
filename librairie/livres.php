<?php
/**
 * Catalogue : liste des livres avec recherche et filtres par auteur et par catégorie.
 */
require_once 'includes/init.php';

// Filtres reçus dans l'adresse (formulaire en GET)
$filtres = [
    'texte'        => trim($_GET['texte'] ?? ''),
    'auteur_id'    => (int) ($_GET['auteur'] ?? 0),
    'categorie_id' => (int) ($_GET['categorie'] ?? 0),
];
$filtreActif = $filtres['texte'] !== '' || $filtres['auteur_id'] > 0 || $filtres['categorie_id'] > 0;

$livres = Livre::rechercher($filtres);
$auteurs = Auteur::trouverTous();
$categories = Categorie::trouverTous();

$titrePage = 'Catalogue';
$pageActive = 'livres.php';
require 'includes/entete.php';
?>

<div class="en-tete-page">
    <h1>Catalogue</h1>
    <p class="texte-discret"><?= count($livres) ?> livre<?= count($livres) > 1 ? 's' : '' ?> affiché<?= count($livres) > 1 ? 's' : '' ?></p>
</div>

<form class="filtres" method="get" action="livres.php" role="search">
    <div class="champ">
        <label for="texte">Titre ou auteur</label>
        <input type="search" id="texte" name="texte" value="<?= e($filtres['texte']) ?>">
    </div>

    <div class="champ">
        <label for="auteur">Auteur</label>
        <select id="auteur" name="auteur">
            <option value="0">Tous les auteurs</option>
            <?php foreach ($auteurs as $auteur) : ?>
                <option value="<?= $auteur->getId() ?>" <?= $auteur->getId() === $filtres['auteur_id'] ? 'selected' : '' ?>>
                    <?= e($auteur->getNomComplet()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="champ">
        <label for="categorie">Catégorie</label>
        <select id="categorie" name="categorie">
            <option value="0">Toutes les catégories</option>
            <?php foreach ($categories as $categorie) : ?>
                <option value="<?= $categorie->getId() ?>" <?= $categorie->getId() === $filtres['categorie_id'] ? 'selected' : '' ?>>
                    <?= e($categorie->getNom()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filtres__actions">
        <button type="submit" class="bouton">Filtrer</button>
        <?php if ($filtreActif) : ?>
            <a href="livres.php">Effacer les filtres</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($livres)) : ?>
    <div class="vide">
        <p><?= $filtreActif ? 'Aucun livre ne correspond à ces filtres.' : 'Le catalogue est vide.' ?></p>
        <a class="bouton" href="livre-form.php">Ajouter un livre</a>
    </div>
<?php else : ?>
    <ul class="rayon">
        <?php foreach ($livres as $livre) : ?>
            <li class="rayon__livre">
                <a href="livre-fiche.php?id=<?= $livre->getId() ?>" tabindex="-1">
                    <?= couvertureHtml($livre) ?>
                </a>
                <div class="rayon__infos">
                    <a class="rayon__titre" href="livre-fiche.php?id=<?= $livre->getId() ?>"><?= e($livre->getTitre()) ?></a>
                    <span class="texte-discret"><?= e($livre->getAuteurNom()) ?></span>
                    <span class="rayon__prix">
                        <?= formatPrix($livre->getPrix()) ?>
                        <?php if ($livre->getQuantiteStock() === 0) : ?>
                            <span class="pastille pastille--rupture">Épuisé</span>
                        <?php elseif ($livre->estEnStockFaible()) : ?>
                            <span class="pastille pastille--faible">Stock faible</span>
                        <?php endif; ?>
                    </span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require 'includes/pied.php'; ?>
