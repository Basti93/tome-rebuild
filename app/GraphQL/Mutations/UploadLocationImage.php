<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Location;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadLocationImage
{

    public function __invoke($_, array $args)
    {
        $location = Location::findOrFail($args['id']);

        $file = $args['file'];
        $oldImage = $location->image;
        $newImage = Storage::disk('public')->putFile('uploads/locations/', $file);
        Image::make(Storage::disk('public')->path($newImage))
        ->resize(null, 600, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })
        ->orientate()
        ->save(Storage::disk('public')->path($newImage));
        $location->image = $newImage;
        $location->save();

        if ($oldImage != null && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }
        return $location;
    }

}
