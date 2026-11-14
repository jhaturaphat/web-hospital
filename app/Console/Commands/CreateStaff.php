<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateStaff extends Command
{
    protected $signature = 'hospital:create-staff';

    protected $description = 'สร้างบัญชีเจ้าหน้าที่สำหรับจัดการข่าวโรงพยาบาล';

    public function handle(): int
    {
        $data = ['name' => $this->ask('ชื่อเจ้าหน้าที่'), 'email' => $this->ask('อีเมล'), 'password' => $this->secret('รหัสผ่าน (อย่างน้อย 12 ตัวอักษร)'), 'password_confirmation' => $this->secret('ยืนยันรหัสผ่าน')];
        $validator = Validator::make($data, ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'password' => ['required', 'string', 'min:12', 'confirmed']]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $user = new User;
        $user->fill(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
        $user->is_staff = true;
        $user->save();
        $this->info('สร้างบัญชีเจ้าหน้าที่แล้ว เข้าสู่ระบบที่ /staff/login');

        return self::SUCCESS;
    }
}
