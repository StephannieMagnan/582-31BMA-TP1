<?php
/**
 * Supprime un auteur. Refusé si des livres lui sont liés un livre ne peut pas exister sans auteur.
 */
require_once 'includes/init.php';

if (!estSoumissionValide()) {
    rediriger('auteurs.php');
}

$auteur = Auteur::trouverParId((int) ($_POST['id'] ?? 0));

if ($auteur === null) {
    ajouterMessage('erreur', 'Cet auteur n\'existe pas ou a déjà été supprimé.');
} elseif (!$auteur->peutEtreSupprime()) {
    $nombre = $auteur->compterLivres();
    ajouterMessage('erreur', $auteur->getNomComplet() . ' a encore ' . $nombre . ' livre(s) au catalogue. Supprimez ou réattribuez ces livres d\'abord.');
} else {
    $nom = $auteur->getNomComplet();
    $auteur->supprimer();
    ajouterMessage('succes', $nom . ' a été supprimé.');
}

rediriger('auteurs.php');
