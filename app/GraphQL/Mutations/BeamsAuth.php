<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use DanielDeWit\LighthouseSanctum\Exceptions\HasApiTokensException;
use DanielDeWit\LighthouseSanctum\Traits\CreatesUserProvider;
use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Config\Repository as Config;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Pusher\PushNotifications\PushNotifications;

class BeamsAuth
{
    use CreatesUserProvider;

    protected AuthManager $authManager;
    protected Config $config;

    public function __construct(AuthManager $authManager, Config $config)
    {
        $this->authManager = $authManager;
        $this->config      = $config;
    }

    /**
     * @param mixed $_
     * @param array<string, string> $args
     * @return string[]
     * @throws Exception
     */
    public function __invoke($_, array $args): array
    {
        $userProvider = $this->createUserProvider();
        $user = $userProvider->retrieveById($args['id']);
        $beamsClient = new PushNotifications([
            'instanceId' => $this->getConfig()->get('services.pusher.beams_instance_id'),
            'secretKey' => $this->getConfig()->get('services.pusher.beams_secret_key'),
        ]);

        // Abort unless the logged in user id is the same
        // as the channel user id he wants to access to
        abort_unless(auth()->id() == $user->id, 401);

        $beamsToken = $beamsClient->generateToken("App.Models.User.".auth()->id());

        return $beamsToken;
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
