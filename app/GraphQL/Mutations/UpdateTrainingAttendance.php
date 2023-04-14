<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Training;
use DanielDeWit\LighthouseSanctum\Traits\CreatesUserProvider;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository as Config;
use Spatie\Permission\Models\Role;

class UpdateTrainingAttendance
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
        $user = $this->getAuthManager()->user();
        $training = Training::findOrFail($args['id']);
        $training->users()->syncWithoutDetaching([$user->id => ['attendance' => $args['attend']]]);
        return $training;
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
