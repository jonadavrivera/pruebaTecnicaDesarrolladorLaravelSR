<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReservationSeeder extends Seeder
{
    /**
     * Seed data focused on reservation API testing.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $roles = [
                ['name' => 'Administrador', 'slug' => 'admin', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Colaborador', 'slug' => 'employee', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Solo Lectura', 'slug' => 'viewer', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('roles')->upsert($roles, ['slug'], ['name', 'is_active', 'updated_at']);

            $permissions = [
                ['name' => 'Ver reservas', 'slug' => 'reservations.view', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Crear reservas', 'slug' => 'reservations.create', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Editar reservas', 'slug' => 'reservations.update', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Eliminar reservas', 'slug' => 'reservations.delete', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('permissions')->upsert($permissions, ['slug'], ['name', 'is_active', 'updated_at']);

            $roleIds = DB::table('roles')->whereIn('slug', ['admin', 'employee', 'viewer'])->pluck('id', 'slug');
            $permissionIds = DB::table('permissions')
                ->whereIn('slug', ['reservations.view', 'reservations.create', 'reservations.update', 'reservations.delete'])
                ->pluck('id', 'slug');

            $rolePermissions = [
                ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['reservations.view']],
                ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['reservations.create']],
                ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['reservations.update']],
                ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['reservations.delete']],
                ['role_id' => $roleIds['employee'], 'permission_id' => $permissionIds['reservations.view']],
                ['role_id' => $roleIds['employee'], 'permission_id' => $permissionIds['reservations.create']],
                ['role_id' => $roleIds['employee'], 'permission_id' => $permissionIds['reservations.update']],
                ['role_id' => $roleIds['viewer'], 'permission_id' => $permissionIds['reservations.view']],
            ];

            DB::table('permission_roles')->insertOrIgnore($rolePermissions);

            $users = [
                ['name' => 'Usuario Admin', 'email' => 'admin@prueba.com', 'password' => Hash::make('Password123'), 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Usuario Owner', 'email' => 'owner@prueba.com', 'password' => Hash::make('Password123'), 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Usuario Viewer ', 'email' => 'viewer@prueba.com', 'password' => Hash::make('Password123'), 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('users')->upsert($users, ['email'], ['name', 'password', 'updated_at']);

            $userIds = DB::table('users')
                ->whereIn('email', ['admin@prueba.com', 'owner@prueba.com', 'viewer@prueba.com'])
                ->pluck('id', 'email');

            $userRoles = [
                ['user_id' => $userIds['admin@prueba.com'], 'role_id' => $roleIds['admin']],
                ['user_id' => $userIds['owner@prueba.com'], 'role_id' => $roleIds['employee']],
                ['user_id' => $userIds['viewer@prueba.com'], 'role_id' => $roleIds['viewer']],
            ];

            DB::table('user_roles')->insertOrIgnore($userRoles);

            $rooms = [
                ['name' => 'Sala Norte', 'slug' => 'sala-norte', 'location' => 'Piso 1', 'capacity' => 8, 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Sala Sur', 'slug' => 'sala-sur', 'location' => 'Piso 2', 'capacity' => 12, 'status' => 'activo', 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('meeting_rooms')->upsert($rooms, ['slug'], ['name', 'location', 'capacity', 'status', 'updated_at']);

            $roomIds = DB::table('meeting_rooms')->whereIn('slug', ['sala-norte', 'sala-sur'])->pluck('id', 'slug');

            $ownerId = $userIds['owner@prueba.com'];
            $adminId = $userIds['admin@prueba.com'];

            $reservations = [
                [
                    'meeting_room_id' => $roomIds['sala-norte'],
                    'user_id' => $ownerId,
                    'start_time' => $now->copy()->addDay()->setTime(10, 0),
                    'end_time' => $now->copy()->addDay()->setTime(11, 0),
                    'title' => 'Daily del equipo',
                    'description' => 'Revisión rápida de pendientes',
                    'status' => 'confirmed',
                    'version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'meeting_room_id' => $roomIds['sala-sur'],
                    'user_id' => $adminId,
                    'start_time' => $now->copy()->addDays(2)->setTime(15, 0),
                    'end_time' => $now->copy()->addDays(2)->setTime(16, 0),
                    'title' => 'Comité directivo',
                    'description' => 'Seguimiento trimestral',
                    'status' => 'pending',
                    'version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            foreach ($reservations as $reservation) {
                DB::table('reservations')->updateOrInsert(
                    [
                        'meeting_room_id' => $reservation['meeting_room_id'],
                        'user_id' => $reservation['user_id'],
                        'start_time' => $reservation['start_time'],
                        'end_time' => $reservation['end_time'],
                    ],
                    $reservation,
                );
            }
        });
    }
}
