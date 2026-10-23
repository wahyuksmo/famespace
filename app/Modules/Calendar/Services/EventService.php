<?php

namespace App\Modules\Calendar\Services;

use App\Models\Event;
use App\Modules\Calendar\Repositories\EventRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function __construct(
        protected EventRepository $eventRepo
    ) {}

    public function createEvent(string $familyId, int $userId, array $data): Event
    {
        return DB::transaction(function () use ($familyId, $userId, $data) {
            $data['family_id'] = $familyId;
            $data['created_by_user_id'] = $userId;

            return $this->eventRepo->create($data);
        });
    }

    public function updateEvent(int $id, string $familyId, array $data): Event
    {
        return DB::transaction(function () use ($id, $familyId, $data) {
            $event = $this->eventRepo->findInFamily($id, $familyId);
            if (! $event) {
                throw new Exception('Agenda bersama tidak ditemukan.');
            }

            return $this->eventRepo->update($event, $data);
        });
    }

    public function deleteEvent(int $id, string $familyId): void
    {
        DB::transaction(function () use ($id, $familyId) {
            $event = $this->eventRepo->findInFamily($id, $familyId);
            if (! $event) {
                throw new Exception('Agenda bersama tidak ditemukan.');
            }

            $this->eventRepo->delete($event);
        });
    }
}
