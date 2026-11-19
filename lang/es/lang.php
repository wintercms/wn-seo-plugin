<?php

return [
    'plugin' => [
        'name' => 'SEO',
        'description' => 'Gestiona las etiquetas SEO de tu sitio web.',
    ],
    'meta' => [
        'og:image:alt' => 'Imagen para redes sociales de :title en :app_name',
    ],
    'models' => [
        'link' => [
            'label' => 'Link',
            'label_plural' => 'Etiquetas link',
            'comment' => 'Gestiona las etiquetas link globales que aparecerán en todo el sitio',
            'prompt' => 'Añadir etiqueta link',
            'rel' => 'Rel',
            'href' => 'Href',
            'description' => 'Descripción opcional de esta etiqueta link',
        ],
        'meta' => [
            'label' => 'Meta',
            'label_plural' => 'Etiquetas meta',
            'instructions' => 'Usa estos campos para definir valores personalizados que se usarán en los buscadores y en las redes sociales',
            'comment' => 'Gestiona las etiquetas meta globales que aparecerán en todo el sitio',
            'prompt' => 'Añadir etiqueta meta',
            'name' => 'Nombre',
            'value' => 'Valor',
            'description' => 'Descripción opcional de esta etiqueta meta',
            'fields' => [
                'title' => 'Título',
                'description' => 'Descripción',
                'image' => 'Imagen',
                'nofollow' => 'Indicar a los buscadores que ignoren los enlaces del contenido',
            ],
        ],
        'settings' => [
            'humans_txt' => 'humans.txt',
            'humans_txt_comment' => 'Contenido del archivo /humans.txt, que sirve para identificar a las personas que hay detrás del sitio. Consulta https://humanstxt.org/',
            'robots_txt' => 'robots.txt',
            'robots_txt_comment' => 'Contenido del archivo de configuración /robots.txt que usan los rastreadores web',
            'security_txt' => 'Política de seguridad',
            'security_txt_comment' => 'Contenido de la política de seguridad del sitio. Consulta https://securitytxt.org/',
            'llms_txt' => 'LLMs.txt',
            'llms_txt_comment' => 'Contenido del archivo /llms.txt, que sirve para ofrecer información sobre el sitio a los sistemas de IA y los LLM. Consulta https://llmstxt.org/',
        ],
    ],
    'permissions' => [
        'manage_meta' => 'Gestionar las etiquetas meta SEO',
    ],
];
