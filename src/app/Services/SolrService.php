<?php

namespace App\Services;

use Solarium\Client;

class SolrService
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function deleteById($id)
    {
        $update = $this->client->createUpdate();
        $update->addDeleteById($id);
        $update->addCommit();
        $this->client->update($update);
    }

    public function deleteByQuery($query)
    {
        $update = $this->client->createUpdate();
        $update->addDeleteQuery($query);
        $update->addCommit();
        $this->client->update($update);
    }
}
