
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | EDM JSON-LD Property Mappings
    |--------------------------------------------------------------------------
    |
    | Each entry maps prefixed JSON-LD property to:
    |  - type: expected JSON-LD type (literal or ref)
    |
    */


    'default_lookup_class_order' => [
        'edm:ProvidedCHO',
        'edm:aggregatedCHO',
        'ore:Aggregation',
        'ore:Proxy',
        'edm:WebResource',
    ],

    'contextual_classes' => [
        'edm:Agent', 'edm:Place', 'edm:TimeSpan', 'skos:Concept', 'cc:License',
    ],

    'mappings' => [
        'dc:title' => [
            'type' => ['literal'], // literal, ref
            'lookup_path' => 'default', // default, custom
            'custom_path' => [],
        ],
        'dc:description'   => [
            'type' => ['literal', 'ref'],
        ],
        'dc:creator'       => [
            'type' => ['literal'],
        ],
        'dc:source'        => [
            'type' => ['literal'],
        ],
        'edm:country'      => [
            'type' => ['literal'],
        ],
        'edm:dataProvider' => [
            'type' => ['literal'],
        ],
        'edm:provider'     => [
            'type' => ['literal'],
        ],
        'edm:rights'       => [
            'type' => ['literal'],
        ],
        'edm:begin'        => [
            'type' => ['literal'],
        ],
        'edm:end'          => [
            'type' => ['literal'],
        ],
        'dc:contributor'   => [
            'type' => ['literal'],
        ],
        'edm:year'         => [
            'type' => ['literal'],
        ],
        'dc:publisher'     => [
            'type' => ['literal'],
        ],
        'dc:coverage'      => [
            'type' => ['literal'],
        ],
        'dc:date'          => [
            'type' => ['literal'],
        ],
        'dc:type'          => [
            'type' => ['literal'],
        ],
        'dc:relation'      => [
            'type' => ['literal'],
        ],
        'dcterms:medium'   => [
            'type' => ['literal'],
        ],
        'edm:datasetName'  => [
            'type' => ['literal'],
        ],
        'edm:isShownAt'    => [
            'type' => ['literal'],
        ],
        'dc:rights'        => [
            'type' => ['literal'],
        ],
        'dc:identifier'    => [
            'type' => ['literal'],
        ],
        'dc:language'      => [
            'type' => ['literal'],
        ],
        'edm:language'     => [
            'type' => ['literal'],
        ],
        'edm:agent'        => [
            'type' => ['literal'],
        ],
        'dcterms:provenance'=> [
            'type' => ['literal'],
        ],
        'dcterms:created'  => [
            'type' => ['literal'],
        ],
    ],
];
