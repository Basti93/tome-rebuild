<?php

namespace App\GraphQL\Subscriptions;

use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\Subscriber;

final class TrainingUpdated extends GraphQLSubscription
{
    /**
     * Check if subscriber is allowed to listen to the subscription.
     *
     * @param  \Nuwave\Lighthouse\Subscriptions\Subscriber  $subscriber
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorize(Subscriber $subscriber, Request $request): bool
    {
        $user = $subscriber->context->user;
        //return $user->can('viewAny', Training::class);
        return true;
    }

    /**
     * Filter which subscribers should receive the subscription.
     *
     * @param  \Nuwave\Lighthouse\Subscriptions\Subscriber  $subscriber
     * @param  mixed  $root
     * @return bool
     */
    public function filter(Subscriber $subscriber, $root): bool
    {
        $user = $subscriber->context->user;
        // Don't broadcast the subscription to the same
        // person who updated it.
        //return $root->updated_by !== $user->id;
        return true;
    }

}
