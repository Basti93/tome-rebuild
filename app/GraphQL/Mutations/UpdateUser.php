<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use DanielDeWit\LighthouseSanctum\Traits\CreatesUserProvider;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository as Config;
use Spatie\Permission\Models\Role;

class UpdateUser
{

    use CreatesUserProvider;

    protected AuthManager $authManager;
    protected Config $config;

    public function __construct(
        AuthManager $authManager,
        Config $config,
    ) {
        $this->authManager = $authManager;
        $this->config = $config;
    }

    public function __invoke($_, array $args)
    {
        $userProvider = $this->createUserProvider();
        $user = $userProvider->retrieveById($args['id']);
        if (isset($args['roles'])) {
            $user->syncRoles($args['roles']);
        }
        if (isset($args['groups'])) {
            $user->groups()->sync($args['groups']);
        }
        $user->fill($args)->save();
        return $user;
    }

    protected function getAuthManager(): AuthManager
    {
        return $this->authManager;
    }

    protected function getConfig(): Config
    {
        return $this->config;
    }

}
