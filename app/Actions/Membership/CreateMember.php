<?php

namespace App\Actions\Membership;

use App\Actions\Action;
use App\Mail\WelcomeEmail;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use App\Models\User;
use App\Notifications\MemberRegisteredNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateMember extends Action
{
    public function handle(array $data): Member
    {
        $member = Member::create($data);

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV/'.Str::upper(Str::random(2)).'/'.str_pad($member->id, 6, '0', STR_PAD_LEFT),
            'balance' => 0,
        ]);

        ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 0,
            'total_value' => 0,
        ]);

        if (! empty($data['email'])) {
            $tempPassword = Str::random(12);
            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($tempPassword),
            ]);
            $user->assignRole('member');
            $user->member_id = $member->id;
            $user->save();
            $member->user_id = $user->id;
            $member->save();

            try {
                Mail::to($data['email'])->send(new WelcomeEmail($user, $member, $tempPassword));
            } catch (\Exception $e) {
                Log::error('Email/notification failed for member: '.$e->getMessage());
            }
        }

        // Notify admins about new member registration
        try {
            $adminUsers = User::where('id', '!=', auth()->id())->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'secretary']);
            })->get();
            foreach ($adminUsers as $admin) {
                $admin->notify(new MemberRegisteredNotification($member));
            }
        } catch (\Exception $e) {
            Log::error('Email/notification failed for member: '.$e->getMessage());
        }

        return $member;
    }
}
