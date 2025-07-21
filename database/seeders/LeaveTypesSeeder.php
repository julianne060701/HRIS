<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            'Absent Leave',
            'Vacation Leave',
            'Vacation Leave Without Pay',
            'Vacation Leave AM',
            'Vacation Leave PM',
            'Sick Leave',
            'Sick Leave Without Pay',
            'Sick Leave AM',
            'Sick Leave PM',
            'Bereavement Leave',
            'Paternity Leave',
            'Maternity Leave',
            'Birthday Leave',
            'Emergency Leave',
            'Emergency Leave Without Pay',
            'Emergency Leave AM',
            'Emergency Leave PM',
            'Solo Parent Leave',
        ];
    
        foreach ($leaveTypes as $type) {
            DB::table('leave_types')->insert([
                'name' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
