<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Preferences;

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
