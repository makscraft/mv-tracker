<?php
return [
    
    'hello' => 'Добро пожаловать в MV tracker!',

    'database' => [
        'Priorities' => [
            [
                'name' => 'Низкий',
                'position' => 1,
                'active' => 1,
                'color' => ''
            ],
            [
                'name' => 'Нормальный',
                'position' => 2,
                'active' => 1,
                'color' => ''
            ],
            [
                'name' => 'Высокий',
                'position' => 3,
                'active' => 1,
                'color' => '#ffded4'
            ],
            [
                'name' => 'Немедленно',
                'position' => 4,
                'active' => 1,
                'color' => '#ffded4'
            ]
        ],

        'Trackers' => [
            [
                'name' => 'Bug',
                'position' => 1,
                'active' => 1,
                'color' => ''
            ],
            [
                'name' => 'Feature',
                'position' => 2,
                'active' => 1,
                'color' => ''
            ],
            [
                'name' => 'Support',
                'position' => 3,
                'active' => 1,
                'color' => ''
            ]                        
        ],
            
        'Statuses' => [
            [
                'name' => 'Новая',
                'active' => 1,
                'closed' =>0,
                'position' => 1,
                'color' => ''
            ],
            [
                'name' => 'В работе',
                'active' => 1,
                'closed' => 0,
                'position' => 2,
                'color' => ''
            ],
            [
                'name' => 'Можно тестировать',
                'active' => 1,
                'closed' => 0,
                'position' => 3,
                'color' => '#f1f9d7'
            ],
            [
                'name' => 'Обратная связь',
                'active' => 1,
                'closed' => 0,
                'position' => 4,
                'color' => ''
            ],
            [
                'name' => 'Закрыта',
                'active' => 1,
                'closed' => 1,
                'position' => 5,
                'color' => ''
            ],
            [
                'name' => 'Отменена',
                'active' => 1,
                'closed' => 1,
                'position' => 6,
                'color' => ''
            ]
        ],

        'Projects' => [
            [
                'name' => 'Тестовый проект',
                'active' => 1,
                'date_created' => I18n :: getCurrentDateTime('SQL'),
                'descriptions' => 'Описание тестового проекта'
            ]
        ],
        
        'Tasks' => [
            [
                'name' => 'Тестовая задача, разработать новый логотип',
                'tracker' => 3,
                'project' => 1,
                'status' => 1,
                'date_created' => I18n :: getCurrentDateTime('SQL'),
                'assigned_to' => 1,
                'priority' => 1,
                'author' => 1,
                'complete' => 30,
                'hours_estimated' => 5,
                'hours_spent' => 3,
                'description' => "h1. Разработать новый логотип\nh2. Подзаголовок\nОбычный текст\n<pre>Пример кода</pre>\n"
            ],
            [
                'name' => 'Тестовая задача, пофиксить баг в АПИ',
                'tracker' => 1,
                'project' => 1,
                'status' => 3,
                'date_created' => I18n :: getCurrentDateTime('SQL'),
                'assigned_to' => 1,
                'priority' => 2,
                'author' => 1,
                'complete' => 100,
                'hours_estimated' => 8,
                'hours_spent' => 7,
                'description' => "h1. Пофиксить багу в АПИ\nh3. Подзаголовок\nОбычный текст\n<pre>Пример кода</pre>"
            ]
        ]
    ]
];