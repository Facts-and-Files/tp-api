
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EDM JSON-LD Field Mappings
    |--------------------------------------------------------------------------
    |
    | This configuration defines how to extract specific fields from JSON-LD data.
    | Each field maps to one or more JSON-LD paths, with optional @type filtering
    | and a separator for multiple values.
    |
    | Structure:
    |
    | 'fieldName' => [
    |     'paths' => [
    |         [
    |             'path' => 'dot.notation.path', // JSON-LD property path (nested keys supported)
    |             'type' => 'OptionalType',      // Optional: only consider items with this @type
    |         ],
    |         // You can list multiple paths for the same field
    |     ],
    |     'separator' => ' | ',                  // Optional: string to join multiple findings (default '|')
    | ],
    |
    | How it works:
    | 1. Multiple paths: tried in order; first match is used.
    | 2. Type filtering: if 'type' is provided, only items with matching @type are used.
    | 3. Separator: joins multiple values into a single string; defaults to '|'.
    | 4. Internal references (@id): automatically resolved via NodeIndexer.
    | 5. Normalization: handled by LiteralResolver and LiteralHelper; deduplicates values
    |    and appends language tags if needed (e.g., "Title [en] | Titel [de]").
    |
    | Example:
    |
    | 'manifestUrl' => [
    |     'paths' => [
    |         ['path' => 'iiif_url', 'type' => 'edm:Manifest'],
    |         ['path' => 'dcterms:isReferencedBy.@id', 'type' => 'edm:WebResource'],
    |     ],
    |     'separator' => ' | ',
    | ],
    |
    | 'externalRecordId' => [
    |     'paths' => [
    |         ['path' => 'externalId', 'type' => null],
    |     ],
    |     'separator' => ', ',
    | ],
    |
    */
    'mappings' => [
        'manifestUrl' => [
            'paths' => [
                ['path' => 'iiif_url'],
                ['path' => 'dcterms:isReferencedBy.@id', 'type' => 'edm:WebResource'],
            ],
        ],
        'externalRecordId' => [
            'paths' => [
                ['path' => '@id', 'type' => 'edm:ProvidedCHO'],
            ],
        ],
        'storyTitle' => [
            'separator' => ' | ',
            'paths' => [
                ['path' => 'dc:title'],
            ],
        ],

        // 'dc:title' => [
        //     'type' => ['literal'], // literal, ref
        //     'lookup_path' => 'default', // default, custom
        //     'custom_path' => [],
        //     'property' => 'dc:title',
        // ],
        // 'dc:description'   => [
        //     'type' => ['literal', 'ref'],
        // ],
        // 'dc:creator'       => [
        //     'type' => ['literal'],
        // ],
        // 'dc:source'        => [
        //     'type' => ['literal'],
        // ],
        // 'edm:country'      => [
        //     'type' => ['literal'],
        // ],
        // 'edm:dataProvider' => [
        //     'type' => ['literal'],
        // ],
        // 'edm:provider'     => [
        //     'type' => ['literal'],
        // ],
        // 'edm:rights'       => [
        //     'type' => ['literal'],
        // ],
        // 'edm:begin'        => [
        //     'type' => ['literal'],
        // ],
        // 'edm:end'          => [
        //     'type' => ['literal'],
        // ],
        // 'dc:contributor'   => [
        //     'type' => ['literal'],
        // ],
        // 'edm:year'         => [
        //     'type' => ['literal'],
        // ],
        // 'dc:publisher'     => [
        //     'type' => ['literal'],
        // ],
        // 'dc:coverage'      => [
        //     'type' => ['literal'],
        // ],
        // 'dc:date'          => [
        //     'type' => ['literal'],
        // ],
        // 'dc:type'          => [
        //     'type' => ['literal'],
        // ],
        // 'dc:relation'      => [
        //     'type' => ['literal'],
        // ],
        // 'dcterms:medium'   => [
        //     'type' => ['literal'],
        // ],
        // 'edm:datasetName'  => [
        //     'type' => ['literal'],
        // ],
        // 'edm:isShownAt'    => [
        //     'type' => ['literal'],
        // ],
        // 'dc:rights'        => [
        //     'type' => ['literal'],
        // ],
        // 'dc:identifier'    => [
        //     'type' => ['literal'],
        // ],
        // 'dc:language'      => [
        //     'type' => ['literal'],
        // ],
        // 'edm:language'     => [
        //     'type' => ['literal'],
        // ],
        // 'edm:agent'        => [
        //     'type' => ['literal'],
        // ],
        // 'dcterms:provenance'=> [
        //     'type' => ['literal'],
        // ],
        // 'dcterms:created'  => [
        //     'type' => ['literal'],
        // ],
    ],
];
