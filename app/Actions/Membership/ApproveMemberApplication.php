<?php

namespace App\Actions\Membership;

use App\Actions\Action;
use App\Mail\WelcomeEmail;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ApproveMemberApplication extends Action
{
    public function handle(Member $member, ?string $email = null): Member
    {
        if ($member->status !== 'pending') {
            throw new \RuntimeException('Only pending members can be approved.');
        }

        $member->update(['status' => 'active']);

        $welcomeEmail = $email ?? $member->email;

        if (!empty($welcomeEmail) && !$member->user_id) {
            $tempPassword = Str::random(12);
            $user = User::create([
                'name' => $member->first_name . ' ' . $member->last_name,
                'email' => $welcomeEmail,
                'password' => Hash::make($tempPassword),
            ]);
            $user->assignRole('member');
            $user->member_id = $member->id;
            $user->save();
            $member->user_id = $user->id;
            $member->save();

            try {
                Mail::to($welcomeEmail)->send(new WelcomeEmail($user, $member, $tempPassword));
            } catch (\Exception $e) {
                Log::error('Welcome email failed for member ' . $member->id . ': ' . $e->getMessage());
            }
        }

        return $member->fresh();
    }
}
