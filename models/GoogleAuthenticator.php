<?php

namespace app\models;

use Carbon\Carbon;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class GoogleAuthenticator extends Model
{
    CONST  SECRET = 'WIJIRLRNVB5XP6NI';
}