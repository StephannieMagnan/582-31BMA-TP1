<?php
/**
 * Supprime un éditeur. Ses livres restent au catalogue sans éditeur.
 */
require_once 'includes/init.php';

if (!estSoumissionValide()) {
    rediriger('editeurs.php');
}

$editeur = Editeur::trouverParId((int) ($_POST['id'] ?? 0));

if ($editeur === null) {
    ajouterMessage('erreur', 'Cet éditeur n\'existe pas ou a déjà été supprimé.');
} else {
    $nom = $editeur->getNom();
    $editeur->supprimer();
    ajouterMessage('succes', $nom . ' a été supprimé. Ses livres restent au catalogue, sans éditeur.');
}

rediriger('editeurs.php');
