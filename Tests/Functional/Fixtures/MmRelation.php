<?php

return [
    'sys_category_record_mm' => [
        [
            'uid_local' => 1,
            'uid_foreign' => 1,
            'tablenames' => 'pages',
            'fieldname' => 'categories',
            'sorting' => 0,
            'sorting_foreign' => 1,
        ],
        // A single one would work.
        // But a 2nd would have the same internal index, an empty key.
        [
            'uid_local' => 1,
            'uid_foreign' => 2,
            'tablenames' => 'pages',
            'fieldname' => 'categories',
            'sorting' => 0,
            'sorting_foreign' => 2,
        ],
    ],
];
