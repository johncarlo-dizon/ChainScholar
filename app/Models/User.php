<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomResetPasswordNotification;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',        // updated from position → role
        'avatar',
        'department',
        'specialization',
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

    public function sendEmailVerificationNotification()
    {
        $this->notify(new  CustomVerifyEmail);
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function titles()
    {
        return $this->hasMany(Title::class, 'owner_id');
    }
    public function researchPapers()
    {
        return $this->hasMany(ResearchPaper::class);
    }

    public function template()
    {
        return $this->hasMany(Template::class);
    }
    public function adviserRequests()
    {
        return $this->hasMany(AdviserRequest::class, 'adviser_id');
    }

    public function adviserNotesGiven()
    {
        return $this->hasMany(\App\Models\AdviserNote::class, 'adviser_id');
    }

    // Notes addressed to this user as a student
    public function adviserNotesReceived()
    {
        return $this->hasMany(\App\Models\AdviserNote::class, 'student_id');
    }
    public function isAdmin() { return $this->role === 'ADMIN'; }
    public function isStudent() { return $this->role === 'STUDENT'; }
    public function isAdviser() { return $this->role === 'ADVISER'; }



 
}
