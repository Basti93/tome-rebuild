<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use DanielDeWit\LighthouseSanctum\Traits\CreatesUserProvider;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository as Config;

class UserToAdmin
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
        //$user->is_admin = true;
        $user->save();

        return [
            'status' => 'SUCCESS',
        ];
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
