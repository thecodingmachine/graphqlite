<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Preferences;

class PreferencesController
{
    /**
     * @Mutation()
     * @param Preferences $preferences
     *
     * @return Preferences
     */
    public function updatePreferences(Preferences $preferences): Preferences
    {
        return $preferences;
    }
}
