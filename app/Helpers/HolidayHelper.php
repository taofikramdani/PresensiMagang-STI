<?php

namespace App\Helpers;

use peace2643\IndonesianHolidays\IndonesianHolidays;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HolidayHelper
{
    private static $holidayService = null;
    
    /**
     * Get holiday service instance
     */
    private static function getHolidayService()
    {
        if (self::$holidayService === null) {
            self::$holidayService = new IndonesianHolidays();
        }
        
        return self::$holidayService;
    }
    
    /**
     * Check if a given date is a holiday
     */
    public static function isHoliday($date = null)
    {
        try {
            if (!$date) {
                $date = Carbon::now('Asia/Jakarta');
            }
            
            if (is_string($date)) {
                $date = Carbon::parse($date, 'Asia/Jakarta');
            }
            
            $dateString = $date->format('Y-m-d');
            $holidayService = self::getHolidayService();
            
            // Check if date is a holiday
            $holidayName = $holidayService->isHoliday($dateString);
            
            if ($holidayName !== false) {
                return [
                    'is_holiday' => true,
                    'name' => $holidayName,
                    'type' => 'public',
                    'date' => $dateString
                ];
            }
            
            return [
                'is_holiday' => false,
                'name' => null,
                'type' => null,
                'date' => $dateString
            ];
            
        } catch (\Exception $e) {
            Log::error('Error checking holiday: ' . $e->getMessage());
            
            // Return false jika ada error
            return [
                'is_holiday' => false,
                'name' => null,
                'type' => null,
                'date' => $date ? $date->format('Y-m-d') : Carbon::now('Asia/Jakarta')->format('Y-m-d'),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all holidays for current year
     */
    public static function getHolidaysForYear($year = null)
    {
        try {
            $holidayService = self::getHolidayService();
            $allHolidays = $holidayService->getAllHolidays();
            
            if ($year) {
                // Filter by year
                $yearString = (string) $year;
                $filtered = [];
                foreach ($allHolidays as $date => $name) {
                    if (strpos($date, $yearString) === 0) {
                        $filtered[$date] = $name;
                    }
                }
                return $filtered;
            }
            
            return $allHolidays;
            
        } catch (\Exception $e) {
            Log::error('Error getting holidays for year: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get holidays for current month
     */
    public static function getHolidaysForCurrentMonth()
    {
        try {
            $holidayService = self::getHolidayService();
            return $holidayService->isThisMonth();
            
        } catch (\Exception $e) {
            Log::error('Error getting holidays for current month: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if today is a holiday
     */
    public static function isTodayHoliday()
    {
        try {
            $holidayService = self::getHolidayService();
            $holidayName = $holidayService->isToday();
            
            if ($holidayName !== false) {
                return [
                    'is_holiday' => true,
                    'name' => $holidayName,
                    'type' => 'public',
                    'date' => Carbon::now('Asia/Jakarta')->format('Y-m-d')
                ];
            }
            
            return [
                'is_holiday' => false,
                'name' => null,
                'type' => null,
                'date' => Carbon::now('Asia/Jakarta')->format('Y-m-d')
            ];
            
        } catch (\Exception $e) {
            Log::error('Error checking today holiday: ' . $e->getMessage());
            return [
                'is_holiday' => false,
                'name' => null,
                'type' => null,
                'date' => Carbon::now('Asia/Jakarta')->format('Y-m-d'),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get next holiday from today
     */
    public static function getNextHoliday()
    {
        try {
            $holidayService = self::getHolidayService();
            return $holidayService->getNext();
            
        } catch (\Exception $e) {
            Log::error('Error getting next holiday: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if date is weekend (Saturday or Sunday)
     */
    public static function isWeekend($date = null)
    {
        if (!$date) {
            $date = Carbon::now('Asia/Jakarta');
        }
        
        if (is_string($date)) {
            $date = Carbon::parse($date, 'Asia/Jakarta');
        }
        
        return $date->isWeekend();
    }
    
    /**
     * Check if date is holiday or weekend
     */
    public static function isHolidayOrWeekend($date = null)
    {
        $holidayCheck = self::isHoliday($date);
        $isWeekend = self::isWeekend($date);
        
        return [
            'is_free_day' => $holidayCheck['is_holiday'] || $isWeekend,
            'is_holiday' => $holidayCheck['is_holiday'],
            'is_weekend' => $isWeekend,
            'holiday_name' => $holidayCheck['name'],
            'date' => $holidayCheck['date']
        ];
    }
}