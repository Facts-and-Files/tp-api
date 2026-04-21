<?php

return [

    'filename_prefix' => env('EXPORT_FILENAME_PREFIX', 'transcribathon'),

    'formats' => [
        'alto' => [
            'content_type' => 'application/xml; charset=utf-8',
            'extension'    => 'xml',
        ],
        'csv' => [
            'content_type' => 'text/csv; charset=utf-8',
            'extension'    => 'csv',
        ],
        'mets' => [
            'content_type' => 'application/xml; charset=utf-8',
            'extension'    => 'xml',
        ],
        'pagexml' => [
            'content_type' => 'application/xml; charset=utf-8',
            'extension'    => 'xml',
        ],
        'yml' => [
            'content_type' => 'application/yaml; charset=utf-8',
            'extension'    => 'yml',
        ],
        'zip' => [
            'content_type' => 'application/zip; charset=utf-8',
            'extension'    => 'zip',
        ],
    ],
];
