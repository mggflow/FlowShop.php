<?php

namespace MGGFLOW\FlowShop\Policies;

use MGGFLOW\FlowShop\Exceptions\AccessDenied;

class Ownership
{
    /**
     * True if user owns something.
     * @param object $something
     * @param object $user
     * @return void
     * @throws AccessDenied
     */
    public static function belongsTo(object $something,object $user){
        if ($user->id != $something->owner_id) {
            throw new AccessDenied();
        }
    }
}