<?php

class DrivingExperience
{
    public $id;
    public $entryDate;
    public $startTime;
    public $endTime;
    public $distanceKm;
    public $weatherId;
    public $trafficId;
    public $slipperinessId;
    public $lightId;

    public $maneuvers = [];   
    public $quantities = [];  

    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->entryDate)) {
            $errors[] = "Date is required";
        }

        if (empty($this->startTime)) {
            $errors[] = "Start time is required";
        }

        if (empty($this->endTime)) {
            $errors[] = "End time is required";
        }

        if ($this->endTime <= $this->startTime) {
            $errors[] = "End time must be after start time";
        }

        if ($this->distanceKm <= 0) {
            $errors[] = "Distance must be greater than 0";
        }

        return $errors;
    }

    private function normalizeManeuvers(): void
{
    $names = [];
    $quantities = [];

    foreach ($this->maneuvers as $m) {
        if (!empty($m['name']) && (int)$m['quantity'] > 0) {
            $names[] = $m['name'];
            $quantities[] = (int)$m['quantity'];
        }
    }

    $this->maneuvers  = $names;
    $this->quantities = $quantities;
}


    public function save(): array
    {
        $this->normalizeManeuvers();
        $errors = $this->validate();
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $sql = "
            INSERT INTO driving_experiences
            (entry_date, start_time, end_time, distance_km,
             weather_id, traffic_id, slipperiness_id, light_id,
             maneuvers, quantities)
            VALUES
            (:date, :start, :end, :distance,
             :weather, :traffic, :slipperiness, :light,
             :maneuvers, :quantities)
        ";

        $this->db->query($sql, [
            ':date'          => $this->entryDate,
            ':start'         => $this->startTime,
            ':end'           => $this->endTime,
            ':distance'      => $this->distanceKm,
            ':weather'       => $this->weatherId,
            ':traffic'       => $this->trafficId,
            ':slipperiness'  => $this->slipperinessId,
            ':light'         => $this->lightId,
            ':maneuvers'     => $this->maneuvers ? implode(',', $this->maneuvers) : null,
            ':quantities'    => $this->quantities ? implode(',', $this->quantities) : null,
        ]);

        $this->id = $this->db->lastInsertId();

        return ['success' => true, 'id' => $this->id];
    }

    public function update(): array
    {
        $this->normalizeManeuvers();
        $errors = $this->validate();
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $sql = "
            UPDATE driving_experiences
            SET entry_date = :date,
                start_time = :start,
                end_time = :end,
                distance_km = :distance,
                weather_id = :weather,
                traffic_id = :traffic,
                slipperiness_id = :slipperiness,
                light_id = :light,
                maneuvers = :maneuvers,
                quantities = :quantities
            WHERE id = :id
        ";

        $this->db->query($sql, [
            ':date'          => $this->entryDate,
            ':start'         => $this->startTime,
            ':end'           => $this->endTime,
            ':distance'      => $this->distanceKm,
            ':weather'       => $this->weatherId,
            ':traffic'       => $this->trafficId,
            ':slipperiness'  => $this->slipperinessId,
            ':light'         => $this->lightId,
            ':maneuvers'     => $this->maneuvers ? implode(',', $this->maneuvers) : null,
            ':quantities'    => $this->quantities ? implode(',', $this->quantities) : null,
            ':id'            => $this->id
        ]);

        return ['success' => true];
    }

    public function delete(): array
    {
        $this->db->query(
            "DELETE FROM driving_experiences WHERE id = :id",
            [':id' => $this->id]
        );

        return ['success' => true];
    }

    public function loadById(int $id): bool
    {
        $sql = "SELECT * FROM driving_experiences WHERE id = :id";
        $data = $this->db->fetchOne($sql, [':id' => $id]);

        if (!$data) {
            return false;
        }

        $this->id              = $data['id'];
        $this->entryDate       = $data['entry_date'];
        $this->startTime       = $data['start_time'];
        $this->endTime         = $data['end_time'];
        $this->distanceKm      = $data['distance_km'];
        $this->weatherId       = $data['weather_id'];
        $this->trafficId       = $data['traffic_id'];
        $this->slipperinessId  = $data['slipperiness_id'];
        $this->lightId         = $data['light_id'];

        $this->maneuvers  = $data['maneuvers']
            ? explode(',', $data['maneuvers'])
            : [];

        $this->quantities = $data['quantities']
            ? array_map('intval', explode(',', $data['quantities']))
            : [];

        return true;
    }

    public static function getAll(Database $db): array
    {
        $sql = "
            SELECT *
            FROM driving_experiences
            ORDER BY entry_date DESC, start_time DESC
        ";

        return $db->fetchAll($sql);
    }
}
