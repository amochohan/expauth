<?php

namespace DrawMyAttention\ExpAuth;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use App\User;

class ExpressionEngineUserProvider extends EloquentUserProvider{


    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain                  = $credentials['password'];
        $options = array();
        if ($user instanceof App\User) {
            $options['salt']        = $user->salt;
            $options['byte_size']   = strlen($user->getAuthPassword());
        }
        return $this->hasher->check($plain, $user->getAuthPassword(), $options);
    }

}
