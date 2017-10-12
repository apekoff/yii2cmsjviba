<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $confirmPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            [
                'username', 'unique',
                'targetClass' => '\common\models\User',
                'message' => Yii::t('app', 'This username has already been taken.')
            ],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email', 'unique',
                'targetClass' => '\common\models\User',
                'message' => Yii::t('app', 'This email address has already been taken.')
            ],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            
            [
                'confirmPassword', 'compare', 'compareAttribute' => 'password',
                'message' => Yii::t('app', 'Password is confirmed incorrectly.')
            ]
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateActivationCode();
        
        if ($user->save()) {
            if ($this->sendActivationEmail($user)) {
                return $user;
            } else {
                $this->addError('email', Yii::t('app', 'Unable to send activation email.'));
            }
        }
        return null;
    }
    
    /**
     * Sends an activation link to the newly registered user
     * @param User $user the user record
     * @return bool
     */
    protected function sendActivationEmail(User $user)
    {
        return Yii::$app->mailer
            ->compose(
                [
                    'html' => 'activateAccount',
                    'text' => 'activateAccount-text'
                ],
                [
                    'user' => $user
                ]
            )
            ->setTo($this->email)
            ->setSubject(
                Yii::t('app', 'You was registered. Please activate your account.')
            )
            ->send();
    }
}
