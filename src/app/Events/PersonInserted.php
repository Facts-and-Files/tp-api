<?php

namespace App\Events;

use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonInserted
{
    use Dispatchable, SerializesModels;

    public $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }
}
