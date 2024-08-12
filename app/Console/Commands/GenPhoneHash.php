<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenPhoneHash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gen-phone-hash {userIdKey} {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Utility to print phone hash';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $userIdKey = $this->argument('userIdKey');
        $phone = $this->argument('phone');

        $userId = User::generateUserIdWithKey($userIdKey, $phone);
        $phoneHash = hash('sha256', "$userId:$phone");
        $this->info("phone hash: $phoneHash");
    }
}
