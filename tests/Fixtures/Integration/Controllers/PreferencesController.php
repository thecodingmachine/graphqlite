<?php

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Models\Preferences;

class PreferencesController
{
    /**
     * @param Preferences $preferences
     *
     * @return Preferences
     */
    #[Mutation]
    public function updatePreferences(Preferences $preferences): Preferences
    {
        return $preferences;
    }
}
