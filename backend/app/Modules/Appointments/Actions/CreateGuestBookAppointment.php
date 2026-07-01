<?php

namespace App\Modules\Appointments\Actions;

use App\Mail\GuestAppointmentAccountCreated;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\User;
use App\Modules\Appointments\DTOs\GuestBookAppointmentDTO;
use App\Modules\Appointments\Services\AppointmentScheduleService;
use App\Modules\Appointments\Support\AppointmentLifecycleDispatcher;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateGuestBookAppointment
{
    public function __construct(
        private readonly AppointmentScheduleService $appointmentScheduleService,
        private readonly AppointmentLifecycleDispatcher $lifecycleDispatcher,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(GuestBookAppointmentDTO $dto): array
    {
        if (User::query()->where('email', $dto->email)->exists()) {
            throw new UnprocessableEntityApiException(
                message: 'An account with this email already exists. Please log in to continue.',
                errorCode: 'EMAIL_ALREADY_REGISTERED',
            );
        }

        if (!$this->appointmentScheduleService->isValidSlotWindow($dto->startTime, $dto->endTime)) {
            throw new UnprocessableEntityApiException(
                message: 'Selected time slot is outside clinic availability.',
                errorCode: 'APPOINTMENT_SLOT_INVALID',
            );
        }

        if (!$this->appointmentScheduleService->isSlotAvailable($dto->startTime, $dto->endTime)) {
            throw new UnprocessableEntityApiException(
                message: 'Selected time slot is not available.',
                errorCode: 'APPOINTMENT_SLOT_UNAVAILABLE',
            );
        }

        $result = DB::transaction(function () use ($dto): array {
            $patientRole = Role::query()
                ->where('name', 'patient')
                ->firstOrFail();

            $temporaryPassword = Str::password(12);

            $user = User::query()->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => $temporaryPassword,
                'role_id' => $patientRole->id,
                'account_status' => 'active',
            ]);

            $appointment = Appointment::query()->create([
                'user_id' => $user->id,
                'appointment_type_id' => $dto->appointmentTypeId,
                'start_time' => $dto->startTime,
                'end_time' => $dto->endTime,
                'status' => 'pending',
                'notes' => $dto->notes,
            ])->load('type');

            $token = $user->createToken('guest-booking')->plainTextToken;

            return [
                'message' => 'Appointment request submitted and patient account created successfully.',
                'data' => [
                    'user' => $user->load('role'),
                    'appointment' => $appointment,
                    'token' => $token,
                    'temporary_password' => $temporaryPassword,
                ],
            ];
        });

        /** @var Appointment $appointment */
        $appointment = $result['data']['appointment'];
        /** @var User $user */
        $user = $result['data']['user'];
        /** @var string $temporaryPassword */
        $temporaryPassword = $result['data']['temporary_password'];

        Mail::to($user->email)->send(
            new GuestAppointmentAccountCreated(
                patientName: $user->name,
                temporaryPassword: $temporaryPassword,
                appointment: $appointment,
            ),
        );

        $this->lifecycleDispatcher->dispatch($appointment, 'created');

        return $result;
    }
}
