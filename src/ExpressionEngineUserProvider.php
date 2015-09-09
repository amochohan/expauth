<?php

namespace DrawMyAttention\ExpAuth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class ExpressionEngineUserProvider extends EloquentUserProvider {


    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param EEUserContract|\Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(EEUserContract $user, array $credentials)
    {
        $plain = $credentials['password'];
        $options = array(
            'salt' => $user->salt,
            'byte_size' => strlen($user->getAuthPassword())
        );

        return $this->hasher->check($plain, $user->getAuthPassword(), $options);
    }

}
