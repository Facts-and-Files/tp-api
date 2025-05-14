<?php

namespace App\Listeners;

use App\Events\StoryDeleted;
use App\Services\SolrService;

class DeleteFromSolrWhenStoryIsDeleted
{
    protected SolrService $solr;

    public function __construct(SolrService $solr)
    {
        $this->solr = $solr;
    }

    public function handle(StoryDeleted $event): void
    {
        $this->solr->deleteByQuery('StoryId:' . $event->storyId);
    }
}
