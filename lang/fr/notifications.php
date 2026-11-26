<?php

return [
    'mail' => [
        'greeting' => 'Bonjour :name,',
        'salutation' => 'L’équipe SkillBridge Guinée',
    ],

    'password_reset' => [
        'subject' => 'Réinitialisation de votre mot de passe',
        'greeting' => 'Bonjour :name,',
        'intro' => 'Vous recevez cet e-mail car une réinitialisation de mot de passe a été demandée pour votre compte SkillBridge Guinée.',
        'action' => 'Choisir un nouveau mot de passe',
        'expiry' => 'Ce lien expirera dans :count minutes.',
        'ignore' => 'Si vous n’êtes pas à l’origine de cette demande, aucune action n’est nécessaire.',
        'salutation' => 'L’équipe SkillBridge Guinée',
    ],

    'booking_request' => [
        'title' => 'Nouvelle demande de réservation',
        'message' => ':customer vous a envoyé une demande de :trade pour le :date.',
        'description' => 'Description : :description',
        'action' => 'Voir la demande',
    ],

    'booking' => [
        'accepted' => [
            'title' => 'Votre demande a été acceptée',
            'message' => ':worker a accepté votre demande de :trade. Contactez-le pour convenir des détails.',
        ],
        'declined' => [
            'title' => 'Votre demande a été déclinée',
            'message' => ':worker n’est pas disponible pour votre demande de :trade. Vous pouvez rechercher un autre professionnel.',
        ],
        'completed' => [
            'title' => 'Travail terminé',
            'message' => ':worker a marqué votre mission de :trade comme terminée. Laissez un avis pour aider les autres clients.',
        ],
        'updated' => [
            'title' => 'Votre réservation a été mise à jour',
            'message' => 'Le statut de votre demande de :trade a changé.',
        ],
        'action' => 'Voir ma réservation',
    ],

    'booking_cancelled' => [
        'title' => 'Demande annulée par le client',
        'message' => ':customer a annulé sa demande de :trade prévue le :date.',
        'action' => 'Voir mon tableau de bord',
    ],

    'review_received' => [
        'title' => 'Nouvel avis reçu',
        'message' => ':customer vous a attribué :rating/5. « :comment »',
        'no_comment' => 'Sans commentaire écrit.',
        'action' => 'Voir mon profil',
    ],
];
