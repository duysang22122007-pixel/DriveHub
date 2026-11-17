<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use Throwable;

final class GarageSchedule
{
    private const BOOKING_TIMEZONE = 'Asia/Ho_Chi_Minh';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function weeklySchedule(): array
    {
        $statement = $this->pdo->query(
            'SELECT weekday, is_open, opens_at, closes_at, slot_interval_minutes,
                    slot_capacity, daily_capacity
             FROM garage_business_hours
             ORDER BY weekday'
        );

        return $statement->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function upcomingClosures(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT closed_date, reason
             FROM garage_closed_dates
             WHERE closed_date >= :today
             ORDER BY closed_date
             LIMIT 100'
        );
        $statement->execute([
            'today' => (new DateTimeImmutable('today', new DateTimeZone(self::BOOKING_TIMEZONE)))->format('Y-m-d'),
        ]);

        return $statement->fetchAll();
    }

    /**
     * @return array{date: string, is_open: bool, message: string, remaining_today: int, slots: list<array{time: string, available: bool, remaining: int, passed: bool}>}
     */
    public function availability(string $date): array
    {
        $parsedDate = $this->parseDate($date);
        if ($parsedDate === null) {
            return $this->unavailableDate($date, 'Ngày không hợp lệ.');
        }

        $timezone = new DateTimeZone(self::BOOKING_TIMEZONE);
        if ($parsedDate < new DateTimeImmutable('today', $timezone)) {
            return $this->unavailableDate($date, 'Không thể chọn ngày trong quá khứ.');
        }

        $weekday = (int) $parsedDate->format('N');
        $schedule = $this->findWeekday($weekday);
        if ($schedule === null || (int) $schedule['is_open'] !== 1) {
            return $this->unavailableDate($date, 'Gara nghỉ vào ngày đã chọn.');
        }

        $closedDate = $this->pdo->prepare(
            'SELECT reason FROM garage_closed_dates WHERE closed_date = :closed_date LIMIT 1'
        );
        $closedDate->execute(['closed_date' => $date]);
        $closureReason = $closedDate->fetchColumn();
        if ($closureReason !== false) {
            $message = is_string($closureReason) && trim($closureReason) !== ''
                ? 'Gara nghỉ: ' . $closureReason
                : 'Gara nghỉ vào ngày đã chọn.';

            return $this->unavailableDate($date, $message);
        }

        $counts = $this->activeBookingCounts($date);
        $dailyCapacity = (int) $schedule['daily_capacity'];
        $remainingToday = max(0, $dailyCapacity - array_sum($counts));
        $slots = [];
        $openingMinutes = $this->minutesFromTime((string) $schedule['opens_at']);
        $closingMinutes = $this->minutesFromTime((string) $schedule['closes_at']);
        $interval = (int) $schedule['slot_interval_minutes'];
        $slotCapacity = (int) $schedule['slot_capacity'];
        $now = new DateTimeImmutable('now', $timezone);

        for ($minutes = $openingMinutes; $minutes <= $closingMinutes; $minutes += $interval) {
            $time = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
            $requested = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $time, $timezone);
            $timeIsFuture = $requested !== false && $requested > $now;
            $remaining = max(0, min($slotCapacity - ($counts[$time] ?? 0), $remainingToday));
            $slots[] = [
                'time' => $time,
                'available' => $timeIsFuture && $remaining > 0,
                'remaining' => $timeIsFuture ? $remaining : 0,
                'passed' => !$timeIsFuture,
            ];
        }

        return [
            'date' => $date,
            'is_open' => true,
            'message' => $remainingToday > 0
                ? 'Chọn một khung giờ còn chỗ.'
                : 'Gara đã nhận đủ lịch trong ngày này.',
            'remaining_today' => $remainingToday,
            'slots' => $slots,
        ];
    }

    /**
     * @param array<mixed> $rows
     */
    public function saveWeeklySchedule(array $rows): void
    {
        $validated = [];
        for ($weekday = 1; $weekday <= 7; $weekday++) {
            $row = $rows[$weekday] ?? null;
            if (!is_array($row)) {
                throw new InvalidArgumentException('Vui lòng gửi đủ cấu hình cho cả 7 ngày trong tuần.');
            }

            $isOpen = ($row['is_open'] ?? null) === '1' || ($row['is_open'] ?? null) === 1;
            $opensAt = is_string($row['opens_at'] ?? null) ? trim($row['opens_at']) : '';
            $closesAt = is_string($row['closes_at'] ?? null) ? trim($row['closes_at']) : '';
            $interval = filter_var($row['slot_interval_minutes'] ?? null, FILTER_VALIDATE_INT);
            $slotCapacity = filter_var($row['slot_capacity'] ?? null, FILTER_VALIDATE_INT);
            $dailyCapacity = filter_var($row['daily_capacity'] ?? null, FILTER_VALIDATE_INT);

            if (
                !$this->isTime($opensAt)
                || !$this->isTime($closesAt)
                || $interval === false
                || $interval < 5
                || $interval > 240
                || $slotCapacity === false
                || $slotCapacity < 1
                || $slotCapacity > 100
                || $dailyCapacity === false
                || $dailyCapacity < 1
                || $dailyCapacity > 1000
            ) {
                throw new InvalidArgumentException('Giờ hoạt động, khoảng cách khung và sức chứa phải là giá trị hợp lệ.');
            }

            $openingMinutes = $this->minutesFromTime($opensAt);
            $closingMinutes = $this->minutesFromTime($closesAt);
            if ($closingMinutes <= $openingMinutes || ($closingMinutes - $openingMinutes) % $interval !== 0) {
                throw new InvalidArgumentException('Giờ đóng cửa phải sau giờ mở cửa và khớp với khoảng cách khung giờ.');
            }

            $validated[$weekday] = [
                'is_open' => $isOpen ? 1 : 0,
                'opens_at' => $opensAt . ':00',
                'closes_at' => $closesAt . ':00',
                'slot_interval_minutes' => $interval,
                'slot_capacity' => $slotCapacity,
                'daily_capacity' => $dailyCapacity,
            ];
        }

        $this->pdo->beginTransaction();
        try {
            $lock = $this->pdo->query('SELECT weekday FROM garage_business_hours ORDER BY weekday FOR UPDATE');
            $lockedWeekdays = array_map('intval', $lock->fetchAll(PDO::FETCH_COLUMN));
            if ($lockedWeekdays !== range(1, 7)) {
                throw new InvalidArgumentException('Cấu hình lịch gara chưa được khởi tạo đầy đủ.');
            }

            $update = $this->pdo->prepare(
                'UPDATE garage_business_hours
                 SET is_open = :is_open,
                     opens_at = :opens_at,
                     closes_at = :closes_at,
                     slot_interval_minutes = :slot_interval_minutes,
                     slot_capacity = :slot_capacity,
                     daily_capacity = :daily_capacity
                 WHERE weekday = :weekday'
            );
            foreach ($validated as $weekday => $schedule) {
                $update->execute($schedule + ['weekday' => $weekday]);
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function addClosure(string $date, string $reason, ?int $adminId): void
    {
        $parsedDate = $this->parseDate($date);
        if ($parsedDate === null) {
            throw new InvalidArgumentException('Ngày nghỉ không hợp lệ.');
        }

        $today = new DateTimeImmutable('today', new DateTimeZone(self::BOOKING_TIMEZONE));
        if ($parsedDate < $today) {
            throw new InvalidArgumentException('Không thể thêm ngày nghỉ trong quá khứ.');
        }
        if (mb_strlen($reason, 'UTF-8') > 255) {
            throw new InvalidArgumentException('Lý do ngày nghỉ không được vượt quá 255 ký tự.');
        }

        $weekday = (int) $parsedDate->format('N');
        $this->pdo->beginTransaction();
        try {
            $this->lockWeekday($weekday);
            $insert = $this->pdo->prepare(
                'INSERT INTO garage_closed_dates (closed_date, reason, created_by)
                 VALUES (:closed_date, :reason, :created_by)
                 ON DUPLICATE KEY UPDATE reason = VALUES(reason), created_by = VALUES(created_by)'
            );
            $insert->execute([
                'closed_date' => $date,
                'reason' => trim($reason) === '' ? null : trim($reason),
                'created_by' => $adminId,
            ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function removeClosure(string $date): void
    {
        $parsedDate = $this->parseDate($date);
        if ($parsedDate === null) {
            throw new InvalidArgumentException('Ngày nghỉ không hợp lệ.');
        }

        $weekday = (int) $parsedDate->format('N');
        $this->pdo->beginTransaction();
        try {
            $this->lockWeekday($weekday);
            $delete = $this->pdo->prepare('DELETE FROM garage_closed_dates WHERE closed_date = :closed_date');
            $delete->execute(['closed_date' => $date]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lockWeekday(int $weekday): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT weekday, is_open, opens_at, closes_at, slot_interval_minutes,
                    slot_capacity, daily_capacity
             FROM garage_business_hours
             WHERE weekday = :weekday
             LIMIT 1 FOR UPDATE'
        );
        $statement->execute(['weekday' => $weekday]);
        $schedule = $statement->fetch();

        return $schedule === false ? null : $schedule;
    }

    public function isClosedDate(string $date): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM garage_closed_dates WHERE closed_date = :closed_date LIMIT 1'
        );
        $statement->execute(['closed_date' => $date]);

        return $statement->fetchColumn() !== false;
    }

    private function findWeekday(int $weekday): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT weekday, is_open, opens_at, closes_at, slot_interval_minutes,
                    slot_capacity, daily_capacity
             FROM garage_business_hours
             WHERE weekday = :weekday
             LIMIT 1'
        );
        $statement->execute(['weekday' => $weekday]);
        $schedule = $statement->fetch();

        return $schedule === false ? null : $schedule;
    }

    /**
     * @return array<string, int>
     */
    private function activeBookingCounts(string $date): array
    {
        $statement = $this->pdo->prepare(
            "SELECT TIME_FORMAT(appointment_time, '%H:%i') AS slot_time, COUNT(*) AS total
             FROM appointments
             WHERE appointment_date = :appointment_date
               AND status IN ('pending', 'confirmed', 'checked_in')
             GROUP BY appointment_time"
        );
        $statement->execute(['appointment_date' => $date]);
        $counts = [];
        foreach ($statement->fetchAll() as $row) {
            $counts[(string) $row['slot_time']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * @return array{date: string, is_open: bool, message: string, remaining_today: int, slots: list<array{time: string, available: bool, remaining: int, passed: bool}>}
     */
    private function unavailableDate(string $date, string $message): array
    {
        return [
            'date' => $date,
            'is_open' => false,
            'message' => $message,
            'remaining_today' => 0,
            'slots' => [],
        ];
    }

    private function parseDate(string $date): ?DateTimeImmutable
    {
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone(self::BOOKING_TIMEZONE));
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $parsed === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $parsed->format('Y-m-d') !== $date
        ) {
            return null;
        }

        return $parsed;
    }

    private function isTime(string $time): bool
    {
        return preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d\z/', $time) === 1;
    }

    private function minutesFromTime(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));

        return $hours * 60 + $minutes;
    }
}
