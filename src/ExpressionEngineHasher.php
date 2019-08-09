<?php

namespace DrawMyAttention\ExpAuth;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Exception;

class ExpressionEngineHasher implements HasherContract{

    private $hash_algorithms = array(
        128     => 'sha512',
        64      => 'sha256',
        40      => 'sha1',
        32      => 'md5'
    );

    /**
     * Default crypt cost factor.
     *
     * @var int
     */
    protected $rounds = 10;

    /**
     * The size of the hash created by the bcrypt algorithm.
     *
     * This is used to detect whether a user's password was created by Expression Engine (doesn't support
     * bcrypt, or from the newer system).
     *
     * @var int
     */
    protected $bcrypt_hash_size = 60;
    
    /**
     * Get information about the given hashed value.
     *
     * @param  string $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    /**
     * Hash the given value.
     *
     * @param  string $value
     * @param  array $options
     * @return string
     * @throws Exception
     */
    public function make($value, array $options = array())
    {
        $this->guardAgainstMd5Collisions($value);

        // If no hash algorithm is explicitly specified, use bcrypt
        if (!isset($options['byte_size']) || $options['byte_size'] === false)
        {
            return $this->hashUsingBcrypt($value, $options);
        }
        elseif ( ! isset($this->hash_algorithms[$options['byte_size']]))
        {
            // The algorithm that was provided wasn't found in the array of available algorithms
            throw new Exception('No matching hash algorithm.');
        }

        // No salt? (not even blank), we'll regenerate
        if ($options['salt'] === false)
        {
            $options['salt'] = $this->generateSalt($options['byte_size']);
        }
        elseif (strlen($options['salt']) !== $options['byte_size'])
        {
            // A salt with an invalid length was provided. This can happen if
            // old code resets a new value, ignore it.
            $options['salt'] = '';
        }

        return hash($this->hash_algorithms[$options['byte_size']], $options['salt'].$value);

    }

    /**
     * Ensure that a hash doesn't exceed an operatble length.
     *
     * MD5 collisions usually happen above 1024 bits, so
     * we artificially limit their password to reasonable size.
     *
     * @access private
     * @param string $value
     * @throws Exception
     */
    private function guardAgainstMd5Collisions($value)
    {
        if (!$value || strlen($value) > 250)
        {
            throw new Exception("Hash length exceeds operable length.");
        }
    }

    /**
     * Generate a new SALT used for hashing the password.
     *
     * The salt should never be displayed, so any ascii character can be used for higher security.
     *
     * @param $byte_size
     * @return string
     */
    public function generateSalt($byte_size = 128)
    {
        $salt = '';
        for ($i = 0; $i < $byte_size; $i++)
        {
            $salt .= chr(mt_rand(33, 126));
        }
        return $salt;
    }

    /**
     * Hash a password using Bcrypt.
     *
     * @param string $value
     * @param array $options
     * @return string
     * @throws Exception
     */
    private function hashUsingBcrypt($value, $options)
    {
        $cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;

        $hash = password_hash($value, PASSWORD_BCRYPT, array('cost' => $cost));

        if ($hash === false)
        {
            throw new Exception("Bcrypt hashing not supported.");
        }

        return $hash;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string $value
     * @param  string $hashedValue
     * @param  array $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = array())
    {
        // If this is a default Laravel bcrypt hashed password,
        if(strlen($hashedValue) == $this->bcrypt_hash_size) {
            return password_verify($value, $hashedValue);
        }

        // As we are using the new ExpressionEngineUserProvider, the salt and byte_size should always be present. TODO revert to return false.
        if (!(isset($options['salt']) && isset($options['byte_size']))) {
            $options['byte_size'] = strlen($hashedValue);
            $options['salt'] = '';
            //return false;
        }
        // hash the incoming password & test against the original hash
        $hashed = $this->make($value, array('salt' => $options['salt'], 'byte_size' => $options['byte_size']));

        return ($hashed !== FALSE AND $hashedValue == $hashed);
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string $hashedValue
     * @param  array $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = array())
    {
        if(strlen($hashedValue) == $this->bcrypt_hash_size){
            $cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;
            return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, array('cost' => $cost));
        }

        //This is an Expression Engine user, so we have the opportunity to switch their password to using BCrypt
        return true;

    }

    /**
     * Set the default password work factor.
     *
     * @param  int  $rounds
     * @return $this
     */
    public function setRounds($rounds)
    {
        $this->rounds = (int) $rounds;

        return $this;
    }

    public function getRounds()
    {
        return $this->rounds;
    }
}
