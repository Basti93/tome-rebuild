<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use DanielDeWit\LighthouseSanctum\Traits\CreatesUserProvider;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadProfileImage
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
        $file = $args['file'];
        $oldImage = $user->image;
        $newImage = Storage::disk('public')->putFile('uploads/' . $user->id, $file);
        Image::make(Storage::disk('public')->path($newImage))
        ->resize(null, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })
        ->orientate()
        ->save(Storage::disk('public')->path($newImage));
        $user->image = $newImage;
        $user->save();

        if ($oldImage != null && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }
        return $user->image;
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
