<?php
/**
 * En-tête commun à toutes les pages.
 */

$liensMenu = [
    'index.php'      => 'Accueil',
    'livres.php'     => 'Catalogue',
    'auteurs.php'    => 'Auteurs',
    'editeurs.php'   => 'Éditeurs',
    'categories.php' => 'Catégories',
];
?>
<!DOCTYPE html>
<html lang="fr-CA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titrePage) ?> | <?= e(NOM_SITE) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Karla:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="entete">
        <div class="entete__interieur">
            <a class="logo" href="index.php">
                <svg class="logo__sigle" viewBox="0 0 32 32" aria-hidden="true">
                    <circle cx="16" cy="16" r="14" />
                    <path d="M3.9 9 H28.1 L16 30 Z" />
                    <circle class="logo__oeil" cx="16" cy="15" r="2.6" />
                </svg>
                <?= e(NOM_SITE) ?>
            </a>

            <nav class="menu" aria-label="Navigation principale">
                <ul>
                    <?php foreach ($liensMenu as $fichier => $libelle) : ?>
                        <li>
                            <a href="<?= e($fichier) ?>" <?= $fichier === $pageActive ? 'aria-current="page"' : '' ?>>
                                <?= e($libelle) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <a class="bouton bouton--clair" href="livre-form.php">Ajouter un livre</a>
        </div>
    </header>

    <main class="contenu">
        <?php afficherMessages(); ?>
