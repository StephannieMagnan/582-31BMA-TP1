<?php
/**
 * Page affichée quand une erreur inattendue survient.
 */
require __DIR__ . '/entete.php';
?>
<section class="vide">
    <h1>La page n'a pas pu être chargée</h1>
    <p>Vérifiez la connexion à la base de données dans <code>config/config.php</code>, puis réessayez.</p>

    <?php if ($detailErreur !== null) : ?>
        <pre class="detail-erreur"><?= e($detailErreur) ?></pre>
    <?php endif; ?>

    <a class="bouton" href="index.php">Retour à l'accueil</a>
</section>
<?php require __DIR__ . '/pied.php'; ?>
