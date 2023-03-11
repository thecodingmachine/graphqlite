<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Preferences;

class PreferencesController
{
    /**
     * @Mutation()
     */
    public function updatePreferences(Preferences $preferences): Preferences
    {
        return $preferences;
    }
}
