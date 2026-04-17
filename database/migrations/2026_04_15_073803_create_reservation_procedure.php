<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS create_reservation;

            CREATE PROCEDURE create_reservation (
                IN p_user_id BIGINT,
                IN p_room_id BIGINT,
                IN p_start DATETIME,
                IN p_end DATETIME,
                IN p_title VARCHAR(255),
                IN p_description TEXT,
                IN p_status VARCHAR(20)
            )
            BEGIN

                DECLARE conflict_count INT;
                DECLARE new_id BIGINT;

                SELECT COUNT(*) INTO conflict_count
                FROM reservations
                WHERE meeting_room_id = p_room_id
                AND deleted_at IS NULL
                AND (
                    (start_time BETWEEN p_start AND p_end)
                    OR (end_time BETWEEN p_start AND p_end)
                    OR (start_time <= p_start AND end_time >= p_end)
                );

                IF conflict_count > 0 THEN
                    SIGNAL SQLSTATE \'45000\'
                    SET MESSAGE_TEXT = \'Horario ocupado\';
                ELSE

                    INSERT INTO reservations (
                        user_id,
                        meeting_room_id,
                        start_time,
                        end_time,
                        title,
                        description,
                        status,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        p_user_id,
                        p_room_id,
                        p_start,
                        p_end,
                        p_title,
                        p_description,
                        IFNULL(NULLIF(p_status, \'\'), \'confirmed\'),
                        NOW(),
                        NOW()
                    );

                    SET new_id = LAST_INSERT_ID();

                    SELECT new_id AS reservation_id;

                END IF;

            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS create_reservation');
    }
};
