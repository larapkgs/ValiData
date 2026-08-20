<?php

return [
    'generators' => [
        'data' => [
            // The absolute base directory where files will be generated.
            'base_path' => app_path(),

            // The root PHP namespace for generated validation classes.
            'base_namespace' => 'App\\',

            // The sub-directory relative to base_path where classes are stored.
            'directory' => 'Data',

            // The default class suffix.
            'type' => 'Data',

            // Determine whether the type suffix should be appended to the generated class name (e.g., UserValidation).
            'force_type' => true,

            // Determine whether existing files should be overwritten without throwing an exception or requiring the --force flag.
            'overwrite' => false,
        ],
    ],
];
