<?php
return [
    
    'hello' => 'Welcome to MV tracker!',

    'database' => [
        'Priorities' => [
            [
                'name' => 'Low',
                'position' => 1,
                'active' => 1,
                'color' => ''
            ],
            [
                'name' => 'Normal',
                'position' => 2,
                'active' => 1,
                'color' => ''
            ],
            [
                'name' => 'High',
                'position' => 3,
                'active' => 1,
                'color' => '#ffded4'
            ],
            [
                'name' => 'Immediate',
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
                'name' => 'New',
                'active' => 1,
                'closed' =>0,
                'position' => 1,
                'color' => ''
            ],
            [
                'name' => 'In progress',
                'active' => 1,
                'closed' => 0,
                'position' => 2,
                'color' => ''
            ],
            [
                'name' => 'Completed',
                'active' => 1,
                'closed' => 0,
                'position' => 3,
                'color' => '#f1f9d7'
            ],
            [
                'name' => 'Feedback',
                'active' => 1,
                'closed' => 0,
                'position' => 4,
                'color' => ''
            ],
            [
                'name' => 'Closed',
                'active' => 1,
                'closed' => 1,
                'position' => 5,
                'color' => ''
            ],
            [
                'name' => 'Canceled',
                'active' => 1,
                'closed' => 1,
                'position' => 6,
                'color' => ''
            ]
        ],

        'Projects' => [
            [
                'name' => 'Test project',
                'active' => 1,
                'date_created' => I18n :: getCurrentDateTime('SQL'),
                'descriptions' => 'Description of the test project.'
            ]
        ],
        
        'Tasks' => [
            [
                'name' => 'Test task, design new logo',
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
                'description' => "h1. Design a new logo\nh2. Subheader\nRegular text\n<pre>Code example</pre>"
            ],
            [
                'name' => 'Test task, fix API bug',
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
                'description' => "h1. Fix API bug description\nh3. Subheader\nRegular text\n<pre>Code example</pre>"
            ]
        ]
    ]
];