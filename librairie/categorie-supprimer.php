<?php
/**
 * Supprime une catégorie. Seuls ses liens dans livre_categorie disparaissent, les livres restent au catalogue.
 */
require_once 'includes/init.php';

if (!estSoumissionValide()) {
    rediriger('categories.php');
}

$categorie = Categorie::trouverParId((int) ($_POST['id'] ?? 0));

if ($categorie === null) {
    ajouterMessage('erreur', 'Cette catégorie n\'existe pas ou a déjà été supprimée.');
} else {
    $nom = $categorie->getNom();
    $categorie->supprimer();
    ajouterMessage('succes', 'La catégorie ' . $nom . ' a été supprimée.');
}

rediriger('categories.php');
