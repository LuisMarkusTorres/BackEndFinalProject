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

    /**
     * Get all driving experiences with JOIN statements to fetch condition names
     * This retrieves the actual names instead of just IDs
     */
    public static function getAll(Database $db): array
    {
        $sql = "
            SELECT 
                de.id,
                de.entry_date,
                de.start_time,
                de.end_time,
                de.distance_km,
                de.weather_id,
                wc.name as weather_name,
                de.traffic_id,
                tl.name as traffic_name,
                de.slipperiness_id,
                rc.name as slipperiness_name,
                de.light_id,
                lc.name as light_name,
                de.maneuvers,
                de.quantities,
                de.created_at,
                de.updated_at
            FROM driving_experiences de
            INNER JOIN weather_conditions wc ON de.weather_id = wc.id
            INNER JOIN traffic_levels tl ON de.traffic_id = tl.id
            INNER JOIN road_conditions rc ON de.slipperiness_id = rc.id
            INNER JOIN light_conditions lc ON de.light_id = lc.id
            ORDER BY de.entry_date DESC, de.start_time DESC
        ";

        return $db->fetchAll($sql);
    }

    /**
     * Get a single driving experience with all condition names using JOINs
     */
    public static function getByIdWithNames(Database $db, int $id): ?array
    {
        $sql = "
            SELECT 
                de.id,
                de.entry_date,
                de.start_time,
                de.end_time,
                de.distance_km,
                de.weather_id,
                wc.name as weather_name,
                de.traffic_id,
                tl.name as traffic_name,
                de.slipperiness_id,
                rc.name as slipperiness_name,
                de.light_id,
                lc.name as light_name,
                de.maneuvers,
                de.quantities
            FROM driving_experiences de
            INNER JOIN weather_conditions wc ON de.weather_id = wc.id
            INNER JOIN traffic_levels tl ON de.traffic_id = tl.id
            INNER JOIN road_conditions rc ON de.slipperiness_id = rc.id
            INNER JOIN light_conditions lc ON de.light_id = lc.id
            WHERE de.id = :id
        ";

        $result = $db->fetchOne($sql, [':id' => $id]);
        return $result ?: null;
    }

    /**
     * Get weather conditions from lookup table
     */
    public static function getWeatherConditions(Database $db): array
    {
        return $db->fetchAll("SELECT id, name, description FROM weather_conditions ORDER BY id");
    }

    /**
     * Get traffic levels from lookup table
     */
    public static function getTrafficLevels(Database $db): array
    {
        return $db->fetchAll("SELECT id, name, description FROM traffic_levels ORDER BY id");
    }

    /**
     * Get road conditions from lookup table
     */
    public static function getRoadConditions(Database $db): array
    {
        return $db->fetchAll("SELECT id, name, description FROM road_conditions ORDER BY id");
    }

    /**
     * Get light conditions from lookup table
     */
    public static function getLightConditions(Database $db): array
    {
        return $db->fetchAll("SELECT id, name, description FROM light_conditions ORDER BY id");
    }
}
