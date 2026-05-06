<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateAuthToken extends Command
{
    protected $signature = 'auth:generate-token
        {--description= : A human-readable label for this token}
        {--permissions=* : Abilities e.g. --permissions=read --permissions=items:write}
        {--ro : Shorthand for read-only}
        {--rw : Shorthand for full access}';

    protected $description = 'Generates an api client auth token for api usage';

    public function handle(): int
    {
        $token = Str::random(60);

        $permissions = $this->resolvePermissions();

        DB::table('api_clients')->insert([
            'api_token'   => hash('sha256', $token),
            'permissions' => json_encode($permissions),
            'description' => $this->option('description'),
        ]);

        $this->info("Token : {$token}");
        $this->info("Permissions: " . implode(', ', $permissions));
        $this->info("Description: " . ($this->option('description') ?? '—'));

        return 0;
    }

    private function resolvePermissions(): array
    {
        if ($this->option('ro')) {
            return ['read'];
        }

        if ($this->option('rw')) {
            return ['*'];
        }

        $permissions = $this->option('permissions');

        return empty($permissions) ? ['*'] : $permissions;
    }
}
