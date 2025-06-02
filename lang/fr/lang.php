<?php

return [
    'plugin' => [
        'name' => 'SEO',
        'description' => 'Gérer les balises SEO pour votre site web.',
    ],
    'meta' => [
        'og:image:alt' => 'Image sociale pour :title sur :app_name',
    ],
    'models' => [
        'link' => [
            'label' => 'Link',
            'label_plural' => 'Balises Link',
            'comment' => 'Gérer les balises "link" qui apparaîtront sur l\'ensemble du site',
            'prompt' => 'Ajout d\'une nouvelle balise "link"',
            'rel' => 'Rel',
            'href' => 'Href',
            'description' => 'Description facultative de cette balise "link"',
        ],
        'meta' => [
            'label' => 'Meta',
            'label_plural' => 'Balises Meta',
            'instructions' => 'Utilisez ces champs pour définir des valeurs personnalisées qui seront utilisées dans les moteurs de recherche et sur les plateformes de médias sociaux.',
            'comment' => 'Gérer les balises "meta" qui apparaîtront sur l\'ensemble du site',
            'prompt' => 'Ajouter une nouvelle balise',
            'name' => 'Nom',
            'value' => 'Valeur',
            'description' => 'Description facultative de cette balise "meta"',
            'fields' => [
                'title' => 'Titre',
                'description' => 'Description',
                'image' => 'Image',
                'nofollow' => 'Indiquer aux moteurs de recherche d\'ignorer les liens dans le contenu',
            ],
        ],
        'settings' => [
            'humans_txt' => 'humans.txt',
            'humans_txt_comment' => 'Contenu du fichier /humans.txt utilisé pour identifier les personnes à l\'origine du site. Voir https://humanstxt.org/',
            'robots_txt' => 'robots.txt',
            'robots_txt_comment' => 'Contenu du fichier de configuration /robots.txt utilisé par les robots d\'indexation.',
            'security_txt' => 'Politique de sécurité',
            'security_txt_comment' => 'Contenu de la politique de sécurité du site, voir https://securitytxt.org/',
        ],
    ],
    'permissions' => [
        'manage_meta' => 'Gérer les balises méta SEO',
    ],
];
