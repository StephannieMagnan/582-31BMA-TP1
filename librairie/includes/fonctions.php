<?php

/**
 * Fonctions utilisées par les pages du projet.
 */

/* ------------------------------------------------------------
 *  Sécurité de l'affichage
 * ------------------------------------------------------------ */

/**
 * Échappe un texte avant de l'afficher dans le HTML .
 */
function e(mixed $valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

/* ------------------------------------------------------------
 *  Protection des formulaires
 * ------------------------------------------------------------ */

/**
 * Retourne le jeton secret de la session, en le créant au besoin.
 */
function jetonCsrf(): string
{
    if (empty($_SESSION['jeton_csrf'])) {
        $_SESSION['jeton_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['jeton_csrf'];
}

/**
 * Champ caché à placer dans chaque formulaire POST.
 */
function champCsrf(): string
{
    return '<input type="hidden" name="jeton_csrf" value="' . e(jetonCsrf()) . '">';
}

/**
 * Vérifie que le formulaire reçu provient bien du site.
 */
function jetonCsrfValide(): bool
{
    return hash_equals(jetonCsrf(), (string) ($_POST['jeton_csrf'] ?? ''));
}

/* ------------------------------------------------------------
 *  Navigation et messages
 * ------------------------------------------------------------ */

/**
 * Redirige vers une autre page et arrête le script.
 */
function rediriger(string $adresse): never
{
    header('Location: ' . $adresse);
    exit;
}

/**
 * Garde un message en session pour l'afficher sur la page suivante.
 */
function ajouterMessage(string $type, string $texte): void
{
    $_SESSION['messages'][] = ['type' => $type, 'texte' => $texte];
}

/**
 * Affiche puis efface les messages en attente.
 */
function afficherMessages(): void
{
    foreach ($_SESSION['messages'] ?? [] as $message) {
        echo '<p class="message message--' . e($message['type']) . '" role="status">' . e($message['texte']) . '</p>';
    }

    unset($_SESSION['messages']);
}

/**
 * Indique si la page a été appelée par un formulaire.
 */
function estSoumissionValide(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST' && jetonCsrfValide();
}

/* ------------------------------------------------------------
 *  Formatage
 * ------------------------------------------------------------ */

/**
 * Formate un prix
 */
function formatPrix(float $prix): string
{
    return number_format($prix, 2, ',', "\u{00A0}") . "\u{00A0}$";
}

/* ------------------------------------------------------------
 *  Morceaux d'interface réutilisés
 * ------------------------------------------------------------ */

/**
 * Affiche le message d'erreur d'un champ de formulaire, s'il y en a un.
 */
function erreurChamp(array $erreurs, string $champ): string
{
    if (!isset($erreurs[$champ])) {
        return '';
    }

    return '<p class="erreur-champ" id="erreur-' . e($champ) . '">' . e($erreurs[$champ]) . '</p>';
}

/**
 * Attributs d'accessibilité pour un champ en erreur.
 */
function attributsErreur(array $erreurs, string $champ): string
{
    return isset($erreurs[$champ])
        ? ' aria-invalid="true" aria-describedby="erreur-' . e($champ) . '"'
        : '';
}

/**
 * Petit formulaire POST pour supprimer un enregistrement, avec une demande de confirmation avant l'envoi.
 */
function boutonSupprimer(string $action, int $id, string $confirmation): string
{
    return '<form class="form-suppression" method="post" action="' . e($action) . '"'
        . ' onsubmit="return confirm(\'' . e(addslashes($confirmation)) . '\');">'
        . champCsrf()
        . '<input type="hidden" name="id" value="' . $id . '">'
        . '<button type="submit" class="bouton-lien bouton-lien--danger">Supprimer</button>'
        . '</form>';
}

/**
 * Génère la couverture illustrée d'un livre.
 */
function couvertureHtml(Livre $livre, bool $grande = false): string
{
    $classes = 'couverture' . ($grande ? ' couverture--grande' : '');
    $style = '--couleur: ' . e($livre->getCouleurCouverture()) . ';';

    return '<div class="' . $classes . '" style="' . $style . '" aria-hidden="true">'
        . '<span class="couverture__titre">' . e($livre->getTitre()) . '</span>'
        . '<span class="couverture__auteur">' . e($livre->getAuteurNom() ?? '') . '</span>'
        . '</div>';
}
