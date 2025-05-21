<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="Модель пользователя системы",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Уникальный идентификатор пользователя",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Имя пользователя",
 *         example="Test User",
 *         minLength=2,
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email пользователя",
 *         example="admin@example.com",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email_verified_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата и время подтверждения email",
 *         example="2024-03-20T12:00:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата и время создания пользователя",
 *         example="2024-03-20T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Дата и время последнего обновления пользователя",
 *         example="2024-03-20T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="news",
 *         type="array",
 *         description="Список новостей пользователя",
 *         @OA\Items(ref="#/components/schemas/News")
 *     ),
 *     @OA\Property(
 *         property="comments",
 *         type="array",
 *         description="Список комментариев пользователя",
 *         @OA\Items(ref="#/components/schemas/Comment")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserCreate",
 *     title="UserCreate",
 *     description="Данные для создания нового пользователя",
 *     required={"name", "email", "password"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Имя пользователя",
 *         example="Test User",
 *         minLength=2,
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email пользователя",
 *         example="admin@example.com",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Пароль пользователя",
 *         example="11211121",
 *         minLength=8,
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         description="Подтверждение пароля",
 *         example="11211121",
 *         minLength=8,
 *         maxLength=255
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserLogin",
 *     title="UserLogin",
 *     description="Данные для входа пользователя",
 *     required={"email", "password"},
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email пользователя",
 *         example="admin@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Пароль пользователя",
 *         example="11211121"
 *     )
 * )
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
