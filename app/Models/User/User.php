<?php

namespace App\Models\User;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Str;

/**
 * @mixin Eloquent
 * @mixin Model
 *
 * @property integer                              $id
 * @property string                               $email
 * @property string                               $nickname
 * @property string                               $password
 * @property string                               $name
 * @property Carbon                               $created_at
 * @property Carbon                               $updated_at
 */
class User extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nickname',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setNicknameAttribute($nickname)
    {
        if (!$this->isValidNickname($nickname, 30)) {
            $nickname = $this->getValidNickname($nickname, 30);
        }

        $this->attributes['nickname'] = $nickname;
    }

    protected function isValidNickname($nickname, $maxLength): bool
    {

        if ($this->id && $usersNickname = static::whereId($this->id)->value('nickname')) {
            if ($usersNickname === $nickname)
                return true;
        }

        if (strlen($nickname) >= $maxLength) {
            return false;
        }

        if (static::where('nickname', $nickname)->exists()) {
            return false;
        }

        return true;
    }

    protected function getValidNickname($nickname, $maxLength): string
    {
        $nickname = $this->getUniqueNickname($nickname);

        if (strlen($nickname) >= $maxLength) {
            $baseNickname = substr($nickname, 0, 10);
            $nickname = $this->getUniqueNickname($baseNickname);
        }

        return $nickname;
    }

    protected function getUniqueNickname($nickname): string
    {
        if (static::where('nickname', $nickname)->exists()) {
            $nickname = $nickname . '-' . uniqid();
        }
        return $nickname;
    }
}
