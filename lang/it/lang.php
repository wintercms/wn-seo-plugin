<?php

return [
    'plugin' => [
        'name' => 'SEO',
        'description' => 'Gestisci i tag SEO per il tuo sito web.',
    ],
    'meta' => [
        'og:image:alt' => 'Immagine social per :title su :app_name',
    ],
    'models' => [
        'link' => [
            'label' => 'Link',
            'label_plural' => 'Tag link',
            'comment' => 'Gestisci i tag link globali che appariranno nell\'intero sito',
            'prompt' => 'Aggiungi un nuovo tag link',
            'rel' => 'Rel',
            'href' => 'Href',
            'description' => 'Descrizione facoltativa di questo tag link',
        ],
        'meta' => [
            'label' => 'Tag meta',
            'label_plural' => 'Tag meta',
            'instructions' => 'Usa questi campi per impostare valori personalizzati che verranno utilizzati nei motori di ricerca e sulle piattaforme di social media',
            'comment' => 'Gestisci i tag meta globali che appariranno nell\'intero sito',
            'prompt' => 'Aggiungi un nuovo tag meta',
            'name' => 'Nome',
            'value' => 'Valore',
            'description' => 'Descrizione facoltativa di questo tag meta',
            'fields' => [
                'title' => 'Titolo',
                'description' => 'Descrizione',
                'image' => 'Immagine',
                'nofollow' => 'Indica ai motori di ricerca di ignorare i link nei contenuti',
            ],
        ],
        'settings' => [
            'humans_txt' => 'humans.txt',
            'humans_txt_comment' => 'Il contenuto del file /humans.txt utilizzato per identificare le persone dietro al sito. Vedi https://humanstxt.org/',
            'robots_txt' => 'robots.txt',
            'robots_txt_comment' => 'Il contenuto del file di configurazione /robots.txt utilizzato dai crawler web',
            'security_txt' => 'Politica di sicurezza',
            'security_txt_comment' => 'Il contenuto della politica di sicurezza del sito, vedi https://securitytxt.org/',
        ],
    ],
    'permissions' => [
        'manage_meta' => 'Gestisci i tag meta SEO',
    ],
];
