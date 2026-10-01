<?php
/**
 * Supprime un livre. Ses liens dans livre_categorie sont effacés automatiquement.
 */
require_once 'includes/init.php';

if (!estSoumissionValide()) {
    rediriger('livres.php');
}

$livre = Livre::trouverParId((int) ($_POST['id'] ?? 0));

if ($livre === null) {
    ajouterMessage('erreur', 'Ce livre n\'existe pas ou a déjà été supprimé.');
} else {
    $titre = $livre->getTitre();
    $livre->supprimer();
    ajouterMessage('succes', '« ' . $titre . ' » a été retiré du catalogue.');
}

rediriger('livres.php');
